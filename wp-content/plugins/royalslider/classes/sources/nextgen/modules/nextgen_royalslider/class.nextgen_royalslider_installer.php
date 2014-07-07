<?php

class C_NextGen_RoyalSlider_Installer extends C_Gallery_Display_Installer
{
	function install($reset=FALSE)
	{
		$this->install_display_type(
			NEXTGEN_ROYALSLIDER_MODULE_NAME, array(
				'title'							=>	'NextGEN RoyalSlider',
				'entity_types'					=>	array('image'),
				'default_source'				=>	'galleries',
				'preview_image_relpath'			=>	'ds-nextgen_royalslider#preview.png',
				'view_order' => NEXTGEN_DISPLAY_PRIORITY_BASE + 100
			)
		);
	}

	function uninstall($hard=FALSE)
	{
		if ($hard) {
			$mapper = C_Display_Type_Mapper::get_instance();
			if (($entity = $mapper->find_by_name(NEXTGEN_ROYALSLIDER_MODULE_NAME))) {
				$mapper->destroy($entity);
			}
		}
	}
}
