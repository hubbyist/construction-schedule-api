<?php
declare(strict_types=1);

class Database
{
	const name = 'testDb';

	private $db;

	public function init(string $path): self
	{
		$this->db = new PDO('sqlite:' . $path . '/' . self::name . '.db', '', '', [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);
		$this->createTables();
		return $this;
	}

	private function createTables(): void
	{
		$sql = file_get_contents(__DIR__ . '/../database/structure.sql');
		$this->db->exec($sql);
	}

	public function populate(): void
	{
		$stmt = $this->db->query('SELECT 1 FROM construction_stages LIMIT 1');
		if (!$stmt->fetchColumn()) {
			$this->loadData();
		}
	}

	private function loadData(): void
	{
		$sql = file_get_contents(__DIR__ . '/../database/data.sql');
		$this->db->exec($sql);
	}

	public function truncate(): void
	{
		$sql = file_get_contents(__DIR__ . '/../database/truncate.sql');
		$this->db->exec($sql);
	}

	public function getDb(): PDO
	{
		return $this->db;
	}
}
