<?php

class A_NextGen_RoyalSlider_Mapper extends Mixin
{
	function initialize()
	{
		$this->object->add_post_hook(
			'set_defaults',
			'RoyalSlider Slideshow Defaults',
			get_class(),
			'set_nextgen_royalslider_defaults'
		);
	}

	function set_nextgen_royalslider_defaults($entity)
	{
		if ($entity->name == NEXTGEN_ROYALSLIDER_MODULE_NAME) {
			// override thumbnail settings
	        $this->object->_set_default_value($entity, 'settings', 'override_thumbnail_settings', 0);
	        $this->object->_set_default_value($entity, 'settings', 'thumbnail_quality', '100');
	        $this->object->_set_default_value($entity, 'settings', 'thumbnail_crop', 1);
	        $this->object->_set_default_value($entity, 'settings', 'thumbnail_watermark', 0);
		}
	}
}
