<?php
	$servername = "localhost";
	$username = "root";
	$password = "";
	$database = "chat";

	$signal = '';

	try {
	    $conn = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password);
	    // set the PDO error mode to exception
	    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    //$signal = '<span class="badge badge-pill badge-success mt-1 ml-2">Server is Up</span>';
	    $signal = 'On';
	}
	catch(PDOException $e) {
    	//$signal = '<span class="badge badge-pill badge-danger mt-1 ml-2">Server is Down</span>';
    	$signal = 'Off';
    }

    # common functions

    function fetch_user_chat_history($from_user_id, $to_user_id, $conn) {
    	update_seen_msgs($from_user_id, $to_user_id, $conn);
		$result = fetch_chat_data($from_user_id, $to_user_id, $conn);
		$output = '<ul class="list-unstyled">';
		
		if ( count( $result ) > 0 ) {
			$check_for_seen = true;

			for ( $i = 0; $i < count( $result ); $i++ ) {
				$seen = '';
				$delete = '';
				$message_align = 'left';
				$user_name = '';

				# for logged user
				if ( $result[$i]["from_user_id"] == $from_user_id ) {
					$message_align = 'right';
					$delete = '<i class="far fa-trash-alt chat-message-delete-btn"></i>';

					if ( $check_for_seen && $result[$i]["status"] == 0 ) {
						$check_for_seen = false;
						$seen = '<i class="fas fa-check ml-1"></i>';
					}

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

				$output .= build_message( $user_name, $message_align, $result[$i]["chat_message"], $result[$i]["timestamp"], $seen );
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

	function update_seen_msgs($from_user_id, $to_user_id, $conn) {
		$query = "
			UPDATE chat_message
			SET status = '0'
			WHERE from_user_id = '" . $to_user_id . "'
			AND to_user_id = '" . $from_user_id . "'
			AND status = '1'
		";

		$statement = $conn->prepare( $query );
		$statement->execute();
	}

	function fetch_chat_data($from_user_id, $to_user_id, $conn) {
		$query = "
			SELECT * FROM chat_message
			WHERE (from_user_id = '" . $from_user_id . "'
			AND to_user_id = '" . $to_user_id . "')
			OR (from_user_id = '" . $to_user_id . "'
			AND to_user_id = '" . $from_user_id . "')
			ORDER BY timestamp DESC LIMIT 20
		";

		$statement = $conn->prepare( $query );
		$statement->execute();
		return $statement->fetchAll();
	}

	function build_message( $user_name, $message_align, $text, $timestamp, $seen ) {
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
					'<small style="font-size: 0.5em;">' . $seen . '</small>' .
				'</div>' .
			'</div>' .
		'</li>';
	}

	function get_user_name($user_id, $conn) {
		$query = "
			SELECT username FROM login
			WHERE user_id = '$user_id'
		";

		$statement = $conn->prepare( $query );
		$statement->execute();
		$result = $statement->fetchAll();

		foreach ($result as $row) {
			return $row['username'];
		}
	}

	function wrap_links_in_anchor( $text ) {
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		if ( preg_match( $reg_exUrl, $text, $url ) ) {
		    return preg_replace($reg_exUrl, "<a rel=\"noopener noreferrer\" target=\"_blank\" href=\"{$url[0]}\">{$url[0]}</a> ", $text);
		}
		return $text;
	}