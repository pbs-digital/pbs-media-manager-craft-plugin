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

class IdentifyStaleMedia extends BaseJob
{

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $date;

    /**
     * @var string
     */
    public string $tags;

    /**
     * @var int
     */
    public int $sectionId;

    /**
     * @var int|array
     */
    public int|array $siteId;

    // Public Methods
    // =========================================================================

    public function execute( $queue ): void
    {

        if (!$this->tags) {
            // too generic, exit
            return;
        }


        $relatedMediaObjects = Entry::find()->sectionId($this->sectionId)->lastSynced("< {$this->date}")->relatedTo(['and', $this->tags])->markedForDeletion(0)->siteId($this->siteId)->ids();

        foreach($relatedMediaObjects as $media) {
					if (!$this->_queueJobExists($media)) {
						$queue = Craft::$app->getQueue();
						$queue->push(new MarkStaleMedia(['entryId' => $media]));
					}
        }
    }

    // Protected Methods
    // =========================================================================

    protected function defaultDescription(): string
    {
        return Craft::t( 'mediamanager', "Marking entries for deletion." );
    }


    // Private Methods
    // =========================================================================
		private function _queueJobExists(int $entryId): bool
		{
			// Preflight check to ensure regular queue in place
			if(!Craft::$app->queue->hasProperty('jobInfo')){
				return false;
			}

			return in_array("Marking entry {$entryId} for deletion.", array_column(Craft::$app->queue->jobInfo, 'description'), true);
		}
}
