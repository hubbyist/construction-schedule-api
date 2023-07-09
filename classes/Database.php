<?php
declare(strict_types=1);

/**
 * Sqlite database operations management class.
 *
 * NOTE : default database name is 'testDb'
 */
class Database
{
	const name = 'testDb';

	private $db;

	/**
	 * Initialize a sqlite database in the given path with the default name.
	 *
	 * @param string $path
	 * @return self
	 */
	public function init(string $path): self
	{
		$this->db = new PDO('sqlite:' . $path . '/' . self::name . '.db', '', '', [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		]);
		$this->createTables();
		return $this;
	}

	/**
	 * Creates database tables using structure.sql file in database folder.
	 *
	 * IMPORTANT : structure.sql should contain only CREATE TABLE commands
	 *
	 * @return void
	 */
	private function createTables(): void
	{
		$sql = file_get_contents(__DIR__ . '/../database/structure.sql');
		$this->db->exec($sql);
	}

	/**
	 * Populates database tables if database is empty.
	 *
	 * @return void
	 */
	public function populate(): void
	{
		$stmt = $this->db->query('SELECT 1 FROM construction_stages LIMIT 1');
		if (!$stmt->fetchColumn()) {
			$this->loadData();
		}
	}

	/**
	 * Inserts table data using data.sql file in database folder.
	 *
	 * IMPORTANT : data.sql should contain only INSERT commands
	 *
	 * @return void
	 */
	private function loadData(): void
	{
		$sql = file_get_contents(__DIR__ . '/../database/data.sql');
		$this->db->exec($sql);
	}

	/**
	 * Inserts table data using truncate.sql file in database folder.
	 *
	 * IMPORTANT : truncate.sql should contain only DELETE commands
	 *
	 * @return void
	 */
	public function truncate(): void
	{
		$sql = file_get_contents(__DIR__ . '/../database/truncate.sql');
		$this->db->exec($sql);
	}

	/**
	 * Returns PDO handler for the database in its current state.
	 *
	 * @return PDO
	 */
	public function getDb(): PDO
	{
		return $this->db;
	}
}
