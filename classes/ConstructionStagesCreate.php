<?php

/**
 * Complete "Unvalidated" representation of ConstructionStagesEntity.
 *
 * IMPORTANT : Use only for ConstructionStagesEntity initialization.
 * should not be used as a data source for any operation.
 */
class ConstructionStagesCreate
{
	public $name;
	public $startDate;
	public $endDate;
	public $duration;
	public $durationUnit = 'DAYS';
	public $color;
	public $externalId;
	public $status = 'NEW';

	public function __construct($data) {

		if(is_object($data)) {

			$vars = get_object_vars($this);

			foreach ($vars as $name => $value) {

				if (isset($data->$name)) {

					$this->$name = $data->$name;
				}
			}
		}
	}
}
