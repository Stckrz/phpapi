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
		handleGetShopItems($mysqli);
		break;
	case 'POST':
		handlePostShopItem($mysqli);
		break;
	case 'PUT':
		handlePutShopItem($mysqli);
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
	header('Content-Type: application/json');
	$response = [];
	if (isset($_GET['shopItemId'])) {
		$shopItemId = $mysqli->real_escape_string($_GET['shopItemId']);
		$result = $mysqli->query("SELECT * FROM shopItems WHERE shopItemId = '$shopItemId'");
	} elseif (isset($_GET['categoryList'])) {
		$result = $mysqli->query("SELECT DISTINCT shopItemCategory from shopItems");
	} elseif (isset($_GET['shopItemName'])) {
		$shopItemName = $mysqli->real_escape_string($_GET['shopItemName']);
		$result = $mysqli->query("SELECT * FROM shopItems WHERE shopItemName = '$shopItemName'");
	} elseif (isset($_GET['category'])) {
		$category = $mysqli->real_escape_string($_GET['category']);
		$result = $mysqli->query("SELECT * FROM shopItems WHERE shopItemCategory = '$category'");
	} elseif (isset($_GET['itemNameString'])) {
		$itemNameString = $mysqli->real_escape_string($_GET['itemNameString']);
		$result = $mysqli->query("SELECT * FROM shopItems WHERE shopItemName LIKE '$itemNameString%'");
	} elseif (isset($_GET['page'])) {
		$page = (int)$_GET['page'];
		$offset = ($page - 1) * 10;
		$result = $mysqli->query("SELECT * FROM shopItems LIMIT 10 OFFSET $offset");
	} elseif (isset($_GET['parReport'])) {
		$result = $mysqli->query("
		SELECT *
		FROM shopItems 
		WHERE quantity < paramount
	");
	} else {
		$result = $mysqli->query('SELECT * FROM shopItems');
	}
	if ($result) {
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = ["message" => "0 results"];
			http_response_code(404);
		}
	} else {
		$response = ["message" => "Failed to fetch shopItems"];
		http_response_code(500);
	}
	echo json_encode($response, JSON_NUMERIC_CHECK);
}

function handlePostShopItem($mysqli)
{
	$shopItemName = $_POST['shopItemName'];
	$price = $_POST['price'];
	$buyPrice = $_POST['buyPrice'];
	$shopItemCategory = $_POST['shopItemCategory'];
	$quantity = $_POST['quantity'];
	$parAmount = $_POST['parAmount'];

	if (!is_string($shopItemName) || !is_numeric($price) || !is_numeric($buyPrice) || !is_int((int)$quantity) || !is_int((int)$parAmount)) {
		echo json_encode(["error" => "Error: " . "Invalid input data"]);
		http_response_code(400);
		return;
	}
	$result = $mysqli->prepare("INSERT INTO shopItems (shopItemName, shopItemCategory, price, buyPrice, quantity, parAmount) VALUES (?, ?, ?, ?, ?, ?)");
	$result->bind_param("ssddii", $shopItemName, $shopItemCategory, $price, $buyPrice, $quantity, $parAmount);

	if ($result->execute()) {
		echo "New record created for " . $shopItemName;
	} else {
		echo json_encode(["error" => "Error: " . $result->error]);
		http_response_code(500);
	}
}

function handlePutShopItem($mysqli)
{
	$queries = [];
	parse_str($_SERVER['QUERY_STRING'], $queries);
	$shopItemId = $queries['shopItemId'];
	if (isset($shopItemId)) {
		$requestBody = file_get_contents('php://input');
		$data = json_decode($requestBody, true);
		$fields = [];

		if (isset($data['shopItemName']) && is_string($data['shopItemName'])) {
			$shopItemName = $mysqli->real_escape_string($data['shopItemName']);
			$fields[] = "shopItemName = '$shopItemName'";
		}
		if (isset($data['shopItemCategory']) && is_string($data['shopItemCategory'])) {
			$shopItemCategory = $mysqli->real_escape_string($data['shopItemCategory']);
			$fields[] = "shopItemCategory = '$shopItemCategory'";
		}
		if (isset($data['price']) && is_numeric($data['price'])) {
			$price = floatval($data['price']);
			$fields[] = "price = $price";
		}
		if (isset($data['buyPrice']) && is_numeric($data['buyPrice'])) {
			$buyPrice = floatval($data['buyPrice']);
			$fields[] = "buyPrice = $buyPrice";
		}
		if (isset($data['quantity']) && is_numeric($data['quantity'])) {
			$quantity = intval($data['quantity']);
			$fields[] = "quantity = $quantity";
		}
		if (isset($data['parAmount']) && is_numeric($data['parAmount'])) {
			$parAmount = intval($data['parAmount']);
			$fields[] = "parAmount = $parAmount";
		}

		if (count($fields) === 6) {
			$query = "UPDATE shopItems SET " . implode(', ', $fields) . " WHERE shopItemId = $shopItemId";
			echo $query;
			if ($mysqli->query($query)) {
				echo json_encode(["message" => "item updated successfully" . json_encode($data)]);
			} else {
				echo json_encode(["message" => "error updating: " . $mysqli->error]);
			}
		} else {
			http_response_code(400);
			echo json_encode(["message" => "error updating: improper json format"]);
		}
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
			echo json_encode(["error" => "Error: " . $result->error]);
			http_response_code(500);
			return;
		}
	}

	echo json_encode($response);
}

$mysqli->close();
