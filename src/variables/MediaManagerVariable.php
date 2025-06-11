<?php

namespace pbsdigital\mediamanager\variables;

use Craft;
use pbsdigital\mediamanager\MediaManager;
use pbsdigital\mediamanager\records\Show as ShowRecord;

class MediaManagerVariable
{
		public function getShow($showId): ShowRecord
		{
				return ShowRecord::findOne($showId);
		}

}
