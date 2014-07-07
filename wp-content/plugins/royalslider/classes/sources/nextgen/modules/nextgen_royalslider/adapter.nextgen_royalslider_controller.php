<?php

class A_NextGen_RoyalSlider_Controller extends Mixin {
	function enqueue_frontend_resources($displayed_gallery)
	{        
        // TODO: find a way to include JS/CSS automatically only on pages with slider
	}
	

	function index_action($displayed_gallery, $return=FALSE)
	{
		$storage = $this->get_registry()->get_utility('I_Gallery_Storage');
		$list = $displayed_gallery->get_included_entities();

		$thumbnail_size_name = 'thumbnail';

		if ($display_settings['override_thumbnail_settings'])
	    {
	        $dynthumbs = $this->object->get_registry()->get_utility('I_Dynamic_Thumbnails_Manager');

	        if ($dynthumbs != null)
	        {
	            $dyn_params = array(
	                'width' => $display_settings['thumbnail_width'],
	                'height' => $display_settings['thumbnail_height'],
	            );

	            if ($display_settings['thumbnail_quality'])
	                $dyn_params['quality'] = $display_settings['thumbnail_quality'];

	            if ($display_settings['thumbnail_crop'])
	                $dyn_params['crop'] = true;

	            if ($display_settings['thumbnail_watermark'])
	                $dyn_params['watermark'] = true;

	            $thumbnail_size_name = $dynthumbs->get_size_name($dyn_params);
	        }
	    }


		$params = array(
			'images'				=> $list,
			'displayed_gallery_id'	=>	$displayed_gallery->id(),
			'storage'				=>	$storage,
			'thumbnail_size_name'	=>  $thumbnail_size_name,
			'custom_css_rules'		=>	''
		);
                
        $params = $this->object->prepare_display_parameters($displayed_gallery, $params);
    
    	return $this->object->render_view('ds-nextgen_royalslider#nextgen_royalslider_template', $params, $return);

	}
}
