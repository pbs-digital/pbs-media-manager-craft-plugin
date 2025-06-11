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
use craft\base\Element;
use craft\helpers\Queue;
use craft\helpers\UrlHelper;
use craft\elements\Entry;
use craft\web\Controller;
use pbsdigital\mediamanager\jobs\CancelStaleMedia;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

use pbsdigital\mediamanager\MediaManager;

class MainController extends Controller
{

    // Protected Properties
    // =========================================================================
    protected const INDEX_TEMPLATE_PATH          = 'mediamanager/index';
    protected const ENTRIES_TEMPLATE_PATH        = 'mediamanager/entries';
    protected array|int|bool $allowAnonymous                    = [ 'index', 'entries' ];

    // Public Methods
    // =========================================================================

    public function actionIndex(): Response
    {
        return $this->renderTemplate( self::INDEX_TEMPLATE_PATH );
    }

    public function actionEntries(): Response
    {
        return $this->renderTemplate( self::ENTRIES_TEMPLATE_PATH );
    }

    public function actionCancelMarkedForDeletion(): Response
    {
        $this->requireLogin();
        Queue::push((new CancelStaleMedia()));

        return $this->asJson('Unchecking items marked for deletion.');
    }
}
