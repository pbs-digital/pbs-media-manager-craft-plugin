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
use yii\console\Controller;
use yii\helpers\Console;
use yii\console\ExitCode;

use pbsdigital\mediamanager\MediaManager;

class SynchronizeConsoleController extends Controller
{
    // Protected
    // =========================================================================

    protected $allowAnonymous = [ 'run' ];


    // Public Methods
    // =========================================================================

    public function actionRun()
    {
        $shows          = MediaManager::getInstance()->show->getShow();
        $validatedShows = [];

        foreach( $shows as $show ) {

            if( $show->apiKey && $show->name ) {

                $show[ 'siteId' ] = json_decode( $show[ 'siteId' ] );
                array_push( $validatedShows, $show );
            }
        }

        if( count( $validatedShows ) ) {
            MediaManager::getInstance()->api->synchronizeAll( $validatedShows, false );
        }

        return ExitCode::OK;
    }
}
