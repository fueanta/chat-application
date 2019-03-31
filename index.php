<?php 
	session_start();
	if ( !isset( $_SESSION['chat_user_id'] ) ) {
		header('location:login.php');
	}
	include('database_connection.php');
 ?>

<!DOCTYPE html>
<html>
<head>

	<!-- bootstrap 4 css cdn -->
	<link rel="stylesheet" href="./styles/bootstrap.css">

	<!-- jquery ui css cdn -->
	<link rel="stylesheet" type="text/css" href="./styles/jquery-ui.css">
	
	<!-- font awesome css cdn -->
	<link rel="stylesheet" href="./fontawesome/css/all.css">

	<!-- emoji-one-area css cdn -->
	<link rel="stylesheet" type="text/css" href="./styles/emojionearea.css">

	<!-- custom css -->
	<link rel="stylesheet" type="text/css" href="./styles/style.css">

	<!-- fonts -->
	<link href="https://fonts.googleapis.com/css?family=Quicksand:400,500" rel="stylesheet">

	<title>Dashboard | Chat Application</title>

</head>
<body>	
	<div class="top-elements-container">
		<h3 class="username top-element" ><i class="far fa-user"></i> <?php echo ucwords( $_SESSION['username'] ); ?></h3>
		<form class="top-element" action="logout.php" method="post" style="float: right;">
			<input type="submit" name="logout" class="btn btn-danger" value="Log Out">
		</form>		
	</div>

	<div id="user_details" class="col-md-6 mx-auto mt-5"></div>

	<div class="chat_box"></div>

	<div id="connectivity"></div>

	<!-- bootstrap 4 script cdn -->
	<script src="./scripts/jquery-3.3.1.js"></script>
	<script src="./scripts/popper.js"></script>
	<script src="./scripts/bootstrap.js"></script>

	<!-- jquery ui script cdn -->
	<script src="./scripts/jquery-ui.js"></script>

	<!-- emoji-one-area script cdn -->
	<!-- <script type="text/javascript" src="./scripts/emojionearea.js"></script> -->
	<!-- custom script -->
	<script src="./scripts/script.js"></script>

	<script type="text/javascript">

		$(document).ready( function() {

			window['user_details'] = '';

			update_connectivity();

			setInterval(function() {
				update_connectivity();
			}, 2000);

			function fetch_user() {
				$.ajax( {
					url:"fetch_user.php",
					method:"POST",
					success:function( data ) {
						if ( window['user_details'].localeCompare(data) != 0 ) {
							window['user_details'] = data;
							$('#user_details').html(data);
						}
					}
				} );
			}

			function display_connectivity_failure_message() {
				$('#user_details').html('<div class="jumbotron jumbotron-fluid text-center"><h2 class="display-5">Please, try again later.</h2></div>');
			}

			function update_last_activity() {
				$.ajax( {
					url:"update_last_activity.php",
					success:function() { }
				} );
			}

			function update_connectivity() {
				$.ajax( {
					url:"update_connectivity.php",
					success:function(data) {
						if (data == 'On') {
							$('#connectivity').html('<span class="badge badge-pill badge-success">You\'re Online!</span>');
							update_chat_history_data();
							fetch_user();
							update_last_activity();
						}
						else if (data == 'Off') {
							$('#connectivity').html('<span class="badge badge-pill badge-danger">You\'re Offline</span>');
							display_connectivity_failure_message();
						}
					}
				} );
			}

			function make_chat_dialog_box(to_user_id, to_user_name) {
				var model_content = 
					'<div id="user_dialog_' + to_user_id + '" class="user_dialog" title="' + to_user_name + '">' +
						'<div class="chat_history vertical-flip horizontal-flip" data-touserid="' + to_user_id + '" id="chat_history_' + to_user_id + '">' +
						'</div>' +
						'<div class="form-group chat_message_holder">' +
							'<textarea placeholder="Type here..." name="chat_message_' + to_user_id + '" id="chat_message_' + to_user_id + '" class="form-control chat_message">' +
							'</textarea>' +
						'</div>' +
						'<div style="margin-top: -0.4em;" align="right">' +
							'<div class="form-check form-check-inline hit-enter-to-send-holder">' +
								'<input class="form-check-input" type="checkbox" id="hit-enter-to-send" checked>' +
								'<label class="form-check-label hit-enter-to-send-label" for="hit-enter-to-send">enter to send</label>' +
							'</div>' +
							'<button name="send_chat" id="' + to_user_id + '" class="send_chat send-btn">' + 'Send' + '</button>' +
						'</div>' +
					'</div>';

				$('.chat_box').html( model_content );
			}

			function make_group_chat_dialog_box() {
				var model_content = 
					'<div id="group_chat_dialog" title="Group Chat">' +
						'<div id="group_chat_history" class="chat_history vertical-flip horizontal-flip"></div>' +
						'<div class="form-group chat_message_holder">' +
							'<textarea placeholder="Type here..." name="group_chat_message" id="group_chat_message" class="form-control chat_message"></textarea>' +
						'</div>' +
						'<div style="margin-top: -0.4em;" align="right">' +
							'<div class="form-check form-check-inline hit-enter-to-send-holder">' +
								'<input class="form-check-input" type="checkbox" id="hit-enter-to-send" checked>' +
								'<label class="form-check-label hit-enter-to-send-label" for="hit-enter-to-send">enter to send</label>' +
							'</div>' +
							'<button name="send_group_chat" id="send_group_chat" class="send-btn">Send</button>' +
						'</div>' +
					'</div>';

				$('.chat_box').html( model_content );
			}

			function fetch_user_chat_history( to_user_id ) {
				if ( $('#chat_history_' + to_user_id).length ) {
					$.ajax( {
						url:"fetch_user_chat_history.php",
						method:"POST",
						data:{ to_user_id:to_user_id },
						success:function(data) {
							if ( window['chat_history' + to_user_id].localeCompare(data) != 0 ) {
								window['chat_history' + to_user_id] = data;
								$('#chat_history_' + to_user_id).html( data );
							}
							// $('.chat_history').scrollTop($('chat_history')[0].scrollHeight);
						}
					} )
				}
			}

			function update_chat_history_data() {
				fetch_group_chat_history();
				$('.chat_history').each( function() {
					var to_user_id = $(this).data( 'touserid' );
					fetch_user_chat_history( to_user_id );
				} );			
			}

			function fetch_group_chat_history() {
				if ( $('#group_chat_history').length ) {
					update_last_group_chat_activity();
					var action = "fetch_data";
					$.ajax( {
						url:"group_chat.php",
						method:"POST",
						data:{ action:action },
						success:function(data) {
							if ( window['group_chat_history'].localeCompare(data) != 0 ) {
								window['group_chat_history'] = data;
								$('#group_chat_history').html( data );
							}							
						}
					} )
				}
			}

			function update_last_group_chat_activity() {
				$.ajax( {
					url:"update_last_group_chat_activity.php",
					success:function() { }
				} );
			}

			$(document).on('keypress', '.chat_message', function(e) {
				var hitEnterCheckBox = $(this).parent().next().find('#hit-enter-to-send');
				if (hitEnterCheckBox.is(':checked')) {
					if (e.which == 13 && ! e.shiftKey) {
				    	$(this).parent().next().find('.send-btn').click();
				    	return false;
				    }
				}
			});

			$(document).on('click', '.start_chat', function() {
				var to_user_id = $(this).data('touserid');
				var to_user_name = $(this).data('tousername');
				to_user_name = to_user_name.charAt(0).toUpperCase() + to_user_name.slice(1);
				
				make_chat_dialog_box(to_user_id, to_user_name);
				fetch_user_chat_history( to_user_id );

				$('#user_dialog_' + to_user_id).dialog( {
					autoOpen:false,
					width:360,
					open: function () {
						window['chat_history' + to_user_id] = '';
					},
		            close: function () {
		                $('#user_dialog_' + to_user_id).dialog('destroy');
    					$('#user_dialog_' + to_user_id).html('');
    					window['chat_history' + to_user_id] = '';
		            }
				} );

				$('#user_dialog_' + to_user_id).dialog('open');
				// $('#chat_message_' + to_user_id).emojioneArea();
			} );

			$(document).on('click', '.send_chat', function() {
				var to_user_id = $(this).attr('id');
				var chat_message = $('#chat_message_' + to_user_id).val();

				if ( chat_message.replace(/\s/g, '').length ) {
					$.ajax( {
						url:"insert_chat.php",
						method:"POST",
						data:{ to_user_id:to_user_id, chat_message:chat_message },
						success:function( data ) {
							$('#chat_message_' + to_user_id).val('');
							setTimeout(function() {
								$('#chat_history_' + to_user_id).html( data );
								$('#chat_history_' + to_user_id).scrollTop(0);
							}, 200);
							
						}
					} )
				}
				else {
					$('#chat_message_' + to_user_id).val('');
					// var text_emoji_area = $('#chat_message_' + to_user_id).emojioneArea();
					// text_emoji_area[0].emojioneArea.setText('');
				}
			} );

			$(document).on('focus', '.chat_message', function() {
				var is_typing = 'yes';
				$.ajax( {
					url:"update_is_typing_status.php",
					method:"POST",
					data:{ is_typing:is_typing },
					success:function() {

					}
				} )
			} );

			$(document).on('blur', '.chat_message', function() {
				var is_typing = 'no';
				$.ajax( {
					url:"update_is_typing_status.php",
					method:"POST",
					data:{ is_typing:is_typing },
					success:function() {
						
					}
				} )
			} );

			$(document).on('click', '#group_chat', function() {
				make_group_chat_dialog_box();
				fetch_group_chat_history();

				$('#group_chat_dialog').dialog( {
					autoOpen:false,
					width:360,
					open: function () {
						window['group_chat_history'] = '';
					},
		            close: function () {
		                $('#group_chat_dialog').dialog('destroy');
						$('#group_chat_dialog').html('');
						update_last_group_chat_activity();
						window['group_chat_history'] = '';
		            }
				} );

				$('#group_chat_dialog').dialog('open');
			} );

			$(document).on('click', '#send_group_chat', function() {
				var chat_message = $('#group_chat_message').val();
				var action = 'insert_data';

				if ( chat_message.replace(/\s/g, '').length ) {
					$.ajax( {
						url:"group_chat.php",
						method:"POST",
						data:{ chat_message:chat_message, action:action },
						success:function( data ) {
							$('#group_chat_message').val('');
							setTimeout(function() {
								$('#group_chat_history').html( data );
								$('#group_chat_history').scrollTop(0);
							}, 200);
						}
					} )
				}
				else {
					$('#group_chat_message').val('');
				}
			} );

		} );

	</script>

</body>
</html>