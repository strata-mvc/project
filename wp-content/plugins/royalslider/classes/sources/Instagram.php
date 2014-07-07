<?php 
if( !defined('WPINC') ) exit('No direct access permitted');

class NewRoyalSliderInstagramSource {

	private static $access_token;

	function __construct( $curr_options = null ) {

	}
	public static function show_admin_options( $curr_options = null ) {
		?>
		<div class="rs-info">
			<p class="rs-awesome-paragraph"><?php _e('Here you may create <a href="http://instagram.com" target="_blank">Instagram</a> gallery from  recent photos, filtered by username or tag.', 'new_royalslider'); ?></p>

			<?php

			self::$access_token = get_option('new_royalslider_instagram_oauth_token');

			if(!self::$access_token || !isset(self::$access_token->access_token)) {
				printf(__('<p style="padding: 12px;background: rgb(255, 236, 236);font-size: 14px;line-height: 20px;"><strong style="color:#C00;">Important note!</strong> Before you start, you need to register Instagram API client.<br/>Please go to <a href="%s">RoyalSlider global settings</a>, enter "Instagram client ID" and "client secret key" and connect to Instagram.</p>', 'new_royalslider'), get_admin_url() . "admin.php?page=new_royalslider_settings");
			}
			?>

			<p><?php _e('Instagram thumbnail image size is 150x150px, for some templates you might need to change default size of thumbnail area ("Thumbnails,tabs,bullets" -> "Thumbnail Width" and "Thumbnail Height").', 'new_royalslider'); ?></p>

            <div class="help-video"><a class="in-page-action" href="http://help.dimsemenov.com/kb/wordpress-royalslider-tutorials/wp-creating-royalslider-from-instagram-photos" target="_blank"><?php _e('View help video about how to create Instagram gallery', 'new_royalslider'); ?></a></div><br/>
        </div>
		<?php
		
		$fields = array(
			

			array(
				'name' => 'usernameortag',
                'label' => __( 'Username or tag', 'new_royalslider' ),
                'desc' => __( '', 'new_royalslider' ),
                'type' => 'text',
                'default' => '',
                'data-type' => 'str',
                'size' => 'short',
                'ignore' => true
			),

			array(
            	'desc' => __( 'Enter Instagram username or #tag. The tag must start with <strong>#</strong> symbol. For example: <strong>david</strong> or <strong>#winter</strong>', 'new_royalslider' ),
            	'delimiter' => true
            ),

            array(
				'name' => 'limit',
                'label' => __( 'Limit', 'new_royalslider' ),
                'desc' => __( '', 'new_royalslider' ),
                'type' => 'number',
                'default' => '10',
                'data-type' => 'num',
                'size' => 'short',
                'ignore' => true
			),
			array(
            	'desc' => __( 'Maximum number of images to fetch from Instagram and include in slider. Max 250.', 'new_royalslider' )
            )
		);

		if( isset($curr_options) && isset($curr_options['rs_instagram']) ) {
			$opts = $curr_options['rs_instagram'];
			$fields = NewRoyalSliderOptions::parseCurrentOptions($fields, $opts);
		}
	
		echo '<div id="rs-instagram-options" class="rs-body-options">';
		echo '<h3>' . __('Instagram Settings', 'new_royalslider') .'</h3>';
		?>

		

		<?php
		foreach ( $fields as $key => $field ) {
        	echo NewRoyalSliderOptions::get_field_html($field, 'rs_instagram');
        }
        echo '</div>';

		
	}
	public static function getInstagramUserID($username) {

	    $username = strtolower($username);
	    $url = "https://api.instagram.com/v1/users/search?q=".$username."&access_token=".self::$access_token;
	    $get = file_get_contents($url);
	    $json = json_decode($get);

	    foreach($json->data as $user)
	    {
	        if($user->username == $username)
	        {
	            return $user->id;
	        }
	    }

	    return false; // return this if nothing is found
	}

	public static function get_data($slides, $options, $type) {
		if($type === 'instagram') {
			if(isset($options['rs_instagram'])) {

				self::$access_token = get_option('new_royalslider_instagram_oauth_token');

				if(self::$access_token && isset(self::$access_token->access_token)) {
					self::$access_token = self::$access_token->access_token;
				} else {
					return "Instagram access token is missing, go to RoyalSlider global settings and connect your Instagram account.";
				}

				$options = $options['rs_instagram'];
			   

			   	if( !isset($options['usernameortag']) ||  !trim($options['usernameortag']) ) {
		    		return __('Enter Instagram username or tag.', 'new_royalslider');
		    	}

		    	$max_images = (int)$options['limit'];
		    	if( !($max_images > 0) ) {
		    		return  __('Incorrect value in "Limit" option, set it to integer.', 'new_royalslider');
		    	}
		    	if($max_images > 300) {
		    		$max_images = 300;
		    	}

			    $data = array(
			    	'access_token' => self::$access_token,
			    	'count' => $max_images
			    );
		        
			    $is_tag = strpos($options['usernameortag'], '#') > -1 ? true : false;

			    if($is_tag) {
			    	$tag = str_replace('#', '', $options['usernameortag']);
			    	$url = 'https://api.instagram.com/v1/tags/'. $tag .'/media/recent?'.http_build_query($data);
			    } else {
			    	$user_id = self::getInstagramUserID( $options['usernameortag'] );
			    	if(!$user_id) {
			    		return 'Username '.$options['usernameortag'].' not found';
			    	}
			    	$url = 'https://api.instagram.com/v1/users/'. $user_id .'/media/recent?'.http_build_query($data);
			    }
			    $url = apply_filters( 'new_rs_instagram_api_url', $url, $options);
				


			    $images = array();
			    $gotAllResults = false;
			    $numPages = 1;
			    $imageCount = 0;
			    while(!$gotAllResults) {
			    	$response = wp_remote_get($url, array(
						'timeout' => 30,
						'redirection' => 5,
						'sslverify' => false
					));

					if (is_wp_error($response)) {
						$gotAllResults = true;
						if(count($images) > 0) {
							break;
						} else {
							return print_r($response, true) . ' Request URL: '.$url;
						}
					}

					$response = $response['body'];
					$response = json_decode($response, ARRAY_A);
					if(isset($response['pagination']) && isset($response['pagination']['next_url']) ) {
						$url = $response['pagination']['next_url'];
					} else {
						$gotAllResults = true;
					}

					if (isset($response['data'])) {
						$response_items = $response['data'];
						foreach ($response_items as $key => $item) {
							$imageCount++;

							if($imageCount > $max_images) {
								$gotAllResults = true;
								break;
							}

							$image = &$images[$imageCount];

							$image['image'] = $item['images']['standard_resolution']['url'];
							$image['thumbnail'] = $item['images']['thumbnail']['url'];
							$image['title'] = isset($item['caption']) ? $item['caption']['text'] : '';
							$image['original_obj'] = $item;
							
						}
					}
					if($imageCount > 300 || $numPages > 20) {
						$gotAllResults = true;
						break;
					}

					$numPages++;
			    }

				if( count($images) > 0) {
					return $images;
				} else {
					return sprintf(__('No images found. %s', 'new_royalslider'), isset($response['error']) ? $response['error'] : '');
				}

				return $images;
			}
		}
		return $slides;
	}



}


