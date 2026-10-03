<?php
/**
 * Media Manager
 *
 * @package       Media Manager
 * @author        PBS Digital
 * @link          https://github.com/pbs-digital/pbs-media-manager-craft-plugin
 */

namespace pbsdigital\mediamanager\migrations;

use Craft;
use craft\db\Migration;
use craft\config\DbConfig;

use pbsdigital\mediamanager\MediaManager;
use pbsdigital\mediamanager\base\ConstantAbstract;

class Install extends Migration
{
    // Private Properties
    // =========================================================================

    private $mediaManagerShowTable        = ConstantAbstract::MEDIAMANAGER_SHOW_TABLE;
    private $mediaManagerOldSettingsTable = ConstantAbstract::MEDIAMANAGER_OLD_SETTINGS_TABLE;
    private $mediaManagerScheduledSyncTable = ConstantAbstract::MEDIAMANAGER_SCHEDULED_SYNC_TABLE;


    // Public Properties
    // =========================================================================

    public $driver;


    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;

        $this->createMediaManagerShowTable();
        $this->createMediaManagerOldSettingsTable();
        $this->createMediaManagerScheduledSyncTable();

        return true;
    }

    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;

        // Dropped before the show table, which it has a foreign key on
        $this->dropTableIfExists( $this->mediaManagerScheduledSyncTable );
        $this->dropTableIfExists( $this->mediaManagerShowTable );
        $this->dropTableIfExists( $this->mediaManagerOldSettingsTable );

        return true;
    }

    // Just need to make sure PostgreSQL using JSON not JSONB
    public function json( $length = null ) {
        return Craft::$app->db->schema->createColumnSchemaBuilder( " json", $length );
    }


    // Protected Methods
    // =========================================================================

    protected function createMediaManagerShowTable()
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema( $this->mediaManagerShowTable );

        if( $tableSchema === null ) {

            $this->createTable(
                $this->mediaManagerShowTable,
                [
                    'id'          => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid'         => $this->uid(),
                    'name'        => $this->string( 255 )->notNull()->defaultValue( '' ),
                    'apiKey'      => $this->string( 255 )->notNull()->defaultValue( '' ),
                    'siteId'      => $this->string( 255 )->notNull()->defaultValue( '' ),
                    'lastActive'  => $this->boolean()->notNull()->defaultValue( false ),
                ]
            );

            $this->createIndex(
                $this->db->getIndexName(
                    $this->mediaManagerShowTable,
                    'id',
                    true
                ),
                $this->mediaManagerShowTable, 'id', true
            );

            Craft::$app->db->schema->refresh();
        }
    }

    protected function createMediaManagerOldSettingsTable()
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema( $this->mediaManagerOldSettingsTable );

        if( $tableSchema === null ) {

            $this->createTable(
                $this->mediaManagerOldSettingsTable,
                [
                    'id'           => $this->primaryKey(),
                    'dateCreated'  => $this->dateTime()->notNull(),
                    'dateUpdated'  => $this->dateTime()->notNull(),
                    'uid'          => $this->uid(),
                    'settingName'  => $this->string( 255 )->notNull()->defaultValue( '' ),
                    'settingValue' => $this->json(),
                ]
            );

            $this->createIndex(
                $this->db->getIndexName(
                    $this->mediaManagerOldSettingsTable,
                    'id',
                    true
                ),
                $this->mediaManagerOldSettingsTable, 'id', true
            );

            Craft::$app->db->schema->refresh();
        }
    }

    protected function createMediaManagerScheduledSyncTable()
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema( $this->mediaManagerScheduledSyncTable );

        if( $tableSchema === null ) {

            $this->createTable(
                $this->mediaManagerScheduledSyncTable,
                [
                    'id'                  => $this->primaryKey(),
                    'name'                => $this->text(),
                    'description'         => $this->text(),
                    'showId'              => $this->integer()->notNull(),
                    'scheduleDate'        => $this->dateTime()->notNull(),
                    'processed'           => $this->boolean()->defaultValue( false ),
                    'mediaFieldsToSync'   => $this->text(),
                    'showFieldsToSync'    => $this->text(),
                    'regenerateThumbnail' => $this->boolean(),
                    'dateCreated'         => $this->dateTime()->notNull(),
                    'dateUpdated'         => $this->dateTime()->notNull(),
                    'uid'                 => $this->uid(),
                ]
            );

            $this->addForeignKey(
                $this->db->getForeignKeyName(
                    $this->mediaManagerScheduledSyncTable,
                    'showId'
                ),
                $this->mediaManagerScheduledSyncTable, 'showId',
                $this->mediaManagerShowTable, 'id',
                'CASCADE', 'CASCADE'
            );

            Craft::$app->db->schema->refresh();
        }
    }
}
