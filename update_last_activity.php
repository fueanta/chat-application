<?php
	session_start();
	if ( !isset( $_SESSION['chat_user_id'] ) ) {
		header('location:login.php');
	}

	include('database_connection.php');

	$query = "
		UPDATE login_details
		SET last_activity = now()
		WHERE login_details_id = " . $_SESSION['login_details_id'] . "
	";
	
	$statement = $conn->prepare( $query );
	$statement->execute();
 ?>