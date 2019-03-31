<?php
	session_start();
	if ( !isset( $_SESSION['chat_user_id'] ) ) {
		header('location:login.php');
	}

	include('database_connection.php');

	$data = array(
		':to_user_id'   => $_POST['to_user_id'],
		':from_user_id' => $_SESSION['chat_user_id'],
		':chat_message' => $_POST['chat_message'],
		':status' 		=> '1'
	);

	$query = "
		INSERT INTO chat_message
		(to_user_id, from_user_id, chat_message, status)
		VALUES (:to_user_id, :from_user_id, :chat_message, :status)
	";

	$statement = $conn->prepare( $query );

	if ( $statement->execute( $data ) ) {
		echo fetch_user_chat_history($_SESSION['chat_user_id'], $_POST['to_user_id'], $conn);
	}

 ?>