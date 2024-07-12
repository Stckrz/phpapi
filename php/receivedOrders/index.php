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
	case "PUT":
		handlePutReceivedOrder($mysqli);
		break;
}

function handleGetReceivedOrders($mysqli)
{
	$result = [];
	if (isset($_GET['receivedOrderId'])) {
		$receivedOrderId = $mysqli->real_escape_string($_GET['receivedOrderId']);
		$result = $mysqli->query("SELECT * FROM receivedOrder WHERE receivedOrderId = $receivedOrderId");
	} else if (isset($_GET['page'])) {
		$page = (int)$_GET['page'];
		$offset = ($page - 1) * 10;
		$result = $mysqli->query("SELECT * FROM receivedOrder LIMIT 10 OFFSET $offset");
	} else if (isset($_GET['unfulfilled'])){
		$result = $mysqli->query("SELECT * FROM receivedOrder WHERE fulfilledDate IS NULL");
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
		return;
	}
	$result = $mysqli->prepare("INSERT INTO receivedOrder (totalOrderAmount, orderDate) VALUES (?, ?)");
	$result->bind_param('ds', $totalOrderAmount, $orderDate);

	if ($result->execute()) {
		$receivedOrderId = $mysqli->insert_id;
		echo json_encode(["message" => "new receivedOrder created", "receivedOrderId" => $receivedOrderId]);
	} else {
		echo json_encode(["error" => "Error: " . $result->error]);
	}
}

function handlePutReceivedOrder($mysqli)
{
	parse_str($_SERVER['QUERY_STRING'], $queries);
	$receivedOrderId = $queries['receivedOrderId'];
	if (isset($receivedOrderId)) {
		$requestBody = file_get_contents('php://input');
		$data = json_decode($requestBody, true);
		$fields = [];

		if (isset($data['totalOrderAmount'])) {
			$totalOrderAmount = floatVal($data['totalOrderAmount']);
			$fields[] = "totalOrderAmount = '$totalOrderAmount'";
		}
		if (isset($data['orderDate'])) {
			$orderDate = $mysqli->real_escape_string($data['orderDate']);
			$fields[] = "orderDate = '$orderDate'";
		}
		if (isset($data['fulfilledDate'])) {
			$fulfilledDate = $mysqli->real_escape_string($data['fulfilledDate']);
			$fields[] = "fulfilledDate = '$fulfilledDate'";
		}

		if (count($fields) === 3) {
			$query = "UPDATE receivedOrder SET " . implode(', ', $fields) . " WHERE receivedOrderId = $receivedOrderId";
			echo $query;
			if ($mysqli->query($query)) {
				echo json_encode(["message" => "item updated successfully"]);
				http_response_code(200);
			} else {
				echo json_encode(["message" => "error updating: " . $mysqli->error]);
			}
		} else {
			echo json_encode(["message" => "error updating: improper json format"]);
			http_response_code(500);
		}
	}
}
