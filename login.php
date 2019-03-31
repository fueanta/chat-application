<?php 
	session_start();
	include('database_connection.php');
	// echo $signal;
	$message = '';

	if ( isset( $_SESSION['chat_user_id'] ) ) {
		header('location:./');
	}

	if ( isset($_POST['login']) ) {
		$query = "
			SELECT * FROM login
			WHERE username = :username
		";
		$statement = $conn->prepare( $query );
		$statement->execute(
			array(
				':username' => $_POST['username']
			)
		);
		$count = $statement->rowCount();

		if ( $count > 0 ) {
			$result = $statement->fetchAll();
			foreach ($result as $row) {
				if ($_POST['password'] == $row['password']) {
					$_SESSION['chat_user_id'] = $row['user_id'];
					$_SESSION['username'] = $row['username'];

					$sub_query = "
						INSERT INTO login_details
						(user_id)
						VALUES (" . $row['user_id'] . ")
					";
					$statement = $conn->prepare( $sub_query );
					$statement->execute();
					$_SESSION['login_details_id'] = $conn->lastInsertId();

					header('location:./');
				}
				else {
					$message = 'Password mismatched.';
				}
			}
		} 
		else {
			$message = 'User not found.';
		}
	}

 ?>

<!DOCTYPE html>
<html>
<head>

	<!-- bootstrap 4 css cdn -->
	<link rel="stylesheet" href="./styles/bootstrap.css">
	
	<!-- font awesome css cdn -->
	<link rel="stylesheet" href="./fontawesome/css/all.css">

	<!-- custom css -->
	<link rel="stylesheet" type="text/css" href="./styles/style.css">

	<title>Log In | Chat Application</title>

</head>
<body>

	<main>

		<span id="connectivity"></span>
		
		<form id="login-form" class="centered-item" method="post" autocomplete="off">
			<section class="form-group">
				<label for="username">Username</label>
				<input placeholder="Username" type="text" name="username" id="username" class="form-control" value="<?php echo isset( $_POST['username'] ) ? $_POST['username'] : ''; ?>" required>
			</section>
			<section class="form-group">
				<label for="password">Password</label>
				<input placeholder="Password" type="password" name="password" id="password" class="form-control" value="<?php echo isset( $_POST['password'] ) ? $_POST['password'] : ''; ?>" required>
			</section>
			<section class="form-group">
				<?php echo isset($message) ? '<label class="text-danger">' . $message . '</label>' : ''; ?>
				<input type="submit" name="login" id="login-btn" class="btn btn-primary" value="Log In">
			</section>
		</form>

	</main>

	<!-- bootstrap 4 script cdn -->
	<script src="./scripts/jquery-3.3.1.js"></script>
	<script src="./scripts/popper.js"></script>
	<script src="./scripts/bootstrap.js"></script>

	<!-- custom script -->
	<script src="./scripts/script.js"></script>

	<script type="text/javascript">
		update_connectivity();

		setInterval( function() {
			update_connectivity();
		}, 3000);

		function update_connectivity() {
			$.ajax( {
				url:"update_connectivity.php",
				success:function(data) {
					if (data == 'On') {
						$('#connectivity').html('<span class="badge badge-pill badge-success">Server is Up</span>');
						$('#login-btn').prop('disabled', false);
					}
					else if (data == 'Off') {
						$('#connectivity').html('<span class="badge badge-pill badge-danger">Server is Down</span>');
						$('#login-btn').prop('disabled', true);
					}
				}
			} );
		}
	</script>

</body>
</html>