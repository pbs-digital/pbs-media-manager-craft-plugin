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

    public function rules(): array
    {
        return [
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
            ]
        ];
    }
}
