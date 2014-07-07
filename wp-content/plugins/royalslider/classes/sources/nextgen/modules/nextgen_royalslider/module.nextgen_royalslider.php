<?php

/*
{
	Module: ds-nextgen_royalslider
}
 */

define('NEXTGEN_ROYALSLIDER_MODULE_NAME', 'ds-nextgen_royalslider');

class M_NextGen_RoyalSlider extends C_Base_Module
{
	function define($context=FALSE)
	{
		parent::define(
			'ds-nextgen_royalslider',
			'NextGen RoyalSlider',
			"Integrates RoyalSlider with NextGEN",
			'0.1',
			'http://dimsemenov.com/plugins/royal-slider/wordpress/',
			'Dmitry Semenov',
			'http://dimsemenov.com',
			$context
		);

		include_once('class.nextgen_royalslider_installer.php');
		C_Photocrati_Installer::add_handler($this->module_id, 'C_NextGen_RoyalSlider_Installer');
	}

	function get_type_list()
	{
		return array(
			'A_Nextgen_RoyalSlider' => 'adapter.nextgen_royalslider.php',
			'A_Nextgen_RoyalSlider_Controller' => 'adapter.nextgen_royalslider_controller.php',
			'A_Nextgen_RoyalSlider_Form' => 'adapter.nextgen_royalslider_form.php',
			'A_Nextgen_RoyalSlider_Forms' => 'adapter.nextgen_royalslider_forms.php',
			'C_NextGen_RoyalSlider_Installer' => 'class.nextgen_royalslider_installer.php',
			'A_Nextgen_RoyalSlider_Mapper' => 'adapter.nextgen_royalslider_mapper.php',
		);
	}

	function _register_adapters()
	{
		$this->get_registry()->add_adapter(
			'I_Display_Type_Controller',
			'A_NextGen_RoyalSlider_Controller',
			$this->module_id
		);

		$this->get_registry()->add_adapter(
			'I_Display_Type_Mapper',
			'A_NextGen_RoyalSlider_Mapper'
		);

		$this->get_registry()->add_adapter(
			'I_Form',
			'A_NextGen_RoyalSlider_Form',
			$this->module_id
		);

        $this->get_registry()->add_adapter(
            'I_Form_Manager',
            'A_NextGen_RoyalSlider_Forms'
        );
	}

}

new M_NextGen_RoyalSlider;
