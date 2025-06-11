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

class Show extends ActiveRecord
{
    // Private Properties
    // =========================================================================

    private static $mediaManagerShowTable = ConstantAbstract::MEDIAMANAGER_SHOW_TABLE;


    // Public Static Methods
    // =========================================================================

    public static function tableName(): string
    {
        return self::$mediaManagerShowTable;
    }


    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            [ 'name', 'required' ]
        ];
    }
}
