<?php

/**
 * Couldn't find a way to store the singleton inside the class
 * sorry for all the WP_Session::get_instance() calls! ... Really 
 */

namespace IP;

use \WP_Session;

class SessionManager {

	protected $wp_session = null;
	private static $instance = false;

	protected function __construct() {
		$this->wp_session = WP_Session::get_instance();
	}

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function set($key, $val){
		$this->wp_session[$key] = $val;
	}

	public function add($key, $val) {
		if(!is_array($val)) {
			throw new Exception('Value must be an array');
		} else {
			if($this->exists($key)){
				$old = $this->get($key);
				if(!is_array($old)){
					throw new Exception('Session key does not correspond to an array');
				} else {
					$old[] = $val;
					$this->set($key, $old);
				}
			} else {
				$this->set($key, array($val));
			}
		}
	}

	public function get($key){
		$value = isset($this->wp_session[$key]) ? $this->wp_session[$key] : null;
		if(gettype($value) == 'object') $value = $value->toArray();
		return $value;
	}

	public function exists($key) {
		return isset($this->wp_session[$key]);
	}

	public function flash($key){
		$value = $this->get($key);
		$this->clear($key);
		return $value;
	}

	public function getFormField($key) {
		$form_fields = $this->getFormFields();
		return (isset($form_fields[$key])) ? $form_fields[$key] : '';
	}

	public function getFormFields() {
		return (isset($this->wp_session['_form_fields'])) ? $this->wp_session['_form_fields']->toArray() : array();
	} 

	public function saveFormField($key, $value) {
		$form_fields = $this->getFormFields();
		$form_fields[$key] = $value;
		$this->set('_form_fields', $form_fields);
	}

	/**
	 * Saves Form Fields Value to Session in order to prefill
	 * subsequent forms
	 * @param  GFEntity $form 
	 */
	public function saveFormFields($form) {
		$fields = $form->getLabelMapping();
		foreach($fields as $label => $field) {
			if($field['saveSession'] && $field['inputName']) {
				$this->saveFormField($field['inputName'], $form->getFieldValue($label));
			}
		}
	}

	public function clearAll() {
		$session_array = $this->wp_session->toArray();
		foreach(array_keys($session_array) as $key) {
			$this->clear($key);
		}
	}

	public function clear($key) {
		if(isset($this->wp_session[$key])) { 
			unset($this->wp_session[$key]); 
		}
	}

}