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
use craft\db\Query;
use craft\errors\ElementNotFoundException;
use craft\helpers\DateTimeHelper;
use craft\helpers\Queue;
use craft\queue\BaseJob;
use craft\elements\Entry;
use craft\elements\Asset;
use craft\elements\Tag;
use craft\helpers\FileHelper;
use craft\helpers\ElementHelper;
use craft\helpers\Assets as AssetHelper;

use DateTime;
use pbsdigital\mediamanager\MediaManager;
use pbsdigital\mediamanager\helpers\SettingsHelper;
use pbsdigital\mediamanager\helpers\SynchronizeHelper;
use yii\base\Exception;

class MediaSync extends BaseJob
{

    // Private Properties
    // =========================================================================

    protected string $apiBaseUrl;
    protected int $sectionId;
    protected int $typeId;
    protected int $authorId;
    protected string $authorUsername;
    protected int $mediaFolderId;
    protected int $logProcess;
    protected string $logFile;


    // Public Properties
    // =========================================================================

    public string $assetType = '';
    public string|array $siteId = [];
    public string $title = '';
    public array $auth = [];

    public string $apiKey = '';
    public bool $singleAsset = false;
    public ?string $singleAssetKey = null;

    public ?bool $forceRegenerateThumbnail = false;


    /**
     * @var string|array
     */
    public string|array $fieldsToSync = '*';

    public array $siteTags = [];
    public array $filmTags = [];
    public array $topicTags = [];


    // Private Properties
    // =========================================================================

    private $dateWithMs = 'Y-m-d\TH:i:s.uP';

    // Public Methods
    // =========================================================================

    public function execute($queue): void
    {
        $this->apiBaseUrl = SettingsHelper::get('apiBaseUrl');
        $this->sectionId = SynchronizeHelper::getSectionId(); // SECTION_ID
        $this->typeId = SynchronizeHelper::getSectionTypeId(); // TYPE_ID
        $this->authorId = SynchronizeHelper::getAuthorId(); // AUTHOR_ID
        $this->authorUsername = SynchronizeHelper::getAuthorUsername(); // AUTHOR_USERNAME
        $this->mediaFolderId = SynchronizeHelper::getAssetFolderId(); // MEDIA_FOLDER_ID
        $this->logProcess = 1; // LOG_PROCESS
        $this->logFile = '@storage/logs/sync.log'; // LOG_FILE

        // Convert Site ID from string (json) to array
        $this->_sanitizeSiteId();

        $url = $this->generateAPIUrl($this->assetType, $this->apiKey, $this->singleAsset, $this->singleAssetKey);
        $mediaAssets = $this->fetchMediaAssets($url);

        if ($this->singleAsset) {
            $mediaAssets = [$mediaAssets];
        }

        $totalAssets = count($mediaAssets);
        $count = 0;

        foreach ($mediaAssets as $mediaAsset) {
            $assetAttributes = $mediaAsset->attributes;
            $availabilities = $assetAttributes->availabilities;

            $existingEntry = $this->findExistingMediaEntry($mediaAsset->id);

            // if all availabilities are null, early return
            if ($availabilities->public->start === null && $availabilities->public->end === null &&
                $availabilities->all_members->start === null && $availabilities->all_members->end === null &&
                $availabilities->station_members->start === null && $availabilities->station_members->end === null
            ) {
                if ($existingEntry) {
                    $existingEntry->setFieldValue('markedForDeletion', true);
                    $existingEntry->enabled = 0;
                    Craft::$app->getElements()->saveElement($existingEntry);
                    $this->setProgress($queue, $count++ / $totalAssets);
                }

                continue;
            }

            $entry = $this->chooseOrCreateMediaEntry($assetAttributes->title, $existingEntry);
            $expirationStatus = $this->determineExpirationStatus($availabilities->public->end);
            $displayPassportIcon = $this->determinePassportStatus(
                $availabilities->all_members->start,
                $availabilities->all_members->end,
                $availabilities->public->start,
                $availabilities->public->end
            );

            $isNew = !$existingEntry;

            // Set default field Values
            $defaultFields = [];

            // Set field values based on API Column Fields on settings
            $apiColumnFields = SettingsHelper::get('apiColumnFields');

            if ($this->fieldsToSync === '*' || in_array('title', $this->fieldsToSync) || $isNew) {
                $entry->title = $assetAttributes->title;
            }

            foreach ($apiColumnFields as $apiColumnField) {

                $apiField = $apiColumnField[0];

                // ensure the field to be updated from MM Settings is included in the fieldsToSync array
                if (!$isNew && ($this->fieldsToSync !== '*' && !in_array($apiField, $this->fieldsToSync))) {
                    continue;
                }

                switch ($apiField) {
                    case 'thumbnail':
                        $thumbnail = $this->createOrUpdateThumbnail($entry->title, $assetAttributes->images[0]);

                        if ($thumbnail) {
                            $defaultFields[SynchronizeHelper::getThumbnailField()] = [$thumbnail->id];
                        }
                        break;
                    case 'images':
                        $imagesHandle = SynchronizeHelper::getApiField($apiField);
                        $fieldRule = SynchronizeHelper::getApiFieldRule($apiField);

                        if (isset($assetAttributes->images) && is_array($assetAttributes->images)) {

                            $assets = [];

                            foreach ($assetAttributes->images as $image) {

                                if ($fieldRule) {

                                    preg_match('/' . $fieldRule . '/', $image->profile, $matches);

                                    if (count($matches)) {

                                        $asset = $this->createOrUpdateThumbnail($entry->title, $image);

                                        if ($asset && isset($asset->id)) {
                                            $assets[] = $asset->id;
                                        }
                                    }

                                    continue;
                                }

                                $asset = $this->createOrUpdateThumbnail($entry->title, $image);

                                if ($asset && isset($asset->id)) {
                                    $assets[] = $asset->id;
                                }
                            }

                            if ($assets) {
                                $defaultFields[$imagesHandle] = $assets;
                            }
                        }
                        break;
                    case 'video_address':
                        if (isset($assetAttributes->slug)) {
                            $defaultFields[SynchronizeHelper::getApiField($apiField)] = 'https://pbs.org/video/' . $assetAttributes->slug;
                        }
                        break;
                    case 'display_passport_icon':
                        $defaultFields[SynchronizeHelper::getDisplayPassportIconField()] = $displayPassportIcon;
                        break;
                    case 'last_synced':
                        $defaultFields[SynchronizeHelper::getLastSyncedField()] = new \DateTime('now');
                        break;
                    case 'site_tags':

                        // Prepare craft field handle and prepare tag group id
                        $siteTagFieldHandle = SynchronizeHelper::getSiteTagsField();
                        $siteTagGroupId = SynchronizeHelper::getTagGroupIdByCraftFieldHandle($siteTagFieldHandle);

                        // Generate Site Tags
                        if (!is_array($this->siteId)) {
                            $this->siteId = json_decode($this->siteId);
                        }

                        foreach ($this->siteId as $siteId) {

                            $site = Craft::$app->sites->getSiteById($siteId);

                            if ($site) {
                                if (SynchronizeHelper::hasSiteBeenEntrified() && $siteTagSectionInfo = SynchronizeHelper::getSiteTagSectionInfo()) {
                                    $tag = $this->findOrCreateTagEntry($site->name, $siteTagSectionInfo);
                                } else {
                                    $tag = $this->findOrCreateTag($site->name, $siteTagGroupId);
                                }

                                if ($tag) {
                                    $this->siteTags[] = $tag->id;
                                }
                            }
                        }

                        $defaultFields[$siteTagFieldHandle] = $this->siteTags;

                        break;
                    case 'film_tags':

                        // Prepare craft field handle and prepare tag group id
                        $filmTagFieldHandle = SynchronizeHelper::getFilmTagsField();

                        // Generate Film Tags
                        $parentTree = $assetAttributes->parent_tree;

                        if ($parentTree) {

                            $film = false;

                            $parentAttributes = $parentTree->attributes;

                            if (isset($parentAttributes->season)) {
                                $parentAttributes = $parentAttributes->season->attributes;
                            }

                            if ($parentAttributes && isset($parentAttributes->show) && isset($parentAttributes->show->attributes) && isset($parentAttributes->show->attributes->title)) {
                                if (SynchronizeHelper::hasSiteBeenEntrified() && $filmTagSectionInfo = SynchronizeHelper::getFilmTagSectionInfo()) {
                                    $film = $this->findOrCreateTagEntry($parentAttributes->show->attributes->title, $filmTagSectionInfo);
                                } else {
                                    $filmTagGroupId = SynchronizeHelper::getTagGroupIdByCraftFieldHandle($filmTagFieldHandle);
                                    $film = $this->findOrCreateTag($parentAttributes->show->attributes->title, $filmTagGroupId);
                                }
                            }

                            if (!$film && $parentTree->type == 'show' && isset($parentTree->attributes) && isset($parentTree->attributes->title)) {
                                if (SynchronizeHelper::hasSiteBeenEntrified() && $filmTagSectionInfo = SynchronizeHelper::getFilmTagSectionInfo()) {
                                    $film = $this->findOrCreateTagEntry($parentTree->attributes->title, $filmTagSectionInfo);
                                } else {
                                    $filmTagGroupId = SynchronizeHelper::getTagGroupIdByCraftFieldHandle($filmTagFieldHandle);
                                    $film = $this->findOrCreateTag($parentTree->attributes->title, $filmTagGroupId);
                                }
                            }

                            if ($film) {
                                $this->filmTags[] = $film->id;
                            }
                        }

                        $defaultFields[$filmTagFieldHandle] = $this->filmTags;

                        break;
                    case 'topic_tags':

                        // Prepare craft field handle and prepare tag group id
                        $topicTagFieldHandle = SynchronizeHelper::getTopicTagsField();
                        // Generate Topic Tags
                        $topicAttributes = $assetAttributes->topics;

                        if ($topicAttributes) {
                            foreach ($topicAttributes as $topic) {

                                if (isset($topic->name)) {
                                    if (SynchronizeHelper::hasSiteBeenEntrified() && $topicTagSectionInfo = SynchronizeHelper::getTopicTagSectionInfo()) {
                                        $tag = $this->findOrCreateTagEntry($topic->name, $topicTagSectionInfo);
                                    } else {
                                        $tagGroupId = SynchronizeHelper::getTagGroupIdByCraftFieldHandle($topicTagFieldHandle);
                                        $tag = $this->findOrCreateTag($topic->name, $tagGroupId);
                                    }

                                    if ($tag) {
                                        $this->topicTags[] = $tag->id;
                                    }
                                }
                            }
                        }

                        $defaultFields[$topicTagFieldHandle] = $this->topicTags;

                        break;
                    case 'expiration_status':
                        $defaultFields[SynchronizeHelper::getExpirationStatusField()] = $expirationStatus;
                        break;
                    case 'media_manager_id':
                        $defaultFields[SynchronizeHelper::getMediaManagerIdField()] = $mediaAsset->id;
                        break;
                    case 'description_log':
                        // Only if new entry add description
                        if (!$existingEntry) {
                            $defaultFields[SynchronizeHelper::getApiField($apiField)] = $assetAttributes->description_long;
                        }
                        break;
                    case 'duration':
                        $defaultFields[SynchronizeHelper::getApiField($apiField)] = $this->convertToReadableTime($assetAttributes->duration);
                        break;
                    case 'object_type':
                        $defaultFields[SynchronizeHelper::getApiField($apiField)] = ucwords(str_replace('_', ' ', $assetAttributes->object_type));
                        break;

                    case 'season':

                        $seasonId = '';
                        $parentTree = $assetAttributes->parent_tree;

                        if ($parentTree) {

                            if ($parentTree->type == 'episode') {

                                $parentAttributes = $parentTree->attributes;

                                if ($parentAttributes && isset($parentAttributes->season) && isset($parentAttributes->season->attributes)) {

                                    $season_attributes = $parentAttributes->season->attributes;

                                    if (isset($season_attributes->ordinal)) {
                                        $seasonId = $season_attributes->ordinal;
                                    }
                                }
                            } elseif ($parentTree->type == 'season') {

                                $parentAttributes = $parentTree->attributes;

                                if (isset($parentAttributes->ordinal)) {
                                    $seasonId = $parentAttributes->ordinal;
                                }
                            }
                        }

                        $defaultFields[SynchronizeHelper::getApiField($apiField)] = $seasonId;

                        break;
                    case 'episode':

                        $episodeId = '';
                        $parentTree = $assetAttributes->parent_tree;

                        if ($parentTree) {

                            if ($parentTree->type == 'episode') {

                                $parentAttributes = $parentTree->attributes;

                                if ($parentAttributes) {
                                    $episodeId = $parentAttributes->ordinal;
                                }
                            }
                        }

                        $defaultFields[SynchronizeHelper::getApiField($apiField)] = $episodeId;

                        break;

                    default:
                        $defaultFields[SynchronizeHelper::getApiField($apiField)] = $assetAttributes->{$apiField};
                        break;
                }
            }

            // Process additional fields
            $defaultFields = $this->processAdditionalFields($defaultFields, $assetAttributes, $existingEntry, $entry, $this->forceRegenerateThumbnail);

            // TODO : Feature Gate for Food Site? I think this was never actually used or implemented. Leaving for posterity
            // if (SynchronizeHelper::getTagGroupIdByCraftFieldHandle('assetType')) {
            //     // TODO : Support Entrified and non-Entrified status
            //     $assetTypeTag = $this->findOrCreateTagEntry($this->assetType, SynchronizeHelper::getTagGroupIdByCraftFieldHandle('assetType'));
            //     $entry->setFieldValue('assetType', [$assetTypeTag->id]);
            // }

            // Set field values and properties
            $entry->setFieldValues($defaultFields);

            if ($availabilities->all_members->end) {
                if (DateTimeHelper::isInThePast($availabilities->all_members->end)) {
                    Craft::warning("{$mediaAsset->id} is expired. Exiting.");
                    return;
                }
                $tempExpiryDate = strtotime($availabilities->all_members->end);
                $entry->expiryDate = new \DateTime(date('Y-m-d H:i:s', $tempExpiryDate));
            }

            $markForDeletion = 0;
            if ($availabilities->public->start === null && $availabilities->all_members->start === null) {
                $markForDeletion = 1;
            }
            $entry->setFieldValue('markedForDeletion', $markForDeletion);
            $entry->enabled = $this->isEntryEnabled($availabilities);


            try {
                $publicStartDate = new DateTime($availabilities->public->start);
                $allMembersStartDate = new DateTime($availabilities->all_members->start);

                $entry->postDate = min($publicStartDate, $allMembersStartDate);
            } catch (\Exception $e) {
                Craft::warning($e->getMessage(), __METHOD__);
            }

            Craft::$app->getElements()->saveElement($entry);
            $this->setProgress($queue, $count++ / $totalAssets);
        }


        $today = (new DateTime())->format('Y-m-d');
        $mergedTags = implode(', ', array_merge($this->filmTags, $this->topicTags));

        Queue::push((new IdentifyStaleMedia([
            'date' => $today,
            'tags' => $mergedTags,
            'sectionId' => $this->sectionId,
            'siteId' => $this->siteId,
        ])));
    }

    // Protected Methods
    // =========================================================================

    protected function defaultDescription(): string
    {
        return Craft::t('mediamanager', 'Syncing media for ' . $this->title);
    }

    // Private Methods
    // =========================================================================

    private function log($message)
    {
        if ($this->logProcess) {
            $log = date('Y-m-d H:i:s') . ' ' . $message . "\n";
            FileHelper::writeToFile(Craft::getAlias($this->logFile), $log, ['append' => true]);
        }
    }

    private function generateAPIUrl($assetType, $apiKey, $singleAsset, $singleAssetKey)
    {

        if ($singleAsset && $singleAssetKey) {
            return $this->apiBaseUrl . 'assets/' . $singleAssetKey . '/?platform-slug=bento&platform-slug=partnerplayer';
        }

        return $this->apiBaseUrl . $assetType . 's/' . $apiKey . '/assets?page=1&platform-slug=bento&platform-slug=partnerplayer';
    }

    private function fetchMediaAssets($url, $page = 1)
    {
        $client = Craft::createGuzzleClient();
        $response = $client->get($url, $this->auth);
        $response = json_decode($response->getBody());

        if (isset($response->links) && isset($response->links->next)) {

            $assets = $this->fetchMediaAssets($response->links->next);
            return array_merge($response->data, $assets);
        }

        return $response->data;
    }

    private function findOrCreateTagEntry($title, $sectionInfo)
    {
        if (!$sectionInfo) {
            return null;
        }

        $section = Craft::$app->sections->getSectionById($sectionInfo['id']);

        $tag = Entry::find()
            ->title($title)
            ->section($section)
            ->one();

        if (!$tag) {
            $tag = new Entry();
            $tag->sectionId = $sectionInfo['id'];
            $tag->typeId = $sectionInfo['entryTypeId'];
            $tag->title = $title;
            $tag->authorId = $this->authorId;
            Craft::$app->getElements()->saveElement($tag);
        }

        return $tag;
    }

    private function findOrCreateTag($title, $groupId)
    {
        $tag = Tag::find()
            ->where(['title' => $title])
            ->groupId($groupId)
            ->one();

        if (!$tag) {

            $tag = new Tag();
            $tag->title = $title;
            $tag->groupId = $groupId;

            Craft::$app->getElements()->saveElement($tag);
        }

        return $tag;
    }

    private function processAdditionalFields($defaultFields, $assetAttributes, $existingEntry, $entry, $forceRegenerateThumbnail)
    {
        // If user choose to force regenerate thumbnail
        if ($forceRegenerateThumbnail && $forceRegenerateThumbnail !== 'false') {

            $thumbnail = $this->createOrUpdateThumbnail($entry->title, $assetAttributes->images[0]);

            if ($thumbnail) {
                $defaultFields[SynchronizeHelper::getThumbnailField()] = [$thumbnail->id];
            }

            return $defaultFields;
        }

        // Check if thumbnail generation is required
        if (!$existingEntry) {

            $thumbnail = $this->createOrUpdateThumbnail($entry->title, $assetAttributes->images[0]);
            if ($thumbnail) {
                $defaultFields[SynchronizeHelper::getThumbnailField()] = [$thumbnail->id];
            }
        } else {
            // Regenerate if entry already exist and thumbnail is empty
            // or inaccessible due to Enabled Sites incomplete against Supported Sites which causing thumbnail empty
            if ($thumbnail = $this->thumbnailNotAccessibleAcrossSites($entry)) {
                $defaultFields[SynchronizeHelper::getThumbnailField()] = [$thumbnail->id];
            }
        }

        return $defaultFields;
    }

    private function findExistingMediaEntry($mediaManagerId)
    {
        // Find existing media
        $entry = Entry::find()
            ->{SynchronizeHelper::getMediaManagerIdField()}($mediaManagerId)
            ->sectionId($this->sectionId)
            ->status(null)
            ->siteId($this->siteId)
            ->one();

        return $entry ?? false;
    }

    private function chooseOrCreateMediaEntry($title, $entry)
    {

        if (!$entry) {

            $apiUserID = $this->authorId;
            if ($this->authorUsername) {
                $user = Craft::$app->users->getUserByUsernameOrEmail($this->authorUsername);

                if ($user) {
                    $apiUserID = $user->id;
                }
            }

            $entry = new Entry();
            $entry->sectionId = $this->sectionId;
            $entry->typeId = $this->typeId;
            $entry->authorId = $apiUserID;
            $entry->title = $title;
        }

        return $entry;
    }

    private function compareEnabledSupportedSites($asset)
    {
        $countSupportedSites = count($asset->getSupportedSites());
        $countEnabledSite = (new Query())
            ->select('siteId')
            ->from('{{%elements_sites}}')
            ->where([
                'enabled' => true,
                'elementId' => $asset->id
            ])
            ->count();

        return ($countEnabledSite == $countSupportedSites) ? true : false;
    }

    private function thumbnailNotAccessibleAcrossSites($entry)
    {
        // If thumbnail empty, don't overwrite it since it might be from the admin
        if (!$entry->{SynchronizeHelper::getThumbnailField()}->collect()->count()) {
            return false;
        }

        $asset = $entry->{SynchronizeHelper::getThumbnailField()}->collect()->first();

        if (!$asset) {
            return false;
        }

        // This means some sites unable to access the asset
        // which causing the thumbnail field looks like empty, regenerate then...
        if (!$this->compareEnabledSupportedSites($asset)) {
            return $this->cloneExistingThumbnail($entry);
        }

        return false;
    }

    private function createImageAsset($imageUrl, $filename)
    {
        $folder = $this->getMediaFolder();
        $localPath = $this->copyImageToServer($imageUrl);

        $asset = new Asset();
        $asset->tempFilePath = $localPath;
        $asset->filename = $filename;
        $asset->newFolderId = $folder->id;
        $asset->volumeId = $folder->volumeId;
        $asset->avoidFilenameConflicts = true;

        $asset->setScenario(Asset::SCENARIO_CREATE);

        try {
            Craft::$app->getElements()->saveElement($asset);
            return $asset;
        } catch (ElementNotFoundException|Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
            return null;
        } catch (\Throwable $e) {
            Craft::error($e->getMessage(), __METHOD__);
            return null;
        }
    }

    private function cloneExistingThumbnail($entry)
    {
        $existingAsset = $entry->{SynchronizeHelper::getThumbnailField()}[0];

        if (!$existingAsset->getUrl()) {
            return false;
        }

        $filename = pathinfo($existingAsset->getUrl())['basename'];

        return $this->createImageAsset($existingAsset->getUrl(), $filename);
    }

    private function createOrUpdateThumbnail($entryTitle, $imageInfo)
    {
        $imageUrl = $imageInfo->image;
        $extension = pathinfo($imageUrl)['extension'];
        $slug = ElementHelper::normalizeSlug($entryTitle);
        $filename = $slug . '-' . md5(ElementHelper::normalizeSlug($imageUrl)) . '.' . $extension;
        $asset = Asset::findOne(['filename' => $filename]);

        if ($asset) {
            // Need to regenerate if existing asset is inaccessible by some sites
            if ($this->compareEnabledSupportedSites($asset)) {
                return $asset;
            }
        }

        return $this->createImageAsset($imageUrl, $filename);
    }

    private function determineExpirationStatus($date)
    {
        if (!$date) {
            return '';
        }

        $generalEndDate = strtotime($date);
        $currentTime = strtotime('now');
        $sevenDaysFromNow = strtotime('+7 days');
        $twoDaysFromNow = strtotime('+2 days');
        $twoHoursFromNow = strtotime('+2 hours');

        if ($generalEndDate > $sevenDaysFromNow) {
            return '';
        }

        if ($generalEndDate <= $sevenDaysFromNow && $generalEndDate > $twoDaysFromNow) {

            $daysLeft = floor(($generalEndDate - $currentTime) / 60 / 60 / 24);

            return 'Expires in ' . $daysLeft . ' day' . ($daysLeft == 1 ? '' : 's');
        }

        if ($generalEndDate <= $twoDaysFromNow && $generalEndDate > $twoHoursFromNow) {

            $currentTimeObj = \DateTime::createFromFormat('U', $currentTime);
            $generalEndDateObj = \DateTime::createFromFormat('U', $generalEndDate);
            $diff = $currentTimeObj->diff($generalEndDateObj);
            $hoursLeft = $diff->format('%h');

            if (intval($hoursLeft) <= 2) {
                return 'Expiring Now';
            }

            return 'Expires in ' . $hoursLeft . ' hour' . ($hoursLeft == 1 ? '' : 's');
        }

        if ($generalEndDate <= $twoHoursFromNow && $generalEndDate >= $currentTime) {
            return 'Expiring Now';
        }

        return '';
    }

    private function determinePassportStatus($allMembersStartDate, $allMembersEndDate, $publicStartDate, $publicEndDate)
    {
        $publicStart = strtotime($publicStartDate);
        $publicEnd = strtotime($publicEndDate);
        $allMembersStart = strtotime($allMembersStartDate);
        $allMembersEnd = strtotime($allMembersEndDate);
        $currentTime = strtotime('0 days');

        // Check if currentTime inside public window
        if ($publicStart < $currentTime && $currentTime < $publicEnd) {
            return false;
        }

        return $allMembersStart < $currentTime && $currentTime < $allMembersEnd;
    }

    private function isEntryEnabled($availabilities)
    {
        // previous logic used $availabilities->all_members->end to calculate status
        // now we will check if all start/end dates are null and if so set as disabled
        $public = $availabilities->public;
        $allMembers = $availabilities->all_members;
        $stationMembers = $availabilities->station_members;

        if ($public->start === null && $public->end === null &&
            $allMembers->start === null && $allMembers->end === null &&
            $stationMembers->start === null && $stationMembers->end === null
        ) {
            return 0;
        }

        $endDate = $allMembers->end;

        if ($allMembers->start && !$endDate) {
            return 1;
        }

        $currentTime = strtotime('now');
        return (strtotime($endDate) > $currentTime) ? 1 : 0;
    }

    private function getMediaFolder()
    {
        $assets = Craft::$app->getAssets();

        return $assets->findFolder(['id' => $this->mediaFolderId]);
    }

    private function copyImageToServer($url)
    {
        $image = file_get_contents($url);
        $extension = pathinfo($url)['extension'];
        $localPath = AssetHelper::tempFilePath($extension);

        file_put_contents($localPath, $image);

        return $localPath;
    }

    private function convertToReadableTime($duration)
    {
        $minutes = floatval(floor($duration / 60));
        $seconds = $duration % 60;

        if ($minutes && $seconds) {
            return "${minutes}m${seconds}s";
        }

        if ($minutes && !$seconds) {
            return "${minutes}m";
        }

        if (!$minutes && $seconds) {
            return "${seconds}s";
        }

        return $seconds;
    }

    private function _sanitizeSiteId()
    {
        if (!is_array($this->siteId)) {
            $this->siteId = json_decode($this->siteId);
        }
    }
}
