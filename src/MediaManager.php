<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager;

use Craft;
use Exception;
use yii\base\Event;
use Psr\Log\LogLevel;
use craft\base\Plugin;
use craft\web\UrlManager;
use craft\web\Application;
use craft\services\Plugins;
use craft\helpers\UrlHelper;
use craft\log\MonologTarget;
use craft\models\FieldGroup;
use craft\events\PluginEvent;
use Monolog\Formatter\LineFormatter;
use craft\events\RegisterUrlRulesEvent;

use pbsdigital\mediamanager\jobs\ShowSync;
use craft\web\twig\variables\CraftVariable;
use pbsdigital\mediamanager\helpers\SetupHelper;
use pbsdigital\mediamanager\models\SettingsModel;
use pbsdigital\mediamanager\base\ConstantAbstract;
use pbsdigital\mediamanager\helpers\SettingsHelper;
use pbsdigital\mediamanager\behaviors\MediaBehavior;
use pbsdigital\mediamanager\helpers\DependencyHelper;
use pbsdigital\mediamanager\services\Api as ApiService;
use pbsdigital\mediamanager\services\Show as ShowService;
use pbsdigital\mediamanager\services\ScheduledSyncService;
use pbsdigital\mediamanager\variables\MediaManagerVariable;
use pbsdigital\mediamanager\services\OldSettings as OldSettingsService;
use pbsdigital\mediamanager\helpers\aftersavesettings\OldSettingsHelper;
use pbsdigital\mediamanager\helpers\aftersavesettings\ApiColumnFieldsHelper;
use pbsdigital\mediamanager\helpers\aftersavesettings\ShowApiColumnFieldsHelper;

/**
 * Class MediaManager
 *
 * @package pbsdigital\mediamanager
 * @property ApiService $api
 * @property ShowService $show
 * @property OldSettingsService $oldsettings
 * @property ScheduledSyncService $scheduledSync
 * @method SettingsModel getSettings()
 */
class MediaManager extends Plugin
{
    // Static Properties
    // =========================================================================
    public static $plugin;


    // Public Properties
    // =========================================================================
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public string $schemaVersion = '1.0.1';
    // Public Methods
    // =========================================================================

    public static function t($message, $params = [], $language = null)
    {
        return Craft::t('mediamanager', $message, $params, $language);
    }

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->registerRoutes();
        $this->registerInstallationEvents();
        $this->registerBehaviors();
        $this->registerPluginServices();

        Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
            'name' => 'media-manager',
            'categories' => ['pbsdigital\mediamanager\*'],
            'level' => LogLevel::INFO,
            'logContext' => false,
            'allowLineBreaks' => false,
            'formatter' => new LineFormatter(
                format: "%datetime% [%level_name%] from %extra.yii_category%: %message%\n",
                dateFormat: 'Y-m-d H:i:s',
            ),
        ]);

        // ? ERROR LOG FILE
        Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
            'name' => 'media-manager-errors',
            'categories' => ['pbsdigital\mediamanager\*'],
            'level' => LogLevel::ERROR,
            'logContext' => true,
            'allowLineBreaks' => true,
            'formatter' => new LineFormatter(
                format: "%datetime% [%level_name%] from %extra.yii_category%: %message%\n",
                dateFormat: 'Y-m-d H:i:s',
            ),
        ]);
    }

    public function beforeInstall(): void
    {
        if (version_compare(Craft::$app->getInfo()->version, '4.0', '<')) {
            throw new Exception('Media Manager 4 requires Craft CMS 4.0+ in order to run.');
        }
    }

    public function afterInstall(): void
    {
        //	create Media Manager field group
        $fieldgroup = \craft\records\FieldGroup::find()->where(['name' => ConstantAbstract::DEFAULT_FIELD_GROUP])->one();

        if (!$fieldgroup) {
            $fieldgroup = new FieldGroup();
            $fieldgroup->name = ConstantAbstract::DEFAULT_FIELD_GROUP;
            Craft::$app->getFields()->saveGroup($fieldgroup);
        }

        foreach (ConstantAbstract::API_COLUMN_FIELDS as $field) {
            $fieldExists = Craft::$app->getFields()->getFieldByHandle($field[3]);

            if (!$fieldExists) {
                $newField = Craft::$app->getFields()->createField([
                    'type' => $field[4],
                    'name' => $field[2],
                    'handle' => $field[3],
                    'groupId' => $fieldgroup->id
                ]);
            }
        }

        foreach (ConstantAbstract::SHOW_API_COLUMN_FIELDS as $field) {
            $fieldExists = Craft::$app->getFields()->getFieldByHandle($field[3]);

            if (!$fieldExists) {
                $newField = Craft::$app->getFields()->createField([
                    'type' => $field[4],
                    'name' => $field[2],
                    'handle' => $field[3],
                    'groupId' => $fieldgroup->id
                ]);
            }
        }
    }

    public function afterSaveSettings(): void
    {
        ApiColumnFieldsHelper::process();
        ShowApiColumnFieldsHelper::process();
        OldSettingsHelper::process();
    }

    public function getSettingsResponse(): mixed
    {
        // This way we can have more flexibility on displaying settings template
        return Craft::$app->controller->renderTemplate('mediamanager/settings', SettingsHelper::templateVariables());
    }

    public function getCpNavItem(): array
    {
        $navigation = parent::getCpNavItem();

        $navigation['label'] = self::t('Media Manager');

        $navigation['subnav']['shows'] = [
            'label' => self::t('Shows'),
            'url' => 'mediamanager/shows'
        ];

        $navigation['subnav']['synchronize'] = [
            'label' => self::t('Synchronize'),
            'url' => 'mediamanager/synchronize'
        ];

        $navigation['subnav']['scheduler'] = [
            'label' => self::t('Scheduler'),
            'url' => 'mediamanager/scheduler'
        ];

        if (SettingsHelper::get('mediaSection')) {

            $navigation['subnav']['entries'] = [
                'label' => self::t('Entries'),
                'url' => 'mediamanager/entries'
            ];
        }

        $navigation['subnav']['clean'] = [
            'label' => self::t('Clean Garbage Entries'),
            'url' => 'mediamanager/clean'
        ];

        $navigation['subnav']['stale-media'] = [
            'label' => self::t('Manage Stale Media'),
            'url' => 'mediamanager/stale-media'
        ];

        $navigation['subnav']['settings'] = [
            'label' => self::t('Settings'),
            'url' => UrlHelper::cpUrl('settings/plugins/mediamanager')
        ];

        return $navigation;
    }


    // Private Methods
    // =========================================================================

    private function registerRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {

                if (SettingsHelper::get('mediaSection')) {
                    $event->rules['mediamanager/entries'] = 'mediamanager/main/entries';
                }

                $event->rules['mediamanager/shows'] = 'mediamanager/show';
                $event->rules['mediamanager/shows/<entryId:\d+>'] = 'mediamanager/show';

                $event->rules['mediamanager/synchronize'] = 'mediamanager/synchronize';
                $event->rules['mediamanager/synchronize/<entryId:\d+>'] = 'mediamanager/synchronize';
                $event->rules['mediamanager/synchronize/all'] = 'mediamanager/synchronize/all';
                $event->rules['mediamanager/synchronize/single'] = 'mediamanager/synchronize/single';
                $event->rules['mediamanager/synchronize/show-entries'] = 'mediamanager/synchronize/show-entries';
                $event->rules['mediamanager/synchronize/synchronize-show'] = 'mediamanager/synchronize/synchronize-show';
                $event->rules['mediamanager/synchronize/synchronize-single'] = 'mediamanager/synchronize/synchronize-single';
                $event->rules['mediamanager/synchronize/synchronize-all'] = 'mediamanager/synchronize/synchronize-all';
                $event->rules['mediamanager/synchronize/synchronize-show-entries'] = 'mediamanager/synchronize/synchronize-show-entries';
                $event->rules['mediamanager/scheduler'] = 'mediamanager/scheduled-sync/index';
                $event->rules['mediamanager/scheduler/<scheduledSyncId:\d+>'] = 'mediamanager/scheduled-sync/edit';
                $event->rules['mediamanager/scheduler/new'] = 'mediamanager/scheduled-sync/edit';

                $event->rules['mediamanager/clean'] = 'mediamanager/synchronize/clean';
            }
        );
    }

    private function registerInstallationEvents()
    {
        // Before plugin installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_BEFORE_INSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this) {
                    // Make sure dependencies installed
                    DependencyHelper::installDependencies();
                }
            }
        );

        // After plugin installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this) {
                    SetupHelper::registerRequiredComponents();
                }
            }
        );


        // Before plugin uninstalled
        Event::on(
            Plugins::class,
            Plugins::EVENT_BEFORE_UNINSTALL_PLUGIN,
            function(PluginEvent $event) {
                if ($event->plugin === $this) {
                    SetupHelper::unregisterRequiredComponents();
                }
            }
        );
    }

    private function registerBehaviors()
    {
        // Attach behaviors
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $e) {
                $variable = $e->sender;
                $variable->attachBehaviors([
                    MediaBehavior::class,
                ]);
                $variable->set('mediamanager', MediaManagerVariable::class);
            }
        );

        Event::on(
            Application::class,
            Application::EVENT_INIT,
            function(Event $event) {
                $scheduledSyncService = MediaManager::$plugin->scheduledSync;
                $pushableSyncs = $scheduledSyncService->getPushableSyncs();

                foreach ($pushableSyncs as $pushableSync) {
                    Craft::$app->getQueue()->push(new ShowSync([
                        'showId' => $pushableSync->showId,
                        'regenerateThumbnails' => $pushableSync->regenerateThumbnail,
                        'scheduledSync' => $pushableSync->id
                    ]));

                    $pushableSync->processed = 1;
                    $scheduledSyncService->saveScheduledSync($pushableSync);
                }
            }
        );
    }

    private function registerPluginServices()
    {
        // Register service
        $this->setComponents([
            'show' => ShowService::class,
            'api' => ApiService::class,
            'oldsettings' => OldSettingsService::class,
            'scheduledSync' => ScheduledSyncService::class,
        ]);
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): SettingsModel
    {
        return new SettingsModel();
    }
}
