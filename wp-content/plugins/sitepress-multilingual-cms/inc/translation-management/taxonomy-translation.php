<?php

define('WPML_TT_TAXONOMIES_NOT_TRANSLATED', 1);
define('WPML_TT_TAXONOMIES_ALL', 0);
define('WPML_TT_TERMS_PER_PAGE', 20);

class WPML_Taxonomy_Translation{

	var $taxonomy;
	var $args;

	function __construct($taxonomy = '', $args = array()){
		global $wpdb, $sitepress, $sitepress_settings;

		$default_language = $sitepress->get_default_language();
		$_active_languages = $sitepress->get_active_languages();

		if(empty($taxonomy)){

			global $wp_taxonomies;
			foreach($wp_taxonomies as $tax_key => $tax){
				if($sitepress->is_translated_taxonomy($tax_key)){
					$this->taxonomy = $tax_key;
					break;
				}
			}

		}else{

			$this->taxonomy = $taxonomy;

		}

		$this->args     = $args;

		$this->show_selector = isset($args['taxonomy_selector']) ? $args['taxonomy_selector'] : true;
		$this->show_tax_sync = isset($args['taxonomy_sync']) ? $args['taxonomy_sync'] : true;


		$this->taxonomy_obj = get_taxonomy($this->taxonomy);

		// filters
		$this->status = isset($this->args['status']) ? $this->args['status'] : WPML_TT_TAXONOMIES_NOT_TRANSLATED;

		if(isset($this->args['languages']) && $this->args['languages']){
			foreach($_active_languages as $language){
				if(in_array($language['code'], $args['languages'])){
					$selected_languages[$language['code']] = $language;
				}
			}
		}

		$this->selected_languages = !empty($selected_languages) ? $selected_languages : $_active_languages;

		if(defined('WPML_ST_FOLDER')){
			// get labels translations

			if($sitepress_settings['st']['strings_language'] != $default_language ){

				$singular_original = $wpdb->get_var($wpdb->prepare("SELECT s.value FROM {$wpdb->prefix}icl_strings s
                    JOIN {$wpdb->prefix}icl_string_translations t ON t.string_id = s.id 
                    WHERE s.context='WordPress' AND t.value = %s AND s.name LIKE %s AND t.language=%s",
					$this->taxonomy_obj->labels->singular_name, 'taxonomy singular name: %', $sitepress->get_admin_language()));

				$general_original  = $wpdb->get_var($wpdb->prepare("SELECT s.value FROM {$wpdb->prefix}icl_strings s
                    JOIN {$wpdb->prefix}icl_string_translations t ON t.string_id = s.id 
                    WHERE s.context='WordPress' AND t.value = %s AND s.name LIKE %s AND t.language=%s",
					$this->taxonomy_obj->labels->name, 'taxonomy general name: %', $sitepress->get_admin_language()));

			}

			if(empty($singular_original)){
				$singular_original = $this->taxonomy_obj->labels->singular_name;
			}
			if(empty($general_original)){
				$general_original  = $this->taxonomy_obj->labels->name;
			}

			$this->taxonomy_obj->labels_translations[$sitepress_settings['st']['strings_language']]['singular'] = $singular_original;
			$this->taxonomy_obj->labels_translations[$sitepress_settings['st']['strings_language']]['general']  = $general_original;


			$languages_pool = array_diff(array_merge(array_keys($this->selected_languages), array( $default_language )), array($sitepress_settings['st']['strings_language']));

			foreach($languages_pool as $language){

				$singular = $wpdb->get_var($wpdb->prepare("SELECT t.value FROM {$wpdb->prefix}icl_string_translations t
                        JOIN {$wpdb->prefix}icl_strings s ON t.string_id = s.id 
                        WHERE s.context='WordPress' and s.name=%s AND t.language=%s", 'taxonomy singular name: ' . $singular_original, $language));
				$general = $wpdb->get_var($wpdb->prepare("SELECT t.value FROM {$wpdb->prefix}icl_string_translations t
                        JOIN {$wpdb->prefix}icl_strings s ON t.string_id = s.id 
                        WHERE s.context='WordPress' and s.name=%s AND t.language=%s", 'taxonomy general name: ' . $general_original, $language));
				$this->taxonomy_obj->labels_translations[$language]['singular'] = $singular ? $singular : '';
				$this->taxonomy_obj->labels_translations[$language]['general'] = $general ? $general : '';

			}

		}

		$this->current_page = isset( $this->args[ 'page' ] ) ? $this->args[ 'page' ] : 1;
		if ( !empty( $this->args[ 'search' ] ) ) {
			$get_terms_args[ 'search' ] = $this->args[ 'search' ];
			$this->search = $args[ 'search' ];
		}

		$taxonomy = $this->taxonomy;

		$per_page = ceil( WPML_TT_TERMS_PER_PAGE / count( $_active_languages ) );

		$term_display_args = array( 'page' => $this->current_page, 'per_page' => $per_page );

		if ( ! empty( $this->search ) ) {
			$term_display_args[ 'search' ] = trim( $this->search );
		}

		if ( ! empty( $this->args[ 'languages' ] ) ) {
			$quoted_langs = array();
			foreach ( $this->args[ 'languages' ] as $lang ) {
				$quoted_langs[ ] = "'" . $lang . "'";
			}

			$term_display_args[ 'langs' ] = join( ', ', $quoted_langs );
		}

		if ( ! empty( $this->args[ 'child_of' ] ) ) {
			$get_terms_args[ 'child_of' ]  = $this->args[ 'child_of' ];
			$this->child_of                = $get_terms_args[ 'child_of' ];
			$term_display_args[ 'parent' ] = $this->child_of;
		} else {
			$this->child_of = 0;
		}

		if ( isset( $this->args[ 'status' ] ) && $this->args[ 'status' ] == 1  ) {
			$term_display_args[ 'untranslated_only' ] = true;
		} else {
			$this->status = 0;
		}

		$terms_information = $this->get_terms_for_taxonomy_translation_screen ( $taxonomy, $term_display_args );

		$this->terms = $terms_information[ 'terms' ];
		$this->terms_count = $terms_information[ 'count' ];
		$this->trid_count = $terms_information[ 'trid_count' ];
	}

	/**
	 * @param $taxonomy string The taxonomy currently displayed
	 * @param $args     array Filter arguments
	 *
	 * @return array holding the terms to be displayed and the overall count of terms in the given taxonomy
	 */
	private function get_terms_for_taxonomy_translation_screen( $taxonomy, $args ) {
		global $wpdb;

		$untranslated_only = false;
		$page              = 1;
		$per_page          = 5;
		$langs             = false;
		$search            = false;
		$parent            = false;

		extract( $args, EXTR_OVERWRITE );

		/*
		 * The returned array from this function is indexed as follows.
		 * It holds an array of all terms to be displayed under [terms]
		 * and the count of all terms matching the filter under [count].
		 *
		 * The array under [terms] itself is index as such:
		 * [trid][lang]
		 *
		 * It holds in itself the terms objects of the to be displayed terms.
		 * These are ordered by their names alphabetically.
		 * Also their objects are amended by the index $term->translation_of holding the term_taxonomy_id of their original element
		 * and their level under $term->level in case of hierarchical terms.
		 *
		 * Also the index [trid][source_lang] holds the source language of the term group.
		 */

		// Only look for terms in active languages when checking for untranslated ones.

		$attributes_to_select                                 = array();
		$icl_translations_table_name                          = $wpdb->prefix . 'icl_translations';
		$attributes_to_select[ $wpdb->terms ]                 = array( 'alias' => 't', 'vars' => array( 'name', 'slug', 'term_id' ) );
		$attributes_to_select[ $wpdb->term_taxonomy ]         = array( 'alias' => 'tt', 'vars' => array( 'term_taxonomy_id', 'parent', 'description' ) );
		$attributes_to_select[ $icl_translations_table_name ] = array( 'alias' => 'i', 'vars' => array( 'language_code', 'trid', 'source_language_code' ) );

		$join_statements = array();

		$as = $this->alias_statements( $attributes_to_select );

		$join_statements [ ] = "{$as['t']} JOIN {$as['tt']} ON tt.term_id = t.term_id";
		$join_statements [ ] = "{$as['i']} ON i.element_id = tt.term_taxonomy_id";

		if ( $search ) {
			$join_statements [ ] = "{$wpdb->terms} AS ts ON ts.term_id = tt.term_id";
		}

		$from_clause = join( ' JOIN ', $join_statements );

		$select_clause = $this->build_select_vars( $attributes_to_select );

		$where_clause = $this->build_where_clause( $attributes_to_select, $taxonomy, $search, $parent );

		$full_statement = "SELECT {$select_clause} FROM {$from_clause} WHERE {$where_clause}";

		if ( $search || $parent ) {
			$where_clause_no_match = $this->build_where_clause( $attributes_to_select, $taxonomy, false, false );
			$full_statement2       = "SELECT {$select_clause} FROM {$from_clause} WHERE {$where_clause_no_match}";

			$lang_constraint = "";
			if ( $langs && ! $untranslated_only && ! $parent ) {
				$lang_constraint = "AND i.language_code IN ({$langs}) ";
			}

			$full_statement = "SELECT table2.* FROM (" . $full_statement . " {$lang_constraint} ) AS table1 INNER JOIN (" . $full_statement2 . ") AS table2 ON table1.trid = table2.trid";
		}

		$all_terms = $wpdb->get_results( $full_statement );

		if ( $all_terms ) {
			$term_count = count( $all_terms );

			$all_terms_indexed = $this->index_terms_array( $all_terms );

			$all_terms_grouped = $this->order_terms_list( $all_terms_indexed );

			if ( $untranslated_only ) {

				$filter_result = $this->filter_for_untranslated_terms_only( $all_terms_grouped );

				$all_terms_grouped = $filter_result[ 'all_terms_grouped' ];

				$term_count += $filter_result[ 'count_adjustment' ];
			}

			if ( !empty( $all_terms_grouped ) ) {
				$terms = array_slice ( $all_terms_grouped, ( $page - 1 ) * $per_page, $per_page );
				$trid_count = count ( $all_terms_grouped );
				return array( 'terms' => $terms, 'count' => $term_count, 'trid_count' => $trid_count );
			}
		}
	}

	/**
	 * @param $all_terms_grouped array
	 *
	 * Filters resultant terms for trids that are not lacking a translation.
	 *
	 * @return array Associative array holding, terms that are lacking a translation as well as the term count reduction,
	 *               resulting from the filtering done by this function.
	 */
	private function filter_for_untranslated_terms_only( $all_terms_grouped ) {
		global $sitepress;

		$count_adjustment = 0;

		$active_languages = $sitepress->get_active_languages ( true );
		$lang_codes = array_keys ( $active_languages );
		$n_langs    = count( $lang_codes );
		foreach ( $all_terms_grouped as $key => $group ) {
			$all_there = true;
			foreach ( $lang_codes as $code ) {
				if ( isset( $this->selected_languages[ $code ] ) && ! isset( $group[ $code ] ) ) {
					$all_there = false;
				}
			}
			if ( $all_there ) {
				unset( $all_terms_grouped[ $key ] );
				$count_adjustment -= $n_langs;
			} else {
				$languages_missing = array_intersect( array_keys( $group ), $lang_codes );

				foreach ( $languages_missing as $lm_code ) {
					if ( ! isset( $this->selected_languages[ $lm_code ] ) ) {
						$this->selected_languages[ $lm_code ] = $active_languages[ $lm_code ];
					}
				}
			}
		}

		return array( 'all_terms_grouped' => $all_terms_grouped, 'count_adjustment' => $count_adjustment );
	}

	/**
	 * @param $terms array
	 *
	 * Turn a numerical array of terms objects into an associative once,
	 * holding the same terms, but indexed by their term_id.
	 *
	 * @return array
	 */
	private function index_terms_array( $terms ) {
		$terms_indexed = array();

		foreach ( $terms as $term ) {
			$terms_indexed[ $term->term_id ] = $term;
		}

		return $terms_indexed;
	}

	/**
	 * @param $trid_group array
	 * @param $terms array
	 *
	 * Transforms the term arrays generated by the Translation Tree class and turns them into standard
	 * WordPress terms objects, amended by language information.
	 *
	 * @return mixed
	 */
	private function set_language_information( $trid_group, $terms ) {

		foreach ( $trid_group[ 'elements' ] as $lang => $term ) {

			$term_object         = $terms[ $term[ 'term_id' ] ];
			$term_object->level  = $term[ 'level' ];
			$trid_group[ $lang ] = $term_object;
			if ( ! $term_object->source_language_code ) {
				$trid_group[ 'source_lang' ] = $term_object->language_code;
			}
			if ( ! isset( $trid_group[ 'source_lang' ] ) ) {
				$trid_group[ 'source_lang' ] = $term_object->source_language_code;
			}
		}

		unset( $trid_group[ 'elements' ] );

		$source_lang = isset( $trid_group[ 'source_lang' ] ) ? $trid_group[ 'source_lang' ] : false;

		$trid_group[ 'source_lang' ] = $source_lang;

		$original_ttid = false;
		if ( $source_lang && isset( $trid_group[ $source_lang ] ) ) {
			$original_element = $trid_group[ $source_lang ];
			$original_ttid    = $original_element->term_taxonomy_id;
		}

		$updated_trid_group = $trid_group;
		unset( $updated_trid_group[ 'source_lang' ] );
		unset( $updated_trid_group[ 'trid' ] );
		foreach ( $updated_trid_group as $lang => $term ) {
			if ( $term->term_taxonomy_id != $original_ttid ) {
				$term->translation_of = $original_ttid;
			} else {
				$term->translation_of = false;
			}
		}

		return $trid_group;
	}

	/**
	 * @param $terms array
	 *
	 * Orders a list of terms alphabetically and hierarchy-wise
	 *
	 * @return array
	 */
	private function order_terms_list( $terms ) {

		$taxonomy = $this->taxonomy;

		$terms_tree = new WPML_Translation_Tree( $taxonomy, false, $terms );

		$ordered_terms = $terms_tree->get_alphabetically_ordered_list();

		foreach ( $ordered_terms as $key => $trid_group ) {

			$ordered_terms[ $key ] = $this->set_language_information( $trid_group, $terms );
		}

		return $ordered_terms;
	}

	/**
	 * @param $selects array
	 *                 Generates a list of to be selected variables in an sql query.
	 *
	 * @return string
	 */
	private function build_select_vars( $selects ) {
		$output = '';

		if ( is_array( $selects ) ) {
			$coarse_selects = array();

			foreach ( $selects as $select ) {

				$vars  = $select[ 'vars' ];
				$table = $select[ 'alias' ];

				foreach ( $vars as $key => $var ) {
					$vars[ $key ] = $table . '.' . $var;
				}
				$coarse_selects[ ] = join( ', ', $vars );
			}

			$output = join( ', ', $coarse_selects );
		}

		return $output;
	}

	/**
	 * @param $selects array
	 *
	 * Returns an array of alias statements to be used in SQL queries with joins.
	 *
	 * @return array
	 */
	private function alias_statements( $selects ) {
		$output = array();
		foreach ( $selects as $key => $select ) {
			$output[ $select[ 'alias' ] ] = $key . ' AS ' . $select[ 'alias' ];
		}

		return $output;
	}

	private function build_where_clause( $selects, $taxonomy, $search = false, $parent = false ) {
		global $wpdb;

		$where_clauses[ ] = $selects[ $wpdb->term_taxonomy ][ 'alias' ] . '.taxonomy = ' . "'" . $taxonomy . "'";
		$where_clauses[ ] = $selects[ $wpdb->prefix . 'icl_translations' ][ 'alias' ] . '.element_type = ' . "'tax_" . $taxonomy . "'";

		if ( $parent ) {
			$where_clauses[ ] = $selects[ $wpdb->term_taxonomy ][ 'alias' ] . '.parent = ' . $parent;
		}

		if ( $search ) {
			$where_clauses [ ] = "ts.name LIKE '%" . wpml_like_escape( $search ) . "%' ";
		}

		$where_clause = join( ' AND  ', $where_clauses );

		return $where_clause;
	}


	function render(){
		if(!empty($this->error)){

			echo '<div class="icl_error_text">' . $this->error . '</div>';

		}
		elseif(!$this->taxonomy_obj){

			echo '<div class="icl_error_text">' . sprintf(__('Unknown taxonomy: %s', 'sitepress'), $this->taxonomy ) . '</div>';

		}else{

			include ICL_PLUGIN_PATH . '/menu/taxonomy-translation-content.php';

		}


	}

	static function show_terms(){
		$taxonomy = isset($_POST['taxonomy']) ? $_POST['taxonomy'] : false;

		$args = array();
		if(!empty($_POST['language'])){
			$args['languages'] = array($_POST['language']);
		}
		$args['status'] = isset($_POST['status']) ? $_POST['status'] : WPML_TT_TAXONOMIES_ALL;

		$args['search'] = isset($_POST['search']) ? $_POST['search'] : '';

		if(isset($_POST['page'])){
			$args['page'] = $_POST['page'];
		}

		if(isset($_POST['parent'])){
			$args['parent'] = $_POST['parent'];
		}

		if(isset($_POST['child_of']) && intval($_POST['child_of']) > 0){
			$args['child_of'] = $_POST['child_of'];
		}

		if(isset($_POST['taxonomy_selector'])){
			$args['taxonomy_selector'] = $_POST['taxonomy_selector'];
		}

		$inst = new WPML_Taxonomy_Translation($taxonomy, $args);

		$inst->render();
		exit;

	}

	public static function save_term_translation() {
		global $sitepress, $wpdb;

		$original_element = $_POST[ 'translation_of' ];
		$taxonomy         = $_POST[ 'taxonomy' ];
		$language         = $_POST[ 'language' ];
		$trid             = $sitepress->get_element_trid( $original_element, 'tax_' . $taxonomy );
		$translations     = $sitepress->get_element_translations( $trid, 'tax_' . $taxonomy );

		$_POST[ 'icl_tax_' . $taxonomy . '_language' ] = $language;
		$_POST[ 'icl_trid' ]                           = $trid;
		$_POST[ 'icl_translation_of' ]                 = $original_element;

		$errors = '';

		$term_args = array(
			'name'        => $_POST[ 'name' ],
			'slug'        => WPML_Terms_Translations::pre_term_slug_filter( $_POST[ 'slug' ], $taxonomy ),
			'description' => $_POST[ 'description' ]
		);

		$original_tax_sql      = "SELECT * FROM {$wpdb->term_taxonomy} WHERE taxonomy=%s AND term_taxonomy_id = %d";
		$original_tax_prepared = $wpdb->prepare( $original_tax_sql, array( $taxonomy, $original_element ) );
		$original_tax          = $wpdb->get_row( $original_tax_prepared );

		// hierarchy - parents
		if ( is_taxonomy_hierarchical( $taxonomy ) ) {
			// fix hierarchy
			if ( $original_tax->parent ) {
				$original_parent_translated = icl_object_id( $original_tax->parent, $taxonomy, false, $_POST[ 'language' ] );
				if ( $original_parent_translated ) {
					$term_args[ 'parent' ] = $original_parent_translated;
				}
			}
		}

		if ( isset( $translations[ $language ] ) ) {
			$result = wp_update_term( $translations[ $language ]->term_id, $taxonomy, $term_args );
		} else {
			$result                        = wp_insert_term( $_POST[ 'name' ], $taxonomy, $term_args );
			$original_element_lang_details = $sitepress->get_element_language_details( $original_element, 'tax_' . $taxonomy );
			if ( isset( $original_element_lang_details->language_code ) ) {
				$sitepress->set_element_language_details( $result[ 'term_taxonomy_id' ], 'tax_' . $taxonomy, $trid, $language, $original_element_lang_details->language_code );
			}
		}

		if ( is_wp_error( $result ) ) {
			foreach ( $result->errors as $ers ) {
				$errors .= join( '<br />', $ers );
			}
			$errors .= '<br />';
		} else {

			// hierarchy - children
			if ( is_taxonomy_hierarchical( $taxonomy ) ) {

				// get children of original
				$children_sql      = "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy=%s AND parent=%d";
				$children_prepared = $wpdb->prepare( $children_sql, array( $taxonomy, $original_tax->term_id ) );
				$children          = $wpdb->get_col( $children_prepared );

				if ( $children ) {
					foreach ( $children as $child ) {
						$child_translated = icl_object_id( $child, $taxonomy, false, $_POST[ 'language' ] );
						if ( $child_translated ) {
							$wpdb->update( $wpdb->term_taxonomy, array( 'parent' => $result[ 'term_id' ] ), array( 'taxonomy' => $taxonomy, 'term_id' => $child_translated ) );
						}
					}
				}

				$sitepress->update_terms_relationship_cache( $children, $taxonomy );
				//delete_option($_POST['taxonomy'] . '_children');

			}

			$term = get_term( $result[ 'term_id' ], $taxonomy );

			do_action( 'icl_save_term_translation', $original_tax, $result );
		}

		$html = '';

		echo json_encode( array( 'html' => $html, 'slug' => isset( $term ) ? urldecode( $term->slug ) : '', 'errors' => $errors ) );
		exit;
	}

	public static function save_labels_translation() {

		$errors = '';

		if ( empty( $_POST[ 'singular' ] ) || empty( $_POST[ 'general' ] ) ) {
			$errors .= __( 'Please fill in all fields!', 'sitepress' ) . '<br />';
		}

		$string_id = icl_st_is_registered_string( 'WordPress', 'taxonomy singular name: ' . $_POST[ 'singular_original' ] );
		if ( !$string_id ) {
			$string_id = icl_register_string( 'WordPress', 'taxonomy singular name: ' . $_POST[ 'singular_original' ], $_POST[ 'singular_original' ] );
		}
		icl_add_string_translation( $string_id, $_POST[ 'language' ], $_POST[ 'singular' ], ICL_STRING_TRANSLATION_COMPLETE );

		$string_id = icl_st_is_registered_string( 'WordPress', 'taxonomy general name: ' . $_POST[ 'general_original' ] );
		if ( !$string_id ) {
			$string_id = icl_register_string( 'WordPress', 'taxonomy general name: ' . $_POST[ 'general_original' ], $_POST[ 'general_original' ] );
		}
		icl_add_string_translation( $string_id, $_POST[ 'language' ], $_POST[ 'general' ], ICL_STRING_TRANSLATION_COMPLETE );

		$html = '';

		echo json_encode( array( 'html' => $html, 'errors' => $errors ) );
		exit;


	}

	public static function sync_taxonomies_in_content_preview(){
		global $wp_taxonomies;

		$html = $message = $errors = '';


		if(isset($wp_taxonomies[$_POST['taxonomy']])){
			$object_types = $wp_taxonomies[$_POST['taxonomy']]->object_type;

			foreach($object_types as $object_type){

				$html .= self::render_assignment_status($object_type, $_POST['taxonomy'], $preview = true);

			}

		}else{
			$errors = sprintf(__('Invalid taxonomy %s', 'sitepress'), $_POST['taxonomy']);
		}


		echo json_encode(array('html' => $html, 'message'=> $message, 'errors' => $errors));
		exit;


	}

	public static function sync_taxonomies_in_content(){
		global $wp_taxonomies;

		$html = $message = $errors = '';

		if(isset($wp_taxonomies[$_POST['taxonomy']])){
			$html .= self::render_assignment_status($_POST['post'], $_POST['taxonomy'], $preview = false);

		}else{
			$errors .= sprintf(__('Invalid taxonomy %s', 'sitepress'), $_POST['taxonomy']);
		}


		echo json_encode(array('html' => $html, 'errors' => $errors));
		exit;


	}


	public static function render_assignment_status($object_type, $taxonomy, $preview = true){
		global $sitepress, $wp_post_types, $wp_taxonomies,$wpdb;

		$default_language = $sitepress->get_default_language();
		$posts            = get_posts( array( 'post_type' => $object_type, 'suppress_filters' => false, 'posts_per_page' => -1  ) );

		foreach($posts as $post){

			$terms = wp_get_post_terms($post->ID, $taxonomy);

			$term_ids = array();
			foreach($terms as $term){
				$term_ids[] = $term->term_id;
			}

			$trid = $sitepress->get_element_trid($post->ID, 'post_' . $post->post_type);
			$translations = $sitepress->get_element_translations($trid, 'post_' . $post->post_type, true, true);

			foreach($translations as $language => $translation){

				if($language != $default_language && $translation->element_id){

					$terms_of_translation =  wp_get_post_terms($translation->element_id, $taxonomy);

					$translation_term_ids = array();
					foreach($terms_of_translation as $term){

						$term_id_original = icl_object_id($term->term_id, $taxonomy, false, $default_language );
						if(!$term_id_original || !in_array($term_id_original, $term_ids)){
							// remove term

							if($preview){
								$needs_sync = true;
								break(3);
							}

							$current_terms = wp_get_post_terms($translation->element_id, $taxonomy);
							$updated_terms = array();
							foreach($current_terms as $cterm){
								if($cterm->term_id != $term->term_id){
									$updated_terms[] = is_taxonomy_hierarchical($taxonomy) ? $term->term_id : $term->name;
								}
								if(!$preview){
									wp_set_post_terms($translation->element_id, $updated_terms, $taxonomy);
								}

							}


						}else{
							$translation_term_ids[] = $term_id_original;
						}

					}

					foreach($term_ids as $term_id){

						if(!in_array($term_id, $translation_term_ids)){
							// add term

							if($preview){
								$needs_sync = true;
								break(3);
							}
							$terms_array = array();
							$term_id_translated = icl_object_id($term_id, $taxonomy, false, $language);

							// not using get_term
							$translated_term = $wpdb->get_row($wpdb->prepare("
                            SELECT * FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} x ON x.term_id = t.term_id WHERE t.term_id = %d AND x.taxonomy = %s", $term_id_translated, $taxonomy));

							if(is_taxonomy_hierarchical($taxonomy)){
								$terms_array[] = $translated_term->term_id;
							} else {
								$terms_array[] = $translated_term->name;
							}

							if(!$preview){
								wp_set_post_terms($translation->element_id, $terms_array, $taxonomy, true);
							}

						}

					}

				}


			}


		}

		$out = '';


		if($preview){

			$out .= '<div class="icl_tt_sync_row">';
			if(!empty($needs_sync)){
				$out .= '<form class="icl_tt_do_sync">';
				$out .= '<input type="hidden" name="post" value="' . $object_type . '" />';
				$out .= '<input type="hidden" name="taxonomy" value="' . $taxonomy . '" />';
				$out .= sprintf(__('Some translated %s have different %s assignments.', 'sitepress'),
					'<strong>' . mb_strtolower($wp_post_types[$object_type]->labels->name) . '</strong>',
					'<strong>' . mb_strtolower($wp_taxonomies[$taxonomy]->labels->name) . '</strong>');
				$out .= '&nbsp;<a class="submit button-secondary" href="#">' . sprintf(__('Update %s for all translated %s', 'sitepress'),
						'<strong>' . mb_strtolower($wp_taxonomies[$taxonomy]->labels->name) . '</strong>',
						'<strong>' . mb_strtolower($wp_post_types[$object_type]->labels->name) . '</strong>') . '</a>' .
					'&nbsp;<img src="'. ICL_PLUGIN_URL . '/res/img/ajax-loader.gif" alt="loading" height="16" width="16" class="wpml_tt_spinner" />';
				$out .= "</form>";
			}else{
				$out .= sprintf(__('All %s have the same %s assignments.', 'sitepress'),
					'<strong>' . mb_strtolower($wp_taxonomies[$taxonomy]->labels->name) . '</strong>',
					'<strong>' . mb_strtolower($wp_post_types[$object_type]->labels->name) . '</strong>');
			}
			$out .= "</div>";

		}else{

			$out .= sprintf(__('Successfully updated %s for all translated %s.', 'sitepress'), $wp_taxonomies[$taxonomy]->labels->name, $wp_post_types[$object_type]->labels->name);

		}

		return $out;

	}

	/**
	 * @param $terms array
	 *
	 * Filters the get_terms function, so to not display any hierarchical terms that do not have child terms.
	 *
	 * @return array
	 */
	public static function wp_dropdown_cats_filter ( $terms ) {

		$parents = array();

		foreach ( $terms as $term ) {
			if ( $term->parent ) {
				$parents [ ] = $term->parent;
			}
		}

		$parents = array_unique ( $parents );

		foreach ( $terms as $key => $term ) {
			if ( !in_array ( $term->term_id, $parents ) ) {
				unset( $terms[ $key ] );
			}
		}

		return $terms;
	}

	public static function render_parent_taxonomies_dropdown($taxonomy, $child_of = 0){
		$args = array(
			'name'              => 'child_of',
			'selected'          => $child_of,
			'hierarchical'      => 1,
			'taxonomy'          => $taxonomy,
			'show_option_none'  => '--- ' . __('select parent', 'sitepress') . ' ---',
			'hide_empty'        => 0,
		);

		remove_all_filters('terms_clauses');
		remove_all_filters('get_terms');
		remove_all_filters('list_terms_exclusions');

		add_filter('get_terms', array('WPML_Taxonomy_Translation', 'wp_dropdown_cats_filter'), 10, 2);
		wp_dropdown_categories($args);
		remove_all_filters('get_terms');

	}
}


add_action('wp_ajax_wpml_tt_show_terms', array('WPML_Taxonomy_Translation', 'show_terms'));

add_action('wp_ajax_wpml_tt_save_term_translation', array('WPML_Taxonomy_Translation', 'save_term_translation'));
add_action('wp_ajax_wpml_tt_save_labels_translation', array('WPML_Taxonomy_Translation', 'save_labels_translation'));

add_action('wp_ajax_wpml_tt_sync_taxonomies_in_content_preview', array('WPML_Taxonomy_Translation', 'sync_taxonomies_in_content_preview'));
add_action('wp_ajax_wpml_tt_sync_taxonomies_in_content', array('WPML_Taxonomy_Translation', 'sync_taxonomies_in_content'));
