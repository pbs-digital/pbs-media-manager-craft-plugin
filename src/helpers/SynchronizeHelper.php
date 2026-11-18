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
        $section = Craft::$app->entries->getSectionByHandle( SettingsHelper::get( 'mediaSection' ) );

        if( !$section ) {
            return null;
        }

        return $section->id;
    }

    public static function getSectionTypeId(): ?int
    {
        $sectionId  = self::getSectionId();
        $entryTypes = Craft::$app->entries->getEntryTypesBySectionId( $sectionId );

        if( !is_array( $entryTypes ) && !$entryTypes[ 0 ] ) {
            return null;
        }

        return $entryTypes[ 0 ]->id;
    }

    public static function getShowSectionId(): ?int
    {
        if( !SettingsHelper::sectionEnabled( 'showSection' ) ) {
            return null;
        }

        $section = Craft::$app->entries->getSectionByHandle( SettingsHelper::get( 'showSection' ) );

        if( !$section ) {
            return null;
        }

        return $section->id;
    }

    public static function getBlogTagSectionInfo(): ?array
    {
        if( !SettingsHelper::sectionEnabled( 'blogTagsSection' ) || !SettingsHelper::get( 'blogTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->entries->getSectionByHandle( SettingsHelper::get( 'blogTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->entries->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getDateTagSectionInfo(): ?array
    {
        if( !SettingsHelper::sectionEnabled( 'dateTagsSection' ) || !SettingsHelper::get( 'dateTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->entries->getSectionByHandle( SettingsHelper::get( 'dateTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->entries->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getFilmTagSectionInfo(): ?array
    {
        if( !SettingsHelper::sectionEnabled( 'filmTagsSection' ) || !SettingsHelper::get( 'filmTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->entries->getSectionByHandle( SettingsHelper::get( 'filmTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->entries->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getSiteTagSectionInfo(): ?array
    {

        if( !SettingsHelper::sectionEnabled( 'siteTagsSection' ) || !SettingsHelper::get( 'siteTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->entries->getSectionByHandle( SettingsHelper::get( 'siteTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->entries->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getThemeTagSectionInfo(): ?array
    {
        if( !SettingsHelper::sectionEnabled( 'themeTagsSection' ) || !SettingsHelper::get( 'themeTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->entries->getSectionByHandle( SettingsHelper::get( 'themeTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->entries->getEntryTypesBySectionId( $sectionId );

        $res = ['id' => $sectionId];

        if($entryTypeId){
            $res['entryTypeId'] = $entryTypeId[0]->id;
        }
        return $res;
    }

    public static function getTopicTagSectionInfo(): ?array
    {
        if( !SettingsHelper::sectionEnabled( 'topicTagsSection' ) || !SettingsHelper::get( 'topicTagsSection' ) ) {
            return null;
        }

        $section = Craft::$app->entries->getSectionByHandle( SettingsHelper::get( 'topicTagsSection' ) );

        if( !$section ) {
            return null;
        }

        $sectionId = $section->id;
        $entryTypeId = Craft::$app->entries->getEntryTypesBySectionId( $sectionId );

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

    //     $section = Craft::$app->entries->getSectionByHandle( SettingsHelper::get( 'assetTypeTagsSection' ) );

    //     if( !$section ) {
    //         return null;
    //     }

    //     $sectionId = $section->id;
    //     $entryTypeId = Craft::$app->entries->getEntryTypesBySectionId( $sectionId );

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

        $entryTypes = Craft::$app->entries->getEntryTypesBySectionId( $sectionId );

        if( !is_array( $entryTypes ) && !$entryTypes[ 0 ] ) {
            return null;
        }

        return $entryTypes[ 0 ]->id;
    }

    public static function getAuthorId()
    {
        $user = self::getAuthor();

        if( !$user ) {
            return null;
        }

        return $user->id;
    }

    public static function getAuthorUsername()
    {
        $user = self::getAuthor();

        if( !$user ) {
            return '';
        }

        return (string) $user->username;
    }

    /**
     * The Craft user selected for API syncs.
     *
     * The setting stores an element ID now, but installs saved before the user
     * picker stored a username, so both are resolved.
     */
    public static function getAuthor()
    {
        $value = SettingsHelper::get( 'apiCraftUser' );

        if( !$value ) {
            return null;
        }

        return is_numeric( $value )
            ? Craft::$app->users->getUserById( (int) $value )
            : Craft::$app->users->getUserByUsernameOrEmail( $value );
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

    public const PLAYER_PARAM_KEYS = ['muted', 'autoplay', 'topbar', 'endscreen'];

    /**
     * Whitelist keys and values of player params ('true'/'false' only). Accepts an array or JSON string.
     */
    public static function sanitizePlayerParams( mixed $params ): array
    {
        if( is_string( $params ) ) {
            $params = json_decode( $params, true );
        }

        if( !is_array( $params ) ) {
            return [];
        }

        $clean = [];

        foreach( self::PLAYER_PARAM_KEYS as $key ) {
            if( isset( $params[ $key ] ) && is_string( $params[ $key ] ) && in_array( $params[ $key ], [ 'true', 'false' ], true ) ) {
                $clean[ $key ] = $params[ $key ];
            }
        }

        return $clean;
    }

    /**
     * Apply player URL params to the iframe src in $playerCode.
     * Precedence: API value < existing entry value (keys not overridden) < explicit overrides.
     */
    public static function applyPlayerParams( string $playerCode, array $params, ?string $existingPlayerCode = null ): string
    {
        $overrides = self::sanitizePlayerParams( $params );
        $srcPattern = '/(<iframe\b(?:[^>]*?\s)?src\s*=\s*)(["\'])(.*?)\2/is';

        if( !preg_match( $srcPattern, $playerCode, $match, PREG_OFFSET_CAPTURE ) ) {
            return $playerCode;
        }

        $rawSrc = $match[3][0];
        $srcOffset = $match[3][1];

        $existing = [];
        if( $existingPlayerCode !== null && preg_match( $srcPattern, $existingPlayerCode, $existingMatch ) ) {
            $existing = self::parsePlayerSrc( $existingMatch[3] )['values'];
        }

        $parsed = self::parsePlayerSrc( $rawSrc );
        $pairs = $parsed['pairs'];
        $changed = false;

        foreach( self::PLAYER_PARAM_KEYS as $key ) {
            if( isset( $overrides[ $key ] ) ) {
                $value = $overrides[ $key ];
            } elseif( array_key_exists( $key, $existing ) ) {
                $value = $existing[ $key ];
            } else {
                continue;
            }

            $found = false;
            foreach( $pairs as $i => $pair ) {
                if( rawurldecode( $pair[0] ) !== $key ) {
                    continue;
                }

                if( $found ) {
                    // drop duplicate occurrences
                    unset( $pairs[ $i ] );
                    $changed = true;
                    continue;
                }

                $found = true;
                if( $pair[1] !== $value ) {
                    $pairs[ $i ] = [ $pair[0], $value ];
                    $changed = true;
                }
            }

            if( !$found ) {
                $pairs[] = [ $key, $value ];
                $changed = true;
            }
        }

        if( !$changed ) {
            return $playerCode;
        }

        $glue = $parsed['encoded'] ? '&amp;' : '&';
        $query = implode( $glue, array_map( fn( $p ) => $p[0] . '=' . $p[1], $pairs ) );
        $newSrc = $parsed['base'] . ( $query !== '' ? '?' . $query : '' ) . $parsed['fragment'];

        return substr( $playerCode, 0, $srcOffset ) . $newSrc . substr( $playerCode, $srcOffset + strlen( $rawSrc ) );
    }

    /**
     * Split a raw src attribute value into base, query pairs (raw, order preserved) and fragment.
     * 'values' maps whitelisted keys to their last value.
     */
    private static function parsePlayerSrc( string $rawSrc ): array
    {
        $encoded = str_contains( $rawSrc, '&amp;' );
        $src = $encoded ? str_replace( '&amp;', '&', $rawSrc ) : $rawSrc;

        $fragment = '';
        if( ( $hashPos = strpos( $src, '#' ) ) !== false ) {
            $fragment = substr( $src, $hashPos );
            $src = substr( $src, 0, $hashPos );
        }

        $query = '';
        $base = $src;
        if( ( $qPos = strpos( $src, '?' ) ) !== false ) {
            $base = substr( $src, 0, $qPos );
            $query = substr( $src, $qPos + 1 );
        }

        $pairs = [];
        $values = [];
        foreach( $query === '' ? [] : explode( '&', $query ) as $part ) {
            if( $part === '' ) {
                continue;
            }
            $kv = explode( '=', $part, 2 );
            $pairs[] = [ $kv[0], $kv[1] ?? '' ];
            $name = rawurldecode( $kv[0] );
            if( in_array( $name, self::PLAYER_PARAM_KEYS, true ) ) {
                $values[ $name ] = $kv[1] ?? '';
            }
        }

        return [ 'encoded' => $encoded, 'base' => $base, 'pairs' => $pairs, 'values' => $values, 'fragment' => $fragment ];
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
        $fieldHandles   = array_combine( array_keys( $fieldsToSearch ), array_column( $fieldsToSearch, ConstantAbstract::API_COLUMN_FIELD_API_INDEX ) );
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
        $fieldHandles   = array_combine( array_keys( $fieldsToSearch ), array_column( $fieldsToSearch, ConstantAbstract::API_COLUMN_FIELD_API_INDEX ) );
        $foundIndex     = array_search( $fieldApiHandle, $fieldHandles );

        if( $foundIndex === false ) {
            return null;
        }

        $foundField = $fieldsToSearch[ $foundIndex ];
        $fieldRule  = ( isset( $foundField[ ConstantAbstract::API_COLUMN_FIELD_RULE_INDEX ] ) ) ? $foundField[ ConstantAbstract::API_COLUMN_FIELD_RULE_INDEX ] : false;

        return $fieldRule;
    }
}
