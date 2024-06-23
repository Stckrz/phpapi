<?php
include 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");

$method = $_SERVER['REQUEST_METHOD'];
$requesturi = explode('?', $_SERVER['REQUEST_URI']);

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
	die("connection failed" . $mysqli->connect_error);
}

switch ($method) {
	case 'GET':
		handleGetShopItems($mysqli);
		break;
	case 'POST':
		handlePostShopItem($mysqli);
		break;
	case 'PATCH':
		break;
	case 'DELETE':
		handleDeleteShopItem($mysqli);
		break;
	default:
		echo json_encode(['message' => 'method not supported']);
		break;
}

function handleGetShopItems($mysqli)
{
	if (isset($_GET['shopItemId'])) {
		$shopItemId = $mysqli->real_escape_string($_GET['shopItemId']);
		$result = $mysqli->query("SELECT * FROM shopItems WHERE shopItemId = '$shopItemId'");
		$response = [];
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = ["message" => "0 results"];
		}
	} elseif (isset($_GET['shopItemName'])) {
		$shopItemName = $mysqli->real_escape_string($_GET['shopItemName']);
		$result = $mysqli->query("SELECT * FROM shopItems WHERE shopItemName = '$shopItemName'");
		$response = [];
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = ["message" => "0 results"];
		}
	} elseif(isset($_GET['page'])){
		$page = $mysqli->real_escape_string($_GET['page']);
		$result = $mysqli->query('SELECT * FROM shopItems LIMIT 10 OFFSET ' . (($page - 1) * 10));
		$response = [];
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = ["message" => "failed to fetch shopItems"];
		}
		
	} else {
		$result = $mysqli->query('SELECT * FROM shopItems');
		$response = [];
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = ["message" => "failed to fetch shopItems"];
		}
	}
	echo json_encode($response);
}
function handlePostShopItem($mysqli)
{
	$shopItemName = $_POST['shopItemName'];
	$price = $_POST['price'];
	$quantity = $_POST['quantity'];
	$parAmount = $_POST['parAmount'];
	$result = $mysqli->prepare('INSERT INTO shopItems (shopItemName, price, quantity, parAmount) VALUES (?, ?, ?, ?)');
	$result->bind_param("siii", $shopItemName, $price, $quantity, $parAmount);
	if ($result->execute()) {
		echo "New record created for " . $shopItemName;
	} else {
		echo "Error: " . $result->error;
	}
}

function handleDeleteShopItem($mysqli)
{
	parse_str($_SERVER['QUERY_STRING'], $queries);
	$shopItemId = $queries['shopItemId'];

	if (isset($shopItemId)) {
		$shopItemId = $_GET['shopItemId'];
		$result = $mysqli->query("DELETE FROM shopItems WHERE shopItemId = '$shopItemId'");
		if ($result->num_rows > 0) {
			$response = ["message" => "Item deleted successfully"];
		} else {
			$response = ["message" => "database error"];
		}
	}

	echo json_encode($response);
}

$mysqli->close();
