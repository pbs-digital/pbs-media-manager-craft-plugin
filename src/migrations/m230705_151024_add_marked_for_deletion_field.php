<?php

namespace pbsdigital\mediamanager\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\ArrayHelper;
use pbsdigital\mediamanager\helpers\SettingsHelper;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * m230705_151019_add_marked_for_deletion_field migration.
 */
class m230705_151024_add_marked_for_deletion_field extends Migration
{
	/**
	 * @inheritdoc
	 * @throws InvalidConfigException
	 * @throws Exception
	 * @throws \Throwable
	 */
    public function safeUp()
    {
	    $schemaVersion = Craft::$app->projectConfig->get(
		    'plugins.mediamanager.schemaVersion',
		    true
	    );

	    if (version_compare($schemaVersion, '1.0.1', '<')) {
		    $fieldService = Craft::$app->getFields();
		    $field = $fieldService->getFieldByHandle('markedForDeletion');

		    if(!$field) {
			    // we know this field will exist
			    $groupToUse = $fieldService->getFieldByHandle('lastSynced')->groupId;

			    $field = Craft::$app->getFields()->createField([
				    'type' => 'craft\fields\Lightswitch',
				    'name' => 'Marked for Deletion',
				    'handle' => 'markedForDeletion',
				    'groupId' => $groupToUse,
				    'searchable' => true,
			    ]);

			    Craft::$app->getFields()->saveField($field);
		    }

		    $mediaSection = Craft::$app->getSections()->getSectionByHandle(SettingsHelper::get( 'mediaSection' ));

		    if($mediaSection) {
			    $entryType = $mediaSection->getEntryTypes()[0];
			    $fieldLayout = $entryType->getFieldLayout();
			    $tabs = $fieldLayout->getTabs();
					$fieldAdded = false;

					$newFieldLayoutElement = [
						'type' => 'craft\fields\Lightswitch',
						'fieldUid' => $field->uid,
						'enabled' => true,
						'required' => false,
						'sortOrder' => 0,
					];

			    foreach($tabs as $tab) {
				    if ($tab->name === 'API'){
							$elements = $tab->elements;
							foreach($elements as $element) {
								if($element->uid === $field->uid) {
									$fieldAdded = true;
								}
							}
							if(!$fieldAdded){
								$tab->setElements(array_merge($elements, [$newFieldLayoutElement]));
					    }
				    }
			    }

					if(!$fieldAdded) {
						$elements = $tabs[0]->getElements();
						$tabs[0]->setElements(array_merge($elements, [$newFieldLayoutElement]));
					}

					$fieldLayout->setTabs($tabs);
					Craft::$app->fields->saveLayout($fieldLayout);
			    $entryType->setFieldLayout($fieldLayout);
			    Craft::$app->getSections()->saveEntryType($entryType);
		    }
	    }

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m230705_151021_add_marked_for_deletion_field cannot be reverted.\n";
        return false;
    }
}
