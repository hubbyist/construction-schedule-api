<?php
declare(strict_types=1);

/**
 * Complete "Validated" representation of ConstructionStagesEntity.
 *
 * NOTE : duration will be auto calculated by this Entity
 */
class ConstructionStagesEntity {

	protected $entity;
	protected $current;
	protected $computeds = [
		'duration',
	];

	public function __construct(ConstructionStagesCreate|ConstructionStagesModify $entity, ?array $current = []){
		$this->entity = $entity;
		$this->current = (object) ($current ?? []);
		$vars = get_object_vars($this->entity);
		foreach($vars as $name => $value){
			if(method_exists($this, $name))
			{
				if(!$this->$name($this->entity->$name))
				{
					throw new DomainException('Invalid : ' . $name);
				}
			}
		}
		foreach($this->computeds as $name){
			if(method_exists($this, "_{$name}_"))
			{
				$this->{"_{$name}_"}();
			}
		}
	}

	protected function name(string $name): bool{
		return Validator::length($name, null, 255);
	}

	protected function startDate(string $startDate): bool{
		return Validator::datetimeofIso8601($startDate);
	}

	protected function endDate(?string $endDate): bool{
		if(is_null($endDate))
		{
			return true;
		}
		$iso8601 = Validator::datetimeofIso8601($endDate);
		$startDate = $this->entity->startDate ?? $this->current->startDate;
		$later = Validator::numericallybigger($endDate, $startDate);
		return $iso8601 && $later;
	}

	protected function duration(?float $duration): bool{
		return is_null($duration);
	}

	protected function durationUnit(string $durationUnit): bool{
		$list = ['HOURS', 'DAYS', 'WEEKS'];
		return Validator::itemofList($durationUnit, $list);
	}

	protected function color(?string $color): bool{
		if(is_null($color))
		{
			return true;
		}
		return Validator::hexcodeofColor($color);
	}

	protected function externalId(?string $externalId): bool{
		if(is_null($externalId))
		{
			return true;
		}
		return Validator::length($externalId, null, 255);
	}

	protected function status(string $status): bool{
		$list = ['NEW', 'PLANNED', 'DELETED'];
		return Validator::itemofList($status, $list);
	}

	public function __get($var){
		return $this->entity->$var ?? null;
	}

	public function __isset($var){
		return isset($this->entity->$var);
	}

	protected function _duration_(): void{
		if(!is_null($this->entity->endDate ?? null))
		{
			$durationUnit = $this->entity->durationUnit ?? $this->current->durationUnit;
			$divisor = match($durationUnit) {
				'HOURS' => 1,
				'DAYS' => 24,
				'WEEKS' => 24 * 7,
			};
			$startDate = $this->entity->startDate ?? $this->current->startDate;
			$difference = floor((strtotime($this->entity->endDate) - strtotime($startDate)) / 3600);
			$this->entity->duration = round($difference / $divisor, 1);
		}
	}
}
