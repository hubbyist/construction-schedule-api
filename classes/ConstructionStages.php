<?php

class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function post(ConstructionStagesCreate $data)
	{
		$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
		$stmt->execute([
			'name' => $data->name,
			'start_date' => $data->startDate,
			'end_date' => $data->endDate,
			'duration' => $data->duration,
			'durationUnit' => $data->durationUnit,
			'color' => $data->color,
			'externalId' => $data->externalId,
			'status' => $data->status,
		]);
		return $this->getSingle($this->db->lastInsertId());
	}

	public function patch(ConstructionStagesModify $data, int $id)
	{
		$columns = [
			'name',
			'start_date',
			'end_date',
			'duration',
			'durationUnit',
			'color',
			'externalId',
			'status',
		];
		$fields = [];
		$values = ['id' => $id];
		foreach ($columns as $column) {
			if(isset($data->$column)){
				$fields[] = "$column = :$column";
				$values[] = $data->{$column};
			}
		}
		$stmt = $this->db->prepare("
			UPDATE construction_stages
			SET " . implode(',', $fields) . "
			WHERE ID = :id
			");
		$stmt->execute($values);
		return $stmt->rowCount() ? $this->getSingle($id) : false;
	}

	public function delete(int $id)
	{
		$stmt = $this->db->prepare("
			UPDATE construction_stages
			SET status = 'DELETED'
			WHERE ID = :id AND status != 'DELETED'
			");
		$stmt->execute(['id' => $id]);
		return $stmt->rowCount() ? true : false;
	}
}
