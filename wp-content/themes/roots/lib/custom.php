<?php
/**
 * Custom functions
 */

/**
 * Removes default width and height attributes on images
 */
add_filter( 'post_thumbnail_html', 'remove_width_attribute', 10);
add_filter( 'image_send_to_editor', 'remove_width_attribute', 10);
function remove_width_attribute( $html ) {
   $html = preg_replace( '/(width|height)="\d*"\s/', "", $html );
   return $html;
}

/**
 * Removes empty keys from the dataLayer Object and sets the format
 * of the date entry to ensure cohesion between languages
 */
add_filter('gtp4wp_compile_datalayer', 'init_gtm_datalayer', 10);
function init_gtm_datalayer($datalayer) {
    return \IP\GTMHelper::initDataLayer($datalayer);
}