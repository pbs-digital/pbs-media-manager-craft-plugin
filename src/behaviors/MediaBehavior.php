<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\behaviors;

use Craft;
use yii\base\Behavior;

use pbsdigital\mediamanager\base\ConstantAbstract;
use pbsdigital\mediamanager\helpers\SettingsHelper;

class MediaBehavior extends Behavior
{
    // Public Methods
    // =========================================================================

    public function transformTags( $tags = null ) {

        if( !$tags ) {
            return false;
        }

        $formattedTags = [];

        foreach( $tags as $tag ) {
            $formattedTags[] = $tag->title;
        }

        return json_encode( $formattedTags );
    }

    public function mediaManagerReady()
    {
        foreach( ConstantAbstract::REQUIRED_SETTINGS as $settingKey ) {
            if( SettingsHelper::get( $settingKey ) === null ) {
                return false;
            }
        }

        return true;
    }
}
