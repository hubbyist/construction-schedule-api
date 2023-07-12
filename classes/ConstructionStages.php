<?php
declare(strict_types=1);

/**
 * ConstructionStages collection actions controller
 */
class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

	/**
	 * Get all construction_stages records
	 *
	 * @return array
	 */
	public function getAll(): array
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

	/**
	 * Get one construction_stages record by id
	 *
	 * @param int $id
	 * @return array
	 */
	public function getSingle(int $id): array
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

	/**
	 * Create one construction_stages record
	 *
	 * @param ConstructionStagesCreate $data
	 * @return array
	 */
	public function post(ConstructionStagesCreate $data): array
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
		return $stmt->rowCount() ? $this->getSingle((int) $this->db->lastInsertId()) : false;
	}

	/**
	 * Modify one construction_stages record by id using supplied data
	 *
	 * NOTE : returns false if UPDATE does not succeed.
	 *
	 * @param ConstructionStagesModify $data
	 * @param int $id
	 * @return array|false
	 */
	public function patch(ConstructionStagesModify $data, int $id): array|false
	{
		$columns = [
			'name' => 'name',
			'start_date' => 'startDate',
			'end_date' => 'endDate',
			'duration' => 'duration',
			'durationUnit' => 'durationUnit',
			'color' => 'color',
			'externalId' => 'externalId',
			'status' => 'status',
		];
		$current = $this->getSingle($id)[0] ?? null;
		if(!$current){
			return false;
		}
		$entity = new ConstructionStagesEntity($data, $current);
		$fields = [];
		$values = ['id' => $id];
		foreach ($columns as $column => $input) {
			if(isset($entity->$input)){
				$fields[] = "$column = :$column";
				$values[] = $entity->{$input};
			}
		}
		if(!count($fields)){
			return false;
		}
		$stmt = $this->db->prepare("
			UPDATE construction_stages
			SET " . implode(',', $fields) . "
			WHERE ID = :id
			");
		$stmt->execute($values);
		return $stmt->rowCount() ? $this->getSingle($id) : false;
	}

	/**
	 * Soft delete one construction_stages record by id
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete(int $id): bool
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
