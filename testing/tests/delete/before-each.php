<?php
$database = (new Database())->init(__DIR__ . '/../../');
return function()use($database) {
	$database->truncate();
	$db = $database->getDb();
	$stmt = $db->prepare("INSERT INTO construction_stages (name, start_date) VALUES (:name, :start_date)");
	$stmt->execute([
		'name' => 'test',
		"start_date" => "2023-02-28T19:59:24Z",
	]);
};
