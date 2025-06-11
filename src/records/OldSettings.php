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

use pbsdigital\mediamanager\MediaManager;
use pbsdigital\mediamanager\base\ConstantAbstract;

class OldSettings extends ActiveRecord
{
    // Private Properties
    // =========================================================================

    private static $mediaManagerOldSettingsTable = ConstantAbstract::MEDIAMANAGER_OLD_SETTINGS_TABLE;


    // Public Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return self::$mediaManagerOldSettingsTable;
    }


    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            [ 'settingName', 'required' ],
            [ 'settingValue', 'required' ]
        ];
    }
}
