<?php
$database = (new Database())->init(__DIR__ . '/../../');
return function()use($database) {
	$database->truncate();
};
