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
			$scheduledSync = $this->scheduledSync ? MediaManager::getInstance()->scheduledSync->getScheduledSyncById((int)$this->scheduledSync) : null;

			if (!$scheduledSync) {
				Craft::warning("Scheduled sync {$this->scheduledSync} no longer exists; skipping show sync.", __METHOD__);
				return;
			}

			$show = $this->_getShow();

			if (empty($show->apiKey)) {
				Craft::warning("Show {$this->showId} no longer exists or has no API key; skipping scheduled sync {$this->scheduledSync}.", __METHOD__);
				return;
			}

			$mediaFieldsToSync = $scheduledSync->getMediaFieldsToSync();
			$showFieldsToSync = $scheduledSync->getShowFieldsToSync();

			MediaManager::$plugin->api->synchronizeShow($show, $this->regenerateThumbnails, $mediaFieldsToSync, $scheduledSync->getPlayerParams());
			MediaManager::$plugin->api->synchronizeShowEntries([$show], $showFieldsToSync);
		}

		// Protected Methods
		// =========================================================================

		protected function defaultDescription(): string
		{
			$show = $this->_getShow();
			$name = $show->name ?? '';
			return Craft::t( 'mediamanager', "Queuing sync for {$name}" );
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
