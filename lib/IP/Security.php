<?php

namespace IP;

/**
 * Security
 * Restrict access to wp-login.php page
 */

class Security {

	private $allowedIPs = array('127.0.0.1', '66.158.136.227'); 

	public function __construct() {
	}

	public function addOptionsPage() {
		if( function_exists('acf_add_options_page') ) {
			// ADD ALLOWED IPS OPTION PAGE
			acf_add_options_page("Allowed IPs");	
		}
	}

	public function protectLoginPage() {
		//IF IS LOGIN PAGE
		if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) ) ) {

			// LIST OF IP ADDRESSES THAT CAN ACCESS THE ADMIN BY DEFAULT
			$allowed = $this->allowedIPs;
			$customIPs = get_field('allowed-ip-addresses', 'option');

			if($customIPs){
				foreach($customIPs as $row) {
					$allowed[] = $row["ip-address"];
				}	
			}

		    $ipaddress = $this->getClientIP();
		    if(!in_array( $ipaddress , $allowed)) {
		    	wp_redirect( home_url('/login-error/') ); 
		    	exit;
		    }
		}
	}

	protected function getClientIP() {
		$ipaddress = '';
	    if (isset($_SERVER['HTTP_CLIENT_IP']))
	        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_X_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	    else if(isset($_SERVER['HTTP_FORWARDED']))
	        $ipaddress = $_SERVER['HTTP_FORWARDED'];
	    else if(isset($_SERVER['REMOTE_ADDR']))
	        $ipaddress = $_SERVER['REMOTE_ADDR'];
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;
	}

}