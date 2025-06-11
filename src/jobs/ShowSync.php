<?php
	/**
	 * Media Manager
	 *
	 * @package       Media Manager
	 * @author        PBS Digital
	 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
	 */

	namespace pbsdigital\mediamanager\jobs;

	use Craft;
	use craft\queue\BaseJob;
	use pbsdigital\mediamanager\MediaManager;

	class ShowSync extends BaseJob
	{

		// Public Properties
		// =========================================================================

		/**
		 * @var int
		 */
		public $showId;

		/**
		 * @var int
		 */
		public $scheduledSync;

		/**
		 * @var bool
		 */
		public $regenerateThumbnails;

		public $_show;

		// Public Methods
		// =========================================================================

		public function execute( $queue ): void
		{
			$show = $this->_getShow();
			$scheduledSync = MediaManager::getInstance()->scheduledSync->getScheduledSyncById($this->scheduledSync);
			$mediaFieldsToSync = $scheduledSync->getMediaFieldsToSync();
			$showFieldsToSync = $scheduledSync->getShowFieldsToSync();

			MediaManager::$plugin->api->synchronizeShow($show, $this->regenerateThumbnails, $mediaFieldsToSync);
			MediaManager::$plugin->api->synchronizeShowEntries([$show], $showFieldsToSync);
		}

		// Protected Methods
		// =========================================================================

		protected function defaultDescription(): string
		{
			$show = $this->_getShow();
			return Craft::t( 'mediamanager', "Queuing sync for {$show->name}" );
		}

		private function _getShow()
		{
			if($this->_show){
				return $this->_show;
			}

			$this->_show = (object)MediaManager::getInstance()->show->getShow( $this->showId );

			return $this->_show;
		}
	}
