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
	 * @var int Number of occurrence slots consumed so far (run or skipped)
	 */
	public $occurrencesRun = 0;

	/**
	 * @var DateTime|null Anchor date of the series (occurrence 0)
	 */
	public $recurrenceStartDate;

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
		$datetimeAttributes[] = 'recurrenceStartDate';

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
		return $this->isRecurring() ? $this->getOccurrenceDate((int)$this->occurrencesRun + 1) : null;
	}

	/**
	 * Returns the date of occurrence $k: the anchor date plus $k intervals.
	 * Monthly dates are clamped to the last day of the target month
	 * (Jan 31 → Feb 28/29 → Mar 31).
	 */
	public function getOccurrenceDate(int $k): ?DateTime
	{
		// Without an anchor, scheduleDate is the date of slot occurrencesRun
		if ($this->recurrenceStartDate) {
			$start = DateTimeHelper::toDateTime($this->recurrenceStartDate);
		} else {
			$start = DateTimeHelper::toDateTime($this->scheduleDate);
			$k -= (int)$this->occurrencesRun;
		}

		if (!$start) {
			return null;
		}

		$date = DateTime::createFromInterface($start);

		switch ($this->recurrenceInterval) {
			case self::RECURRENCE_DAILY:
				return $date->modify(sprintf('%+d days', $k));
			case self::RECURRENCE_WEEKLY:
				return $date->modify(sprintf('%+d days', $k * 7));
			case self::RECURRENCE_MONTHLY:
				$day = (int)$date->format('j');
				$monthIndex = (int)$date->format('n') - 1 + $k;
				$year = (int)$date->format('Y') + (int)floor($monthIndex / 12);
				$month = $monthIndex - 12 * (int)floor($monthIndex / 12) + 1;
				$lastDay = (int)(new DateTime(sprintf('%04d-%02d-01', $year, $month)))->format('t');
				return $date->setDate($year, $month, min($day, $lastDay));
			default:
				return null;
		}
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
