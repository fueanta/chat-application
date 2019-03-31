<?php
	session_start();
	if ( !isset( $_SESSION['chat_user_id'] ) ) {
		header('location:login.php');
	}
	include('database_connection.php');

	echo fetch_user_chat_history($_SESSION['chat_user_id'], $_POST['to_user_id'], $conn);
 ?>