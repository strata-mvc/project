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

/*
* Initialize Login Security
*/
if(current_theme_supports("login-security")) {
	function setup_login_security() {
		$security = new \IP\Security();
		$security->addOptionsPage();
		$security->protectLoginPage();
	}
	add_action('init', 'setup_login_security');
}

/**
 * Replaces the {site_name} tag with bloginfo(name) in Gravity Forms notifications
 */

add_filter('gform_replace_merge_tags', 'replace_download_link', 10, 7);
function replace_download_link($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    
    $custom_merge_tag = '{site_name}';
    
    if(strpos($text, $custom_merge_tag) === false)
        return $text;
    
    $site_name = get_bloginfo("name");
    $text = str_replace($custom_merge_tag, $site_name, $text);
    
    return $text;
}

