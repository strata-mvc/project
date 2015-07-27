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

function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

function get_placeholder_image( $size = '', $type = '' ) {

    global $_wp_additional_image_sizes;

    $sizes = array();
    $get_intermediate_image_sizes = get_intermediate_image_sizes();

    // Create the full array with sizes and crop info
    foreach( $get_intermediate_image_sizes as $_size ) {

            if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {

                    $sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
                    $sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
                    $sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );

            } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

                    $sizes[ $_size ] = array( 
                            'width' => $_wp_additional_image_sizes[ $_size ]['width'],
                            'height' => $_wp_additional_image_sizes[ $_size ]['height'],
                            'crop' =>  $_wp_additional_image_sizes[ $_size ]['crop']
                    );
            }
    }
    $img = array();
    // Get only 1 size if found
    if ( $size ) {
        if( isset( $sizes[ $size ] ) ) {
            $img = get_bloginfo('stylesheet_directory')."/assets/img/default-".$sizes[ $size ]['width']."x".$sizes[ $size ]['height'].".jpg";
            return $img;
        } else {
            return false;
        }
    }

    return $sizes;
}