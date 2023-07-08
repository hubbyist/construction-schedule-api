<?php

class Validator {

	static function length(string $text, ?int $minimum = null, ?int $maximum = null){
		$lower = is_int($minimum) ? ($minimum <= strlen($text)) : true;
		$upper = is_int($maximum) ? (strlen($text) <= $maximum) : true;
		return $lower && $upper;
	}

	static function datetimeofIso8601(string $datetime){
		if(preg_match('#^([1-9][0-9]{3})-([0-1][0-9])-([0-3][0-9]|31)T([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])Z$#', $datetime, $matches))
		{
			if(checkdate($matches[2], $matches[3], $matches[1]))
			{
				return true;
			}
		}
		return false;
	}

	static function numericallybigger(string $subject, string $criteria){
		$pattern = '#[^0-9]+#';
		return preg_filter($pattern, '', $criteria) < preg_filter($pattern, '', $subject);
	}

	static function itemofList(mixed $item, array $list = []){
		return in_array($item, $list);
	}

	static function hexcodeofColor(string $code){
		if(preg_match('#^([#][0-9A-F]{6}$#', $code, $matches))
		{
			return true;
		}
		return false;
	}
}