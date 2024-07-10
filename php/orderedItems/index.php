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
		handleGetOrderedItems($mysqli);
		break;
	case 'POST':
		handlePostOrderedItem($mysqli);
		break;
	case 'PUT':
		break;
	case 'DELETE':
		break;
}

function handleGetOrderedItems($mysqli)
{
	$response = [];
	//gets all orderedItems where a certain shopItemId is included
	if (isset($_GET['OrderedItemId'])) {
		$orderedItemId = $mysqli->real_escape_string($_GET['orderedItemId']);
		$result = $mysqli->query("
			SELECT 
				shopItems.shopItemId AS 'Item Id', 
				shopItems.shopItemName AS 'Item Name', 
				orderedItems.shopItemQuantity AS 'Purchased Quantity' 
			FROM shopItems 
			JOIN orderedItems ON shopItems.shopItemId = orderedItems.shopItemId 
			WHERE orderedItems.orderedItemId = $orderedItemId
		");
	} else if (isset($_GET['receivedOrderId'])) {
		//get all of the items in a specific receivedOrder
		$receivedOrderId = $mysqli->real_escape_string($_GET['receivedOrderId']);
		$result = $mysqli->query("
			SELECT 
				shopItems.shopItemId AS 'Item Id', 
				shopItems.shopItemName AS 'Item Name', 
				orderedItems.shopItemQuantity AS 'Purchased Quantity' 
			FROM shopItems 
			JOIN orderedItems ON shopItems.shopItemId = orderedItems.shopItemId 
			WHERE orderedItems.receivedOrderId = $receivedOrderId
		");
	} else {
		$result = $mysqli->query("SELECT * FROM orderedItems");
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
		$response = (["message" => "Failed to fetch orderedItem"]);
		http_response_code(500);
	}
	echo json_encode($response);
}

function handlePostOrderedItem($mysqli)
{
	$shopItemId = $_POST['shopItemId'];
	$receivedOrderId = $_POST['receivedOrderId'];
	$orderedItemQuantity = $_POST['shopItemQuantity'];
	//(int) in this check is explicitly converting it to an integer before checking if it 'is_int'
	if (!is_int((int)$shopItemId) || !is_int((int)$receivedOrderId) || !is_int((int)$orderedItemQuantity)) {
		echo json_encode(["message" => "error: invalid json data"]);
		http_response_code(400);
		return;
	}
	$result = $mysqli->prepare("INSERT INTO orderedItems (shopItemId, receivedOrderId, shopItemQuantity) VALUES (?, ?, ?)");
	$result->bind_param('iii', $shopItemId, $receivedOrderId, $orderedItemQuantity);

	if ($result->execute()) {
		//uses mysqli.insert_id to retrieve the last inserted id
		echo json_encode(["message" => "new orderedItem created"]);
	} else {
		echo json_encode(["error" => "Error: " . $result->error]);
		http_response_code(500);
	}
}
