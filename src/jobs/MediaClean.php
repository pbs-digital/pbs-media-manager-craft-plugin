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
use craft\queue\BaseJob;
use craft\elements\Entry;
use craft\elements\Asset;
use craft\elements\Tag;
use craft\helpers\ElementHelper;
use craft\helpers\Assets as AssetHelper;

use pbsdigital\mediamanager\MediaManager;
use pbsdigital\mediamanager\helpers\SettingsHelper;

class MediaClean extends BaseJob
{

    // Public Properties
    // =========================================================================
    public $title;
    public $entryId;
    public $total;
    public $count;

    // Public Methods
    // =========================================================================

    public function execute( $queue ): void
    {
        Craft::$app->getElements()->deleteElementById( $this->entryId, Entry::class );
        $this->setProgress( $queue, $this->count / $this->total );
    }

    // Protected Methods
    // =========================================================================

    protected function defaultDescription(): string
    {
        return Craft::t( 'mediamanager', 'Removing "' . $this->title .'" ID : '. $this->entryId );
    }

    // Private Methods
    // =========================================================================
}
