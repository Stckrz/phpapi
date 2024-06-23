<?php
include 'config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: *");

//gets the request method
$method = $_SERVER['REQUEST_METHOD'];

//this is giving us an array of items where the first index is the file path, and the rest is everything after the question mark
$requestUri = explode('?', $_SERVER['REQUEST_URI'], 2);
//this line is taking in the raw body data, reads it in as a string, and then decodes it into an associative array
$input = json_encode(file_get_contents('php://input'), true);

$mysqli = new mysqli($host, $user, $password, $database);

if ($mysqli->connect_error) {
	die("Connection failed" . $mysqli->connect_error);
}

switch ($method) {
	case 'GET':
		handleGetAll($mysqli);
		break;
	case 'POST':
		handlePostUser($mysqli);
		break;
	case 'DELETE':
		handleUserDelete($mysqli);
		break;
	default:
		echo json_encode(['message' => 'method not supported']);
		break;
}

function handleGetAll($mysqli)
{
	//searches user by username, should probably have functionality to check if there are query params, and then have a 
	//switch case as needed for different get requests..
	if (isset($_GET['userid'])) {
		$userid = $mysqli->real_escape_string($_GET['userid']);
		echo $userid;
		$result = $mysqli->query("SELECT * FROM users WHERE userid = '$userid'");
		$response = [];
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = ["message" => "0 results"];
		}
	} else {
		$result = $mysqli->query('SELECT * FROM users');
		$response = [];
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$response[] = $row;
			}
		} else {
			$response = ["message" => "0 results"];
		}
	}
	echo json_encode($response);
}

function handlePostUser($mysqli)
{
	$username = $_POST['username'];
	$age = $_POST['age'];
	$result = $mysqli->prepare('INSERT INTO users (username, age) VALUES (?, ?)');
	$result->bind_param("ss", $username, $age);

	if ($result->execute()) {
		echo "New record created for " . $username;
	} else {
		echo "Error: " . $result->error;
	}
}

function handleUserDelete($mysqli)
{

	parse_str($_SERVER['QUERY_STRING'], $queries);
	$userid = $queries['userid'];

	// echo $username;
	if (isset($userid)) {
		$userid = $_GET['userid'];
		$result = $mysqli->query("DELETE FROM users WHERE userid = '$userid'");
		if ($mysqli->query($result) === TRUE) {
			if ($mysqli->affected_rows > 0) {
				echo json_encode(array("message" => "user deleted successfully"));
			} else {
				echo json_encode(array("message" => "user not found"));
			}
		} else {
			echo json_encode(array("message" => "database query failed"));
		}

		// echo $result;
	}
}

$mysqli->close();
