<?php

class C_Ds_RoyalSlider_Installer
{
	function uninstall($hard=FALSE)
	{
		foreach (P_Ds_RoyalSlider::$modules as $module_name) {
			if (($handler = C_Photocrati_Installer::get_handler_instance($module_name))) {
				if (method_exists($handler, 'uninstall')) $handler->uninstall($hard);
			}
		}
	}
}