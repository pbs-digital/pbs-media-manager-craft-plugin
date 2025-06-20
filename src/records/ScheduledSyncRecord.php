<?php
	/**
	 * Media Manager
	 *
	 * @package       Media Manager
	 * @author        PBS Digital
	 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
	 */

	namespace pbsdigital\mediamanager\records;

	use Craft;
	use craft\db\ActiveRecord;

	use DateTime;
	use pbsdigital\mediamanager\MediaManager;
	use pbsdigital\mediamanager\base\ConstantAbstract;

	/**
	 * ScheduledSyncRecord
	 * @return string the table name
	 * @property string|mixed $name Schedule Name
	 * @property string $description Schedule Description
	 * @property int $showId Show ID
	 * @property int $id ID
	 * @property DateTime $scheduleDate Schedule Date
	 * @property bool $processed Processed
	 * @property string $mediaFieldsToSync
	 * @property string $showFieldsToSync
	 * @property bool $regenerateThumbnail
	 */
	class ScheduledSyncRecord extends ActiveRecord
	{
		// Private Properties
		// =========================================================================

		private static $mediaManagerScheduledSyncTable = ConstantAbstract::MEDIAMANAGER_SCHEDULED_SYNC_TABLE;


		// Public Static Methods
		// =========================================================================

		public static function tableName(): string
		{
			return self::$mediaManagerScheduledSyncTable;
		}


		// Public Methods
		// =========================================================================

		public function rules()
		{
			return [
				[ ['showId', 'scheduleDate'], 'required' ]
			];
		}
	}
