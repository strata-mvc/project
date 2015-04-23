<?php

class WPML_Post_Edit_Ajax {

	/**
	 * Ajax handler for adding a term via Ajax.
	 */
	public static function wpml_save_term() {
        $nonce       = filter_input( INPUT_POST, '_icl_nonce' );
        if ( !wp_verify_nonce( $nonce, 'wpml_save_term_nonce' ) ) {
            wp_send_json_error( 'Wrong Nonce' );
        }

		global $sitepress;

		$lang        = filter_input ( INPUT_POST, 'term_language_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$taxonomy    = filter_input ( INPUT_POST, 'taxonomy' );
		$slug        = filter_input ( INPUT_POST, 'slug' );
		$name        = filter_input ( INPUT_POST, 'name' );
		$trid        = filter_input ( INPUT_POST, 'trid', FILTER_SANITIZE_NUMBER_INT );
		$description = filter_input ( INPUT_POST, 'description' );
		$sync        = filter_input ( INPUT_POST, 'force_hierarchical_sync' );

		$new_term_object = false;

		if ( $name && $taxonomy && $trid && $lang ) {

			$args = array(
				'taxonomy'  => $taxonomy,
				'lang_code' => $lang,
				'term'      => $name,
				'trid'      => $trid,
				'overwrite' => true
			);

			if ( $slug ) {
				$args[ 'slug' ] = urlencode($slug);
			}
			if ( $description ) {
				$args[ 'description' ] = $description;
			}

			$res = WPML_Terms_Translations::create_new_term( $args );

			if ( $res && isset( $res[ 'term_taxonomy_id' ] ) ) {
				/* res holds the term taxonomy id, we return the whole term objects to the ajax call */
				$new_term_object                = get_term_by( 'term_taxonomy_id', (int) $res[ 'term_taxonomy_id' ], $taxonomy );
				$lang_details                   = $sitepress->get_element_language_details( $new_term_object->term_taxonomy_id, 'tax_' . $new_term_object->taxonomy );
				$new_term_object->trid          = $lang_details->trid;
				$new_term_object->language_code = $lang_details->language_code;

				WPML_Terms_Translations::icl_save_term_translation_action( $taxonomy, $res );
				if ( $sync ) {
					$tree = new WPML_Translation_Tree( $taxonomy );
					$tree->sync_tree( $lang, $sync );
				}
			}
		}
		wp_send_json_success( $new_term_object );
	}

	/**
	 * Ajax handler for previewing potentially untranslated terms on a posts,
	 * the language of which is about to be changed and whose connection to the post
	 * will therefore be lost.
	 */
	public static function wpml_before_switch_post_language() {
		$to      = false;
		$post_id = false;
        $nonce = filter_input( INPUT_POST, '_icl_nonce' );
        if ( !wp_verify_nonce( $nonce, 'wpml_switch_post_lang_nonce' ) ) {
            wp_send_json_error( 'Wrong Nonce' );
        }

		$result = false;

		if ( isset( $_POST[ 'wpml_to' ] ) ) {
			$to = $_POST[ 'wpml_to' ];
		}
		if ( isset( $_POST[ 'wpml_post_id' ] ) ) {
			$post_id = $_POST[ 'wpml_post_id' ];
		}

		if ( $to && $post_id ) {
			$result = WPML_Terms_Translations::get_untranslated_terms_for_post( $post_id, $to );

			if ( empty( $result ) ) {
				$result = false;
			}
		}

		wp_send_json_success( $result );
	}

	/**
	 * Ajax handler for switching the language of a post.
	 */
	public static function wpml_switch_post_language() {
		global $sitepress, $wpdb;

		$to      = false;
		$post_id = false;

		if ( isset( $_POST[ 'wpml_to' ] ) ) {
			$to = $_POST[ 'wpml_to' ];
		}
		if ( isset( $_POST[ 'wpml_post_id' ] ) ) {
			$post_id = $_POST[ 'wpml_post_id' ];
		}

		$result = false;

		set_transient( md5( $sitepress->get_current_user()->ID . 'current_user_post_edit_lang' ), $to );
		if ( $post_id && $to ) {

			$post_type      = get_post_type( $post_id );
			$wpml_post_type = 'post_' . $post_type;
			$trid           = $sitepress->get_element_trid( $post_id, $wpml_post_type );

			/* Check if a translation in that language already exists with a different post id.
			 * If so, then don't perform this action.
			 */

			$query_for_existing_translation = $wpdb->prepare( "SELECT translation_id, element_id FROM {$wpdb->prefix}icl_translations WHERE element_type = %s AND trid = %d AND language_code = %s", $wpml_post_type, $trid, $to );
			$existing_translation           = $wpdb->get_row( $query_for_existing_translation );

			if ( $existing_translation && $existing_translation->element_id != $post_id ) {
				$result = false;
			} else {
				$sitepress->set_element_language_details( $post_id, $wpml_post_type, $trid, $to );
				// Synchronize the posts terms languages. Do not create automatic translations though.
				WPML_Terms_Translations::sync_post_terms_language( $post_id, false );
				require_once ICL_PLUGIN_PATH . '/inc/cache.php';
				icl_cache_clear( $post_type . 's_per_language', true );

				$result = $to;
			}
		}

		wp_send_json_success( $result );
	}

	/**
	 * Saves the language from which a user is editing the currently edited post as a transient.
	 * This is done so that filtering the language from which terms for the flat terms preview dropdown can be performed.
	 */
	public static function wpml_set_post_edit_lang() {
		global $sitepress;
		$lang_code = false;
		if ( isset( $_POST[ 'wpml_post_lang' ] ) ) {
			$lang_code = $_POST[ 'wpml_post_lang' ];
		}

		set_transient( md5( $sitepress->get_current_user()->ID . 'current_user_post_edit_lang' ), $lang_code );
	}

	public static function wpml_get_default_lang() {
		global $sitepress;
		wp_send_json_success( $sitepress->get_default_language() );
	}
}
