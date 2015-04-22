<?php
/**
 * WP SEO by Yoast sitemap filter class
 *
 * @version 1.0.2
 */
class WPSEO_XML_Sitemaps_Filter {

    protected $wp_home_url, $active_languages, $current_domain, $home_pages;

    public function __construct() {

        // Set home URL before filtered
        $this->wp_home_url = get_home_url();

        // Debug - disable sitemap caching
        if ( defined( 'WPML_WPSEO_DEBUG' ) && WPML_WPSEO_DEBUG ) {
            add_filter( 'wpseo_enable_xml_sitemap_transient_caching',
                        array($this, 'enable_caching_filter') );
        }
        // Sure hook
        add_filter( 'wpseo_build_sitemap_post_type',
                    array($this, 'build_sitemap_post_type_filter') );
        // Early actions for per_domain
        if ( $this->is_per_domain() ) {
            // List domains on WP SEO 'Sitemap' screen
            add_action( 'wpseo_xmlsitemaps_config', array($this, 'list_domains') );
            // Fix for top 'Sitemap Index' link
            if ( $this->is_xsl_request() || $this->is_sitemap_request() ) {
                add_filter( 'home_url', array($this, 'home_url_filter'), 1, 4 );
            }
        }
    }

    public function build_sitemap_post_type_filter( $sitemap_type ) {

        global $sitepress;

        // Set active languages
        $this->active_languages = $sitepress->get_active_languages();

        if ( $this->is_per_domain() ) {
            // Fix home URL
            add_filter( 'home_url', array( $this, 'home_url_filter' ), 1, 4 );
            // Fix stylesheet URL if per_domain (considered as cross site URL)
            add_filter( 'wpseo_stylesheet_url', array($this, 'stylesheet_url_filter') );
            // Set current domain
            $settings = icl_get_setting( 'language_domains', array() );
            $current_lang = $sitepress->get_current_language();
            $this->current_domain = isset( $settings[$current_lang] ) ? $settings[$current_lang] : null;
        }

        // Same as WP SEO check
        if ( $sitemap_type == 1 ) {
            // Index sitemap
            add_filter( 'post_type_archive_link',
                        array($this, 'post_type_archive_link_filter'), 10, 2 );
            // Exclude post type filter
            add_filter( 'wpseo_sitemap_exclude_post_type',
                        array($this, 'exclude_post_type_filter'), 10, 2 );
            // Exclude taxonomy filter
            add_filter( 'wpseo_sitemap_exclude_taxonomy',
                        array($this, 'exclude_taxonomy_filter'), 10, 2 );
        } else if ( post_type_exists( $sitemap_type ) ) {
            // Post type sitemap
            add_filter( 'post_type_archive_link',
                        array($this, 'post_type_archive_link_filter'), 10, 2 );
            if ( $this->is_per_domain() ) {
                add_filter( 'wpseo_typecount_join',
                            array($this, 'typecount_join_filter'), 10, 2 );
                add_filter( 'wpseo_typecount_where',
                            array($this, 'typecount_where_filter'), 10, 2 );
                add_filter( 'wpseo_posts_join',
                            array($this, 'posts_join_filter'), 10, 2 );
                add_filter( 'wpseo_posts_where',
                            array($this, 'posts_where_filter'), 10, 2 );
            }
            if ( $sitemap_type == 'page' ) {
                if ( !$this->is_per_domain() ) {
                    // Collect home pages ID
                    $this->home_pages = array();
                    foreach ( $this->active_languages as $l ) {
                        if ( $l['code'] == $sitepress->get_default_language() ) {
                            continue;
                        }
                        $this->home_pages[$l['code']] = icl_object_id(
                            get_option( 'page_on_front' ), 'page', false, $l['code'] );
                    }
                    add_filter( 'wpseo_sitemap_entry',
                                array($this, 'page_entry_filter'), 10, 3 );
                }
                if ( $this->wpml_root_page() ) {
                    add_filter( 'wpseo_posts_where',
                                array($this, 'wpml_root_page_posts_where_filter'), 10, 2 );
                }
            }
        } elseif ( $tax = get_taxonomy( $sitemap_type ) ) {
            // Taxonomy sitemap
        } elseif ( $sitemap_type == 'author' ) {
            // Author sitemap
        }

        return $sitemap_type;
    }

    public function list_domains() {
        global $sitepress;
        echo '<h2>WPML</h2>';
        echo __('Sitemaps for each languages can be accessed here:', 'sitepress') . '<ul>';
        foreach ($sitepress->get_ls_languages() as $lang) {
            $url = $lang['url'] . 'sitemap_index.xml';
            echo '<li>' . $lang['translated_name'] . ' <a href="' . $url
                 . '" target="_blank">' . $url . '</a></li>';
        }
        echo '</ul>';
    }

    /**
     * WP SEO early collects un-filtered home_url(), causing linking to default language.
     */
    public function home_url_filter( $url ) {
        if ( !empty( $this->current_domain ) && strpos( $url, $this->wp_home_url ) === 0 ) {
            $url = substr_replace( $url, $this->current_domain, 0, strlen( $this->wp_home_url ) );
        }
        return $url;
    }

    public function enable_caching_filter(){
        return false;
    }

    /**
     * Filters post type archive link if slugs translated.
     */
    public function post_type_archive_link_filter( $link, $post_type ) {
        global $sitepress, $wp_rewrite;
        $settings = icl_get_setting( 'posts_slug_translation', array() );
        $translate = !empty( $settings['types'][$post_type] );
        if ( $translate && class_exists( 'WPML_Slug_Translation') ) {
            $post_type_obj = get_post_type_object($post_type);
            $translated_slug = WPML_Slug_Translation::get_translated_slug( $post_type,
                                                                           $sitepress->get_current_language() );
            if ( get_option( 'permalink_structure' )
                 && is_array( $post_type_obj->rewrite ) ) {
                $struct = ( true === $post_type_obj->has_archive ) ? $translated_slug : $post_type_obj->has_archive;
                if ( $post_type_obj->rewrite['with_front'] ) {
                    $struct = $wp_rewrite->front . $struct;
                } else {
                    $struct = $wp_rewrite->root . $struct;
                }
                $link = home_url( user_trailingslashit( $struct,
                                                        'post_type_archive' ) );
            } else {
                $link = home_url( '?post_type=' . $translated_slug );
            }
        }
        return $link;
    }

    public function typecount_join_filter($join, $post_type){
        global $wpdb, $sitepress;

        if($sitepress->is_translated_post_type($post_type)){
            $join .= " INNER JOIN {$wpdb->prefix}icl_translations
    	              ON $wpdb->posts.ID = {$wpdb->prefix}icl_translations.element_id";
        }

        return $join;
    }

    public function typecount_where_filter($where, $post_type){
        global $wpdb, $sitepress;

        if($sitepress->is_translated_post_type($post_type)){
            $sitemap_language = $this->is_per_domain() ? $sitepress->get_current_language() : $sitepress->get_default_language();
            $where .= $wpdb->prepare(" AND {$wpdb->prefix}icl_translations.language_code = %s
                        AND {$wpdb->prefix}icl_translations.element_type = %s", $sitemap_language, "post_{$post_type}");
        }

        return $where;
    }

    public function posts_join_filter($join, $post_type){
        global $wpdb, $sitepress;

        if ( $sitepress->is_translated_post_type( $post_type ) ) {
            $join .= " INNER JOIN {$wpdb->prefix}icl_translations 
    	               ON $wpdb->posts.ID = {$wpdb->prefix}icl_translations.element_id";
        }

        return $join;
    }

    public function posts_where_filter($where, $post_type){
        global $wpdb, $sitepress;

        if ( $sitepress->is_translated_post_type( $post_type ) ) {
            $sitemap_language = $this->is_per_domain() ? $sitepress->get_current_language() : $sitepress->get_default_language();
            $where .= $wpdb->prepare(" AND {$wpdb->prefix}icl_translations.language_code = %s
                        AND {$wpdb->prefix}icl_translations.element_type = %s ", $sitemap_language, "post_{$post_type}");
        }

        return $where;
    }

    /**
     * Filters home page URLs. If there is page_on_front,
     * adjust URL to e.g. http://site.com/es
     * do not leave actual page name e.g. http://site.com/es/sample-page
     * Handle wpml_root_page if necessary.
     */
    public function page_entry_filter( $url, $post_type, $post ) {
        global $sitepress;
        if ( get_option( 'page_on_front' ) ) {
            // If post ID in home_pages
            if ( $lang = array_search( $post->ID, $this->home_pages ) ) {
                $url['loc'] = $sitepress->language_url( $lang );
            }
        }
        // If wpml_root_page but wpml_root_path used, show default permalink
        if ( $this->get_wpml_root_page_id() == $post->ID && $this->wpml_root_path() ) {
            remove_filter( 'page_link', array( 'WPML_Root_Page', 'filter_root_permalink' ), 10, 2 );
            $url['loc'] = get_permalink( $post->ID );
        }

        return $url;
    }

    /**
     * WP SEO should not list un-translatable post types on non-default language domains.
     */
    public function exclude_post_type_filter( $false, $post_type ) {
        global $sitepress;
        return $this->is_per_domain()
               && $sitepress->get_current_language() != $sitepress->get_default_language()
               && !$sitepress->is_translated_post_type($post_type) ? true : $false;
    }

    /**
     * WP SEO should not list un-translatable taxonomies on non-default language domains.
     */
    public function exclude_taxonomy_filter( $false, $tax ) {
        global $sitepress;
        return $this->is_per_domain()
               && $sitepress->get_current_language() != $sitepress->get_default_language()
               && !$sitepress->is_translated_taxonomy($tax) ? true : $false;
    }

    /**
     * WP SEO uses URL for default domain but it's not allowed to use XSL from other domains.
     */
    public function stylesheet_url_filter( $stylesheet ){
        global $sitepress;
        return '<?xml-stylesheet type="text/xsl" href="'
               . preg_replace( '/(^http[s]?:)/', '', esc_url( $sitepress->language_url( $sitepress->get_current_language() ) ) )
               . '/main-sitemap.xsl"?>';
    }

    public function is_per_domain() {
        return icl_get_setting( 'language_negotiation_type', false ) == 2;
    }

    public function is_as_param() {
        return icl_get_setting( 'language_negotiation_type', false ) == 3;
    }

    public function is_default_language_in_directory() {
        $settings = icl_get_setting( 'urls', array() );
        return icl_get_setting( 'language_negotiation_type', false ) == 1
               && !empty($settings['directory_for_default_language'] );
    }

    /**
     * Returns WPML root page ID if set and used.
     */
    public function wpml_root_page() {
        $settings = icl_get_setting( 'urls', array() );
        return $this->is_default_language_in_directory()
               && isset( $settings['show_on_root'] ) && $settings['show_on_root'] == 'page'
               && !empty( $settings['root_page'] ) ? $settings['root_page'] : null;
    }

    public function get_wpml_root_page_id() {
        $settings = icl_get_setting( 'urls', array() );
        return !empty( $settings['root_page'] ) ? $settings['root_page'] : null;
    }

    /**
     * Returns WPML root path if set and used.
     */
    public function wpml_root_path() {
        $settings = icl_get_setting( 'urls', array() );
        return $this->is_default_language_in_directory()
               && isset( $settings['show_on_root'] )
               && $settings['show_on_root'] == 'html_file'
               && !empty( $settings['root_html_file_path'] ) ? $settings['root_html_file_path'] : null;
    }

    public function is_xsl_request() {
        return basename( $_SERVER['REQUEST_URI'] ) == 'main-sitemap.xsl';
    }

    /**
     * Checks if sitemap request, use only if absolutely necessary (early hooks).
     * Sitemaps can have numeration e.g. sitemap-attachments1.xml.
     */
    public function is_sitemap_request() {
        return !empty( $_REQUEST['sitemap'] )
               || basename( $_SERVER['REQUEST_URI'] ) == 'sitemap_index.xml'
               || preg_match( "/sitemap(\d+)?.xml$/", $_SERVER['REQUEST_URI'] );
    }

    public function wpml_root_page_posts_where_filter( $where, $post_type ) {
        global $wpdb;

        $where .= $wpdb->prepare( " AND ID != %d ", WPML_Root_Page::get_root_id() );

        return $where;
    }
}

$wpseo_xml_filter = new WPSEO_XML_Sitemaps_Filter();