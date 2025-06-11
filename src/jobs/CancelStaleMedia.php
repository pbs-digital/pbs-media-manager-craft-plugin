<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\jobs;

use Craft;
use craft\helpers\Db;
use craft\queue\BaseJob;
use craft\elements\Entry;

class CancelStaleMedia extends BaseJob
{

    // Public Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    public function execute( $queue ): void
    {

        $relatedMediaObjects = Entry::find()->markedForDeletion(1);

        foreach(Db::each($relatedMediaObjects) as $media) {
            $media->setFieldValue('markedForDeletion', 0);
            Craft::$app->getElements()->saveElement($media);
        }
    }

    // Protected Methods
    // =========================================================================

    protected function defaultDescription(): string
    {
        return Craft::t( 'mediamanager', "Unmarking entries for deletion." );
    }

    // Private Methods
    // =========================================================================
}
