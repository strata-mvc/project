<?php

/***
	{
		Product: ds-royalslider,
		Depends: { photocrati-nextgen }
	}
***/

class P_Ds_RoyalSlider extends C_Base_Product
{
	static $modules = array(
		 'ds-nextgen_royalslider'
	);

	function define()
	{
		parent::define(
			'ds-royalslider',
			'RoyalSlider for NextGEN',
			'RoyalSlider for NextGEN',
			'1.0',
			'http://dimsemenov.com/plugins/royal-slider/wordpress/',
			'Dmitry Semenov',
			'http://dimsemenov.com'
		);

		$module_path = path_join(dirname(__FILE__), 'modules');
		$registry = $this->get_registry();
		$registry->set_product_module_path($this->module_id, $module_path);
		$registry->add_module_path($module_path, TRUE, FALSE);

		foreach (self::$modules as $module_name) $registry->load_module($module_name);

		include_once('class.ds_royalslider_installer.php');
		C_Photocrati_Installer::add_handler($this->module_id, 'C_Ds_RoyalSlider_Installer');
	}
}

new P_Ds_RoyalSlider();
