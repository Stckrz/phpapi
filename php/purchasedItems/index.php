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
		handleGetPurchasedItems($mysqli);
		break;
	case 'POST':
		handlePostPurchasedItems($mysqli);
		break;
	case 'PUT':
		break;
	case 'DELETE':
		break;
}

function handleGetPurchasedItems($mysqli)
{
	$response = [];
	//gets all purchasedItems where a certain shopItemId is included
	if (isset($_GET['bulkPurchaseId'])) {
		$bulkPurchaseId = $mysqli->real_escape_string($_GET['bulkPurchaseId']);
		$result = $mysqli->query("
			SELECT 
				shopItems.shopItemId AS 'Item Id', 
				shopItems.shopItemName AS 'Item Name', 
				purchasedItems.shopItemQuantity AS 'Purchased Quantity' 
			FROM shopItems 
			JOIN purchasedItems ON shopItems.shopItemId = purchasedItems.shopItemId 
			WHERE purchasedItems.bulkPurchaseId = $bulkPurchaseId
		");
	} else {
		$result = $mysqli->query("SELECT * FROM purchasedItems");
	}
	if ($result) {
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = (["message" => "0 results"]);
		}
	} else {
		$response = (["message" => "Failed to fetch purchasedItemss"]);
		http_response_code(500);
	}
	echo json_encode($response);
}

function handlePostPurchasedItems($mysqli)
{
	$shopItemId = $_POST['shopItemId'];
	$bulkPurchaseId = $_POST['bulkPurchaseId'];
	$shopItemQuantity = $_POST['shopItemQuantity'];
	//(int) in this check is explicitly converting it to an integer before checking if it 'is_int'
	if (!is_int((int)$shopItemId) || !is_int((int)$bulkPurchaseId) || !is_int((int)$shopItemQuantity)) {
		echo json_encode(["message" => "error: invalid json data"]);
		http_response_code(400);
		return;
	}
	$result = $mysqli->prepare("INSERT INTO purchasedItems (shopItemId, bulkPurchaseId, shopItemQuantity) VALUES (?, ?, ?)");
	$result->bind_param('iii', $shopItemId, $bulkPurchaseId, $shopItemQuantity);

	if ($result->execute()) {
		//uses mysqli.insert_id to retrieve the last inserted id
		echo json_encode(["message" => "new purchasedItems created"]);
	} else {
		echo json_encode(["error" => "Error: " . $result->error]);
		http_response_code(500);
	}
}

