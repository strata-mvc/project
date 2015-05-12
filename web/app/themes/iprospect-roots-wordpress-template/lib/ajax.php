<?php 

/** AJAX Action Template ********************************

You can use the template below to create an ajax action.

1.  Copy the following code below, and replace ACTION by a unique name for you 
	ajax action.

	add_action( 'wp_ajax_ACTION', 'ACTION_ajax_callback' );
	add_action( 'wp_ajax_nopriv_ACTION', 'ACTION_ajax_callback' );

	function ACTION_ajax_callback() {
		check_ajax_referer(AJAX_NONCE_KEY, 'security' );
		
		// Return the new data to the template
		echo json_encode(array(
			'ajax-test' => 'successfully made an ajax call',
		));
	    die();
	}

2.  Copy the following code in your javascript to call the ajax action, replacing ACTION
	with the unique name you gave your action in (1)

	$.ajax({
			url: WpConfig.ajaxurl,
			method: 'POST',
			data: {
				action: 'ACTION',
				security: WpConfig.security,
			},
			dataType: 'json'
		})
		.done(function(data){
			console.log(data['ajax-test']);			
		})
		.fail(function(data){
			console.log("Oh noes! An error occured whilst ajaxing!");
		});

3.  Run your javascript, and you should see 'successfully made an ajax call' in your console. Don't forget
	to compile your JS with the Grunt file before testing. 

************** Put your actions below ********************************/


