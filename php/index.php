<!DOCTYPE html>
<html>
		<head>
			<link rel='stylesheet' type='text/css' href='css/style.css'>
		</head>
	<body>
<div class="main-div">
		<h1>hello world!</h1>
<div class="content-div">

		<?php
			include 'config.php';
			$conn = new mysqli($host, $user, $password, $database);
			if ($conn->connect_error){
				die("Connection failed: " . $conn->connect_error);
			}
			echo "<br />";
			echo "Connected successfully";
			echo "<br />";
			$sql = "SELECT * FROM users";
			$result = $conn->query($sql);

			if ($result->num_rows > 0){
				echo "<ul>";
				while($row = $result->fetch_assoc()) {
					echo "<li>" . "username: " . $row["username"] . "<br />" . "age: " . $row["age"] . "</li>";
				}
				echo "</ul>";
			} else {
				echo "0 results";
			}
			$conn->close();
		?>
		<a href='add_user_form.php'>create new user</a>
<div>
</div>
	</body>
</html>
