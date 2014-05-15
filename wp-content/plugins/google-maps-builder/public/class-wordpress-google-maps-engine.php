<?php
/**
 * Google Maps Builder Engine
 *
 * The Google Maps engine class for WordPress Google Maps Builder
 *
 * @package   Google_Maps_Builder
 * @author    Devin Walker <devin@wordimpress.com>
 * @license   GPL-2.0+
 * @link      http://wordimpress.com
 * @copyright 2014 WordImpress, Devin Walker
 */

class Google_Maps_Builder_Engine {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	public function __construct() {

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin            = Google_Maps_Builder::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();


		// Filter to automatically add maps to post type content
		add_filter( 'the_content', array( $this, 'the_content' ), 2 );

		//add shortcode support
		add_shortcode( 'google_maps', array( $this, 'google_maps_shortcode' ) );

	}

	/**
	 * Google Map display on Single Posts.
	 *
	 * the [google_maps] shortcode will be prepended/appended to the post body, once for each map
	 * The shortcode is used so it can be filtered - for example WordPress will remove it in excerpts by default.
	 *
	 */
	function the_content( $content ) {

		global $post;

		if ( $post->post_type == 'google_maps' ) {

			$shortcode = '[google_maps ';
			$shortcode .= 'id="' . $post->ID . '"';
			$shortcode .= ']';

			//Output shortcode
			echo do_shortcode( $shortcode );

		}

		return $content;


	}


	/**
	 * Single Template Function
	 *
	 * @param $single_template
	 *
	 * @return string
	 */
	public function get_google_maps_template( $single_template ) {
		global $post;
		if ( file_exists( get_stylesheet_directory() . '/google-maps/' . $single_template ) ) {
			$output = get_stylesheet_directory() . '/google-maps/' . $single_template;
		} else {
			$output = dirname( __FILE__ ) . '/views/' . $single_template;
		}


		return $output;
	}


	/**
	 * Google Maps Builder Shortcode
	 *
	 * Google Maps output relies on the shortcode to display
	 *
	 * @param $atts
 	 * @return string
	 */
	public function google_maps_shortcode( $atts ) {

		extract(
			shortcode_atts(
				array(
					'title'     => '',
					'id'        => '',
					'reference' => '',
				), $atts
			)
		);

		//gather data for this shortcode
		$post        = get_post( $id );
		$all_meta    = get_post_custom( $id );
		$visual_info = maybe_unserialize( $all_meta['gmb_width_height'][0] );
		$lat_lng = maybe_unserialize( $all_meta['gmb_lat_lng'][0] );

		//Put markers into an array for JS usage
		$map_marker_array   = array();
		$markers_repeatable = maybe_unserialize( $all_meta['gmb_markers_group'][0] );
		foreach ( $markers_repeatable as $marker ) {
			array_push( $map_marker_array, $marker );
		}

		//send data for AJAX usage
		//Add params to AJAX for Shortcode Usage
		//@see: http://benjaminrojas.net/using-wp_localize_script-dynamically/
		$localized_data = array(
			$post->ID => array(
				'id'           => $id,
				'map_params'   => array(
					'title'          => $post->post_title,
					'width'          => $visual_info['width'],
					'height'         => $visual_info['height'],
					'latitude'       => $lat_lng['latitude'],
					'longitude'      => $lat_lng['longitude'],
					'zoom'           => ! empty( $all_meta['gmb_zoom'][0] ) ? $all_meta['gmb_zoom'][0] : '15',
					'default_marker' => GMB_PLUGIN_URL . '/public/assets/img/default-marker.png'
				),
				'map_controls' => array(
					'zoom_control'      => ! empty( $all_meta['gmb_zoom_control'][0] ) ? strtoupper( $all_meta['gmb_zoom_control'][0] ) : 'STANDARD',
					'pan_control'       => ! empty( $all_meta['gmb_pan'][0] ) ? $all_meta['gmb_pan'][0] : 'none',
					'map_type_control'  => ! empty( $all_meta['gmb_map_type_control'][0] ) ? $all_meta['gmb_map_type_control'][0] : 'none',
					'draggable'         => ! empty( $all_meta['gmb_draggable'][0] ) ? $all_meta['gmb_draggable'][0] : 'none',
					'double_click_zoom' => ! empty( $all_meta['gmb_double_click'][0] ) ? $all_meta['gmb_double_click'][0] : 'none',
					'wheel_zoom'        => ! empty( $all_meta['gmb_wheel_zoom'][0] ) ? $all_meta['gmb_wheel_zoom'][0] : 'none',
					'street_view'       => ! empty( $all_meta['gmb_street_view'][0] ) ? $all_meta['gmb_street_view'][0] : 'none',
				),
				'map_theme'    => array(
					'map_type'       => ! empty( $all_meta['gmb_type'][0] ) ? $all_meta['gmb_type'][0] : 'RoadMap',
					'map_theme_json' => ! empty( $all_meta['gmb_theme_json'][0] ) ? $all_meta['gmb_theme_json'][0] : 'none',

				),
				'map_markers'  => $map_marker_array,
				'places_api'   => array(
					'show_places'   => ! empty( $all_meta['gmb_show_places'][0] ) ? $all_meta['gmb_show_places'][0] : 'no',
					'search_radius' => ! empty( $all_meta['gmb_search_radius'][0] ) ? $all_meta['gmb_search_radius'][0] : '3000',
					'search_places' => ! empty( $all_meta['gmb_places_search_multicheckbox'][0] ) ? maybe_unserialize( $all_meta['gmb_places_search_multicheckbox'][0] ) : '',
				),
			)
		);

		$this->array_push_localized_script( $localized_data );

		$map_include = $this->get_google_maps_template( 'public.php' );
		ob_start();
		include( $map_include );
	    return ob_get_clean();

		//echo $map_output;

	}

	/**
	 *  Add params to AJAX for Shortcode Usage
	 *
	 *  http://benjaminrojas.net/using-wp_localize_script-dynamically/
	 *
	 */
	function array_push_localized_script( $localized_data ) {
		global $wp_scripts;
		$data = $wp_scripts->get_data( $this->plugin_slug . '-plugin-script', 'data' );

		if ( empty( $data ) ) {
			wp_localize_script( $this->plugin_slug . '-plugin-script', 'gmb_data', $localized_data );
		} else {

			if ( ! is_array( $data ) ) {

				$data = json_decode( str_replace( 'var gmb_data = ', '', substr( $data, 0, - 1 ) ), true );

			}

			foreach ( $data as $key => $value ) {
				$localized_data[$key] = $value;
			}

			$wp_scripts->add_data( $this->plugin_slug . '-plugin-script', 'data', '' );
			wp_localize_script( $this->plugin_slug . '-plugin-script', 'gmb_data', $localized_data );

		}

	}


	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}