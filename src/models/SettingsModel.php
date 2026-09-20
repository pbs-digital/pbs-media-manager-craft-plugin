<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\models;

use Craft;
use craft\base\Model;

use pbsdigital\mediamanager\base\ConstantAbstract;
use pbsdigital\mediamanager\validators\BasicAuthValidator;
use pbsdigital\mediamanager\validators\CronExpressionValidator;
use pbsdigital\mediamanager\validators\ApiColumnFieldsValidator;
use pbsdigital\mediamanager\validators\ShowApiColumnFieldsValidator;

class SettingsModel extends Model
{
    // Public Properties
    // =========================================================================

    public $mediaSection;
    public $mediaUsedBySection;
    public $mediaAssetVolume;
    public $mediaFieldGroup;
    public $showSection;

    public $blogTagsSection;
    public $dateTagsSection;
    public $filmTagsSection;
    public $siteTagsSection;
    public $themeTagsSection;
    public $topicTagsSection;
    // public $assetTypeTagsSection;

    /**
     * Which of ConstantAbstract::TOGGLEABLE_SECTIONS this site actually uses,
     * keyed by setting handle. Missing keys fall back to isSectionEnabled().
     */
    public $enabledSections = [];

    public $apiCraftUser        = '';
    public $apiBaseUrl          = '';

    public $apiColumnFields     = ConstantAbstract::API_COLUMN_FIELDS;
    public $showApiColumnFields = ConstantAbstract::SHOW_API_COLUMN_FIELDS;

    public $fieldLayout         = ConstantAbstract::DEFAULT_FIELD_LAYOUT;
    public $showFieldLayout     = ConstantAbstract::DEFAULT_SHOW_FIELD_LAYOUT;

    public $syncSchedule        = ConstantAbstract::SYNC_SCHEDULE;
    public $syncCustomSchedule  = ConstantAbstract::SYNC_CUSTOM_SCHEDULE;
    public $syncPingChangelog   = ConstantAbstract::SYNC_PING_CHANGELOG;
    public $defaultRichtextField = ConstantAbstract::DEFAULT_RICHTEXT_TYPE;

    // Public Methods
    // =========================================================================

    /**
     * Whether the given toggleable section is in use on this site.
     *
     * Installs that predate the toggles have no `enabledSections` value stored,
     * so fall back to whether the section was already configured. That keeps
     * existing setups validating exactly as they did before.
     */
    public function isSectionEnabled( string $settingKey ): bool
    {
        if( is_array( $this->enabledSections ) && array_key_exists( $settingKey, $this->enabledSections ) ) {
            return (bool) $this->enabledSections[ $settingKey ];
        }

        return !empty( $this->{ $settingKey } );
    }

    public function rules(): array
    {
        $rules = [
            [
                ConstantAbstract::REQUIRED_SETTINGS,
                'required'
            ],
            [
                [ 'apiCraftUser' ],
                'required'
            ],
            [
                [ 'apiBaseUrl' ],
                BasicAuthValidator::class
            ],
            [
                [ 'apiColumnFields' ],
                ApiColumnFieldsValidator::class
            ],
            [
                [ 'apiColumnFields' ],
                ApiColumnFieldsValidator::class
            ],
            [
                [ 'showApiColumnFields' ],
                ShowApiColumnFieldsValidator::class
            ],
            [
                [ 'syncCustomSchedule' ],
                CronExpressionValidator::class
            ],
            [
                [ 'enabledSections' ],
                'safe'
            ]
        ];

        // Only require a section select when the site says it uses that section.
        foreach( array_keys( ConstantAbstract::TOGGLEABLE_SECTIONS ) as $settingKey ) {
            $rules[] = [
                [ $settingKey ],
                'required',
                'when' => function( $model ) use ( $settingKey ) {
                    return $model->isSectionEnabled( $settingKey );
                }
            ];
        }

        return $rules;
    }
}
