<?php

class A_NextGen_RoyalSlider_Forms extends Mixin
{
    function initialize()
    {
        $this->add_form(
			NEXTGEN_DISPLAY_SETTINGS_SLUG, NEXTGEN_ROYALSLIDER_MODULE_NAME
		);
    }
}