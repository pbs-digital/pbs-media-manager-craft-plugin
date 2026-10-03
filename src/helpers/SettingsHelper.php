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

    /**
     * Whether a toggleable section (ConstantAbstract::TOGGLEABLE_SECTIONS) is
     * switched on for this site.
     */
    public static function sectionEnabled( $key )
    {
        return self::settings()->isSectionEnabled( $key );
    }

    public static function set( array $settings )
    {
        Craft::$app->getPlugins()->savePluginSettings( MediaManager::$plugin, $settings );
        Craft::$app->trigger( Application::EVENT_AFTER_REQUEST ); // This event required for triggering saveModifiedConfigData to run and store settings to database
    }

    /**
     * The selected API user, as an element list for the user picker.
     *
     * The setting used to store a username, so fall back to a username/email
     * lookup for installs saved before it became an element ID.
     */
    public static function apiCraftUserElements(): array
    {
        $value = self::get( 'apiCraftUser' );

        if( !$value ) {
            return [];
        }

        $user = is_numeric( $value )
            ? Craft::$app->users->getUserById( (int) $value )
            : Craft::$app->users->getUserByUsernameOrEmail( $value );

        return $user ? [ $user ] : [];
    }

    public static function templateVariables()
    {
        $isCraft35 = version_compare( Craft::$app->schemaVersion, '3.5.0', '>=' );

        return [
            'plugin'        => MediaManager::$plugin,
            'settings'      => self::settings(),
            'apiCraftUserElements' => self::apiCraftUserElements(),
            'isCraft35'     => $isCraft35,
            'toggleableSections' => ConstantAbstract::TOGGLEABLE_SECTIONS
        ];
    }
}
