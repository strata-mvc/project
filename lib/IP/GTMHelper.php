<?php 
namespace IP;

use \IP\SessionManager;
use \Timber;

class GTMHelper {

	protected static $session_key = 'gtm_event';

	public static function getEvents() {
		$session = SessionManager::getInstance();
		$events = $session->flash(self::$session_key);

		return Timber::compile('gtm-tracking.twig', array('events' => $events));
	}

	public static function addEvent($category = '', $action = '', $label = '') {
		$session = SessionManager::getInstance();
		$session->add(self::$session_key, array(
			'category' => $category,
			'action' => $action,
			'label' => $label
		));
	}

	public static function initDataLayer($datalayer) {
		global $post;
		if(is_null($post)) return;

	    foreach ($datalayer as $key => $value) {
	        if(empty($value)){
	            unset($datalayer[$key]);
	        }
	    }
	    
	    $datalayer['pageTitle'] = $post->post_title;

	    return $datalayer;
	}

}