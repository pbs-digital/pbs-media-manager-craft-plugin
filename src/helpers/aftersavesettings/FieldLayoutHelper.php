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
use craft\elements\Entry;
use craft\models\FieldLayoutTab;

use pbsdigital\mediamanager\MediaManager;
use pbsdigital\mediamanager\base\ConstantAbstract;
use pbsdigital\mediamanager\helpers\SettingsHelper;

class FieldLayoutHelper
{
    // Public Static Methods
    // =========================================================================

    public static function process()
    {
        // Process Field Layout
        $mediaSectionHandle = SettingsHelper::get( 'mediaSection' );
        $mediaSection       = Craft::$app->getSections()->getSectionByHandle( $mediaSectionHandle );
        $entryType          = Craft::$app->getSections()->getEntryTypesBySectionId( $mediaSection->id )[ 0 ];
        $fieldTabs          = self::_registerTabAndFields( SettingsHelper::get( 'fieldLayout' ) );
        $fieldLayout        = $entryType->getFieldLayout();
        $fieldLayout->type  = Entry::class;

        $fieldLayout->setTabs( $fieldTabs[ 'layoutTabs' ] );
        $fieldLayout->setFields( $fieldTabs[ 'layoutFields' ] );

        if( !Craft::$app->getSections()->saveEntryType( $entryType ) ) {
            throw new Exception( 'Failed to save media section.' );
        }
    }


    // Private Static Methods
    // =========================================================================

    private static function _registerTabAndFields( $fields )
    {
        $layoutTabs   = [];
        $layoutFields = [];
        $sortOrder    = 0;

        foreach( $fields as $key => $fieldItems ) {

            $tab            = new FieldLayoutTab();
            $tab->name      = $key;
            $tab->sortOrder = $sortOrder + 1;
            $tabFields      = [];

            if( !empty( $fieldItems ) ) {

                foreach( $fieldItems as $fieldItem ) {

                    $fieldModel = Craft::$app->getFields()->getFieldByHandle( $fieldItem );

                    if( $fieldModel ) {
                        $tabFields[]    = $fieldModel;
                        $layoutFields[] = $fieldModel;
                    }
                }
            }

            $tab->setFields( $tabFields );
            $layoutTabs[] = $tab;
        }

        return [
            'layoutTabs'   => $layoutTabs,
            'layoutFields' => $layoutFields
        ];
    }
}
