<?php

namespace pbsdigital\mediamanager\migrations;

use Craft;
use craft\db\Migration;
use pbsdigital\mediamanager\base\ConstantAbstract;

/**
 * m261118_000000_add_recurrence_to_scheduled_sync migration.
 */
class m261118_000000_add_recurrence_to_scheduled_sync extends Migration
{
    private $scheduledSyncTable = ConstantAbstract::MEDIAMANAGER_SCHEDULED_SYNC_TABLE;

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema($this->scheduledSyncTable);

        if (!$tableSchema) {
            echo "No scheduled sync table found.\n";
            return false;
        }

        echo "Adding recurrence columns to scheduled sync table…\n";

        if (!$tableSchema->getColumn('recurrenceInterval')) {
            $this->addColumn($this->scheduledSyncTable, 'recurrenceInterval', $this->string()->notNull()->defaultValue('none'));
        }

        if (!$tableSchema->getColumn('recurrenceCount')) {
            $this->addColumn($this->scheduledSyncTable, 'recurrenceCount', $this->integer()->null());
        }

        if (!$tableSchema->getColumn('occurrencesRun')) {
            $this->addColumn($this->scheduledSyncTable, 'occurrencesRun', $this->integer()->notNull()->defaultValue(0));
        }

        Craft::$app->db->schema->refresh();

        echo "Finished!\n";

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m261118_000000_add_recurrence_to_scheduled_sync cannot be reverted.\n";
        return false;
    }
}
