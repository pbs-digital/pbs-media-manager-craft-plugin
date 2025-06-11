<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\helpers\aftersavesettings;

use Craft;
use yii\base\Application;

use pbsdigital\mediamanager\MediaManager;
use pbsdigital\mediamanager\base\ConstantAbstract;
use pbsdigital\mediamanager\helpers\SettingsHelper;

class OldSettingsHelper
{
    // Private Properties
    // =========================================================================

    private static $settingsToStore = [
        'apiColumnFields',
        'mediaSection',
        'fieldLayout',
        'showSection',
        'showApiColumnFields',
        'showFieldLayout'
    ];


    // Public Static Methods
    // =========================================================================

    public static function process()
    {
        foreach( self::$settingsToStore as $settingName ) {

            $settingValue = SettingsHelper::get( $settingName );
            MediaManager::getInstance()->oldsettings->save( $settingName, $settingValue );

        }
    }
}
