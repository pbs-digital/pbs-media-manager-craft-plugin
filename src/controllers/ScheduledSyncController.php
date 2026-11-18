<?php
	/**
	 * Media Manager
	 *
	 * @package       Media Manager
	 * @author        PBS Digital
	 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
	 */

	namespace pbsdigital\mediamanager\controllers;

	use Craft;
	use craft\helpers\DateTimeHelper;
	use craft\web\Controller;

	use craft\web\Response;
	use pbsdigital\mediamanager\MediaManager;
	use pbsdigital\mediamanager\models\ScheduledSyncModel;
	use yii\web\BadRequestHttpException;
    use function join;

    class ScheduledSyncController extends Controller
	{

		protected array|int|bool $allowAnonymous = [];

		public function actionIndex(): Response
		{
			$scheduledSyncs = MediaManager::getInstance()->scheduledSync->getAllScheduledSyncs();


			/** @var Response */
			return $this->renderTemplate('mediamanager/scheduler/index', [
				'scheduledSyncs' => $scheduledSyncs
			]);
		}
		public function actionEdit(int $scheduledSyncId = null, ScheduledSyncModel $scheduledSync = null): Response
		{
			$isNew = false;
			$title = $scheduledSync->name ?? 'Create a new scheduled sync';
			$shows = MediaManager::getInstance()->show->getShow();

			if (!$scheduledSync && $scheduledSyncId) {
				$scheduledSync = MediaManager::getInstance()->scheduledSync->getScheduledSyncById($scheduledSyncId);
				$title = $scheduledSync->name;
			}

			if(!$scheduledSync) {
				$scheduledSync = new ScheduledSyncModel();
				$isNew = true;
				$title = Craft::t('mediamanager', 'Create a new scheduled sync');
			}

			$tabs = ['scheduledSyncSettings' => [
				'label' => Craft::t('mediamanager', 'Settings'),
				'url' => '#scheduled-sync-settings'
			]];

			$variables = [
				'title' => $title,
				'scheduledSyncId' => $scheduledSyncId,
				'scheduledSync' => $scheduledSync,
				'tabs' => $tabs,
				'isNew' => $isNew,
				'selectedTab' => 'scheduledSyncSettings',
				'shows' => $shows
			];

			/** @var craft\web\Response */
			return $this->renderTemplate('mediamanager/scheduler/_edit', $variables);
		}

		/**
		 * @throws BadRequestHttpException
		 * @throws \Exception
		 */
		public function actionSaveScheduledSync()
		{
			$this->requirePostRequest();
			$request = $this->request;

			$scheduledSyncId = $request->getBodyParam('scheduledSyncId');

			if($scheduledSyncId){
				$scheduledSync = MediaManager::getInstance()->scheduledSync->getScheduledSyncById($scheduledSyncId);
				if(!$scheduledSync) {
					throw new BadRequestHttpException('Invalid scheduled sync ID: ' . $scheduledSyncId);
				}
			} else {
				$scheduledSync = new ScheduledSyncModel();
			}

			$isNewSync = !$scheduledSync->id;
			$previousScheduleDate = $isNewSync ? null : DateTimeHelper::toDateTime($scheduledSync->scheduleDate);

			// The form may be stale (the series can advance after it was rendered), so compare
			// the posted date with the date the form was rendered with, not the current DB value.
			$originalScheduleParam = $request->getBodyParam('originalScheduleDate');
			$originalScheduleDate = $originalScheduleParam ? DateTimeHelper::toDateTime($originalScheduleParam) : $previousScheduleDate;
			$previousInterval = $scheduledSync->recurrenceInterval;
			$previousCount = $scheduledSync->recurrenceCount;

			$scheduledSync->name = $request->getBodyParam('name');
			$scheduledSync->description = $request->getBodyParam('description');
			$scheduledSync->scheduleDate = $request->getBodyParam('scheduleDate');

			$scheduledSync->scheduleDate = DateTimeHelper::toDateTime($scheduledSync->scheduleDate);

			$scheduledSync->recurrenceInterval = $request->getBodyParam('recurrenceInterval') ?: ScheduledSyncModel::RECURRENCE_NONE;
			$recurrenceCount = $request->getBodyParam('recurrenceCount');
			$scheduledSync->recurrenceCount = ($scheduledSync->isRecurring() && $recurrenceCount !== null && $recurrenceCount !== '')
				? (int)$recurrenceCount
				: null;

			$newScheduleDate = $scheduledSync->scheduleDate;
			$scheduleDateChanged = $isNewSync
				|| !$originalScheduleDate
				|| !$newScheduleDate
				|| intdiv($newScheduleDate->getTimestamp(), 60) !== intdiv($originalScheduleDate->getTimestamp(), 60);

			if (!$scheduleDateChanged) {
				// Unchanged by the editor: keep the DB value, the series may have advanced
				$scheduledSync->scheduleDate = $previousScheduleDate;
			}

			if ($scheduleDateChanged
				|| $previousInterval !== $scheduledSync->recurrenceInterval
			) {
				// New series: start over, anchored on the (new) schedule date
				$scheduledSync->occurrencesRun = 0;
				$scheduledSync->recurrenceStartDate = $scheduledSync->scheduleDate;
				$scheduledSync->processed = 0;
			} elseif ($scheduledSync->isRecurring() && (int)$previousCount !== (int)$scheduledSync->recurrenceCount) {
				// Count-only change: keep progress, re-evaluate completion
				$scheduledSync->processed = (int)$scheduledSync->occurrencesRun >= (int)$scheduledSync->recurrenceCount ? 1 : 0;

				if (!$scheduledSync->processed) {
					// Re-opened series: pending row's date must be the date of its current slot
					$scheduledSync->scheduleDate = $scheduledSync->getOccurrenceDate((int)$scheduledSync->occurrencesRun);
				}
			}

			$scheduledSync->showId = $request->getBodyParam('showId');

			$mediaFieldsToSync = $request->getBodyParam('mediaFieldsToSync');
			$showFieldsToSync = $request->getBodyParam('showFieldsToSync');

            if($mediaFieldsToSync && $mediaFieldsToSync !== ""){
                $scheduledSync->mediaFieldsToSync = $mediaFieldsToSync === '*' ? '*' : join(',', $mediaFieldsToSync);
            }

            if ($showFieldsToSync && $showFieldsToSync !== "") {
                $scheduledSync->showFieldsToSync = $showFieldsToSync === '*' ? '*' : join(',', $showFieldsToSync);
            }

			$scheduledSync->regenerateThumbnail = $request->getBodyParam('forceRegenerateThumbnail') ?? false;

			if(!MediaManager::getInstance()->scheduledSync->saveScheduledSync($scheduledSync)) {
				Craft::$app->getSession()->setError(Craft::t('mediamanager', 'Couldn’t save scheduled sync.'));
				Craft::$app->getUrlManager()->setRouteParams([
					'scheduledSync' => $scheduledSync,
					'originalScheduleDate' => $originalScheduleDate?->format('c'),
				]);
				return null;
			}

			Craft::$app->getSession()->setNotice(Craft::t('mediamanager', 'Scheduled sync saved.'));
			return $this->redirectToPostedUrl($scheduledSync);
		}

        public function actionDelete(): Response
        {
            $this->requirePostRequest();
            $request = $this->request;

            $scheduledSyncId = $request->getBodyParam('scheduledSyncId');

            $success = MediaManager::getInstance()->scheduledSync->deleteScheduledSyncById($scheduledSyncId);

            if(!$success) {
                Craft::$app->getSession()->setError(Craft::t('mediamanager', 'Couldn’t delete scheduled sync.'));
            } else {
                Craft::$app->getSession()->setNotice(Craft::t('mediamanager', 'Scheduled sync deleted.'));
            }

            if($request->getBodyParam('isJson')) {
                return $this->asJson([
                    'success' => $success
                ]);
            }

            return $this->redirectToPostedUrl();
        }
	}
