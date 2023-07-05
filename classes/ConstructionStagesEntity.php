<?php

class ConstructionStagesEntity {

	protected $entity;

	public function __construct(ConstructionStagesCreate|ConstructionStagesModify $entity){
		$this->entity = $entity;
		$vars = get_object_vars($this->entity);
		foreach($vars as $name => $value){
			if(method_exists($this, $name))
			{
				if(!$this->$name($this->entity->$name))
				{
					throw new DomainException(__CLASS__ . ' Invalid : ' . $name);
				}
			}
		}
	}

	protected function name($name): bool{
		return Validator::length($name, null, 255);
	}

	protected function startDate($startDate): bool{
		return Validator::datetimeofIso8601($startDate);
	}

	protected function endDate($endDate): bool{
		if(is_null($endDate))
		{
			return true;
		}
		$iso8601 = Validator::datetimeofIso8601($endDate);
		$later = Validator::numericallybigger($endDate, $this->entity->startDate);
		return $iso8601 && $later;
	}

	protected function duration($duration): bool{
		return is_null($duration);
	}

	protected function durationUnit($durationUnit): bool{
		$list = ['HOURS', 'DAYS', 'WEEKS'];
		return Validator::itemofList($durationUnit, $list);
	}

	protected function color($color): bool{
		if(is_null($color))
		{
			return true;
		}
		return Validator::hexcodeofColor($color);
	}

	protected function externalId($externalId): bool{
		if(is_null($externalId))
		{
			return true;
		}
		return Validator::length($externalId, null, 255);
	}

	protected function status($status): bool{
		$list = ['NEW', 'PLANNED', 'DELETED'];
		return Validator::itemofList($status, $list);
	}

	public function __get($var){
		return $this->entity->$var ?? null;
	}

	public function __isset($var){
		return isset($this->entity->$var);
	}
}
