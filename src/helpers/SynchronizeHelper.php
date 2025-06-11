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
use craft\helpers\App;
use Exception;
use craft\elements\User;

use pbsdigital\mediamanager\base\ConstantAbstract;
use pbsdigital\mediamanager\helpers\SettingsHelper;

class SynchronizeHelper
{
    // Public Static Methods
    // =========================================================================

    public static function getSectionId(): ?int
    {
        $section = Craft::$app->sections->getSectionByHandle( SettingsHelper::get( 'mediaSection' ) );

        if( !$section ) {
            return null;
        }

        return $section->id;
    }

    public static function getSectionTypeId(): ?int
    {
        $sectionId  = self::getSectionId();
        $entryTypes = Craft::$app->sections->getEntryTypesBySectionId( $sectionId );

        if( !is_array( $entryTypes ) && !$entryTypes[ 0 ] ) {
            return null;
        }

        return $entryTypes[ 0 ]->id;
    }

    public static function getShowSectionId(): ?int
    {
        $section = Craft::$app->sections->getSectionByHandle( SettingsHelper::get( 'showSection' ) );

        if( !$section ) {
            return null;
        }

        return $section->id;
    }

    public static function getBlogTagSectionInfo(): ?array
    {
        if( !SettingsHelper::get( 'blogTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->sections->getSectionByHandle( SettingsHelper::get( 'blogTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->sections->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getDateTagSectionInfo(): ?array
    {
        if( !SettingsHelper::get( 'dateTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->sections->getSectionByHandle( SettingsHelper::get( 'dateTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->sections->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getFilmTagSectionInfo(): ?array
    {
        if( !SettingsHelper::get( 'filmTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->sections->getSectionByHandle( SettingsHelper::get( 'filmTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->sections->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getSiteTagSectionInfo(): ?array
    {

        if( !SettingsHelper::get( 'siteTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->sections->getSectionByHandle( SettingsHelper::get( 'siteTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->sections->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getThemeTagSectionInfo(): ?array
    {
        if( !SettingsHelper::get( 'themeTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->sections->getSectionByHandle( SettingsHelper::get( 'themeTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->sections->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getTopicTagSectionInfo(): ?array
    {
        if( !SettingsHelper::get( 'topicTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->sections->getSectionByHandle( SettingsHelper::get( 'topicTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->sections->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    // public static function getAssetTypeSectionInfo(): ?array
    // {
    //     if( !SettingsHelper::get( 'assetTypeTagsSection' ) ) {
    //         return null;
    //     }

    //     $section = Craft::$app->sections->getSectionByHandle( SettingsHelper::get( 'assetTypeTagsSection' ) );

    //     if( !$section ) {
    //         return null;
    //     }

    //     $sectionId = $section->id;
    //     $entryTypeId = Craft::$app->sections->getEntryTypesBySectionId( $sectionId );

    //     $res = ['id' => $sectionId];

    //     if($entryTypeId){
    //         $res['entryTypeId'] = $entryTypeId[0]->id;
    //     }
    //     return $res;
    // }

    public static function getShowSectionTypeId()
    {
        $sectionId  = self::getShowSectionId();

        if( !$sectionId ) {
            return null;
        }

        $entryTypes = Craft::$app->sections->getEntryTypesBySectionId( $sectionId );

        if( !is_array( $entryTypes ) && !$entryTypes[ 0 ] ) {
            return null;
        }

        return $entryTypes[ 0 ]->id;
    }

    public static function getAuthorId()
    {
        $user = Craft::$app->users->getUserByUsernameOrEmail( SettingsHelper::get( 'apiCraftUser' ) );

        if( !$user ) {
            return null;
        }

        return $user->id;
    }

    public static function getAuthorUsername()
    {
        return SettingsHelper::get( 'apiCraftUser' );
    }

    public static function getAssetFolderId()
    {
        $volume = Craft::$app->volumes->getVolumeByHandle( SettingsHelper::get( 'mediaAssetVolume' ) );

        if( !$volume ) {
            return null;
        }

        $folder = Craft::$app->assets->findFolder( [ 'parentId' => $volume->id ] );

        if( !$folder ) {
            return null;
        }

        return $folder->id;
    }

    public static function getMediaManagerIdField()
    {
        return self::getCraftFieldHandleByApiHandle( 'media_manager_id' );
    }

    public static function getThumbnailField()
    {
        return self::getCraftFieldHandleByApiHandle( 'thumbnail' );
    }

    public static function getDisplayPassportIconField()
    {
        return self::getCraftFieldHandleByApiHandle( 'display_passport_icon' );
    }

    public static function getLastSyncedField()
    {
        return self::getCraftFieldHandleByApiHandle( 'last_synced' );
    }

    public static function getSiteTagsField()
    {
        return self::getCraftFieldHandleByApiHandle( 'site_tags' );
    }

    public static function getFilmTagsField()
    {
        return self::getCraftFieldHandleByApiHandle( 'film_tags' );
    }

    public static function getTopicTagsField()
    {
        return self::getCraftFieldHandleByApiHandle( 'topic_tags' );
    }

    public static function getExpirationStatusField()
    {
        return self::getCraftFieldHandleByApiHandle( 'expiration_status' );
    }

    public static function getApiField( $apiFieldHandle, $settingName = 'apiColumnFields' )
    {
        return self::getCraftFieldHandleByApiHandle( $apiFieldHandle, $settingName );
    }

    public static function getApiFieldRule( $apiFieldHandle, $settingName = 'apiColumnFields' )
    {
        return self::getApiRuleByApiHandle( $apiFieldHandle, $settingName );
    }

    public static function getSeasonField()
    {
        return self::getCraftFieldHandleByApiHandle( 'season' );
    }

    public static function getEpisodeField()
    {
        return self::getCraftFieldHandleByApiHandle( 'episode' );
    }

    public static function getShowLastSyncedField()
    {
        return self::getCraftFieldHandleByApiHandle( 'show_last_synced', 'showApiColumnFields' );
    }

    public static function getShowMediaManagerIdField()
    {
        return self::getCraftFieldHandleByApiHandle( 'show_media_manager_id', 'showApiColumnFields' );
    }

    public static function getShowImagesField()
    {
        return self::getCraftFieldHandleByApiHandle( 'show_images', 'showApiColumnFields' );
    }

    public static function getTagGroupIdByCraftFieldHandle( $craftFieldHandle )
    {
        $field = Craft::$app->fields->getFieldByHandle( $craftFieldHandle );

        if( !$field || !$field->source ) {
            return null;
        }

        $tagGroup = Craft::$app->tags->getTagGroupByUid( str_replace( 'taggroup:', '', $field->source ) );

        if( !$tagGroup ) {
            return null;
        }

        return $tagGroup->id;
    }

    public static function hasSiteBeenEntrified(): bool
    {
        return (bool)(self::getBlogTagSectionInfo() || self::getDateTagSectionInfo() || self::getFilmTagSectionInfo() || self::getSiteTagSectionInfo() || self::getThemeTagSectionInfo() || self::getTopicTagSectionInfo());
    }

    public static function apiChangelog( $since = null, $id = null, $type = null )
    {
        $client          = Craft::createGuzzleClient();
        $changelogUrl    = SettingsHelper::get( 'apiBaseUrl' ) . 'changelog/';
        $changelogParams = [];

        if( $since ) {
            $changelogParams[ 'since' ] = $since;
        }

        if( $id ) {
            $changelogParams[ 'id' ] = $id;
        }

        if( $type ) {
            $changelogParams[ 'type' ] = $type;
        }

        if( $changelogParams ) {
            $changelogUrl = $changelogUrl . '?' . http_build_query( $changelogParams );
        }

        $pbsApiUsername = '';
        $pbsApiPassword = '';

        if( method_exists( 'Craft', 'parseEnv' ) ) {

            $pbsApiUsername = Craft::parseEnv( '$PBS_API_BASIC_AUTH_USERNAME' );
            $pbsApiPassword = Craft::parseEnv( '$PBS_API_BASIC_AUTH_PASSWORD' );
        }

        if( method_exists( 'App', 'parseEnv' ) ) {

            $pbsApiUsername = App::parseEnv( '$PBS_API_BASIC_AUTH_USERNAME' );
            $pbsApiPassword = App::parseEnv( '$PBS_API_BASIC_AUTH_PASSWORD' );
        }

        $response = $client->get( $changelogUrl, [
            'auth' => [
                $pbsApiUsername,
                $pbsApiPassword,
            ]
        ]);

        return json_decode( $response->getBody() );
    }


    // Private Static Methods
    // =========================================================================

    private static function getCraftFieldHandleByApiHandle( $fieldApiHandle, $settingName = 'apiColumnFields' )
    {
        $fieldsToSearch = SettingsHelper::get( $settingName );
        $fieldHandles   = array_column( $fieldsToSearch, ConstantAbstract::API_COLUMN_FIELD_API_INDEX );
        $foundIndex     = array_search( $fieldApiHandle, $fieldHandles );

        if( $foundIndex === false ) {
            return null;
        }

        $foundField    = $fieldsToSearch[ $foundIndex ];
        $existingField = $foundField[ ConstantAbstract::API_COLUMN_EXISTING_FIELD_INDEX ];
        $fieldHandle   = ( $foundField[ ConstantAbstract::API_COLUMN_FIELD_HANDLE_INDEX ] ) ? $foundField[ ConstantAbstract::API_COLUMN_FIELD_HANDLE_INDEX ] : false;

        if( $existingField ) {
            return $existingField;
        }

        return $fieldHandle;
    }

    private static function getApiRuleByApiHandle( $fieldApiHandle, $settingName = 'apiColumnFields' )
    {
        $fieldsToSearch = SettingsHelper::get( $settingName );
        $fieldHandles   = array_column( $fieldsToSearch, ConstantAbstract::API_COLUMN_FIELD_API_INDEX );
        $foundIndex     = array_search( $fieldApiHandle, $fieldHandles );

        if( $foundIndex === false ) {
            return null;
        }

        $foundField = $fieldsToSearch[ $foundIndex ];
        $fieldRule  = ( isset( $foundField[ ConstantAbstract::API_COLUMN_FIELD_RULE_INDEX ] ) ) ? $foundField[ ConstantAbstract::API_COLUMN_FIELD_RULE_INDEX ] : false;

        return $fieldRule;
    }
}
