<?php

namespace pbsdigital\mediamanager\models;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use craft\validators\DateTimeValidator;
use DateTime;
use pbsdigital\mediamanager\models\BaseModel;

class ScheduledSyncModel extends BaseModel
{
	// Constants
	// =========================================================================

	const RECURRENCE_NONE = 'none';
	const RECURRENCE_DAILY = 'daily';
	const RECURRENCE_WEEKLY = 'weekly';
	const RECURRENCE_MONTHLY = 'monthly';

	const RECURRENCE_INTERVALS = [
		self::RECURRENCE_NONE,
		self::RECURRENCE_DAILY,
		self::RECURRENCE_WEEKLY,
		self::RECURRENCE_MONTHLY,
	];

	// Public Properties
	// =========================================================================

	/**
	 * @var int|null ID
	 */
	public $id;

	/**
	 * @var string Schedule Name
	 */
	public $name;

	/**
	 * @var string  Schedule Description
	 */
	public $description;

	/**
	 * @var int Show ID
	 */
	public $showId;

	/**
	 * @var string
	 */
	public $mediaFieldsToSync;

	/**
	 * @var string
	 */
	public $showFieldsToSync;

	/**
	 * @var DateTime Schedule Date
	 */
	public $scheduleDate;

	/**
	 * @var bool Processed
	 */
	public $processed;

	/**
	 * @var bool
	 */
	public $regenerateThumbnail;

	/**
	 * @var string Recurrence interval: none, daily, weekly or monthly
	 */
	public $recurrenceInterval = self::RECURRENCE_NONE;

	/**
	 * @var int|null Total number of runs (including the first) when recurring
	 */
	public $recurrenceCount;

	/**
	 * @var int Number of times this sync has run so far
	 */
	public $occurrencesRun = 0;

	public function __toString()
	{
		return $this->name;
	}

	public function rules(): array
	{
		return [
			[['id', 'showId'], 'number', 'integerOnly' => true],
			[['name', 'description'], 'string'],
			[['name', 'showId', 'scheduleDate'], 'required'],
			[['scheduleDate'], DateTimeValidator::class],
			[['recurrenceInterval'], 'in', 'range' => self::RECURRENCE_INTERVALS],
			[['recurrenceCount'], 'integer', 'min' => 1],
			[['recurrenceCount'], 'required', 'when' => fn() => $this->isRecurring(), 'message' => 'Occurrences is required when the sync repeats.'],
			[['occurrencesRun'], 'integer', 'min' => 0],
		];
	}

	public function datetimeAttributes(): array
	{
		$datetimeAttributes =  parent::datetimeAttributes();
		$datetimeAttributes[] = 'scheduleDate';

		return $datetimeAttributes;
	}

	public function getCpEditUrl(): string
	{
		return UrlHelper::cpUrl('mediamanager/scheduler/' . $this->id);
	}

	/**
	 * @return array|string
	 */
	public function getMediaFieldsToSync()
	{
		return $this->mediaFieldsToSync === '*' ? '*' : explode(',', $this->mediaFieldsToSync);
	}
	public function getShowFieldsToSync()
	{
		return $this->showFieldsToSync === '*' ? '*' : explode(',', $this->showFieldsToSync);
	}

	public function isRecurring(): bool
	{
		return !empty($this->recurrenceInterval) && $this->recurrenceInterval !== self::RECURRENCE_NONE;
	}

	/**
	 * Returns the schedule date of the occurrence after the current one, or null when not recurring.
	 */
	public function getNextScheduleDate(): ?DateTime
	{
		$modifier = match ($this->recurrenceInterval) {
			self::RECURRENCE_DAILY => '+1 day',
			self::RECURRENCE_WEEKLY => '+1 week',
			self::RECURRENCE_MONTHLY => '+1 month',
			default => null,
		};

		$scheduleDate = DateTimeHelper::toDateTime($this->scheduleDate);

		if ($modifier === null || !$scheduleDate) {
			return null;
		}

		return DateTime::createFromInterface($scheduleDate)->modify($modifier);
	}

	/**
	 * Returns how many runs are left, or null when the sync is not recurring.
	 */
	public function getRemainingOccurrences(): ?int
	{
		if (!$this->isRecurring()) {
			return null;
		}

		return max(0, (int)$this->recurrenceCount - (int)$this->occurrencesRun);
	}


}
