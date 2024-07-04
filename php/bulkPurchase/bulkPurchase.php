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
		handleGetBulkPurchase($mysqli);
		break;
	case 'POST':
		handlePostBulkPurchase($mysqli);
		break;
	case 'PUT':
		break;
	case 'DELETE':
		break;
}

function handleGetBulkPurchase($mysqli)
{
	$response = [];

	if (isset($_GET['bulkPurchaseId'])) {
		$bulkPurchaseId = $mysqli->real_escape_string($_GET['bulkPurchaseId']);
		$result = $mysqli->query("SELECT * FROM bulkPurchase WHERE bulkPurchaseId = '$bulkPurchaseId'");
	} else if(isset($_GET['page'])){
		$page = (int)$_GET['page'];
		$offset = ($page - 1) * 10;
		$result = $mysqli->query("SELECT * FROM bulkPurchase LIMIT 10 OFFSET $offset");

	} else {
		$result = $mysqli->query("SELECT * FROM bulkPurchase");
	}
	if ($result) {
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = (["message" => "0 results"]);
			http_response_code(400);
		}
	} else {
		$response = (["message" => "Failed to fetch bulkPurchases"]);
		http_response_code(500);
	}
	echo json_encode($response);
}

function handlePostBulkPurchase($mysqli)
{
	$totalPurchaseAmount = $_POST['totalPurchaseAmount'];
	$purchaseDate = $_POST['purchaseDate'];
	if (!is_numeric($totalPurchaseAmount) || !is_string($purchaseDate)) {
		echo json_encode(["message" => "error: invalid json data"]);
		http_response_code(400);
		return;
	}
	$result = $mysqli->prepare("INSERT INTO bulkPurchase (totalPurchaseAmount, purchaseDate) VALUES (?, ?)");
	$result->bind_param('ds', $totalPurchaseAmount, $purchaseDate);

	if ($result->execute()) {
		//uses mysqli.insert_id to retrieve the last inserted id
		$bulkPurchaseId = $mysqli->insert_id;
		echo json_encode(["message" => "new bulkPurchase created", "bulkPurchaseId" => $bulkPurchaseId]);

	} else {
		echo json_encode(["error" => "Error: " . $result->error]);
		http_response_code(500);
	}
}
