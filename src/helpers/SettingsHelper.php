<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\helpers;

use Craft;
use craft\elements\User;
use yii\base\Application;

use pbsdigital\mediamanager\MediaManager;
use pbsdigital\mediamanager\base\ConstantAbstract;

class SettingsHelper
{
    // Public Static Methods
    // =========================================================================

    public static function settings()
    {
        return MediaManager::getInstance()->getSettings();
    }

    public static function get( $key )
    {
        return self::settings()->{ $key } ?? null;
    }

    public static function set( array $settings )
    {
        Craft::$app->getPlugins()->savePluginSettings( MediaManager::$plugin, $settings );
        Craft::$app->trigger( Application::EVENT_AFTER_REQUEST ); // This event required for triggering saveModifiedConfigData to run and store settings to database
    }

    public static function templateVariables()
    {
        $isCraft35 = version_compare( Craft::$app->schemaVersion, '3.5.0', '>=' );

        return [
            'plugin'        => MediaManager::$plugin,
            'settings'      => self::settings(),
            'users'         => User::find()->all(),
            'isCraft35'     => $isCraft35
        ];
    }
}
