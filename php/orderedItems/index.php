<?php
include '../config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");

$method = $_SERVER['REQUEST_METHOD'];

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
	die("connection failed" . $mysqli->connect_error);
}

switch ($method) {
	case 'GET':
		break;
	case 'POST':
		break;
	case 'PUT':
		break;
	case 'DELETE':
		break;
}

?>
