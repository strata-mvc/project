<?php

class WPML_Language_Resolution {

    public function __construct() {
        add_filter( 'icl_set_current_language', array( $this, 'current_lang_filter' ), 10, 2 );
    }

    public function current_lang_filter( $lang ) {
        $preview_id = filter_input( INPUT_GET, 'preview_id', FILTER_SANITIZE_NUMBER_INT );
        $preview_flag = filter_input( INPUT_GET, 'preview', FILTER_VALIDATE_BOOLEAN );
        $preview_id = $preview_id ? $preview_id : filter_input( INPUT_GET, 'p', FILTER_SANITIZE_NUMBER_INT );
        $preview_id = $preview_id ? $preview_id : filter_input( INPUT_GET, 'page_id', FILTER_SANITIZE_NUMBER_INT );
        if ( $preview_id || $preview_flag || $preview_id ) {
            global $wpdb;
            $lang = $wpdb->get_var( $wpdb->prepare( "	SELECT language_code
														FROM {$wpdb->prefix}icl_translations
														WHERE element_id = %d
															AND element_type LIKE 'post%%'",
                                                    $preview_id ) );
        } elseif ( !is_admin() ) {
            $lang = $this->filter_for_legal_langs_frontend( $lang );
        }

        return $lang;
    }

    /**
     *
     * Sets the language of frontend requests to false, if they are not for
     * a hidden or active language code. The handling of permissions in case of
     * hidden languages is done in \SitePress::init.
     *
     * @param string $lang
     * @return bool|string
     */
    private function filter_for_legal_langs_frontend( $lang ) {
        global $sitepress;
        $active_langs = $sitepress->get_active_languages();
        $hidden_lang_codes = $sitepress->get_setting( 'hidden_languages', array() );
        $active_lang_codes = array_keys( $active_langs );
        $legal_lang_codes = array_merge( $hidden_lang_codes, $active_lang_codes );
        if ( !in_array( $lang, $legal_lang_codes ) ) {
            $lang = $sitepress->get_default_language();
        }

        return $lang;
    }
}