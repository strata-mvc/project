<?php if (!defined ('ABSPATH')) die ('No direct access allowed');


	
	foreach ($images as &$image) {
		$thumb_size = $storage->get_image_dimensions($image, $thumbnail_size_name);
		$image->title = $image->alttext;
		$image->thumbnailsSize = $storage->get_image_dimensions($image, $thumbnail_size_name);
		$image->thumbnailURL = $storage->get_image_url($image, $thumbnail_size_name);
		$image->imageURL = $storage->get_image_url($image);
	}

	require_once(NEW_ROYALSLIDER_PLUGIN_PATH .  'classes/rsgenerator/NewRoyalSliderGenerator.php');
	echo NewRoyalSliderGenerator::generateSlides(
		true,
		true,
		$displayed_gallery_id,
		'nextgen', 
		null,
		$images,
		null,
		null,
		null,
		true
	);

	NewRoyalSliderMain::custom_footer_scripts( array(
		$displayed_gallery_id => NewRoyalSliderMain::$sliders_init_code[$displayed_gallery_id]
	) );
	unset(NewRoyalSliderMain::$sliders_init_code[$displayed_gallery_id]);