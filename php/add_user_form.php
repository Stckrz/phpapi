<!DOCTYPE html>
<html lang=en>
	<head>
		<title>Create new user</title>
	</head>
	<body>
		<h1>Add new user</h1>
		<form action="insert_user.php" method="post">
			<label for="username">Username</label><br />
			<input type="text" id="username" name="username"></input><br />

			<label for="age">Age</label><br />
			<input type="text" id="age" name="age"></input><br /><br />
			<input type="submit" value="Submit">
		</form>
	</body>
</html>
