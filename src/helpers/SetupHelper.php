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
use Exception;
use craft\elements\User;

use pbsdigital\mediamanager\base\ConstantAbstract;

class SetupHelper
{
    // Public Static Methods
    // =========================================================================

    public static function registerRequiredComponents()
    {
        // Craft CMS API User is now using plugin settings
    }

    public static function unregisterRequiredComponents()
    {
        // No Craft CMS API User removal
    }

}
