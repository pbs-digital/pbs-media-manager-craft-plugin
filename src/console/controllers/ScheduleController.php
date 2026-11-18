<?php
	/**
	 * Media Manager
	 *
	 * @package       Media Manager
	 * @author        PBS Digital
	 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
	 */

	namespace pbsdigital\mediamanager\console\controllers;

	use Craft;
	use pbsdigital\mediamanager\MediaManager;
	use yii\console\Controller;
	use yii\console\ExitCode;
	use yii\helpers\Console;

	use pbsdigital\mediamanager\helpers\SettingsHelper;


	class ScheduleController extends Controller
	{
		public $defaultAction = 'index';
		protected $allowAnonymous = ['index', 'run'];

		public function actionIndex(): int
		{
			echo "Checking for scheduled syncs... \n";

			$pushed = MediaManager::$plugin->scheduledSync->pushDueSyncs();

			if($pushed === 0)
			{
				echo "No scheduled syncs found. \n";
				return ExitCode::OK;
			}

			echo "Pushed " . $pushed . " scheduled sync(s) to queue. \n";

			return ExitCode::OK;
		}

		public function actionRun(): int
		{
			return $this->actionIndex();
		}
	}
