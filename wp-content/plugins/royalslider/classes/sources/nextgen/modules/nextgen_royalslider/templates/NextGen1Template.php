<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><?php if (!empty ($gallery)) :

	/* Template for old versions of NextGEN (< 2.0) */

	require_once(NEW_ROYALSLIDER_PLUGIN_PATH .  'classes/rsgenerator/NewRoyalSliderGenerator.php');
	echo NewRoyalSliderGenerator::generateSlides(
		true,
		true,
		$gallery->ID,
		'nextgen', 
		null,
		$images,
		null,
		null,
		null,
		true
	);

endif;
?>
