<?php

/**
 * Partial "Unvalidated" representation of ConstructionStagesEntity.
 *
 * IMPORTANT : Use only for ConstructionStagesEntity initialization.
 * should not be used as a data source for any operation.
 *
 * NOTE : Only keys supplied in data will have corresponding properties in this object.
 */
class ConstructionStagesModify
{
	public $name;
	public $startDate;
	public $endDate;
	public $duration;
	public $durationUnit;
	public $color;
	public $externalId;
	public $status;

	public function __construct($data) {

		if(is_object($data)) {

			$vars = get_object_vars($this);

			foreach ($vars as $name => $value) {

				if (isset($data->$name)) {
					$this->$name = $data->$name;
				} else {
					unset($this->$name);
				}
			}
		}
	}
}
