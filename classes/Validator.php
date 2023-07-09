<?php
declare(strict_types=1);

class Validator {

	/**
	 * Validates string length is longer than minimum and shorter than maximum.
	 *
	 * NOTE : when null is supplied as limit respective check is skipped and assumed successful
	 *
	 * @param string $text
	 * @param ?int $minimum = null
	 * @param ?int $maximum = null
	 * @return bool
	 */
	static function length(string $text, ?int $minimum = null, ?int $maximum = null): bool{
		$lower = is_int($minimum) ? ($minimum <= strlen($text)) : true;
		$upper = is_int($maximum) ? (strlen($text) <= $maximum) : true;
		return $lower && $upper;
	}

	/**
	 * Validates string is in Iso8601 format.
	 *
	 * @param string $datetime
	 * @return bool
	 */
	static function datetimeofIso8601(string $datetime): bool{
		if(preg_match('#^([1-9][0-9]{3})-([0-1][0-9])-([0-3][0-9]|31)T([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])Z$#', $datetime, $matches))
		{
			if(checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * Validates digits in the subject string are bigger than the digits in the criteria criteria.
	 *
	 * @param string $subject
	 * @param string $criteria
	 * @return bool
	 */
	static function numericallybigger(string $subject, string $criteria): bool{
		$pattern = '#[^0-9]+#';
		return preg_filter($pattern, '', $criteria) < preg_filter($pattern, '', $subject);
	}

	/**
	 * Validates item is in the given list of items.
	 *
	 * @param mixed $item,string
	 * @param array $list = []
	 * @return bool
	 */
	static function itemofList(mixed $item, array $list = []): bool{
		return in_array($item, $list);
	}

	/**
	 * Validates string is composed of six hexadecimal digits preceded by a single '#' character.
	 *
	 * @param string $code
	 * @return bool
	 */
	static function hexcodeofColor(string $code): bool{
		if(preg_match('/^([#][0-9A-Fa-f]{6})$/', $code, $matches))
		{
			return true;
		}
		return false;
	}
}
