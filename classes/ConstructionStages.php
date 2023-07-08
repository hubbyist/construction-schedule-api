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

	public function getSingle(int $id)
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
		$entity = new ConstructionStagesEntity($data);
		$stmt->execute([
			'name' => $entity->name,
			'start_date' => $entity->startDate,
			'end_date' => $entity->endDate,
			'duration' => $entity->duration,
			'durationUnit' => $entity->durationUnit,
			'color' => $entity->color,
			'externalId' => $entity->externalId,
			'status' => $entity->status,
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
		$entity = new ConstructionStagesEntity($data);
		$fields = [];
		$values = ['id' => $id];
		foreach ($columns as $column) {
			if(isset($entity->$column)){
				$fields[] = "$column = :$column";
				$values[] = $entity->{$column};
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
