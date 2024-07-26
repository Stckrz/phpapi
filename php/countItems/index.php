<?php
include '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");

$method = $_SERVER['REQUEST_METHOD'];
$requesturi = explode('?', $_SERVER['REQUEST_URI']);

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
	die("connection failed" . $mysqli->connect_error);
}
if (isset($_GET['countType'])) {
	if ($_GET['countType'] === 'shopItems') {
		$countQuery = "SELECT COUNT(*) AS totalItems FROM shopItems";
		$countResults = $mysqli->query($countQuery);
	} else if ($_GET['countType'] === 'purchaseReports') {
		$countQuery = "SELECT COUNT(*) AS totalItems FROM bulkPurchase";
		$countResults = $mysqli->query($countQuery);
	}
	if ($countResults) {
		$row = $countResults->fetch_assoc();
		$totalItems = $row['totalItems'];
		$numberOfPages = $totalItems / 10;
	} else {
		echo "Error: " . $mysqli->error;
		$totalItems = 0;
	}
	$mysqli->close();

	echo json_encode(['totalItems' => $totalItems, 'numberOfPages' => ceil($numberOfPages)]);
}
