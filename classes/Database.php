<?php

class Database
{
	const name = 'testDb';

	private $db;

	public function init(string $path)
	{
		$this->db = new PDO('sqlite:' . $path . '/' . self::name . '.db', '', '', [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);
		$this->createTables();
		return $this;
	}

	private function createTables()
	{
		$sql = file_get_contents(__DIR__ . '/../database/structure.sql');
		$this->db->exec($sql);
	}

	public function populate()
	{
		$stmt = $this->db->query('SELECT 1 FROM construction_stages LIMIT 1');
		if (!$stmt->fetchColumn()) {
			$this->loadData();
		}
	}

	private function loadData()
	{
		$sql = file_get_contents(__DIR__ . '/../database/data.sql');
		$this->db->exec($sql);
	}

	public function truncate()
	{
		$sql = file_get_contents(__DIR__ . '/../database/truncate.sql');
		$this->db->exec($sql);
	}

	public function getDb()
	{
		return $this->db;
	}
}
