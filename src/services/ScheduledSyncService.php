<?php
	/**
	 * Media Manager
	 *
	 * @package       Media Manager
	 * @author        PBS Digital
	 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
	 */

	namespace pbsdigital\mediamanager\services;

	use Craft;
	use craft\base\Component;
	use craft\helpers\DateTimeHelper;
	use craft\helpers\Db;
	use craft\helpers\StringHelper;
	use pbsdigital\mediamanager\jobs\ShowSync;
	use pbsdigital\mediamanager\events\ScheduledSyncModelEvent;
	use pbsdigital\mediamanager\models\ScheduledSyncModel;
	use pbsdigital\mediamanager\records\ScheduledSyncRecord;
	use yii\db\StaleObjectException;
	use yii\web\NotFoundHttpException;

	class ScheduledSyncService extends Component
	{
		// Public Methods
		// =========================================================================
		const EVENT_BEFORE_SAVE_SCHEDULED_SYNC = 'beforeSaveScheduledSync';
		const EVENT_AFTER_SAVE_SCHEDULED_SYNC = 'afterSaveScheduledSync';

		public function getAllScheduledSyncs(): array
		{
			$currentDateTime = new \DateTime();
			$scheduledSyncRecords = ScheduledSyncRecord::find()
				->where(['>=', 'scheduleDate', $currentDateTime->format('Y-m-d H:i:s')])
				->orderBy(['scheduleDate' => SORT_ASC])
				->all();

			return ScheduledSyncModel::populateModels($scheduledSyncRecords, false);
		}

		public function getPastScheduledSyncs(): array
		{
			$currentDateTime = new \DateTime();
			$scheduledSyncRecords = ScheduledSyncRecord::find()
				->where(['<', 'scheduleDate', $currentDateTime->format('Y-m-d H:i:s')])
				->orderBy(['scheduleDate' => SORT_DESC])
				->all();

			return ScheduledSyncModel::populateModels($scheduledSyncRecords, false);
		}

		public function getPushableSyncs(): array
		{
			$scheduledSyncRecords = ScheduledSyncRecord::find()
				->where(['processed' => false])
				->orderBy(['scheduleDate' => SORT_DESC])
				->all();

			$filteredScheduledSyncRecords = [];

			foreach($scheduledSyncRecords as $scheduledSyncRecord){
				if(DateTimeHelper::isInThePast($scheduledSyncRecord->scheduleDate)){
					$filteredScheduledSyncRecords[] = $scheduledSyncRecord;
				}
			}

			return ScheduledSyncModel::populateModels($filteredScheduledSyncRecords, false);
		}

		/**
		 * Pushes every due scheduled sync to the queue and advances or completes it.
		 *
		 * `occurrencesRun` counts occurrence slots consumed (run or skipped). After running
		 * slot i, slots whose date has already passed (e.g. the site was down) are skipped
		 * but still count toward `recurrenceCount`, so a series ends on its calendar end date.
		 *
		 * Each sync is claimed with one conditional UPDATE; the job is pushed only if this
		 * process won the claim, so concurrent requests can't double-push.
		 *
		 * @return int Number of syncs pushed
		 */
		public function pushDueSyncs(): int
		{
			$pushed = 0;

			foreach ($this->getPushableSyncs() as $sync) {
				$oldRun = (int)$sync->occurrencesRun;
				$values = [];

				if ($sync->isRecurring()) {
					$count = (int)$sync->recurrenceCount;
					$k = $oldRun + 1;
					$next = null;

					while ($k < $count) {
						$next = $sync->getOccurrenceDate($k);
						if (!$next || !DateTimeHelper::isInThePast($next)) {
							break;
						}
						$k++;
					}

					if ($k >= $count || !$next) {
						$values = ['occurrencesRun' => max($count, $oldRun + 1), 'processed' => 1];
					} else {
						$values = [
							'occurrencesRun' => $k,
							'processed' => 0,
							'scheduleDate' => Db::prepareDateForDb($next),
						];
					}
				} else {
					$values = ['occurrencesRun' => $oldRun + 1, 'processed' => 1];
				}

				$claimed = ScheduledSyncRecord::updateAll($values, [
					'id' => $sync->id,
					'processed' => 0,
					'occurrencesRun' => $oldRun,
				]);

				if ($claimed !== 1) {
					Craft::warning('Scheduled sync ' . $sync->id . ' was already claimed by another process; skipping.', __METHOD__);
					continue;
				}

				Craft::$app->getQueue()->push(new ShowSync([
					'showId' => $sync->showId,
					'regenerateThumbnails' => $sync->regenerateThumbnail,
					'scheduledSync' => $sync->id,
				]));

				Craft::info('Pushed scheduled sync ' . $sync->id . ' (occurrence ' . ($oldRun + 1) . ').', __METHOD__);
				$pushed++;
			}

			return $pushed;
		}

		/**
		 * Returns a scheduled sync by its ID.
		 */
		public function getScheduledSyncById(int $scheduledSyncId): ?ScheduledSyncModel
		{
			$scheduledSyncRecord = ScheduledSyncRecord::findOne($scheduledSyncId);

			if (!$scheduledSyncRecord) {
				return null;
			}

			/** @var ScheduledSyncModel */
			return ScheduledSyncModel::populateModel($scheduledSyncRecord, false);
		}

		/**
		 * @param ScheduledSyncModel $scheduledSyncModel
		 * @return bool
		 */
		public function saveScheduledSync(ScheduledSyncModel $scheduledSyncModel): bool
		{

			if($scheduledSyncModel->validate() === false) {
				return false;
			}

			$isNew = $scheduledSyncModel-> id === null;

			if(!$isNew){
				$scheduledSyncRecord = ScheduledSyncRecord::findOne($scheduledSyncModel->id);

				if(!$scheduledSyncRecord){
					return false;
				}

			} else {
				$scheduledSyncRecord = new ScheduledSyncRecord();
			}

			$scheduledSyncRecord->setAttributes($scheduledSyncModel->getAttributes(), false);

			$this->trigger(self::EVENT_BEFORE_SAVE_SCHEDULED_SYNC, new ScheduledSyncModelEvent([
				'scheduledSync' => $scheduledSyncRecord,
				'isNew' => $isNew,
			]));

			if(!$scheduledSyncRecord->save(false)){
				return false;
			}

			$this->trigger(self::EVENT_AFTER_SAVE_SCHEDULED_SYNC, new ScheduledSyncModelEvent([
				'scheduledSync' => $scheduledSyncRecord,
				'isNew' => $isNew,
			]));

			$scheduledSyncModel->id = $scheduledSyncRecord->id;

			return true;
		}

		/**
		 * @throws StaleObjectException
		 */
		public function deleteScheduledSyncById(int $scheduledSyncId): bool
		{
			$scheduledSyncRecord = ScheduledSyncRecord::findOne($scheduledSyncId);

			if(!$scheduledSyncRecord){
				return false;
			}

			return (bool)$scheduledSyncRecord->delete();
		}
	}
