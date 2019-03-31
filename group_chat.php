<?php 
	session_start();
	if ( !isset( $_SESSION['chat_user_id'] ) ) {
		header('location:login.php');
	}
	include('database_connection.php');

	if ( $_POST['action'] == "insert_data" ) {
		$data = array(
			':from_user_id' => $_SESSION['chat_user_id'],
			':chat_message' => $_POST['chat_message'],
			':status'		=> '1'
		);

		$query = "
			INSERT INTO chat_message
			(from_user_id, chat_message, status)
			VALUES (:from_user_id, :chat_message, :status)
		";

		$statement = $conn->prepare( $query );
		
		if ($statement->execute( $data) ) {
			echo fetch_group_chat_history($conn);
		}
	}
	elseif ( $_POST['action'] == "fetch_data" ) {
		echo fetch_group_chat_history($conn);
	}

	function fetch_group_chat_history( $conn ) {
		$from_user_id = $_SESSION['chat_user_id'];
		
		$result = fetch_group_chat_data($conn);

		$output = '<ul class="list-unstyled">';

		if ( count( $result ) > 0 ) {

			for ( $i = 0; $i < count( $result ); $i++ ) {
				$delete = '';
				$message_align = 'left';
				$user_name = '';

				# for logged user
				if ( $result[$i]["from_user_id"] == $from_user_id ) {
					// $user_name = '<strong class="text-success">You</strong>';
					$message_align = 'right';
					$delete = '<i class="far fa-trash-alt chat-message-delete-btn"></i>';
				}
				# for other person in the chat
				else {
					if ( $i < count( $result ) - 1 ) {
						if ( $result[$i + 1]["from_user_id"] == $result[$i]["from_user_id"] ) {
							$user_name = '';
						} else {
							$user_name = '<strong class="other-person">' . ucwords( get_user_name($result[$i]['from_user_id'], $conn) ) . '</strong>';
						}
					}
					else {
						$user_name = '<strong class="other-person">' . ucwords( get_user_name($result[$i]['from_user_id'], $conn) ) . '</strong>';
					}
				}

				# wheather to show timestamp or not
				$j = $i + 1;
				if ( $j < count( $result ) ) {
					if ( $result[$j - 1]["from_user_id"] == $result[$j]["from_user_id"] ) {
						$next_msg_time = date('Y-m-d H:i:s', strtotime( $result[$j - 1]["timestamp"] . ' -1 minutes'));
						$msg_time = date('Y-m-d H:i:s', strtotime( $result[$j]["timestamp"]));
						if ( $next_msg_time < $msg_time ) {
							$result[$j]["timestamp"] = '';
						}
					}
				}
				# ends

				$output .= build_group_chat_message( $user_name, $message_align, $result[$i]["chat_message"], $result[$i]["timestamp"] );
			}
		}
		else {
			$output .=
				'<li>' .
					'<div align="center" class="vertical-flip horizontal-flip">' .
						'Say Hi !!!' .
					'</div>' .
				'</li>'
			;
		}

		$output .= '</ul>';

		return $output;
	}

	function fetch_group_chat_data( $conn ) {
		$query = "
			SELECT * FROM chat_message
			WHERE to_user_id = '0'
			ORDER BY timestamp DESC LIMIT 30
		";

		$statement = $conn->prepare( $query );
		$statement->execute();

		return $statement->fetchAll();
	}

	function build_group_chat_message( $user_name, $message_align, $text, $timestamp ) {
		return
		'<li class="chat_item vertical-flip horizontal-flip">' .
			'<div class="message" align="' . $message_align . '">' .
			    '<div class="message-title">' . $user_name . '</div>' .
			    '<div>' .
			   	    '<div align="left" class="message-content">' .
			   	   		wrap_links_in_anchor( nl2br( $text ), false) .
					'</div>' .
				'</div>' .
				'<div class="message-misc">' .
					'<small><em>' . $timestamp . '</em>' . '</small>' .
				'</div>' .
			'</div>' .
		'</li>';
	}

 ?>