<?php

class A_NextGen_RoyalSlider_Form extends Mixin_Display_Type_Form
{
    function get_display_type_name()
	{
		return NEXTGEN_ROYALSLIDER_MODULE_NAME;
	}

    function enqueue_static_resources()
    {
        wp_enqueue_script(
            $this->get_display_type_name() . '-js',
            $this->get_static_url('ds-nextgen_royalslider#settings.js')
        );
    }
    
    function _get_field_names()
    {
        return array(
          'thumbnail_override_settings'
        );
    }
}
