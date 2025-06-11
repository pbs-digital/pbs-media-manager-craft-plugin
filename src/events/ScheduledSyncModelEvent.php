<?php

namespace pbsdigital\mediamanager\events;

use craft\events\CancelableEvent;
use pbsdigital\mediamanager\models\ScheduledSyncModel;

class ScheduledSyncModelEvent extends CancelableEvent
{
	// Properties
	// =========================================================================

	/**
	 * @var ScheduledSyncModel|null
	 */
	public $scheduledSync;

	/**
	 * @var bool
	 */
	public $isNew = false;
}
