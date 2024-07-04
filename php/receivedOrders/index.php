<?php
include '../config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");

$method = $_SERVER['REQUEST_METHOD'];

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
	die("connection failed: " . $mysqly->connect_error);
}

switch ($method) {
	case "GET":
		handleGetReceivedOrders($mysqli);
		break;
	case "POST":
		handlePostReceivedOrder($mysqli);
		break;
}

function handleGetReceivedOrders($mysqli)
{
	$result = [];
	if (isset($_GET['receivedOrderId'])) {
		$receivedOrderId = $mysqli->real_escape_string($_GET['receivedOrderId']);
		$result = $mysqli->query("SELECT * FROM receivedOrders WHERE receivedOrderId = $receivedOrderId");
	} else if (isset($_GET['page'])) {
		$page = (int)$_GET['page'];
		$offset = ($page - 1) * 10;
		$result = $mysqli->query("SELECT * FROM receivedOrders LIMIT 10 OFFSET $offset");
	}
	if ($result) {
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = (["message" => "0 results found"]);
		}
	} else {
		$response = (["message" => "failed to fetch received orders"]);
	}
	echo json_encode($response);
};

function handlePostReceivedOrder($mysqli)
{
	$totalOrderAmount = $_POST['totalOrderAmount'];
	$orderDate = $_POST['orderDate'];

	if (!is_numeric($totalOrderAmount) | !is_string($orderDate)) {
		json_encode(['message' => 'error: invalid json data']);
		http_response_code(400);
		return;
	}
	$result = $mysqli->prepare("INSERT INTO receivedOrders (totalOrderAmount, orderDate) VALUES (?, ?)");
	$result->bind_param('ds', $totalOrderAmount, $orderDate);

	if ($result->execute()) {
		$receivedOrderId = $mysqli->insert_id;
		echo json_encode(["message" => "new receivedOrder created", "receivedOrderId" => $receivedOrderId]);
	} else {
		echo json_encode(["error" => "Error: " . $result->error]);
		http_response_code(500);
	}
}
