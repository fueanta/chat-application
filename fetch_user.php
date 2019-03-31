<?php 
	session_start();
	if ( !isset( $_SESSION['chat_user_id'] ) ) {
		header('location:login.php');
	}
	include('database_connection.php');

	$output = '';

	$query = "
		SELECT * FROM login
		WHERE user_id <> " . $_SESSION['chat_user_id'] . "
	";
	$statement = $conn->prepare( $query );
	$statement->execute();
	$count = $statement->rowCount();

	if ( $count > 0 ) {
		$result = $statement->fetchAll();

		$output = '
			<div class="mb-2"><button type="button" name="group_chat" id="group_chat" class="btn btn-sm btn-success">Group Chat ' . count_unseen_group_messages($_SESSION['chat_user_id'], $conn) . '</button></div>

			<div class="table-responsive">
			  <table class="table table-borderless border-top">
				  <thead class="">
				    <tr>
				      <th scope="col">Username</th>
				      <th scope="col">Status</th>
				      <th scope="col">Action</th>
				    </tr>
				  </thead>
				  <tbody>
		';

		foreach ($result as $row) {
			
			$current_timestamp = strtotime(date('Y-m-d H:i:s') . '-5 second');
			$current_timestamp = date('Y-m-d H:i:s', $current_timestamp);
			$user_last_activity = fetch_user_last_activity($row['user_id'], $conn);
			$status = ($user_last_activity > $current_timestamp) ? '<span class="badge badge-pill badge-success">Online</span>' : '<span class="badge badge-pill badge-danger">Offline</span>';

			$output .= '
				<tr>
					<td class="username">' . ucwords( $row['username'] ) . ' ' . count_unseen_messages($row['user_id'], $_SESSION['chat_user_id'], $conn) . ' ' . fetch_is_typing_status($row['user_id'], $conn) . '</td>
					<td>' . $status . '</td>
					<td><button type="button" class="btn btn-info btn-sm start_chat" data-touserid="' . $row['user_id'] . '" data-tousername="' . $row['username'] . '">Open Chat</button></td>
				</tr>
			';
		}

		$output .= '
				</tbody>
				</table>
			</div>
		';
	}
	else {
		$output = '<div class="jumbotron jumbotron-fluid text-center"><h2 class="display-5">You\'re alone in this universe.</h2></div>';
	}

	echo $output;

	function fetch_user_last_activity($user_id, $conn) {
		$query = "
			SELECT * FROM login_details
			WHERE user_id = '$user_id'
			ORDER BY last_activity DESC
			LIMIT 1
		";
		$statement = $conn->prepare( $query );
		$statement->execute();
		$result = $statement->fetchAll();

		foreach ($result as $row) {
			return $row['last_activity'];
		}
	}

	function count_unseen_messages( $from_user_id, $to_user_id, $conn ) {
		$query = "
			SELECT * FROM chat_message
			WHERE from_user_id = '$from_user_id'
			AND to_user_id = '$to_user_id'
			AND status = '1'
		";
		$statement = $conn->prepare( $query );
		$statement->execute();
		$count = $statement->rowCount();

		$output = '';
		if ( $count > 0 ) {
			$output = '<span class="badge badge-pill badge-primary">' . $count . '</span>';
		}
		return $output;	
	}

	function count_unseen_group_messages( $from_user_id, $conn ) {
		$query = "
			SELECT * FROM chat_message
			WHERE to_user_id = 0 AND from_user_id <> $from_user_id AND timestamp >
			( SELECT MAX(last_group_chat_activity) FROM login_details
			WHERE user_id = $from_user_id )
		";
		$statement = $conn->prepare( $query );
		$statement->execute();
		$count = $statement->rowCount();

		$output = '';
		if ( $count > 0 ) {
			$output = '<span class="badge badge-pill badge-info">' . $count . '</span>';
		}
		return $output;	
	}

	function fetch_is_typing_status($user_id, $conn) {
		$query = "
			SELECT is_typing FROM login_details
			WHERE user_id = '" . $user_id . "'
			ORDER BY last_activity DESC
			LIMIT 1
		";

		$statement = $conn->prepare( $query );
		$statement->execute();
		$result = $statement->fetchAll();
		$output = '';
		foreach ($result as $row) {
			if ( $row['is_typing'] == 'yes' ) {
				$output = '
					<small class="is-typing"><em><span class="text-muted">
						typing..
					</span></em></small>
				';
			}
		}
		return $output;
	}

 ?>