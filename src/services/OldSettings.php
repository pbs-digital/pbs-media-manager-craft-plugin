<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\services;

use Craft;
use craft\base\Component;

use pbsdigital\mediamanager\MediaManager;
use pbsdigital\mediamanager\records\OldSettings as OldSettingsRecord;

class OldSettings extends Component
{
    // Public Methods
    // =========================================================================

    public function get( $settingName = null )
    {
        $oldSetting = new OldSettingsRecord();

        if( $settingName ) {

            $oldSettingValue = $oldSetting->find()
                                    ->where( [ '=', 'settingName', $settingName ] )
                                    ->one();

            return $oldSettingValue;
        }

        return $oldSetting->find()
                    ->where( [ '!=', 'settingName', '' ] )
                    ->orderBy( [ '(id)' => SORT_ASC ] )
                    ->all();
    }

    public function save( string $settingName, $settingValue )
    {
        $oldSetting         = new OldSettingsRecord();
        $existingOldSetting = $oldSetting->find()
                                    ->where( [ '=', 'settingName', $settingName ] )
                                    ->one();

        if( !is_array( $settingValue ) ) {
            $settingValue = ( array ) $settingValue;
        }

        if( $existingOldSetting ) {

            if( $settingName ) {
                $existingOldSetting->settingName = $settingName;
            }

            if( $settingValue ) {
                $existingOldSetting->settingValue = $settingValue;
            }

            $existingOldSetting->update();

            return $existingOldSetting;
        }

        $oldSetting->settingName  = $settingName;
        $oldSetting->settingValue = $settingValue;
        $oldSetting->save();

        return $oldSetting;
    }
}
