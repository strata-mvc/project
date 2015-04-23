<?php

// deprecated - used for string translations
define ( 'CMS_TARGET_LANGUAGE_CREATED', 0);
define ( 'CMS_TARGET_LANGUAGE_ASSIGNED', 1);
define ( 'CMS_TARGET_LANGUAGE_TRANSLATED', 2);
define ( 'CMS_TARGET_LANGUAGE_DONE', 3);
define ( 'CMS_REQUEST_DONE', 6);
define ( 'CMS_REQUEST_FAILED', 7);


class ICL_Pro_Translation{
    
    private $tmg;
    protected static $__asian_languages = array('ja', 'ko', 'zh-hans', 'zh-hant', 'mn', 'ne', 'hi', 'pa', 'ta', 'th');
    
    function __construct(){
        global $iclTranslationManagement;
        $this->tmg =& $iclTranslationManagement;
        
        add_filter('xmlrpc_methods',array($this, 'custom_xmlrpc_methods'));
        add_action('post_submitbox_start', array($this, 'post_submitbox_start'));
        
        add_action('icl_ajx_custom_call', array($this, 'ajax_calls'), 10, 2);
        
        add_action('icl_hourly_translation_pickup', array($this, 'poll_for_translations'));
        
    }
    
    function ajax_calls($call, $data){
        global $sitepress_settings, $sitepress;
        switch($call){
            case 'set_pickup_mode':
                $method = intval($data['icl_translation_pickup_method']);
                $iclsettings['translation_pickup_method'] = $method;
                $iclsettings['icl_disable_reminders'] = isset($_POST['icl_disable_reminders']) ? 1 : 0;
                $iclsettings['icl_notify_complete'] = isset($_POST['icl_notify_complete']) ? 1 : 0;
                
                $sitepress->save_settings($iclsettings);
                
                if(!empty($sitepress_settings) && !empty($sitepress_settings['site_id']) && !empty($sitepress_settings['access_key'])){
                    $data['site_id'] = $sitepress_settings['site_id'];
                    $data['accesskey'] = $sitepress_settings['access_key'];
                    $data['create_account'] = 0;
                    $data['pickup_type'] = $method;
                    $data['notifications'] = $iclsettings['icl_notify_complete'];
                    
                    $icl_query = new ICanLocalizeQuery();                
                    $icl_query->updateAccount($data);
                }
                
                if($method == ICL_PRO_TRANSLATION_PICKUP_XMLRPC){
                    wp_clear_scheduled_hook('icl_hourly_translation_pickup');    
                }else{
                    wp_schedule_event(time(), 'hourly', 'icl_hourly_translation_pickup');    
                }
                
                echo json_encode(array('message'=>'OK'));
                break;
            case 'pickup_translations':
                if($sitepress_settings['translation_pickup_method']==ICL_PRO_TRANSLATION_PICKUP_POLLING){
                    $fetched = $this->poll_for_translations(true);
                    echo json_encode(array('message'=>'OK', 'fetched'=> urlencode('&nbsp;' . sprintf(__('Fetched %d translations.', 'sitepress'), $fetched))));
                }else{
                    echo json_encode(array('error'=>__('Manual pick up is disabled.', 'sitepress')));
                }
                break;
        }
    }

	function send_post( $post, $target_languages, $translator_id = 0 ) {
		global $sitepress, $sitepress_settings, $wpdb, $iclTranslationManagement;

		// don't wait for init
		if ( empty( $this->tmg->settings ) ) {
			$iclTranslationManagement->init();
		}

		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}
		$post_id = $post->ID;

		$err = false;

		if ( ! $post ) {
			return false;
		}

		$orig_lang            = $sitepress->get_language_for_element( $post_id, 'post_' . $post->post_type );
		$__ld                 = $sitepress->get_language_details( $orig_lang );
		$orig_lang_for_server = $this->server_languages_map( $__ld[ 'english_name' ] );

		if ( empty( $target_languages ) ) {
			return false;
		}

		$res = false;

		// Make sure the previous request is complete.
		// Only send if it needs update
		foreach ( $target_languages as $target_lang ) {

			if ( $target_lang == $orig_lang ) {
				continue;
			}

			$translation = $this->tmg->get_element_translation( $post_id, $target_lang, 'post_' . $post->post_type );

			if ( empty( $translation ) ) { // translated the first time
				$tdata = array(
					'translate_from' => array( $orig_lang ),
					'translate_to'   => array( $target_lang => 1 ),
					'post'           => array( $post_id ),
					'translator'     => $translator_id,
					'service'        => 'icanlocalize'
				);
				$this->tmg->send_jobs( $tdata );
				$translation = $this->tmg->get_element_translation( $post_id, $target_lang, 'post_' . $post->post_type );
			}

			if ( $translation->needs_update || $translation->status == ICL_TM_NOT_TRANSLATED || $translation->status == ICL_TM_WAITING_FOR_TRANSLATOR ) {

				$iclq = new ICanLocalizeQuery( $sitepress_settings[ 'site_id' ], $sitepress_settings[ 'access_key' ] );
				if ( $post->post_type == 'page' ) {
					$post_url = get_home_url() . '?page_id=' . ( $post_id );
				} else {
					$post_url = get_home_url() . '?p=' . ( $post_id );
				}

				$__ld              = $sitepress->get_language_details( $target_lang );
				$target_for_server = $this->server_languages_map( $__ld[ 'english_name' ] );

				if ( isset( $post->external_type ) && $post->external_type ) {

					$data[ 'url' ]              = htmlentities( $post_url );
					$data[ 'target_languages' ] = array( $target_for_server );

					foreach ( $post->string_data as $key => $value ) {
						$data[ 'contents' ][ $key ] = array(
							'translate' => 1,
							'data'      => base64_encode( $value ),
							'format'    => 'base64'
						);
					}

					$data[ 'contents' ][ 'original_id' ] = array(
						'translate' => 0,
						'data'      => $post->post_id,
					);
				} else {

					// TAGS
					// ***************************************************************************
					$post_tags = array();
					foreach ( wp_get_object_terms( $post_id, 'post_tag' ) as $tag ) {
						$post_tags[ $tag->term_taxonomy_id ] = $tag->name;
					}

					$target_trid_query = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE language_code=%s AND trid=%d AND element_id IS NOT NULL";
					$trid_query        = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type=%s";

					$tags_to_translate = array();
					if ( $post_tags ) {
						//only send tags that don't have a translation
						foreach ( $post_tags as $term_taxonomy_id => $pc ) {
							$element_type   = 'tax_post_tag';
							$trid_args      = array( $term_taxonomy_id, $element_type );
							$trid_prepare   = $wpdb->prepare( $trid_query, $trid_args );
							$trid           = $wpdb->get_var( $trid_prepare );
							$not_translated = false;
							foreach ( $target_languages as $lang ) {
								$target_trid_args    = array( $lang, $trid );
								$target_trid_prepare = $wpdb->prepare( $target_trid_query, $target_trid_args );
								$target_trid         = $wpdb->get_var( $target_trid_prepare );
								if ( $trid != $target_trid ) {
									$not_translated = true;
									break;
								}
							}
							if ( $not_translated ) {
								$tags_to_translate[ $term_taxonomy_id ] = $pc;
							}
						}
						sort( $post_tags, SORT_STRING );
					}

					// CATEGORIES
					// ***************************************************************************
					$post_categories = array();
					foreach ( wp_get_object_terms( $post_id, 'category' ) as $cat ) {
						$post_categories[ $cat->term_taxonomy_id ] = $cat->name;
					}

					$categories_to_translate = array();
					if ( $post_categories ) {
						//only send categories that don't have a translation
						foreach ( $post_categories as $term_taxonomy_id => $pc ) {
							$element_type   = 'tax_category';
							$trid_args      = array( $term_taxonomy_id, $element_type );
							$trid_prepare   = $wpdb->prepare( $trid_query, $trid_args );
							$trid           = $wpdb->get_var( $trid_prepare );
							$not_translated = false;
							foreach ( $target_languages as $lang ) {
								$target_trid_args    = array( $lang, $trid );
								$target_trid_prepare = $wpdb->prepare( $target_trid_query, $target_trid_args );
								$target_trid         = $wpdb->get_var( $target_trid_prepare );
								if ( $trid != $target_trid ) {
									$not_translated = true;
									break;
								}
							}
							if ( $not_translated ) {
								$categories_to_translate[ $term_taxonomy_id ] = $pc;
							}
						}
						sort( $post_categories, SORT_STRING );
					}

					// CUSTOM TAXONOMIES
					// ***************************************************************************

					$taxonomies_to_translate = array();

					$taxonomies_query   = "
                        SELECT DISTINCT tx.taxonomy
                        FROM {$wpdb->term_taxonomy} tx JOIN {$wpdb->term_relationships} tr ON tx.term_taxonomy_id = tr.term_taxonomy_id
                        WHERE tr.object_id = %d
                    ";
					$taxonomies_args    = array( $post_id );
					$taxonomies_prepare = $wpdb->prepare( $taxonomies_query, $taxonomies_args );
					$taxonomies         = $wpdb->get_col( $taxonomies_prepare );
					foreach ( $taxonomies as $t ) {
						if ( isset( $sitepress_settings[ 'taxonomies_sync_option' ][ $t ] ) && $sitepress_settings[ 'taxonomies_sync_option' ][ $t ] == 1 ) {
							$object_terms_query          = "
                                SELECT x.term_taxonomy_id, t.name 
                                FROM {$wpdb->terms} t 
                                    JOIN {$wpdb->term_taxonomy} x ON t.term_id=x.term_id
                                    JOIN {$wpdb->term_relationships} r ON x.term_taxonomy_id = r.term_taxonomy_id
                                WHERE x.taxonomy = %s AND r.object_id = %d
                                ";
							$object_terms_query_prepared = $wpdb->prepare( $object_terms_query, array( $t, $post_id ) );
							$object_terms                = $wpdb->get_results( $object_terms_query_prepared );
							foreach ( $object_terms as $trm ) {
								$trid_query     = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type=%s";
								$trid_args      = array( $trm->term_taxonomy_id, 'tax_' . $t );
								$trid_prepare   = $wpdb->prepare( $trid_query, $trid_args );
								$trid           = $wpdb->get_var( $trid_prepare );
								$not_translated = false;
								foreach ( $target_languages as $lang ) {

									$target_trid_query   = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE l.english_name=%s AND trid=%d AND element_id IS NOT NULL";
									$target_trid_args    = array( $lang, $trid );
									$target_trid_prepare = $wpdb->prepare( $target_trid_query, $target_trid_args );
									$target_trid         = $wpdb->get_var( $target_trid_prepare );

									if ( $trid != $target_trid ) {
										$not_translated = true;
										break;
									}
								}
								if ( $not_translated ) {
									$taxonomies_to_translate[ $t ][ $trm->term_taxonomy_id ] = $trm->name;
								}
							}
						}
					}

					$data[ 'url' ]                 = htmlentities( $post_url );
					$data[ 'contents' ][ 'title' ] = array(
						'translate' => 1,
						'data'      => base64_encode( icl_strip_control_chars( $post->post_title ) ),
						'format'    => 'base64'
					);
					if ( $sitepress_settings[ 'translated_document_page_url' ] == 'translate' ) {
						$data[ 'contents' ][ 'URL' ] = array(
							'translate' => 1,
							'data'      => base64_encode( $post->post_name ),
							'format'    => 'base64'
						);
					}

					if ( ! empty( $post->post_excerpt ) ) {
						$data[ 'contents' ][ 'excerpt' ] = array(
							'translate' => 1,
							'data'      => base64_encode( icl_strip_control_chars( $post->post_excerpt ) ),
							'format'    => 'base64'
						);
					}
					$data[ 'contents' ][ 'body' ]        = array(
						'translate' => 1,
						'data'      => base64_encode( icl_strip_control_chars( $post->post_content ) ),
						'format'    => 'base64'
					);
					$data[ 'contents' ][ 'original_id' ] = array(
						'translate' => 0,
						'data'      => $post_id
					);
					$data[ 'target_languages' ]          = array( $target_for_server );

					$custom_fields = array();
					foreach ( (array) $iclTranslationManagement->settings[ 'custom_fields_translation' ] as $cf => $op ) {
						if ( $op == 2 ) {
							$custom_fields[ ] = $cf;
						}
					}

					foreach ( $custom_fields as $cf ) {
						$custom_fields_value = get_post_meta( $post_id, $cf, true );
						if ( $custom_fields_value != '' ) {
							$data[ 'contents' ][ 'field-' . $cf ]           = array(
								'translate' => 1,
								'data'      => base64_encode( $custom_fields_value ),
								'format'    => 'base64',
							);
							$data[ 'contents' ][ 'field-' . $cf . '-name' ] = array(
								'translate' => 0,
								'data'      => $cf,
							);
							$data[ 'contents' ][ 'field-' . $cf . '-type' ] = array(
								'translate' => 0,
								'data'      => 'custom_field',
							);
						}
					}

					if ( $categories_to_translate ) {
                        $data[ 'contents' ][ 'categories' ]   = array(
                            'translate' => 1,
                            'data' => implode(
                                ',',
                                array_map(
                                    array( $this, 'base64_encode_quote' ),
                                    $categories_to_translate
                                )
                            ),
                            'format' => 'csv_base64'
                        );
                        $data[ 'contents' ][ 'category_ids' ] = array(
                            'translate' => 0,
                            'data' => implode( ',', array_keys( $categories_to_translate ) ),
                            'format' => ''
                        );
					}

					if ( $tags_to_translate ) {
                        $data[ 'contents' ][ 'tags' ] = array(
                            'translate' => 1,
                            'data' => implode(
                                ',',
                                array_map( array( $this, 'base64_encode_quote' ), $tags_to_translate )
                            ),
                            'format' => 'csv_base64'
                        );
						$data[ 'contents' ][ 'tag_ids' ] = array(
							'translate' => 0,
							'data'      => implode( ',', array_keys( $tags_to_translate ) ),
							'format'    => ''
						);
					}

                    if ( $taxonomies_to_translate ) {
                        foreach ( $taxonomies_to_translate as $k => $v ) {
                            $data[ 'contents' ][ $k ]          = array(
                                'translate' => 1,
                                'data' => implode( ',', array_map( array( $this, 'base64_encode_quote' ), $v ) ),
                                'format' => 'csv_base64'
                            );
                            $data[ 'contents' ][ $k . '_ids' ] = array(
                                'translate' => 0,
                                'data' => implode( ',', array_keys( $v ) ),
                                'format' => ''
                            );
                        }
                    }

					if ( $post->post_status == 'publish' ) {
						$permlink = $post_url;
					} else {
						$permlink = false;
					}

					$note = get_post_meta( $post_id, '_icl_translator_note', true );

					// if this is an old request having a old request_id, include that
					$icl_content_status_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}icl_content_status'" );
					if ( $wpdb->prefix . 'icl_content_status' == $icl_content_status_exists ) {
						$prev_rid_query   = "SELECT MAX(rid) FROM {$wpdb->prefix}icl_content_status WHERE nid=%d";
						$prev_rid_prepare = $wpdb->prepare( $prev_rid_query, $post_id );
						$prev_rid         = $wpdb->get_var( $prev_rid_prepare );
						if ( ! empty( $prev_rid ) ) {
							$data[ 'previous_cms_request_id' ] = $prev_rid;
						}
					}
				}

				$data = apply_filters( 'icl_data_for_pro_translation', $data );

				$xml    = $iclq->build_cms_request_xml( $data, $orig_lang_for_server );
				$cms_id = sprintf( '%s_%d_%s_%s', $post->post_type, $post->ID, $orig_lang, $target_lang );
				$args   = array(
					'cms_id'        => $cms_id,
					'xml'           => $xml,
					'title'         => $post->post_title,
					'to_languages'  => array( $target_for_server ),
					'orig_language' => $orig_lang_for_server,
					'permlink'      => isset( $permlink ) ? $permlink : false,
					'translator_id' => $translator_id,
					'note'          => isset( $note ) ? $note : '',
				);

				$res = $iclq->send_request( $args );
				if ( $res > 0 ) {
					$this->tmg->update_translation_status( array(
						                                       'translation_id' => $translation->translation_id,
						                                       'status'         => ICL_TM_IN_PROGRESS,
						                                       'needs_update'   => 0
					                                       ) );
				} else {
					$_prevstate_query   = "SELECT _prevstate FROM {$wpdb->prefix}icl_translation_status WHERE translation_id=%d";
					$_prevstate_prepare = $wpdb->prepare( $_prevstate_query, $translation->translation_id );
					$_prevstate         = $wpdb->get_var( $_prevstate_prepare );
					if ( ! empty( $_prevstate ) ) {
						$_prevstate = unserialize( $_prevstate );
						$wpdb->update( $wpdb->prefix . 'icl_translation_status', array(
							                                                       'status'              => $_prevstate[ 'status' ],
							                                                       'translator_id'       => $_prevstate[ 'translator_id' ],
							                                                       'needs_update'        => $_prevstate[ 'needs_update' ],
							                                                       'md5'                 => $_prevstate[ 'md5' ],
							                                                       'translation_service' => $_prevstate[ 'translation_service' ],
							                                                       'translation_package' => $_prevstate[ 'translation_package' ],
							                                                       'timestamp'           => $_prevstate[ 'timestamp' ],
							                                                       'links_fixed'         => $_prevstate[ 'links_fixed' ]
						                                                       ), array( 'translation_id' => $translation->translation_id ) );
					} else {
						$wpdb->update( $wpdb->prefix . 'icl_translation_status', array( 'status' => ICL_TM_NOT_TRANSLATED, 'needs_update' => 0 ), array( 'translation_id' => $translation->translation_id ) );
					}
					$err = true;
				}
			} // if needs translation
		} // foreach target lang
		return $err ? false : $res; //last $ret
	}

    function base64_encode_quote( $content ) {

        return '&quot;' . base64_encode( $content ) . '&quot;';
    }
    
    public static function server_languages_map($language_name, $server2plugin = false){
        if(is_array($language_name)){
            return array_map(array(__CLASS__, 'icl_server_languages_map'), $language_name);
        }
        $map = array(
            'Norwegian Bokmål' => 'Norwegian',
            'Portuguese, Brazil' => 'Portuguese',
            'Portuguese, Portugal' => 'Portugal Portuguese'
        );
        if($server2plugin){
            $map = array_flip($map);
        }    
        if(isset($map[$language_name])){
            return $map[$language_name];
        }else{
            return $language_name;    
        }
    }    
    
    function custom_xmlrpc_methods($methods){
        
        $icl_methods['icanlocalize.update_status_by_cms_id'] = array($this, 'get_translated_document');
        
        // for migration to 2.0.0
        $icl_methods['icanlocalize.set_translation_status'] =  array($this,'_legacy_set_translation_status'); 

        $icl_methods['icanlocalize.test_xmlrpc'] = array($this, '_test_xmlrpc');
        $icl_methods['icanlocalize.cancel_translation_by_cms_id'] = array($this, '_xmlrpc_cancel_translation');
        
        // for migration to 2.0.0
        $icl_methods['icanlocalize.cancel_translation'] = array($this, '_legacy_xmlrpc_cancel_translation');
        
        $icl_methods['icanlocalize.notify_comment_translation'] =  array($this, '_xmlrpc_add_message_translation');    
        
        
        $methods = $methods + $icl_methods;    
        if(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST){
            if (preg_match('#<methodName>([^<]+)</methodName>#i', $GLOBALS['HTTP_RAW_POST_DATA'], $matches)) {
            	$method = $matches[1];    
            	if(in_array($method, array_keys($icl_methods))){  
                	//error_reporting(E_NONE);                
                	//ini_set('display_errors', '0');        
                	$old_error_handler = set_error_handler(array($this, "_translation_error_handler"),E_ERROR|E_USER_ERROR);
            	}
	    }
        }
        return $methods;
        
    }

	function _legacy_set_translation_status( $args ) {
		global $sitepress_settings, $sitepress, $wpdb;
		try {

			$signature  = $args[ 0 ];
			$site_id    = $args[ 1 ];
			$request_id = $args[ 2 ];
			$language   = $args[ 4 ];
			$status     = $args[ 5 ];
			$message    = $args[ 6 ];

			if ( $site_id != $sitepress_settings[ 'site_id' ] ) {
				return 3;
			}

			//check signature
			$signature_chk = sha1( $sitepress_settings[ 'access_key' ] . $sitepress_settings[ 'site_id' ] . $request_id . $language . $status . $message );
			if ( $signature_chk != $signature ) {
				return 2;
			}

			$lang_code                = $sitepress->get_language_code( $this->server_languages_map( $language, true ) );//the 'reverse' language filter
			$cms_request_info_query   = "SELECT * FROM {$wpdb->prefix}icl_core_status WHERE rid=%d AND target=%s";
			$cms_request_info_args    = array( $request_id, $lang_code );
			$cms_request_info_prepare = $wpdb->prepare( $cms_request_info_query, $cms_request_info_args );
			$cms_request_info         = $wpdb->get_row( $cms_request_info_prepare );

			if ( empty( $cms_request_info ) ) {
				$this->_throw_exception_for_mysql_errors();

				return 4;
			}

			if ( $this->_legacy_process_translated_document( $request_id, $language, $args ) ) {
				$this->_throw_exception_for_mysql_errors();

				return 1;
			} else {
				$this->_throw_exception_for_mysql_errors();

				return 6;
			}
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}
    
    function _legacy_process_translated_document($request_id, $language, $args){
                
        global $sitepress_settings, $wpdb, $sitepress, $iclTranslationManagement;
        $ret = false;
        $iclq = new ICanLocalizeQuery($sitepress_settings['site_id'], $sitepress_settings['access_key']);       
        $post_type = $wpdb->get_var($wpdb->prepare("SELECT p.post_type FROM {$wpdb->posts} p JOIN {$wpdb->prefix}icl_content_status c ON p.ID = c.nid WHERE c.rid=%d", $request_id));
        $trid = $wpdb->get_var($wpdb->prepare("
            SELECT trid 
            FROM {$wpdb->prefix}icl_translations t 
            JOIN {$wpdb->prefix}icl_content_status c ON t.element_id = c.nid AND t.element_type = %s AND c.rid=%d", 'post_' . $post_type, $request_id));
        $translation = $iclq->cms_do_download($request_id, $language);                           
                
        if($translation){
            if (icl_is_string_translation($translation)){
                $ret = $this->get_translated_string($args);
            } else {
                // we need to create a cms_id for this
                list($lang_from, $lang_to) = $wpdb->get_row($wpdb->prepare("
                    SELECT origin, target FROM {$wpdb->prefix}icl_core_status WHERE rid=%d ORDER BY id DESC LIMIT 1
                ", $request_id), ARRAY_N);
                $translation_id = $wpdb->get_var($wpdb->prepare("
                    SELECT translation_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d and language_code='%s'
                ", $trid, $lang_to));
                
                if(!$translation_id){
                    $wpdb->insert($wpdb->prefix.'icl_translations', array(
                        'element_type'  => 'post_' . $post_type,
                        'trid'          => $trid,
                        'language_code' => $lang_to,
                        'source_language_code' => $lang_from
                    ));
                    $translation_id = $wpdb->insert_id;
                }
                
                $original_post_id = $wpdb->get_var($wpdb->prepare("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND source_language_code IS NULL", $trid));
                
                $translation_package = $iclTranslationManagement->create_translation_package($original_post_id);
                $md5 = $iclTranslationManagement->post_md5($original_post_id);
                
                
                $translator_id = $wpdb->get_var($wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE ID=%d", $original_post_id));
                // add translation_status record        
                                
                list($rid, $update) = $iclTranslationManagement->update_translation_status(array(
                    'translation_id'        => $translation_id,
                    'status'                => 2,
                    'translator_id'         => $translator_id,
                    'needs_update'          => 0,
                    'md5'                   => $md5,
                    'translation_service'   => 'icanlocalize',
                    'translation_package'   => serialize($translation_package)
                ));
                $job_ids[] = $iclTranslationManagement->add_translation_job($rid, $translator_id, $translation_package);                                
                                
                                
                $ret = $this->add_translated_document($translation_id, $request_id);
                
            }
            if($ret){
                $iclq->cms_update_request_status($request_id, CMS_TARGET_LANGUAGE_DONE, $language);
            } 
            
        }        
        return $ret;
    }
            
    /*
     * 0 - unknown error
     * 1 - success
     * 2 - signature mismatch
     * 3 - website_id incorrect
     * 4 - cms_id not found
     * 5 - icl translation not enabled
     * 6 - unknown error processing translation
     */    
    function get_translated_document($args){
        global $sitepress_settings, $sitepress, $wpdb;                
        try{
            
            $signature   = $args[0];
            $site_id     = $args[1];
            $request_id  = $args[2];
            $cms_id      = $args[3];            
            $status      = $args[4];
            $message     = $args[5];  
            
            
            if ($site_id != $sitepress_settings['site_id']) {
                return 3;                                                             
            }
            
            //check signature
            $signature_chk = sha1($sitepress_settings['access_key'].$sitepress_settings['site_id'].$cms_id.$status.$message);
            if($signature_chk != $signature){
                return 2;
            }
            
            // decode cms_id
            $int = preg_match('#(.+)_([0-9]+)_([^_]+)_([^_]+)#', $cms_id, $matches);
            
            $_element_type  = $matches[1];
            $_element_id    = $matches[2];
            $_original_lang = $matches[3];
            $_lang          = $matches[4];
            
            $trid = $sitepress->get_element_trid($_element_id, 'post_'. $_element_type);
            if(!$trid){
                return 4;
            }
            
            $translation = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND language_code=%s", $trid, $_lang));                    
            
            if (empty($translation)){ // if the translation was deleted re-create it
                
                $wpdb->insert($wpdb->prefix.'icl_translations', array(
                    'element_type'          => 'post_' . $_element_type,
                    'trid'                  => $trid,
                    'language_code'         => $_lang,
                    'source_language_code'  => $_original_lang
                ));

                $translation_id = $wpdb->insert_id;
                
                $md5 = $this->tmg->post_md5($_element_id);
            
                $translation_package = $this->tmg->create_translation_package($_element_id);
                     
                $translator_id = 0; //TO FIX!          
                list($rid, $update) = $this->tmg->update_translation_status(array(
                    'translation_id'        => $translation_id,
                    'status'                => ICL_TM_IN_PROGRESS,
                    'translator_id'         => $translator_id,
                    'needs_update'          => 0,
                    'md5'                   => $md5,
                    'translation_service'   => 'icanlocalize',
                    'translation_package'   => serialize($translation_package)
                ));
                $this->tmg->add_translation_job($rid, $translator_id, $translation_package);                                                
            
            }else{
                
                $translation_id = $translation->translation_id;
                
                // if the post is trashed set the element_id to null
                if('trash' == $wpdb->get_var($wpdb->prepare("SELECT post_status FROM {$wpdb->posts} WHERE ID=%d", $translation->element_id))){
										$query = "UPDATE {$wpdb->prefix}icl_translations SET element_id = NULL WHERE translation_id=%d";
										$query_prepared = $wpdb->prepare($query, $translation->translation_id);
                    $wpdb->query($query_prepared);
                }
                
            }
            
            if ($this->add_translated_document($translation_id, $request_id) === true){
                $this->_throw_exception_for_mysql_errors();
                return 1;
            } else {
                $this->_throw_exception_for_mysql_errors();                
                return 6;
            }
            
        }catch(Exception $e) {
            return $e->getMessage();
        }
    }
    
    function add_translated_document($translation_id, $request_id){
        global $sitepress_settings, $wpdb, $sitepress;            
        
        $iclq = new ICanLocalizeQuery($sitepress_settings['site_id'], $sitepress_settings['access_key']);                               
        $tinfo = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}icl_translations WHERE translation_id=%d", $translation_id));                
        $_lang = $sitepress->get_language_details($tinfo->language_code);
        $translation = $iclq->cms_do_download($request_id, $this->server_languages_map($_lang['english_name']));                                 
        
        $translation = apply_filters('icl_data_from_pro_translation', $translation);
        
        $ret = false;
        
        if(!empty($translation)){
            $language_code = $wpdb->get_var($wpdb->prepare("
                SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE translation_id=%d", $translation_id
            ));            
            $parts = explode('_', $translation['original_id']);
            if ($parts[0] == 'external') {
                
                // Translations are saved in the string table for 'external' types
                
                $id = array_pop($parts);
                unset($parts[0]);
                $type = implode('_', $parts);

                unset($translation['original_id']);
                foreach($translation as $field => $value){
                    if (function_exists('icl_st_is_registered_string')) {
                        $value = str_replace('&#0A;', "\n", $value);
                        $string_id = icl_st_is_registered_string($type, $id . '_' . $field);
                        if (!$string_id) {
                            icl_register_string($type, $id . '_' . $field, $value);
                            $string_id = icl_st_is_registered_string($type, $id . '_' . $field);
                        }
                        if ($string_id) {
                            icl_add_string_translation($string_id, $language_code, $value, ICL_STRING_TRANSLATION_COMPLETE);
                        }
                    }
                }
                $ret = true;
            } else {
                $ret = $this->save_post_translation($translation_id, $translation);    
            }

            if($ret){
                $lang_details = $sitepress->get_language_details($language_code);
                $language_server = $this->server_languages_map($lang_details['english_name']);
                $iclq->cms_update_request_status($request_id, CMS_TARGET_LANGUAGE_DONE, $language_server);
                
                $translations = $sitepress->get_element_translations($tinfo->trid, $tinfo->element_type);

	            if ( isset( $translations[ $tinfo->language_code ] ) && is_numeric( $ret ) ) {
                    $iclq->report_back_permalink($request_id, $language_server, $translations[$tinfo->language_code]);
                }
                
            } 
        }
          
        return $ret;
    }
    
    function save_post_translation($translation_id, $translation){        
        global $wpdb, $sitepress_settings, $sitepress, $wp_taxonomies, $icl_adjust_id_url_filter_off;
        $icl_adjust_id_url_filter_off = true;

	    $tinfo_prepare = $wpdb->prepare( "
                SELECT * FROM {$wpdb->prefix}icl_translations tr
                    JOIN {$wpdb->prefix}icl_translation_status ts ON ts.translation_id = tr.translation_id
                WHERE tr.translation_id=%d", $translation_id );
	    $tinfo = $wpdb->get_row( $tinfo_prepare );
        $lang_code = $tinfo->language_code;
        $trid = $tinfo->trid;

	    $original_post_details_query = $wpdb->prepare("
            SELECT p.post_author, p.post_type, p.post_status, p.comment_status, p.ping_status, p.post_parent, p.menu_order, p.post_date, t.language_code
            FROM {$wpdb->prefix}icl_translations t
            JOIN {$wpdb->posts} p ON t.element_id = p.ID AND CONCAT('post_',p.post_type) = t.element_type
            WHERE trid = %d AND p.ID = %d
        ", array($trid, $translation['original_id']));
	    $original_post_details = $wpdb->get_row( $original_post_details_query );
        
        //is the original post a sticky post?
        remove_filter('option_sticky_posts', array($sitepress,'option_sticky_posts')); // remove filter used to get language relevant stickies. get them all
        $sticky_posts = get_option('sticky_posts');
        $is_original_sticky = $original_post_details->post_type=='post' && in_array($translation['original_id'], $sticky_posts);
        
               
        $this->_content_fix_image_paths_in_body($translation);        
        $this->_content_fix_relative_link_paths_in_body($translation);
        $this->_content_decode_shortcodes($translation);
        
        
        // deal with tags
	    $term_different_language_query = "
                    SELECT tm.term_id
                    FROM {$wpdb->term_taxonomy} tx
                        JOIN {$wpdb->terms} tm ON tx.term_id = tm.term_id
                        JOIN {$wpdb->prefix}icl_translations tr ON tx.term_taxonomy_id = tr.element_id
                    WHERE tm.name=%s AND tr.element_type LIKE 'tax%%' AND tr.language_code <> %s
                ";
	    if(isset($translation['tags'])){
            $translated_tags = $translation['tags'];   
            $translated_tag_ids = explode(',', $translation['tag_ids']);
            foreach($translated_tags as $k=>$v){
	            $tag_trid_query   = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type='tax_post_tag'";
	            $tag_trid_args    = array( $translated_tag_ids[ $k ] );
	            $tag_trid_prepare = $wpdb->prepare( $tag_trid_query, $tag_trid_args );
	            $tag_trid         = $wpdb->get_var( $tag_trid_prepare );
                
                // before adding the new term make sure that another tag with the same name doesn't exist. If it does append @lang                                        
                // same term name exists in a different language?
	            $term_different_language_args = array($v, $lang_code);
	            $term_different_language_prepare = $wpdb->prepare($term_different_language_query, $term_different_language_args);
                $term_different_language = $wpdb->get_var( $term_different_language_prepare );
                if($term_different_language){
                    $v .= ' @'.$lang_code;    
                }
                
                //tag exists? (in the current language)
                $etag = get_term_by('name', $v, 'post_tag');
                if(!$etag){
                    $etag = get_term_by('name', $v . ' @'.$lang_code, 'post_tag');
                }                
                if(!$etag){                                          
                    $tmp = wp_insert_term($v, 'post_tag');
                    if(!is_wp_error($tmp) && isset($tmp['term_taxonomy_id'])){
                        $wpdb->update($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$tag_trid, 'source_language_code'=>$original_post_details->language_code), 
                            array('element_type'=>'tax_post_tag','element_id'=>$tmp['term_taxonomy_id']));
                    }         
                }else{
                    $term_taxonomy_id = $etag->term_taxonomy_id; 
                    // check whether we have an orphan translation - the same trid and language but a different element id                                                     
	                $__translation_id_query   = "
                        SELECT translation_id FROM {$wpdb->prefix}icl_translations
                        WHERE   trid = %d
                            AND language_code = %s
                            AND element_id <> %d
                    ";
	                $__translation_id_args    = array( $tag_trid, $lang_code, $term_taxonomy_id );
	                $__translation_id_prepare = $wpdb->prepare( $__translation_id_query, $__translation_id_args );
	                $__translation_id = $wpdb->get_var( $__translation_id_prepare );
                    if($__translation_id){
						$q = "DELETE FROM {$wpdb->prefix}icl_translations WHERE translation_id=%d";
						$q_prepared = $wpdb->prepare($q, $__translation_id);
                        $wpdb->query($q_prepared);    
                    }

	                $tag_translation_id_query   = "SELECT translation_id FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type='tax_post_tag'";
	                $tag_translation_id_args    = array( $term_taxonomy_id );
	                $tag_translation_id_prepare = $wpdb->prepare( $tag_translation_id_query, $tag_translation_id_args );
	                $tag_translation_id         = $wpdb->get_var( $tag_translation_id_prepare );
                    if($tag_translation_id){
                        $wpdb->update($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$tag_trid, 'source_language_code'=>$original_post_details->language_code), 
                            array('element_type'=>'tax_post_tag','translation_id'=>$tag_translation_id));                
                    }else{                                                
                        $wpdb->insert($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$tag_trid, 'element_type'=>'tax_post_tag', 'element_id'=>$term_taxonomy_id, 'source_language_code'=>$original_post_details->language_code));                                
                    }
                }        
            } 
        }
        
        $original_post_tags = array();
        foreach(wp_get_object_terms($translation['original_id'] , 'post_tag') as $t){
            $original_post_tags[] = $t->term_taxonomy_id;
        }
	    $translated_tags = array();
	    if ( $original_post_tags ) {
		    $tag_trid_element_id_in = wpml_prepare_in( $original_post_tags, '%s' );
		    $tag_trids_query        = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_type='tax_post_tag' AND element_id IN (" . $tag_trid_element_id_in . ")";
		    $tag_trids              = $wpdb->get_col( $tag_trids_query );
		    if ( ! empty( $tag_trids ) ) {
			    $tag_trid_in        = wpml_prepare_in( $tag_trids, '%d' );
			    $tag_tr_tts_query   = "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type='tax_post_tag' AND language_code=%s AND trid IN (" . $tag_trid_in . ")";
			    $tag_tr_tts_args    = $lang_code;
			    $tag_tr_tts_prepare = $wpdb->prepare( $tag_tr_tts_query, $tag_tr_tts_args );
			    $tag_tr_tts         = $wpdb->get_col( $tag_tr_tts_prepare );
		    }
		    if ( ! empty( $tag_tr_tts ) ) {
			    $tag_tr_tts_in           = wpml_prepare_in( $tag_tr_tts, '%d' );
			    $translated_tags_query   = "SELECT t.name FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tx ON tx.term_id = t.term_id WHERE tx.taxonomy='post_tag' AND tx.term_taxonomy_id IN (" . $tag_tr_tts_in . ")";
			    $translated_tags         = $wpdb->get_col( $translated_tags_query );
		    }
	    }
        
        // deal with categories
        if(isset($translation['categories'])){
            $translated_cats = $translation['categories'];   
            $translated_cats_ids = explode(',', $translation['category_ids']);    
            foreach($translated_cats as $k=>$v){
	            $cat_trid_query   = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id=%d AND element_type='tax_category'";
	            $cat_trid_args    = array( $translated_cats_ids[ $k ] );
	            $cat_trid_prepare = $wpdb->prepare( $cat_trid_query, $cat_trid_args );
	            $cat_trid         = $wpdb->get_var( $cat_trid_prepare );
                
                // before adding the new term make sure that another tag with the same name doesn't exist. If it does append @lang                                        
                // same term name exists in a different language?

	            $term_different_language_args = array($v, $lang_code);
	            $term_different_language_prepare = $wpdb->prepare($term_different_language_query, $term_different_language_args);
                $term_different_language = $wpdb->get_var( $term_different_language_prepare );

                if($term_different_language){
                    $v .= ' @'.$lang_code;    
                }
                
                //cat exists?
                $ecat = get_term_by('name', $v, 'category');
                if(!$ecat){
                    $ecat = get_term_by('name', $v . ' @'.$lang_code, 'category');
                }     
                           
                if(!$ecat){                    
                    // get original category parent id
                    $original_category_parent_id = $wpdb->get_var($wpdb->prepare("SELECT parent FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id=%d",$translated_cats_ids[$k]));
                    if($original_category_parent_id){
                        $_op_tax_id = $wpdb->get_var($wpdb->prepare("SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy='category' AND term_id=%d",$original_category_parent_id));
                        $_op_trid   = $wpdb->get_var($wpdb->prepare("SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_type='tax_category' AND element_id=%d",$_op_tax_id));
                        // get id of the translated category parent
                        $_tp_tax_id = $wpdb->get_var($wpdb->prepare("SELECT element_id FROM {$wpdb->prefix}icl_translations
																	 WHERE language_code=%s AND trid=%d",$lang_code, $_op_trid));
                        if($_tp_tax_id){
                            $category_parent_id = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy='category' AND term_taxonomy_id=%d",$_tp_tax_id));
                        }else{
                            $category_parent_id = 0;
                        }                        
                    }else{
                        $category_parent_id = 0;
                    }                    
                    $tmp = wp_insert_term($v, 'category', array('parent'=>$category_parent_id));
                    if(!is_wp_error($tmp) && isset($tmp['term_taxonomy_id'])){
                        $wpdb->update($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$cat_trid, 'source_language_code'=>$original_post_details->language_code), 
                            array('element_type'=>'tax_category','element_id'=>$tmp['term_taxonomy_id']));
                            
                        // if this is a parent category, make sure that nesting is correct for all translations
                        $orig_cat_tax_id   = $wpdb->get_var($wpdb->prepare("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND source_language_code IS NULL", $cat_trid));                        
                        $orig_cat_term_id  = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id=%d AND taxonomy='category'",$orig_cat_tax_id));
                        $orig_cat_children = $wpdb->get_col($wpdb->prepare("SELECT term_id FROM {$wpdb->term_taxonomy} WHERE parent=%d",$orig_cat_term_id));                        
                        if(!empty($orig_cat_children)) foreach($orig_cat_children as $ch){
                            $_tr_child = icl_object_id($ch, 'category', false, $lang_code);
                            if($_tr_child){
                                $wpdb->update($wpdb->term_taxonomy, array('parent'=>$tmp['term_id']), array(
                                    'taxonomy'=>'category', 'term_id' => $_tr_child
                                ));
	                            $sitepress->update_terms_relationship_cache( array($category_parent_id, $tmp['term_id'], $_tr_child), 'category' );
                            }
                        }
//                        delete_option('category_children');
                    }
                }else{
                    $term_taxonomy_id = $ecat->term_taxonomy_id;
                    // check whether we have an orphan translation - the same trid and language but a different element id                                                     
                    $__translation_id = $wpdb->get_var( $wpdb->prepare("
                        SELECT translation_id FROM {$wpdb->prefix}icl_translations 
                        WHERE   trid = %d
                            AND language_code = %s
                            AND element_id <> %d
                    ", $cat_trid, $lang_code, $term_taxonomy_id ) );
                    if($__translation_id){
						$q = "DELETE FROM {$wpdb->prefix}icl_translations WHERE translation_id=%d";
						$q_prepared = $wpdb->prepare($q, $__translation_id);
                        $wpdb->query($q_prepared);    
                    }
                    
                    $cat_translation_id = $wpdb->get_var(
	                    $wpdb->prepare("SELECT translation_id FROM {$wpdb->prefix}icl_translations WHERE element_id = %d AND element_type='tax_category'", $term_taxonomy_id ) );
                    if($cat_translation_id){
                        $wpdb->update($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$cat_trid, 'source_language_code'=>$original_post_details->language_code), 
                            array('element_type'=>'tax_category','translation_id'=>$cat_translation_id));                
                    }else{
                        $wpdb->insert($wpdb->prefix.'icl_translations', 
                            array('language_code'=>$lang_code, 'trid'=>$cat_trid, 'element_type'=>'tax_category', 'element_id'=>$term_taxonomy_id, 'source_language_code'=>$original_post_details->language_code));                                
                    }            
                }        
            }
        }
        $original_post_cats = array();    
        foreach(wp_get_object_terms($translation['original_id'] , 'category') as $t){
            $original_post_cats[] = $t->term_taxonomy_id;
        }
        if($original_post_cats){    
            $cat_trids = $wpdb->get_col("SELECT trid FROM {$wpdb->prefix}icl_translations
										 WHERE element_type='tax_category'
										  AND element_id IN (". wpml_prepare_in($original_post_cats,'%d') . ")");
            if(!empty($cat_trids))
            $cat_tr_tts = $wpdb->get_col($wpdb->prepare("SELECT element_id FROM {$wpdb->prefix}icl_translations
										  WHERE element_type='tax_category'
										    AND language_code = %s
										    AND trid IN (" . wpml_prepare_in($cat_trids, '%d') . ")", $lang_code));
            if(!empty($cat_tr_tts))
            $translated_cats_ids = $wpdb->get_col(" SELECT t.term_id
													FROM {$wpdb->terms} t
													JOIN {$wpdb->term_taxonomy} tx
														ON tx.term_id = t.term_id
													WHERE tx.taxonomy='category'
														AND tx.term_taxonomy_id IN (" . wpml_prepare_in($cat_tr_tts, '%d') . ")");
        }   
        
                
        // deal with custom taxonomies
	    $translated_taxs = array();
	    $translated_tax_ids = array();
	    if(!empty($sitepress_settings['taxonomies_sync_option'])){
            foreach($sitepress_settings['taxonomies_sync_option'] as $taxonomy=>$value){
                if($value == 1 && isset($translation[$taxonomy])){
                    $translated_taxs[$taxonomy] = $translation[$taxonomy];   
                    $translated_tax_ids[$taxonomy] = explode(',', $translation[$taxonomy.'_ids']);                    
                    foreach($translated_taxs[$taxonomy] as $k=>$v){
	                    $tax_trid_query   = "
                                SELECT trid FROM {$wpdb->prefix}icl_translations
                                WHERE element_id=%d AND element_type=%s";
	                    $tax_trid_args = array($translated_tax_ids[$taxonomy][$k], 'tax_'.$taxonomy);
	                    $tax_trid_prepare = $wpdb->prepare($tax_trid_query, $tax_trid_args);
	                    $tax_trid = $wpdb->get_var( $tax_trid_prepare );
                        // before adding the new term make sure that another tag with the same name doesn't exist. If it does append @lang
                        // same term name exists in a different language?                        
	                    $term_different_language_query   = "
                                SELECT tm.term_id
                                FROM {$wpdb->term_taxonomy} tx
                                    JOIN {$wpdb->terms} tm ON tx.term_id = tm.term_id
                                    JOIN {$wpdb->prefix}icl_translations tr ON tx.term_taxonomy_id = tr.element_id
                                WHERE tm.name=%s AND tr.element_type LIKE 'tax%%' AND tr.language_code <> %s
                            ";
	                    $term_different_language_args = array($v, $lang_code);
	                    $term_different_language_prepare = $wpdb->prepare($term_different_language_query, $term_different_language_args);
	                    $term_different_language = $wpdb->get_var( $term_different_language_prepare );
                        if($term_different_language){
                            $v .= ' @'.$lang_code;    
                        }
                            
                        //tax exists? (in the current language)
                        $etag = get_term_by('name', $v, $taxonomy);
                        if(!$etag){
                            $etag = get_term_by('name', $v . ' @'.$lang_code, $taxonomy);
                        }         
                        
                        if(!$etag){      
                            $tmp = wp_insert_term($v, $taxonomy);
                            if(!is_wp_error($tmp) && isset($tmp['term_taxonomy_id'])){
                                $wpdb->update($wpdb->prefix.'icl_translations', 
                                        array('language_code'=>$lang_code, 'trid'=>$tax_trid, 'source_language_code'=>$original_post_details->language_code), 
                                        array('element_type'=>'tax_'.$taxonomy,'element_id'=>$tmp['term_taxonomy_id']));
                                        
                                
                                // if this is a parent category, make sure that nesting is correct for all translations
	                            $orig_tax_id_query         = "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND source_language_code IS NULL";
	                            $orig_tax_id_args          = array( $tax_trid );
	                            $orig_tax_id_prepare       = $wpdb->prepare( $orig_tax_id_query, $orig_tax_id_args );
	                            $orig_tax_id               = $wpdb->get_var( $orig_tax_id_prepare );
	                            $orig_term_id_query        = "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id=%d AND taxonomy=%s";
	                            $orig_term_id_args         = array( $orig_tax_id, $taxonomy );
	                            $orig_term_id_prepare      = $wpdb->prepare( $orig_term_id_query, $orig_term_id_args );
	                            $orig_term_id              = $wpdb->get_var( $orig_term_id_prepare );
	                            $orig_tax_children_query   = "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE parent=%d";
	                            $orig_tax_children_prepare = $wpdb->prepare( $orig_tax_children_query, $orig_term_id );
	                            $orig_tax_children         = $wpdb->get_col( $orig_tax_children_prepare );
                                if(!empty($orig_tax_children)) foreach($orig_tax_children as $ch){
                                    $_tr_child = icl_object_id($ch, $taxonomy, false, $lang_code);
                                    if($_tr_child){
                                        $wpdb->update($wpdb->term_taxonomy, array('parent'=>$tmp['term_id']), array(
                                            'taxonomy'=>$taxonomy, 'term_id' => $_tr_child
                                        ));
	                                    $sitepress->update_terms_relationship_cache( array($tmp['term_id'], $_tr_child), $taxonomy );
                                    }
                                }
                            }
                        }else{
                            $term_taxonomy_id = $etag->term_taxonomy_id;
                            // check whether we have an orphan translation - the same trid and language but a different element id                             
	                        $__translation_id_query   = "
                                SELECT translation_id FROM {$wpdb->prefix}icl_translations
                                WHERE   trid = %s
                                    AND language_code = %s
                                    AND element_id <> %d
                            ";
	                        $__translation_id_args = array( $tax_trid, $lang_code, $term_taxonomy_id );
	                        $__translation_id_prepare = $wpdb->prepare( $__translation_id_query, $__translation_id_args );
	                        $__translation_id = $wpdb->get_var( $__translation_id_prepare );
                            if($__translation_id){
	                            $q          = "DELETE FROM {$wpdb->prefix}icl_translations WHERE translation_id=%d";
	                            $q_prepared = $wpdb->prepare( $q, $__translation_id );
	                            $wpdb->query( $q_prepared );
                            }

	                        $tax_translation_id_query   = "
                                SELECT translation_id FROM {$wpdb->prefix}icl_translations
                                WHERE element_id=%d AND element_type=%s
                                ";
	                        $tax_translation_id_args    = array( $term_taxonomy_id, 'tax_' . $taxonomy );
	                        $tax_translation_id_prepare = $wpdb->prepare( $tax_translation_id_query, $tax_translation_id_args );
	                        $tax_translation_id         = $wpdb->get_var( $tax_translation_id_prepare );
                            if($tax_translation_id){
                                $wpdb->update($wpdb->prefix.'icl_translations', 
                                    array('language_code'=>$lang_code, 'trid'=>$tax_trid, 'source_language_code'=>$original_post_details->language_code), 
                                    array('element_type'=>'tax_'.$taxonomy,'translation_id'=>$tax_translation_id));                
                            }else{                                                
                                $wpdb->insert($wpdb->prefix.'icl_translations', 
                                    array('language_code'=>$lang_code, 'trid'=>$tax_trid, 'element_type'=>'tax_'.$taxonomy, 
                                        'element_id'=>$term_taxonomy_id, 'source_language_code'=>$original_post_details->language_code));                                                      
                            }
                        }        
                    }
                }
                
                $oterms = wp_get_object_terms($translation['original_id'] , $taxonomy);
                if(!is_wp_error($oterms)){
                    foreach($oterms as $t){
                        $original_post_taxs[$taxonomy][] = $t->term_taxonomy_id;    
                    }    
                }
                
                if(!empty($original_post_taxs[$taxonomy])){
	                $element_id_in     = wpml_prepare_in( $original_post_taxs[ $taxonomy ], '%d' );
	                $tax_trids_query   = "
	                    SELECT trid
	                    FROM {$wpdb->prefix}icl_translations
                        WHERE element_type=%s AND element_id IN (" . $element_id_in . ")
                        ";
	                $element_type_arg  = 'tax_' . $taxonomy;
	                $tax_trids_args    = $element_type_arg;
	                $tax_trids_prepare = $wpdb->prepare( $tax_trids_query, $tax_trids_args );
	                $tax_trids         = $wpdb->get_col( $tax_trids_prepare );
                    if(!empty($tax_trids)){
	                    $trid_in            = wpml_prepare_in( $tax_trids, '%d' );
	                    $tax_tr_tts_query   = "
	                        SELECT element_id
	                        FROM {$wpdb->prefix}icl_translations
                            WHERE element_type=%s AND language_code=%s AND trid IN (" . $trid_in . ")
                            ";
	                    $tax_tr_tts_args    = array( $element_type_arg, $lang_code );
	                    $tax_tr_tts_prepare = $wpdb->prepare( $tax_tr_tts_query, $tax_tr_tts_args );
	                    $tax_tr_tts         = $wpdb->get_col( $tax_tr_tts_prepare );
                    }

	                if ( ! empty( $tax_tr_tts ) ) {
		                $term_taxonomy_id_in = wpml_prepare_in( $tax_tr_tts, '%d' );
		                if ( $wp_taxonomies[ $taxonomy ]->hierarchical ) {
			                $translated_tax_ids_taxonomy_query   = "
			                SELECT term_id
			                FROM {$wpdb->term_taxonomy}
			                WHERE term_taxonomy_id IN (" . $term_taxonomy_id_in . ")
			                ";
			                $translated_tax_ids[ $taxonomy ]     = $wpdb->get_col( $translated_tax_ids_taxonomy_query );
		                } else {
			                $translated_taxs_taxonomy_query   = "
				                SELECT t.name
				                FROM {$wpdb->terms} t
	                                JOIN {$wpdb->term_taxonomy} tx ON tx.term_id = t.term_id
                                WHERE tx.taxonomy=%s AND tx.term_taxonomy_id IN (" . $term_taxonomy_id_in . ")
                                ";
			                $translated_taxs_taxonomy_args    = $taxonomy;
			                $translated_taxs_taxonomy_prepare = $wpdb->prepare( $translated_taxs_taxonomy_query, $translated_taxs_taxonomy_args );
			                $translated_taxs[ $taxonomy ]     = $wpdb->get_col( $translated_taxs_taxonomy_prepare );
		                }
	                }
                }
            }
        }
           
                     
    
        // handle the page parent and set it to the translated parent if we have one.
        if($original_post_details->post_parent){
	        $post_parent_trid_query   = "SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_type=%s AND element_id=%d";
	        $post_parent_trid_args    = array( 'post_' . $original_post_details->post_type, $original_post_details->post_parent );
	        $post_parent_trid_prepare = $wpdb->prepare( $post_parent_trid_query, $post_parent_trid_args );
	        $post_parent_trid         = $wpdb->get_var( $post_parent_trid_prepare );
            if($post_parent_trid){
	            $parent_id_query   = "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type=%s AND trid=%d AND language_code=%s";
	            $parent_id_args    = array( 'post_' . $original_post_details->post_type, $post_parent_trid, $lang_code );
	            $parent_id_prepare = $wpdb->prepare( $parent_id_query, $parent_id_args );
	            $parent_id         = $wpdb->get_var( $parent_id_prepare );
            }            
        }

        // determine post id based on trid
        $post_id = $tinfo->element_id;
        
        if($post_id){
            // see if the post really exists - make sure it wasn't deleted while the plugin was 
	        $check_post_id_query   = "SELECT ID FROM {$wpdb->posts} WHERE ID=%d";
	        $check_post_id_args    = array( $post_id );
	        $check_post_id_prepare = $wpdb->prepare( $check_post_id_query, $check_post_id_args );
	        $check_post_id         = $wpdb->get_var( $check_post_id_prepare );
	        if(! $check_post_id ){
		        $is_update  = false;
		        $q          = "DELETE FROM {$wpdb->prefix}icl_translations WHERE element_type=%s AND element_id=%d";
		        $q_prepared = $wpdb->prepare( $q, array( 'post_' . $original_post_details->post_type, $post_id ) );
		        $wpdb->query( $q_prepared );
            }else{
                $is_update = true;
                $postarr['ID'] = $_POST['post_ID'] = $post_id;
            }
        }else{
            $is_update = false;
        } 
        $postarr['post_title'] = $translation['title'];        
        if($sitepress_settings['translated_document_page_url'] == 'translate' && isset($translation['URL'])){
            $postarr['post_name'] = $translation['URL'];
        }
        $postarr['post_content'] = $translation['body'];
        if (isset($translation['excerpt']) && $translation['excerpt'] != "") {
            $postarr['post_excerpt'] = $translation['excerpt'];
        }
        if($translated_tags){
            $postarr['tags_input'] = join(',',(array)$translated_tags);
        }
        if($translated_taxs){
            foreach($translated_taxs as $taxonomy=>$values){
                $postarr['tax_input'][$taxonomy] = join(',',(array)$values);
            }
        } 
        if(@is_array($translated_tax_ids)){
            $postarr['tax_input'] = $translated_tax_ids;
        }           
        if(isset($translated_cats_ids)){
            $postarr['post_category'] = $translated_cats_ids;        
        }
        $postarr['post_author'] = $original_post_details->post_author;  
        $postarr['post_type'] = $original_post_details->post_type;
        if($sitepress_settings['sync_comment_status']){
            $postarr['comment_status'] = $original_post_details->comment_status;
        }
        if($sitepress_settings['sync_ping_status']){
            $postarr['ping_status'] = $original_post_details->ping_status;
        }
        if($sitepress_settings['sync_page_ordering']){
            $postarr['menu_order'] = $original_post_details->menu_order;
        }
        if($sitepress_settings['sync_private_flag'] && $original_post_details->post_status=='private'){    
            $postarr['post_status'] = 'private';
        }
        if(!$is_update){
            $postarr['post_status'] = !$sitepress_settings['translated_document_status'] ? 'draft' : $original_post_details->post_status;
        } else {
            // set post_status to the current post status.
            $postarr['post_status'] = $wpdb->get_var( $wpdb->prepare("SELECT post_status
																	  FROM {$wpdb->prefix}posts
																	  WHERE ID = %d", $post_id ) );
        }
        if($sitepress_settings['sync_post_date']){
            $postarr['post_date'] = $original_post_details->post_date;
        }        
        
        if(isset($parent_id) && $sitepress_settings['sync_page_parent']){
            $_POST['post_parent'] = $postarr['post_parent'] = $parent_id;  
            $_POST['parent_id'] = $postarr['parent_id'] = $parent_id;  
        }
        
        if($is_update){
	        $post_name_query      = "SELECT post_name FROM {$wpdb->posts} WHERE ID=%d";
	        $post_name_prepare    = $wpdb->prepare( $post_name_query, $post_id );
	        $postarr['post_name'] = $wpdb->get_var( $post_name_prepare );
        }
         
        $_POST['trid'] = $trid;
        $_POST['lang'] = $lang_code;
        $_POST['skip_sitepress_actions'] = true;
        
        
        global $wp_rewrite;
        if(!isset($wp_rewrite)) $wp_rewrite = new WP_Rewrite();
            
        kses_remove_filters();
        
        $postarr = apply_filters('icl_pre_save_pro_translation', $postarr);
        
        $new_post_id = wp_insert_post($postarr);    
        
        do_action('icl_pro_translation_saved', $new_post_id);
        
        // associate custom taxonomies by hand        
        if ( !empty($postarr['tax_input']) ) {
            foreach ( $postarr['tax_input'] as $taxonomy => $tags ) {
                if($wp_taxonomies[$taxonomy]->hierarchical){
                    wp_set_post_terms( $new_post_id, $tags, $taxonomy );
                }else{
                    wp_set_post_terms( $new_post_id, $translated_taxs[$taxonomy], $taxonomy );
                }
            }
        }
        
        // set stickiness
        if($is_original_sticky && $sitepress_settings['sync_sticky_flag']){
            stick_post($new_post_id);
        }else{
            if($original_post_details->post_type=='post' && $is_update){
                unstick_post($new_post_id); //just in case - if this is an update and the original post stckiness has changed since the post was sent to translation
            }
        }

	    if ( isset( $sitepress_settings[ 'translation-management' ] ) && isset( $sitepress_settings[ 'translation-management' ][ 'custom_fields_translation' ] ) ) {
		    foreach ( (array) $sitepress_settings[ 'translation-management' ][ 'custom_fields_translation' ] as $cf => $op ) {
			    if ( $op == 1 ) {
				    $sitepress->_sync_custom_field( $translation[ 'original_id' ], $new_post_id, $cf );
			    } elseif ( $op == 2 && isset( $translation[ 'field-' . $cf ] ) ) {
				    $field_translation = $translation[ 'field-' . $cf ];
				    $field_type        = $translation[ 'field-' . $cf . '-type' ];
				    if ( $field_type == 'custom_field' ) {
					    $field_translation = str_replace( '&#0A;', "\n", $field_translation );
					    // always decode html entities  eg decode &amp; to &
					    $field_translation = html_entity_decode( $field_translation );
					    update_post_meta( $new_post_id, $cf, $field_translation );
				    }
			    }
		    }
	    }
        
        
        // set specific custom fields
        $copied_custom_fields = array('_top_nav_excluded', '_cms_nav_minihome');    
        foreach($copied_custom_fields as $ccf){
            $val = get_post_meta($translation['original_id'], $ccf, true);
            update_post_meta($new_post_id, $ccf, $val);
        }    
        
        // sync _wp_page_template
        if($sitepress_settings['sync_page_template']){
            $_wp_page_template = get_post_meta($translation['original_id'], '_wp_page_template', true);
            update_post_meta($new_post_id, '_wp_page_template', $_wp_page_template);
        }

		// sync post format
		if ( $sitepress_settings[ 'sync_post_format' ] ) {
			$_wp_post_format = get_post_format( $translation[ 'original_id' ] );
			set_post_format( $new_post_id, $_wp_post_format );
		}

		if(!$new_post_id){
            return false;
        }
        
        if(!$is_update){
            $wpdb->update($wpdb->prefix.'icl_translations', array('element_id'=>$new_post_id), array('translation_id' => $translation_id));
        }        
        update_post_meta($new_post_id, '_icl_translation', 1);
        
        TranslationManagement::set_page_url($new_post_id);
        
        global $iclTranslationManagement;
        
        
        $ts = array(
            'status'=>ICL_TM_COMPLETE, 'needs_update'=>0,
            'translation_id'=>$translation_id
        );        
        
        $translator_id_query = "SELECT translator_id FROM {$wpdb->prefix}icl_translation_status WHERE translation_id=%d";
	    $translator_id_prepare = $wpdb->prepare( $translator_id_query, $translation_id );
	    $translator_id = $wpdb->get_var( $translator_id_prepare );
        if(!$translator_id){
            foreach($sitepress_settings['icl_lang_status'] as $lpair){
                if($lpair['from'] == $original_post_details->language_code && $lpair['to'] == $lang_code && isset($lpair['translators'][0]['id'])){
                    $ts['translator_id'] = $lpair['translators'][0]['id'];
                    break;
                }
            }
        }
                
        
         // update translation status 
        $iclTranslationManagement->update_translation_status($ts);
        
       
        
        // add new translation job
        
        $job_id = $iclTranslationManagement->get_translation_job_id($trid, $lang_code);
        // save the translation
        $iclTranslationManagement->mark_job_done($job_id);
        $parts = explode('_', $translation['original_id']);
        if ($parts[0] != 'external') {
            $iclTranslationManagement->save_job_fields_from_post($job_id, get_post($new_post_id));
            
            $this->_content_fix_links_to_translated_content($new_post_id, $lang_code, "post_{$original_post_details->post_type}");

	        // Now try to fix links in other translated content that may link to this post.
	        $needs_fixing_query    = "SELECT
                        tr.element_id
                    FROM
                        {$wpdb->prefix}icl_translations tr
                    JOIN
                        {$wpdb->prefix}icl_translation_status ts
                    ON
                        tr.translation_id = ts.translation_id
                    WHERE
                        ts.links_fixed = 0 AND tr.element_type = %s AND tr.language_code = %s AND tr.element_id IS NOT NULL";
	        $needs_fixing_args     = array( 'post_' . $original_post_details->post_type, $lang_code );
	        $needs_fixing_prepared = $wpdb->prepare( $needs_fixing_query, $needs_fixing_args );
	        $needs_fixing          = $wpdb->get_results( $needs_fixing_prepared );
	        foreach ( $needs_fixing as $id ) {
		        if ( $id->element_id != $new_post_id ) { // fix all except the new_post_id. We have already done this.
			        $this->_content_fix_links_to_translated_content( $id->element_id, $lang_code, "post_{$original_post_details->post_type}" );
		        }
	        }
            
            // if this is a parent page then make sure it's children point to this.
            $this->fix_translated_children($translation['original_id'], $new_post_id, $lang_code);
        }
        
        do_action('icl_pro_translation_completed', $new_post_id);
                
        return true;
    }        
    
    // old style - for strings
    function get_translated_string($args){
        global $sitepress_settings, $sitepress, $wpdb;        

        try{
            
            $signature   = $args[0];
            $site_id     = $args[1];
            $request_id  = $args[2];
            $language    = $args[4];
            $status      = $args[5];
            $message     = $args[6];  
            
            if ($site_id != $sitepress_settings['site_id']) {
                return 3;                                                             
            }
            
            //check signature
            $signature_chk = sha1($sitepress_settings['access_key'].$sitepress_settings['site_id'].$request_id.$language.$status.$message);
            if($signature_chk != $signature){
                return 2;
            }
            
            $lang_code = $sitepress->get_language_code($this->server_languages_map($language, true));//the 'reverse' language filter 

	        $cms_request_info_query   = "SELECT * FROM {$wpdb->prefix}icl_core_status WHERE rid=%d AND target=%s";
	        $cms_request_info_args    = array( $request_id, $lang_code );
	        $cms_request_info_prepare = $wpdb->prepare( $cms_request_info_query, $cms_request_info_args );
	        $cms_request_info         = $wpdb->get_row( $cms_request_info_prepare );
            
            if (empty($cms_request_info)){
                $this->_throw_exception_for_mysql_errors();
                return 4;
            }

            //return $this->process_translated_string($request_id, $language);
            
            if ($this->process_translated_string($request_id, $language) === true){
                $this->_throw_exception_for_mysql_errors();
                return 1;
            } else {
                $this->_throw_exception_for_mysql_errors();
                return 6;
            }
            
        }catch(Exception $e) {
            return $e->getMessage();
        }
    }
    
    // old style - for strings
    function process_translated_string($request_id, $language){
        global $sitepress_settings, $wpdb, $sitepress;
        $ret = false;
        $iclq = new ICanLocalizeQuery($sitepress_settings['site_id'], $sitepress_settings['access_key']);       
        
        $translation = $iclq->cms_do_download($request_id, $language);                                   
        
        if($translation){            
            $ret = icl_translation_add_string_translation($request_id, $translation, $sitepress->get_language_code($this->server_languages_map($language, true))); 
            if($ret){
                $iclq->cms_update_request_status($request_id, CMS_TARGET_LANGUAGE_DONE, $language);
            } 
        }   
             
        // if there aren't any other unfulfilled requests send a global 'done'
	    $rid_count_query   = "SELECT COUNT(rid) FROM {$wpdb->prefix}icl_core_status WHERE rid=%d AND status < %d";
	    $rid_count_args    = array( $request_id, CMS_TARGET_LANGUAGE_DONE );
	    $rid_count_prepare = $wpdb->prepare( $rid_count_query, $rid_count_args );
	    $rid_count         = $wpdb->get_var( $rid_count_prepare );
	    if ( 0 == $rid_count ) {
		    $iclq->cms_update_request_status( $request_id, CMS_REQUEST_DONE, false );
	    }
        return $ret;
    }
    
    function _content_fix_image_paths_in_body(&$translation) {
        $body = $translation['body'];
        $image_paths = $this->_content_get_image_paths($body);
        
        $source_path = get_permalink($translation['original_id']);
      
        foreach($image_paths as $path) {
      
            $src_path = $this->resolve_url($source_path, $path[2]);
            if ($src_path != $path[2]) {
                $search = $path[1] . $path[2] . $path[1];
                $replace = $path[1] . $src_path . $path[1];
                $new_link = str_replace($search, $replace, $path[0]);
          
                $body = str_replace($path[0], $new_link, $body);
            }
        }
        $translation['body'] = $body;
    }    
    
    /*
     Decode any html encoding in shortcodes
     http://codex.wordpress.org/Shortcode_API
    */
    function _content_decode_shortcodes(&$translation) {
        $body = $translation['body'];
        
        global $shortcode_tags;
        if (isset($shortcode_tags)) {
            $tagnames = array_keys($shortcode_tags);
        $tagregexp = join( '|', array_map('preg_quote', $tagnames) );

            $regexp = '/\[('.$tagregexp.')\b(.*?)\]/s';
            
            if (preg_match_all($regexp, $body, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $body = str_replace($match[0], '[' . $match[1] . html_entity_decode($match[2]) . ']', $body);
                }
            }
            
        }
        
        $translation['body'] = $body;
    }    
    
    /**
     * get the paths to images in the body of the content
     */

    function _content_get_image_paths($body) {

      $regexp_links = array(
                          "/<img\ssrc\s*=\s*([\"\']??)([^\"]*)\".*>/siU",
                          "/&lt;script\ssrc\s*=\s*([\"\']??)([^\"]*)\".*>/siU",
                          "/<embed\ssrc\s*=\s*([\"\']??)([^\"]*)\".*>/siU",
                          );

      $links = array();

      foreach($regexp_links as $regexp) {
        if (preg_match_all($regexp, $body, $matches, PREG_SET_ORDER)) {
          foreach ($matches as $match) {
            $links[] = $match;
          }
        }
      }

      return $links;
    }


    /**
     * Resolve a URL relative to a base path. This happens to work with POSIX
     * filenames as well. This is based on RFC 2396 section 5.2.
     */
    function resolve_url($base, $url) {
            if (!strlen($base)) return $url;
            // Step 2
            if (!strlen($url)) return $base;
            // Step 3
            if (preg_match('!^[a-z]+:!i', $url)) return $url;
            $base = parse_url($base);
            if ($url{0} == "#") {
                    // Step 2 (fragment)
                    $base['fragment'] = substr($url, 1);
                    return $this->unparse_url($base);
            }
            unset($base['fragment']);
            unset($base['query']);
            if (substr($url, 0, 2) == "//") {
                    // Step 4
                    return $this->unparse_url(array(
                            'scheme'=>$base['scheme'],
                            'path'=>$url,
                    ));
            } else if ($url{0} == "/") {
                    // Step 5
                    $base['path'] = $url;
            } else {
                    // Step 6
                    $path = explode('/', $base['path']);
                    $url_path = explode('/', $url);
                    // Step 6a: drop file from base
                    array_pop($path);
                    // Step 6b, 6c, 6e: append url while removing "." and ".." from
                    // the directory portion
                    $end = array_pop($url_path);
                    foreach ($url_path as $segment) {
                            if ($segment == '.') {
                                    // skip
                            } else if ($segment == '..' && $path && $path[sizeof($path)-1] != '..') {
                                    array_pop($path);
                            } else {
                                    $path[] = $segment;
                            }
                    }
                    // Step 6d, 6f: remove "." and ".." from file portion
                    if ($end == '.') {
                            $path[] = '';
                    } else if ($end == '..' && $path && $path[sizeof($path)-1] != '..') {
                            $path[sizeof($path)-1] = '';
                    } else {
                            $path[] = $end;
                    }
                    // Step 6h
                    $base['path'] = join('/', $path);

            }
            // Step 7
            return $this->unparse_url($base);
    }

    function unparse_url($parsed){
        if (! is_array($parsed)) return false;
        $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((mb_strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
        $uri .= isset($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
        $uri .= isset($parsed['host']) ? $parsed['host'] : '';
        $uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';
        if(isset($parsed['path']))
            {
            $uri .= (substr($parsed['path'],0,1) == '/')?$parsed['path']:'/'.$parsed['path'];
            }
        $uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
        $uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
        return $uri;
    }

    function _content_fix_relative_link_paths_in_body(&$translation) {
        $body = $translation['body'];
        $link_paths = $this->_content_get_link_paths($body);

        $source_path = get_permalink($translation['original_id']);

        foreach($link_paths as $path) {
          
            if ($path[2][0] != "#"){
                $src_path = $this->resolve_url($source_path, $path[2]);
                if ($src_path != $path[2]) {
                    $search = $path[1] . $path[2] . $path[1];
                    $replace = $path[1] . $src_path . $path[1];
                    $new_link = str_replace($search, $replace, $path[0]);
                    
                    $body = str_replace($path[0], $new_link, $body);
                }
            }      
        }
        $translation['body'] = $body;
    }

    function _content_get_link_paths($body) {
      
        $regexp_links = array(
                            /*"/<a.*?href\s*=\s*([\"\']??)([^\"]*)[\"\']>(.*?)<\/a>/i",*/
                            "/<a[^>]*href\s*=\s*([\"\']??)([^\"^>]+)[\"\']??([^>]*)>/i",
                            );
        
        $links = array();
        
        foreach($regexp_links as $regexp) {
            if (preg_match_all($regexp, $body, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                  $links[] = $match;
                }
            }
        }
        return $links;
    }    
    
    public static function _content_make_links_sticky($element_id, $element_type='post', $string_translation = true) {        
        if(strpos($element_type, 'post') === 0){
            // only need to do it if sticky links is not enabled.
            // create the object
            require_once ICL_PLUGIN_PATH . '/inc/absolute-links/absolute-links.class.php';        
            $icl_abs_links = new AbsoluteLinks;
            $icl_abs_links->process_post($element_id);
        }elseif($element_type=='string'){             
            require_once ICL_PLUGIN_PATH . '/inc/absolute-links/absolute-links.class.php';        
            $icl_abs_links = new AbsoluteLinks; // call just for strings
            $icl_abs_links->process_string($element_id, $string_translation);                                        
        }
    }

    function _content_fix_links_to_translated_content($element_id, $target_lang_code, $element_type='post'){
        global $wpdb, $sitepress, $wp_taxonomies;
        self::_content_make_links_sticky($element_id, $element_type);

	    $body = '';
	    $post = false;
        if(strpos($element_type, 'post') === 0){
	        $post_query   = "SELECT * FROM {$wpdb->posts} WHERE ID=%d";
	        $post_prepare = $wpdb->prepare($post_query, $element_id);
	        $post = $wpdb->get_row( $post_prepare );
            $body = $post->post_content;        
        }elseif($element_type=='string'){
	        $body_query   = "SELECT value FROM {$wpdb->prefix}icl_string_translations WHERE id=%d";
	        $body_prepare = $wpdb->prepare( $body_query, $element_id);
	        $body = $wpdb->get_var( $body_prepare );
        }    
        $new_body = $body;

        $base_url_parts = parse_url(get_home_url());
        
        $links = $this->_content_get_link_paths($body);
        
        $all_links_fixed = 1;

        $pass_on_qvars = array();        
        $pass_on_fragments = array();
	    $all_links_arr = array();
        foreach($links as $link_idx => $link) {
            $path = $link[2];
            $url_parts = parse_url($path);
            
            if(isset($url_parts['fragment'])){
                $pass_on_fragments[$link_idx] = $url_parts['fragment'];
            }
            
            if((!isset($url_parts['host']) or $base_url_parts['host'] == $url_parts['host']) and
                    (!isset($url_parts['scheme']) or $base_url_parts['scheme'] == $url_parts['scheme']) and
                    isset($url_parts['query'])) {
                $query_parts = explode('&', $url_parts['query']);
                
                foreach($query_parts as $query){
                    // find p=id or cat=id or tag=id queries
                    list($key, $term_taxonomy_id) = explode('=', $query);
                    $translations = NULL;
                    $is_tax = false;
	                $kind = false;
                    if($key == 'p'){
	                    $post_type_query   = "SELECT post_type FROM {$wpdb->posts} WHERE ID=%d";
	                    $post_type_prepare = $wpdb->prepare( $post_type_query, $term_taxonomy_id );
	                    $post_type         = $wpdb->get_var( $post_type_prepare );
	                    $kind              = 'post_' . $post_type;
                    } else if($key == "page_id"){
                        $kind = 'post_page';
                    } else if($key == 'cat' || $key == 'cat_ID'){
                        $kind = 'tax_category';
                        $taxonomy = 'category';
                    } else if($key == 'tag'){
	                    $is_tax                   = true;
	                    $taxonomy = 'post_tag';
	                    $kind = 'tax_' . $taxonomy;
	                    $term_taxonomy_id_prepare = "
	                    SELECT term_taxonomy_id
	                    FROM {$wpdb->terms} t
                            JOIN {$wpdb->term_taxonomy} x ON t.term_id = x.term_id
	                    WHERE x.taxonomy=%s AND t.slug=%s
	                    ";
	                    $term_taxonomy_id_args = array( $taxonomy, $term_taxonomy_id );
	                    $term_taxonomy_id_prepare = $wpdb->prepare( $term_taxonomy_id_prepare, $term_taxonomy_id_args );
	                    $term_taxonomy_id = $wpdb->get_var( $term_taxonomy_id_prepare );
                    } else {
                        $found = false;
                        foreach($wp_taxonomies as $ktax => $tax){
                            if($tax->query_var && $key == $tax->query_var){
	                            $found                    = true;
	                            $is_tax                   = true;
	                            $kind                     = 'tax_' . $ktax;
	                            $term_taxonomy_id_query   = "
                                SELECT term_taxonomy_id
                                FROM {$wpdb->terms} t
                                    JOIN {$wpdb->term_taxonomy} x ON t.term_id = x.term_id
	                            WHERE x.taxonomy=%s AND t.slug=%s";
	                            $term_taxonomy_id_args    = array( $ktax, $term_taxonomy_id );
	                            $term_taxonomy_id_prepare = $wpdb->prepare( $term_taxonomy_id_query, $term_taxonomy_id_args );
	                            $term_taxonomy_id         = $wpdb->get_var( $term_taxonomy_id_prepare );
	                            $taxonomy                 = $ktax;
                            }                        
                        }
                        if(!$found){
                            $pass_on_qvars[$link_idx][] = $query;
                            continue;
                        } 
                    }

                    $link_id = (int)$term_taxonomy_id;
                    
                    if (!$link_id || !$kind) {
                        continue;
                    }

                    $trid = $sitepress->get_element_trid($link_id, $kind);
                    if(!$trid){
                        continue;
                    }
                    if($trid !== NULL){
                        $translations = $sitepress->get_element_translations($trid, $kind);
                    }
                    if(isset($translations[$target_lang_code]) && $translations[$target_lang_code]->element_id != null){
                        
                        // use the new translated id in the link path.
                        
                        $translated_id = $translations[$target_lang_code]->element_id;
                        
                        if($is_tax){
	                        $translated_id_query    = "
	                        SELECT slug
	                        FROM {$wpdb->terms} t
                            JOIN {$wpdb->term_taxonomy} x
                              ON t.term_id = x.term_id
	                        WHERE x.term_taxonomy_id = %d
	                        LIMIT 1
	                        ";
	                        $translated_id_args = array($translated_id);
	                        $translated_id_prepared = $wpdb->prepare($translated_id_query, $translated_id_args);
	                        $translated_id = $wpdb->get_var( $translated_id_prepared );
                        }
                        
                        // if absolute links is not on turn into WP permalinks
                        if(empty($GLOBALS['WPML_Sticky_Links'])){
                            ////////
                            if(preg_match('#^post_#', $kind)){
                                $replace = get_permalink($translated_id);
                            }elseif(preg_match('#^tax_#', $kind)){
                                if(is_numeric($translated_id)) $translated_id = intval($translated_id);
                                $replace = get_term_link($translated_id, $taxonomy);                                
                            }
                            $new_link = str_replace($link[2], $replace, $link[0]);
                            
                            $replace_link_arr[$link_idx] = array('from'=> $link[2], 'to'=>$replace);
                        }else{
                            $replace = $key . '=' . $translated_id;    
                            $new_link = str_replace($query, $replace, $link[0]);                            
                            
                            $replace_link_arr[$link_idx] = array('from'=> $query, 'to'=>$replace);
                        }
                        
                        // replace the link in the body.                        
                        // $new_body = str_replace($link[0], $new_link, $new_body);
                        $all_links_arr[$link_idx] = array('from'=> $link[0], 'to'=>$new_link);
                        // done in the next loop
                        
                    } else {
                        // translation not found for this.
                        $all_links_fixed = 0;
                    }
                }
            }
                        
        }

        if ( !empty( $replace_link_arr ) && $all_links_arr ) {
            foreach ( $replace_link_arr as $link_idx => $rep ) {
			    $rep_to   = $rep[ 'to' ];
			    $fragment = '';

			    // if sticky links is not ON, fix query parameters and fragments
			    if ( empty( $GLOBALS[ 'WPML_Sticky_Links' ] ) ) {
				    if ( ! empty( $pass_on_fragments[ $link_idx ] ) ) {
					    $fragment = '#' . $pass_on_fragments[ $link_idx ];
				    }
				    if ( ! empty( $pass_on_qvars[ $link_idx ] ) ) {
					    $url_glue = ( strpos( $rep[ 'to' ], '?' ) === false ) ? '?' : '&';
					    $rep_to   = $rep[ 'to' ] . $url_glue . join( '&', $pass_on_qvars[ $link_idx ] );
				    }
			    }

			    $all_links_arr[ $link_idx ][ 'to' ] = str_replace( $rep[ 'to' ], $rep_to . $fragment, $all_links_arr[ $link_idx ][ 'to' ] );
		    }
	    }

	    if ( ! empty( $all_links_arr ) ) {
		    foreach ( $all_links_arr as $link ) {
			    $new_body = str_replace( $link[ 'from' ], $link[ 'to' ], $new_body );
		    }
	    }
        
        if ($new_body != $body){
            
            // save changes to the database.
            if($post && strpos($element_type, 'post') === 0){
                $wpdb->update($wpdb->posts, array('post_content'=>$new_body), array('ID'=>$element_id));
                
                // save the all links fixed status to the database.
                $icl_element_type = 'post_' . $post->post_type;
                $translation_id = $wpdb->get_var( $wpdb->prepare("SELECT translation_id
																  FROM {$wpdb->prefix}icl_translations
																  WHERE element_id = %d
																    AND element_type = %s",
                                                                 $element_id, $icl_element_type));
                
								$q = "UPDATE {$wpdb->prefix}icl_translation_status SET links_fixed=%s WHERE translation_id=%d";
								$q_prepared = $wpdb->prepare($q, array($all_links_fixed, $translation_id) );
                $wpdb->query($q_prepared);
                
            }elseif($element_type == 'string'){
                $wpdb->update($wpdb->prefix.'icl_string_translations', array('value'=>$new_body), array('id'=>$element_id));
            }
                    
        }
        
    }
    
    function fix_translated_children($original_id, $translated_id, $lang_code){
        global $wpdb, $sitepress;

        // get the children of of original page.
	    $original_children_query   = "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'page'";
	    $original_children_prepare = $wpdb->prepare( $original_children_query, $original_id );
	    $original_children         = $wpdb->get_col( $original_children_prepare );
        foreach($original_children as $original_child){
            // See if the child has a translation.
            $trid = $sitepress->get_element_trid($original_child, 'post_page');
            if($trid){
                $translations = $sitepress->get_element_translations($trid, 'post_page');
                if (isset($translations[$lang_code]) && isset($translations[$lang_code]->element_id)){
	                $current_parent_query   = "SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d";
	                $current_parent_prepare = $wpdb->prepare( $current_parent_query, $translations[ $lang_code ]->element_id );
	                $current_parent         = $wpdb->get_var( $current_parent_prepare );
                    if ($current_parent != $translated_id){
						$q = "UPDATE {$wpdb->posts} SET post_parent=%d WHERE ID = %d";
						$q_prepared = $wpdb->prepare($q, array($translated_id, $translations[$lang_code]->element_id) );
                        $wpdb->query($q_prepared);
                    }
                }
            }
        }
    }

    function fix_translated_parent($original_id, $translated_id, $lang_code){
        global $wpdb, $sitepress;

	    $original_parent_query   = "SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d AND post_type = 'page'";
	    $original_parent_prepare = $wpdb->prepare( $original_parent_query, $original_id );
	    $original_parent         = $wpdb->get_var( $original_parent_prepare );
        if ($original_parent){
            $trid = $sitepress->get_element_trid($original_parent, 'post_page');
            if($trid){
                $translations = $sitepress->get_element_translations($trid, 'post_page');
                if (isset($translations[$lang_code])){
	                $current_parent_query   = "SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d";
	                $current_parent_prepare = $wpdb->prepare( $current_parent_query, $translated_id );
	                $current_parent         = $wpdb->get_var( $current_parent_prepare );
                    if ($current_parent != $translations[$lang_code]->element_id){
	                    $q          = "UPDATE {$wpdb->posts} SET post_parent=%d WHERE ID = %d";
	                    $q_args     = array( $translations[ $lang_code ]->element_id, $translated_id );
	                    $q_prepared = $wpdb->prepare( $q, $q_args );
                        $wpdb->query($q_prepared);
                    }
                }
            }
        }
    }

	function _throw_exception_for_mysql_errors() {
		global $EZSQL_ERROR, $sitepress_settings;
		if ( isset( $sitepress_settings[ 'troubleshooting_options' ][ 'raise_mysql_errors' ] ) && $sitepress_settings[ 'troubleshooting_options' ][ 'raise_mysql_errors' ] ) {
			if ( ! empty( $EZSQL_ERROR ) ) {
				$mysql_errors = array();
				foreach ( $EZSQL_ERROR as $k => $v ) {
					$mysql_errors[ ] = $v[ 'error_str' ] . ' [' . $v[ 'query' ] . ']';
				}
				throw new Exception( join( "\n", $mysql_errors ) );
			}
		}
	}
    
    function _translation_error_handler($errno, $errstr, $errfile, $errline){    
        switch($errno){
            case E_ERROR:
            case E_USER_ERROR:
                throw new Exception ($errstr . ' [code:e' . $errno . '] in '. $errfile . ':' . $errline);
            case E_WARNING:
            case E_USER_WARNING:
                return true;                
            default:
                return true;
        }
        
    }    
    
    function post_submitbox_start(){
        global $post, $iclTranslationManagement;
        if(empty($post)|| !$post->ID){
            return;
        }
        
        $translations = $iclTranslationManagement->get_element_translations($post->ID, 'post_' . $post->post_type);
        $show_box = 'display:none';
        foreach($translations as $t){
            if($t->element_id == $post->ID){
                if(!empty($t->source_language_code)) return;
                else continue;
            } 
            if($t->status == ICL_TM_COMPLETE && !$t->needs_update){
                $show_box = '';
                break;
            }
        }
        
        echo '<p id="icl_minor_change_box" style="float:left;padding:0;margin:3px;'.$show_box.'">';
        echo '<label><input type="checkbox" name="icl_minor_edit" value="1" style="min-width:15px;" />&nbsp;';
        echo __('Minor edit - don\'t update translation','sitepress');        
        echo '</label>';
        echo '<br clear="all" />';
        echo '</p>';
    }   
    
    public static function estimate_word_count($data, $lang_code) {
        $words = 0;
        if(isset($data->post_title)){
            if(in_array($lang_code, self::$__asian_languages)){
                $words += strlen(strip_tags($data->post_title)) / 6;
            } else {
                $words += count(preg_split(
                    '/[\s\/]+/', $data->post_title, 0, PREG_SPLIT_NO_EMPTY));
            }
        }
        if(isset($data->post_content)){
            if(in_array($lang_code, self::$__asian_languages)){
                $words += strlen(strip_tags($data->post_content)) / 6;
            } else {
                $words += count(preg_split(
                    '/[\s\/]+/', strip_tags($data->post_content), 0, PREG_SPLIT_NO_EMPTY));
            }
		}

		return (int) $words;
	}

	public static function estimate_custom_field_word_count( $post_id, $lang_code ) {
		global $sitepress;

		$tm_settings = $sitepress->get_setting('translation-management');
		$tm_cf_settings = ($tm_settings && isset($tm_settings['custom_fields_translation']) ? $tm_settings['custom_fields_translation'] : false);
		if(!$tm_cf_settings) {
			$tm_cf_settings = array();
		}

		$words         = 0;
		$custom_fields = array();
		foreach ( $tm_cf_settings as $cf => $op ) {
			if ( $op == 2 ) {
				$custom_fields[ ] = $cf;
			}
		}
		foreach ( $custom_fields as $cf ) {
			$custom_fields_value = get_post_meta( $post_id, $cf );
			if ( $custom_fields_value && is_scalar( $custom_fields_value ) ) {
				if ( in_array( $lang_code, self::$__asian_languages ) ) {
					$words += strlen( strip_tags( $custom_fields_value ) ) / 6;
				} else {
					$words += count( preg_split( '/[\s\/]+/', strip_tags( $custom_fields_value ), 0, PREG_SPLIT_NO_EMPTY ) );
				}
			} else {
				foreach ( $custom_fields_value as $custom_fields_value_item ) {
					if ( $custom_fields_value_item && is_scalar( $custom_fields_value_item ) ) {
						if ( in_array( $lang_code, self::$__asian_languages ) ) {
							$words += strlen( strip_tags( $custom_fields_value_item ) ) / 6;
						} else {
							$words += count( preg_split( '/[\s\/]+/', strip_tags( $custom_fields_value_item ), 0, PREG_SPLIT_NO_EMPTY ) );
						}
					}
				}
			}
        }        
        return (int)$words;
    }    
    
    public static function get_translator_name($translator_id){
        global $sitepress_settings;
        static $translators;
        if(is_null($translators)){
            foreach($sitepress_settings['icl_lang_status'] as $lp){
                if(!empty($lp['translators'])){
                    foreach($lp['translators'] as $tr){
                        $translators[$tr['id']] = $tr['nickname'];                    
                    }
                }
            }
        }        
        if(isset($translators[$translator_id])){
            return $translators[$translator_id];
        }else{
            return false;
        }
    }
    
    
    function _xmlrpc_cancel_translation($args){
        global $sitepress_settings, $sitepress, $wpdb;        
        $signature = $args[0];
        $website_id = $args[1];
        $request_id = $args[2];
        $cms_id = $args[3];
        $checksum = $sitepress_settings['access_key'] . $sitepress_settings['site_id'] . $request_id . $cms_id;

        // decode cms_id
        $int = preg_match('#(.+)_([0-9]+)_([^_]+)_([^_]+)#', $cms_id, $matches);
        
        $_element_type  = $matches[1];
        $_element_id    = $matches[2];
        $_lang          = $matches[4];
        
        $trid = $sitepress->get_element_trid($_element_id, 'post_' . $_element_type);
        
        if (sha1 ( $checksum ) == $signature) {
            $wid = $sitepress_settings['site_id'];
            if ($website_id == $wid) {
	            $translation_entry_query   = "
	            SELECT *
	            FROM {$wpdb->prefix}icl_translation_status s
	                JOIN {$wpdb->prefix}icl_translations t ON t.translation_id = s.translation_id
	            WHERE t.trid=%d AND t.language_code=%s
	            ";
	            $translation_entry_args    = array( $trid, $_lang );
	            $translation_entry_prepare = $wpdb->prepare( $translation_entry_query, $translation_entry_args );
	            $translation_entry         = $wpdb->get_row( $translation_entry_prepare );
                    
                if (empty($translation_entry)){
                    return 4; // cms_request not found
                }
	            $job_id_query   = "SELECT job_id FROM {$wpdb->prefix}icl_translate_job WHERE rid=%d AND revision IS NULL";
	            $job_id_prepare = $wpdb->prepare( $job_id_query, $translation_entry->rid );
	            $job_id         = $wpdb->get_var( $job_id_prepare );
                if($job_id){
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}icl_translate_job WHERE job_id=%d", $job_id));    
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}icl_translate WHERE job_id=%d", $job_id));    
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}icl_translate_job SET revision = NULL WHERE rid=%d ORDER BY job_id DESC LIMIT 1", $translation_entry->rid));
                }
                
                if(!empty($translation_entry->_prevstate)){
                    $_prevstate = unserialize($translation_entry->_prevstate);
                    $wpdb->update($wpdb->prefix . 'icl_translation_status', 
                        array(
                            'status'                => $_prevstate['status'], 
                            'translator_id'         => $_prevstate['translator_id'], 
                            'needs_update'          => $_prevstate['needs_update'],
                            'md5'                   => $_prevstate['md5'], 
                            'translation_service'   => $_prevstate['translation_service'], 
                            'translation_package'   => $_prevstate['translation_package'], 
                            'timestamp'             => $_prevstate['timestamp'], 
                            'links_fixed'           => $_prevstate['links_fixed'] 
                        ), 
                        array('translation_id'=>$translation_entry->translation_id)
                    ); 
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}icl_translation_status SET _prevstate = NULL WHERE translation_id=%d",$translation_entry->translation_id));
                }else{
                    $wpdb->update($wpdb->prefix . 'icl_translation_status', array('status'=>ICL_TM_NOT_TRANSLATED, 'needs_update'=>0), array('translation_id'=>$translation_entry->translation_id)); 
                }
                return 1;
            } else {
                return 3; // Website id incorrect
            }
        } else {
            return 2; // Signature failed
        }
    }
    
    
    function _legacy_xmlrpc_cancel_translation($args){
        global $sitepress_settings, $wpdb;
	    $signature  = $args[ 0 ];
	    $website_id = $args[ 1 ];
	    $request_id = $args[ 2 ];

	    $accesskey = $sitepress_settings[ 'access_key' ];
	    $checksum  = $accesskey . $website_id . $request_id;
        
        $args['sid'] = sha1 ( $checksum );
        
        if (sha1 ( $checksum ) == $signature) {
            $wid = $sitepress_settings['site_id'];
            if ($website_id == $wid) {

	            $cms_request_info_prepare = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}icl_core_status WHERE rid=%d", $request_id);
	            $cms_request_info = $wpdb->get_row( $cms_request_info_prepare );
                
                if (empty($cms_request_info)){
                    return 4; // cms_request not found
                }

	            // cms_request have been found.
	            // delete it

	            $q          = "DELETE FROM {$wpdb->prefix}icl_core_status WHERE rid=%d";
	            $q_prepared = $wpdb->prepare( $q, $request_id );
	            $wpdb->query( $q_prepared );
	            $q          = "DELETE FROM {$wpdb->prefix}icl_content_status WHERE rid=%d";
	            $q_prepared = $wpdb->prepare( $q, $request_id );
	            $wpdb->query( $q_prepared );
                
                // find cms_id
	            $nid_query   = "SELECT nid FROM {$wpdb->prefix}icl_content_status WHERE rid=%d";
	            $nid_prepare = $wpdb->prepare( $nid_query, $request_id );
	            $nid         = $wpdb->get_var( $nid_prepare );

                if($nid){
	                $trid_query   = "
                        SELECT trid FROM {$wpdb->prefix}icl_translations
                        WHERE element_id=%d AND post_type LIKE 'post\_%'";
	                $trid_prepare = $wpdb->prepare( $trid_query, $nid );
	                $trid = $wpdb->get_var( $trid_prepare
                    );

	                $translation_query   = "
	                SELECT translation_id
	                FROM {$wpdb->prefix}icl_translations
	                WHERE trid=%d AND language_code=%s
	                ";
	                $translation_args = array( $trid, $cms_request_info->target );
	                $translation_prepare = $wpdb->prepare( $translation_query, $translation_args );
	                $translation = $wpdb->get_row( $translation_prepare );
	                $original_element_id_query = "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND language_code=%s";
	                $original_element_id_args = array( $translation->trid, $translation->source_language_code );
	                $original_element_id_prepare = $wpdb->prepare( $original_element_id_query, $original_element_id_args );
	                $original_element_id = $wpdb->get_var( $original_element_id_prepare );
	                $cms_id = sprintf( '%s_%d_%s_%s', preg_replace( '#^post_#', '', $translation->element_type, $original_element_id, $translation->source_language_code, $translation->language_code ) );

                    $args[3] = $cms_id;
                    return $this->_xmlrpc_cancel_translation($args);
                }

                return 1;
                
            } else {
                return 3; // Website id incorrect
            }
        } else {
            return 2; // Signature failed
        }
    }

	function _test_xmlrpc() {
		return true;
	}
    
    function _xmlrpc_add_message_translation($args){
        global $wpdb, $sitepress_settings, $wpml_add_message_translation_callbacks;
        $signature      = $args[0];
        $rid            = $args[2];
        $translation    = $args[3];
        
        $signature_check = md5($sitepress_settings['access_key'] . $sitepress_settings['site_id'] . $rid);
        if($signature != $signature_check){
            return 0; // array('err_code'=>1, 'err_str'=> __('Signature mismatch','sitepress'));
        }

	    $res_args    = array( $rid );
	    $res_query   = "SELECT to_language, object_id, object_type FROM {$wpdb->prefix}icl_message_status WHERE rid=%d";
	    $res_prepare = $wpdb->prepare( $res_query, $res_args );
	    $res         = $wpdb->get_row( $res_prepare );
        if(!$res){
            return 0;
        }

	    $to_language = $res->to_language;
	    $object_id   = $res->object_id;
	    $object_type = $res->object_type;
        
        try{
            if(is_array($wpml_add_message_translation_callbacks[$object_type])){
                foreach($wpml_add_message_translation_callbacks[$object_type] as $callback){
                    if ( !is_null($callback) ) {
                        call_user_func($callback, $object_id, $to_language, $translation);    
                    } 
                }
            }                            
            $wpdb->update($wpdb->prefix.'icl_message_status', array('status'=>MESSAGE_TRANSLATION_COMPLETE), array('rid'=>$rid));
        }catch(Exception $e){
            return $e->getMessage().'[' . $e->getFile() . ':' . $e->getLine() . ']';
        }
        return 1;
        
    }
    
    function get_jobs_in_progress(){
        global $wpdb;
	    $jip_query   = "SELECT COUNT(*) FROM {$wpdb->prefix}icl_translation_status WHERE status=%d AND translation_service='icanlocalize'";
	    $jip_prepare = $wpdb->prepare( $jip_query, ICL_TM_IN_PROGRESS );
	    $jip         = $wpdb->get_var( $jip_prepare );
        return $jip;
    }
    
    function get_strings_in_progress(){
        global $wpdb;
	    $sip_query   = "SELECT COUNT(*) FROM {$wpdb->prefix}icl_core_status WHERE status < %d";
	    $sip_prepare = $wpdb->prepare( $sip_query, 3 );
	    $sip         = $wpdb->get_var( $sip_prepare );
        return $sip;
    }    
    
    function poll_for_translations($force = false){
        global $sitepress_settings, $sitepress, $wpdb;
        
        if (!$force) {
            // Limit to once per hour
            $toffset = strtotime(current_time('mysql')) - @intval($sitepress_settings['last_picked_up']) - 3600;
            if($toffset < 0 || $force){
                return 0;
            }
        }
        
        $iclq = new ICanLocalizeQuery($sitepress_settings['site_id'], $sitepress_settings['access_key']);
        $pending = $iclq->cms_requests();
        
        $fetched = 0;
        if(!empty($pending)){
            foreach($pending as $doc){
                
                if(empty($doc['cms_id'])){ // it's a string
	                $target_query   = "SELECT target FROM {$wpdb->prefix}icl_core_status WHERE rid=%d";
	                $target_prepare = $wpdb->prepare( $target_query, $doc[ 'id' ] );
	                $target         = $wpdb->get_var( $target_prepare );
                    $__ld = $sitepress->get_language_details($target);
                    $language = $this->server_languages_map($__ld['english_name']);                    
                    $ret = $this->process_translated_string($doc['id'], $language);
                    if($ret){
                        $fetched++;
                    }
                }else{
                    
                    // decode cms_id
                    preg_match('#(.+)_([0-9]+)_([^_]+)_([^_]+)#', $doc['cms_id'], $matches);
                    
                    $_element_type  = $matches[1];
                    $_element_id    = $matches[2];
                    $_lang          = $matches[4];

	                $trid                = $sitepress->get_element_trid( $_element_id, 'post_' . $_element_type );
	                $translation_query   = "SELECT * FROM {$wpdb->prefix}icl_translations WHERE trid=%d AND language_code=%s";
	                $translation_args    = array( $trid, $_lang );
	                $translation_prepare = $wpdb->prepare( $translation_query, $translation_args );
	                $translation         = $wpdb->get_row( $translation_prepare );
                    
                    $ret = $this->add_translated_document($translation->translation_id, $doc['id']);                
                    if($ret){
                        $fetched++;
                    }
                }
            }
        }
        
        $iclsettings['last_picked_up'] = strtotime(current_time('mysql'));
        $sitepress->save_settings($iclsettings);
        
        return $fetched;
    }
    
    function get_icl_manually_tranlations_box($wrap_class=""){
        global $sitepress_settings;
        
        if(isset($_GET['icl_pick_message'])){
            ?>
                <div id="icl_tm_pickup_wrap"><p><?php echo esc_html($_GET['icl_pick_message']) ?></p></div>
            <?php
        }
        
        $job_in_progress = $this->get_jobs_in_progress() or $this->get_strings_in_progress();
        if($sitepress_settings['translation_pickup_method'] == ICL_PRO_TRANSLATION_PICKUP_POLLING)
        {
            $last_time_picked_up = !empty($sitepress_settings['last_picked_up']) ? date_i18n('Y, F jS @g:i a', $sitepress_settings['last_picked_up']) : __('never', 'sitepress'); 
            $toffset = strtotime(current_time('mysql')) - @intval($sitepress_settings['last_picked_up']) - 5 * 60;            
            if($toffset < 0){
                $gettdisabled = ' disabled="disabled" ';
                $waittext = '<p><i>' . sprintf(__('You can check again in %s minutes.', 'sitepress'), '<span id="icl_sec_tic">' . floor(abs($toffset)/60) . '</span>') . '</i></p>';
            }else{
                $waittext = '';
                $gettdisabled = '';
            }
            
            ?>
            <span id="icl_tm_pickup_wrap">
            
            <div class="<?php echo $wrap_class ?>">
            <p><?php printf(__('%d job(s) sent to ICanLocalize.', 'sitepress'), $job_in_progress); ?></p>
            <p><input type="button" class="button-secondary" value="<?php _e('Get completed translations', 'sitepress')?>" id="icl_tm_get_translations"<?php echo $gettdisabled ?>/><?php echo $waittext ?></p>
            <?php wp_nonce_field('pickup_translations_nonce', '_icl_nonce_pickt'); ?>                
            <p><?php printf(__('Last time translations were picked up: %s', 'sitepress'), $last_time_picked_up) ?></p>    
            </div></span>
            <br clear="all" />
            <?php 
        }
    }
}
