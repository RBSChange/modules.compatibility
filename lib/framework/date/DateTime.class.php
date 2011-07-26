<?php
/**
 * @deprecated Use date_Calendar and date_Formatter
 */
class date_DateTime
{
	/**
	 * @deprecated
	 */
	private $calendar;

	/**
	 * @deprecated
	 */
	public function __construct($dateString = null)
	{
		$this->calendar = date_GregorianCalendar::getInstance($dateString);
		$this->calendar->useSmartEndOfMonth(false);
	}

	/**
	 * @deprecated
	 */
	public static function fromString($dateString)
	{
		return new date_DateTime($dateString);
	}

	/**
	 * @deprecated
	 */
	public static function now($keepTimeInformation = true)
	{
		$className = get_class();
		$instance = new $className();
		if (!$keepTimeInformation)
		{
			$instance->toMidnight();
		}
		return $instance;
	}

	/**
	 * @deprecated
	 */
	public static function yesterday($keepTimeInformation = true)
	{
		$className = get_class();
		$format = 'Y-m-d ' . ($keepTimeInformation ? 'H:i:s' : '00:00:00');
		$instance = new $className(date($format, time() - 60*60*24));
		return $instance;
	}
	
	/**
	 * @deprecated
	 */
	public static function tomorrow($keepTimeInformation = true)
	{
		$className = get_class();
		$format = 'Y-m-d ' . ($keepTimeInformation ? 'H:i:s' : '00:00:00');
		$instance = new $className(date($format, time() + 60*60*24));
		return $instance;
	}

	/**
	 * @deprecated
	 */
	public function toMidnight()
	{
		$this->calendar->toMidnight();
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function toMidday()
	{
		$this->calendar->toMidday();
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getTimestamp()
	{
		return $this->calendar->getTimestamp();
	}

	/**
	 * @deprecated
	 */
    public function setSecond($second)
    {
    	$this->calendar->setSecond($second);
		return $this;
    }

	/**
	 * @deprecated
	 */
    public function getSecond()
    {
    	return $this->calendar->getSecond();
    }

	/**
	 * @deprecated
	 */
    public function setMinute($minute)
    {
    	$this->calendar->setMinute($minute);
		return $this;
    }

	/**
	 * @deprecated
	 */
    public function getMinute()
    {
    	return $this->calendar->getMinute();
    }

	/**
	 * @deprecated
	 */
    public function setHour($hour)
    {
    	$this->calendar->setHour($hour);
		return $this;
    }

	/**
	 * @deprecated
	 */
    public function getHour()
    {
    	return $this->calendar->getHour();
    }

	/**
	 * @deprecated
	 */
    public function setDay($day)
    {
    	$this->calendar->setDay($day);
		return $this;
    }

	/**
	 * @deprecated
	 */
    public function getDay()
    {
    	return $this->calendar->getDay();
    }

	/**
	 * @deprecated
	 */
    public function setMonth($month)
    {
    	$this->calendar->setMonth($month);
		return $this;
    }

	/**
	 * @deprecated
	 */
    public function getMonth()
    {
    	return $this->calendar->getMonth();
    }

	/**
	 * @deprecated
	 */
    public function setYear($year)
    {
    	$this->calendar->setYear($year);
		return $this;
    }

	/**
	 * @deprecated
	 */
    public function getYear()
    {
    	return $this->calendar->getYear();
    }

	/**
	 * @deprecated
	 */
    public function getCentury()
    {
    	return $this->calendar->getCentury();
    }

	/**
	 * @deprecated
	 */
	public function getDaysInMonth()
	{
		return $this->calendar->getDaysInMonth();
	}

	/**
	 * @deprecated
	 */
	public function isLeapYear()
	{
		// Leap years have been created in year 1582, by Gregoire III.
		return $this->calendar->isLeapYear();
	}

	/**
	 * @deprecated
	 */
	public function getDayOfWeek()
	{
		return $this->calendar->getDayOfWeek();
	}

	/**
	 * @deprecated
	 */
	public function getDayOfYear()
	{
		return $this->calendar->getDayOfYear();
	}

	/**
	 * @deprecated
	 */
	public function toString()
	{
		return $this->calendar->toString();
	}

	/**
	 * @deprecated
	 */
    public function __toString()
    {
    	return $this->toString();
    }

	/**
	 * @deprecated
	 */
	public function format($format, $lang = null)
	{
		return date_DateFormat::format($this->calendar, $format, $lang);
	}

	/**
	 * @deprecated
	 */
	public function addSeconds($amount)
	{
		$this->calendar->add(date_Calendar::SECOND, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function addMinutes($amount)
	{
		$this->calendar->add(date_Calendar::MINUTE, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function addHours($amount)
	{
		$this->calendar->add(date_Calendar::HOUR, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function addDays($amount)
	{
		$this->calendar->add(date_Calendar::DAY, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function addWeeks($amount)
	{
		$this->calendar->add(date_Calendar::DAY, $amount * 7);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function addMonths($amount)
	{
		$this->calendar->add(date_Calendar::MONTH, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function addYears($amount)
	{
		$this->calendar->add(date_Calendar::YEAR, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function addTimeSpan($timeSpan, $returnNewInstance = false)
	{
		if ($returnNewInstance)
		{
			$newCalendar = $this->calendar->addTimeSpan($timeSpan, true);
			return new date_DateTime($newCalendar->toString());
		}
		else
		{
			$this->calendar->addTimeSpan($timeSpan, false);
		}
		return $this;
	}
	
	/**
	 * @deprecated
	 */
	public function sub($other)
	{
		$diff = $this->getTimestamp()-$other->getTimestamp();
		$span = new date_TimeSpan();
		$span->setNumberOfSeconds($diff);
		return $span;
	}

	/**
	 * @deprecated
	 */
	public function subSeconds($amount)
	{
		$this->calendar->sub(date_Calendar::SECOND, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function subMinutes($amount)
	{
		$this->calendar->sub(date_Calendar::MINUTE, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function subHours($amount)
	{
		$this->calendar->sub(date_Calendar::HOUR, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function subDays($amount)
	{
		$this->calendar->sub(date_Calendar::DAY, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function subWeeks($amount)
	{
		$this->calendar->sub(date_Calendar::DAY, $amount * 7);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function subMonths($amount)
	{
		$this->calendar->sub(date_Calendar::MONTH, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function subYears($amount)
	{
		$this->calendar->sub(date_Calendar::YEAR, $amount);
		return $this;
	}

	/**
	 * @deprecated
	 */
	public function subTimeSpan($timeSpan, $returnNewInstance = false)
	{
		if ($returnNewInstance)
		{
			$newCalendar = $this->calendar->subTimeSpan($timeSpan, true);
			return new date_DateTime($newCalendar->toString());
		}
		else
		{
			$this->calendar->subTimeSpan($timeSpan, false);
		}
		return $this;
	}

	/**
	 * @deprecated
	 */
    public function isBefore($dateTime, $strict = true)
    {
		return $this->calendar->isBefore($dateTime->calendar, $strict);
    }

	/**
	 * @deprecated
	 */
    public function isAfter($dateTime, $strict = true)
    {
		return $this->calendar->isAfter($dateTime->calendar, $strict);
    }

	/**
	 * @deprecated
	 */
    public function isBetween($dt1, $dt2, $strict = true)
    {
		return $this->calendar->isBetween($dt1->calendar, $dt2->calendar, $strict);
    }

	/**
	 * @deprecated
	 */
    public function belongsToPast()
    {
    	return $this->calendar->belongsToPast();
    }

	/**
	 * @deprecated
	 */
    public function belongsToFuture()
    {
    	return $this->calendar->belongsToFuture();
    }

	/**
	 * @deprecated
	 */
    public function isToday()
    {
    	return $this->calendar->isToday();
    }

	/**
	 * @deprecated
	 */
    public function equals($dateTime)
    {
    	return $this->calendar->equals($dateTime->calendar);
    }
}

/**
 * @deprecated use date_Calendar
 */
class date_Date extends date_DateTime
{
	
}