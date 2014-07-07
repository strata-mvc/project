<?php 
if( !defined('WPINC') ) exit('No direct access permitted');

class NewRoyalSliderGenerator {

	function __construct( ) {

	}
	static function escapeMustache($value) {
		return $value;
	}
	static function generateSlides($fetch_data, $refresh_cache, $id, $type, $markup = null, $slides = null, $options = null, $template = null, $skin = null, $disable_cache = false) {

		$arr = false;

		$refresh_hours = NewRoyalSliderMain::$refresh_hours;
		if(!$disable_cache) {
			if( $refresh_hours > 0 ) {
				$disable_cache = false;
			} else {
				$disable_cache = true;
			}
		}

		if(!$disable_cache) {
			$transient_key = NewRoyalSliderMain::get_transient_key($id, $type);
			if( !$id ) {
				$refresh_cache = true;
			}
			if($refresh_cache) {
				// delete cached version
				delete_transient($transient_key);
			} else {
				$arr = get_transient($transient_key);  
			}
		}

		if($arr === false) {
			

			if($fetch_data) {
				if($type != 'nextgen') {
					$rsdata = NewRoyalSliderMain::query_slider_data( $id );
				} else {
					$rsdata = NewRoyalSliderMain::query_nextgen_slider_config();
				}
				
				if(!$rsdata || !$rsdata[0]) {
					if($type == 'nextgen') {
						return NewRoyalSliderMain::frontend_error(__('NextGEN configuration not found. Likely you just haven\'t created it yet, please go to "RoyalSlider admin page > Create New Slider > NextGEN config" and create it.' , 'new_royalslider'));
					} else {
						return NewRoyalSliderMain::frontend_error(__('Incorrect RoyalSlider ID or problem with query.', 'new_royalslider'));
					}
		    		
		    	}
				$rsdata = $rsdata[0];
				$rsdata = apply_filters( 'new_rs_slider_data', $rsdata );

				$type = $rsdata['type'];
				$markup = $rsdata['template_html'];

				if(!$slides) {
					$slides = $rsdata['slides'];
				}
				
				$options = $rsdata['options'];
				$template = $rsdata['template'];
				$skin = $rsdata['skin'];
			}

			if(!is_array($slides))
				$slides = json_decode($slides, ARRAY_A);
			

			if ( !class_exists( 'Mustache_Autoloader' ) ) {
				require_once( NEW_ROYALSLIDER_PLUGIN_PATH . 'lib/Mustache/Autoloader.php' );
			}
						

			require_once( NEW_ROYALSLIDER_PLUGIN_PATH . 'classes/NewRoyalSliderOptions.php' );
			Mustache_Autoloader::register();
			
		   
		    $m = new Mustache_Engine(array(
		    	'escape' => array('NewRoyalSliderGenerator','escapeMustache')
		    ));

		    

		    
		    $css_id =  'new-royalslider-'.$id;
		    
		    $t = NewRoyalSliderOptions::getRsTemplates();
		    $add_js = '';
		    $curr_template;
		    $selector = '';
		    if(!isset( $t[$template] )) {
		    	$template = ' noTemplate';
		    } else {
		    	$curr_template =  $t[$template];
		    	if(isset($curr_template['add_js']) ) {
		    		$add_js = $curr_template['add_js'];
		    		$add_js = str_replace('{{selector}}', '.'.$css_id, $add_js);
		    	}
		    	$template = ' ' . $curr_template['template-css-class'];
		    }


		    if($options) {
		    	

			    if(!is_array($options)) {
			    		$options = json_decode($options, ARRAY_A);

			    } else {

			    }

			    $gen_opts = self::preParseOpts($options);

			    if(isset($options['sopts'])) {
			    	$o = array_merge($options, $options['sopts']);

			    	$to_unset = array('sopts', 'posts', 'rs_500px', 'flickr', 'rs_instagram');

			    	foreach ($to_unset as $key => $value) {
			    		if(isset($o[$value]))
			    			unset($o[$value]);
			    	}

			    	foreach ($o as $key => $option) {
						
						if(is_array($option)) {
							foreach ($option as $subkey => $suboption) {
								if(is_numeric($suboption)) {
									$o[$key][$subkey] = (float)$suboption;
								}
							}
						} else {
							if(is_numeric($option)) {
								$o[$key] = (float)$option;
							}
						}
					}
					//return;
			    	$init_opts = json_encode($o);
			    	$init_opts = str_replace(':"true"', ':!0', $init_opts);
			    	$init_opts = str_replace(':"false"', ':!1', $init_opts);
			    	$init_opts = str_replace('"', '\'', $init_opts);
			    	$init_opts = str_replace(',\'', ',', $init_opts);
			    	$init_opts = str_replace('\':', ':', $init_opts);
			    	$init_opts = str_replace('{\'', '{', $init_opts);

			    } else {
			    	$init_opts = $options;
			    }
		    } else {
		    	$options = array();
		    	 $gen_opts = self::preParseOpts(null);
		    	$init_opts = '';
		    }

		    
		    $js_init_code = "\t$('.".$css_id."').royalSlider(". $init_opts .");\n" . $add_js;
		    
		    

		    if(!isset($skin)) {
		    	$skin = 'rsDefault';
		    }
		    $skin = ' '.$skin;
		    $out = '';


		    if(NewRoyalSliderMain::$include_style_tag) {

			    if( $gen_opts['thumb_width'] != 96 || $gen_opts['thumb_height'] != 72 ) {
			    	$out .= "\n<style type=\"text/css\">\n";
			    	$out .= '.' . $css_id . ' .rsThumbsHor { height:' . $gen_opts['thumb_height'] . 'px; }' . "\n"; 

					$out .= '.' . $css_id . ' .rsThumbsVer { width:' . $gen_opts['thumb_width'] . 'px; } 
			.'. $css_id .' .rsThumb { width: ' . $gen_opts['thumb_width'] . 'px; height: ' . $gen_opts['thumb_height'] . 'px; }';
					$out .= "\n</style>\n";
			    }

		    }
			

		    if(isset($curr_template) && isset($curr_template['wrapHTML'])) {
		    	$out .= str_replace('%width%', $gen_opts['width'], $curr_template['wrapHTML']['before']);

		    }

		    $options['id'] = $id;
		    $slides = apply_filters( 'new_rs_slides_filter', $slides, $options, $type );



		    if($type === 'custom') {
	    		 require_once( 'renderers/DefaultRenderer.php' );
	    	} else if($type === 'gallery') {
	    		require_once( 'renderers/PostGalleryRenderer.php' );
	    	} else if($type === 'flickr' || $type === '500px') {
	    		require_once( 'renderers/CustomSourceRenderer.php' );
	    	} else if($type === 'posts') {
	    		require_once( 'renderers/PostsListRenderer.php' );
	    	} else if($type === 'nextgen') {
	    		require_once( 'renderers/NextGenRenderer.php' );
	    	} else if($type === 'instagram') {
	    		require_once( 'renderers/InstagramRenderer.php' );
	    	}

		   
			
			
			
			

		    
		    if(is_array($slides) && count($slides) > 0) {
		    	$width = $gen_opts['width'];
		    	if(is_numeric($width)) {
		    		$width .= 'px';
		    	}
		    	if($width) {
		    		$width = 'width:' . $width . ';';
		    	} else {
		    		$width = '';
		    	}

		    	$height = $gen_opts['height'];
		    	if(is_numeric($height)) {
		    		$height .= 'px';
		    	}
		    	if($height) {
		    		$height = 'height:' . $height . ';';
		    	} else {
		    		$height = '';
		    	}
		    	

		    	$out .= '<div id="'.$css_id.'" class="royalSlider '.$css_id.$skin.$template.'" style="'. $width . $height .';">' . "\n";  
			    foreach($slides as $key => $slide) { 

			    	if($type === 'custom') {
			    		$renderer = new NewRoyalSliderDefaultRenderer($slide, $gen_opts, $options);
			    	} else if($type === 'gallery') {
			    		$renderer = new NewRoyalSliderPostGalleryRenderer($key, $slide, $gen_opts, $options);
			    	} else if($type === 'flickr' || $type === '500px') {
			    		$renderer = new NewRoyalSliderCustomSourceRenderer($slide, $gen_opts, $options);
			    	} else if($type === 'posts') {
			    		$renderer = new NewRoyalSliderPostsRenderer($slide, $gen_opts, $options);
			    	} else if($type === 'nextgen') {
			    		$renderer = new NewRoyalSliderNextGenRenderer($slide, $gen_opts, $options);
			    	} else if($type === 'instagram') {
			    		$renderer = new NewRoyalSliderInstagramRenderer($slide, $gen_opts, $options);
			    	}
			    	
			    	$m->getHelpers()->clear();
				   	apply_filters( 'new_rs_slides_renderer_helper', $m, $slide, $options );
			    	$out .= $m->render($markup, $renderer) ."\n";
			    }
			    $out = apply_filters( 'new_rs_slides_output_before_end', $out, $id, $type);
			    $out .=   "\n" . '</div>' . "\n";
		    } else {
		    	if($type !== 'posts') {
		    		$error_message = print_r($slides, true);
		    		if( strlen($error_message) > 5 ) {
		    			$out .= NewRoyalSliderMain::frontend_error( print_r($slides, true) );
		    		} else {
		    			$out .= NewRoyalSliderMain::frontend_error( __('Slides are missing. ','new_royalslider') . print_r($slides, true) );
		    		}
		    		
		    	} else {
		    		$out .= NewRoyalSliderMain::frontend_error( __('No posts found matching your criteria.','new_royalslider') );
		    	}
		    	
		    }
		    
		   
		   	if(isset($curr_template) && isset($curr_template['wrapHTML'])) {
		    	$out .= $curr_template['wrapHTML']['after'];
		    }
		    

		    if($type != '500px' && $type != 'flickr' && $type != 'instagram' ) {
				$pattern = '\[(\[?)(gallery|new_royalslider)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
		        preg_match_all('/'.$pattern.'/s', $out, $matches);
				$out = preg_replace_callback( "/$pattern/s", array('NewRoyalSliderGenerator','strip_shortcode_tag'), $out );
				$out = do_shortcode($out);
			}
			$arr = array(
				'out' => $out,
				'js_init' => $js_init_code
			);
			if(!$disable_cache) {
				set_transient($transient_key, $arr, 60 * 60 * $refresh_hours );
			}
			
			
		}
		NewRoyalSliderMain::register_slider( $id, $arr['js_init'] );
		return $arr['out'];
		
	}
	public static function strip_shortcode_tag( $m ) {
		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}

		return $m[1] . $m[6];
	}

	static function preParseOpts($options) {
		$o = array();
		$o_default = array(
			'width' => '100%',
			'height' => '500px', 
			'thumb_type' => 'none',
			'fs_image' => false,
			'lazy_loading' => false,
			'thumb_width' => 96,
			'thumb_height' => 72,
			'globalCaption' => false
		);
		if($options) {
            if( isset($options['sopts']) ) {
                $sopts = $options['sopts'];
                $o['thumb_type'] = 'image';
                $o['width'] = isset($sopts['width']) ? $sopts['width'] : '100%';
                $o['height'] = isset($sopts['height']) ? $sopts['height'] : '500px';
                $o['globalCaption'] = isset($sopts['globalCaption']) ? $sopts['globalCaption'] : false;

                if( is_numeric($o['width']) ) {
                	$o['width'] = $o['width'] + 'px';
                }
                if( is_numeric($o['height']) ) {
                	$o['height'] = $o['height'] + 'px';
                }

                if(!isset($sopts['controlNavigation']) || $sopts['controlNavigation'] == 'bullets') {
                	$o['thumb_type'] = null;
                } else {
                    $nav = $sopts['controlNavigation'];
                   // echo 'NAV_TYPE:'.$nav;
                    if($nav == 'tabs') {
                         $o['thumb_type'] = 'title';
                    } else if($nav == 'thumbnails') {

                    	if(isset($options['thumbs']) ) {
                    		$o['thumb_width'] = (int)$options['thumbs']['thumbWidth'];
                    		$o['thumb_height'] = (int)$options['thumbs']['thumbHeight'];
                    	}
                    	

                        if(isset($options['thumbs']) && isset($options['thumbs']['thumbContent'])) {
                           $c = $options['thumbs']['thumbContent'];
                           if($c === 'title') {
                                $o['thumb_type'] = 'title';
                           } else {
                                $o['thumb_type'] = 'image';
                           }
                        } else {
                            $o['thumb_type'] = 'image';
                        }
                    } else {
                       $o['thumb_type'] = 'title';
                    }
                }
            }

            

            $o['fs_image'] = isset($options['fullscreen']) && isset($options['fullscreen']['enabled']);
            $o['lazy_loading']  = isset($options['image_generation']) && isset($options['image_generation']['lazyLoading']);

        }

        return array_merge($o_default, $o);
	}
	static function generateGallerySlides() {

	}
	static function preRenderOptions() {

	}

	static function get_image_data($self, $isThumb = false) {
     
		$sizes = NewRoyalSliderMain::$image_sizes; 
		

        $s;
        $image_data;
        if($isThumb) {
            $s = 'thumbI';
        } else {
            $s = 'i';
        }


        if( (!$isThumb && !$self->image_data) || ($isThumb && !$self->thumb_image_data) ) {
            if( isset($self->options['image_generation']) 
                && isset($self->options['image_generation'][$s.'mageWidth'])
                && isset($self->options['image_generation'][$s.'mageHeight']) ) {


            	$img_width = (int)$self->options['image_generation'][$s.'mageWidth'];
                $img_height = (int)$self->options['image_generation'][$s.'mageHeight'];

            	if($img_width == 0 || $img_height == 0) {
            		$image_data = wp_get_attachment_image_src(  $self->attachment_id, !$isThumb ? $sizes['large'] : $sizes['thumbnail'] );
            	} else {
            
	                if(!$self->full_img_url) {
	                	$self->full_img_url = wp_get_attachment_image_src($self->attachment_id, NewRoyalSliderMain::$image_sizes['full'] );
			            if( is_array($self->full_img_url) > 0 ) {
			                $self->full_img_url = $self->full_img_url[0];
			            }
	                }
	                
	                $image_data = NewRoyalSliderMain::aq_resize(  wp_get_attachment_url($self->attachment_id), $img_width, $img_height, true, false );
            	}
            } else {
                $image_data = wp_get_attachment_image_src(  $self->attachment_id, !$isThumb ? $sizes['large'] : $sizes['thumbnail'] );
            }
            if($isThumb) {
                $self->thumb_image_data = $image_data;
            } else {
                $self->image_data = $image_data;
            }
        }
      
        if(!$isThumb && $self->image_data) {

        	

            return $self->image_data;
        } if($isThumb && $self->thumb_image_data) {
            return $self->thumb_image_data;
        } else {
            return array(
                0 => '',
                1 => '',
                2 => ''
            );
        }
    }

	static function get_thumbnail($self) {

        if($self->slider_opts['thumb_type'] == 'title') {
            return '<div class="rsTmb">' . $self->title() . '</div>';
        } else  if($self->slider_opts['thumb_type'] == 'image') {
        	$t_url = $self->thumbnail_url() ? '<img src="'. $self->thumbnail_url() .'" alt="" />' : '';
            return '<div class="rsTmb">' . $t_url . '</div>';
        } else {
            return '';
        }
	}
	static function get_image_tag($self) {
		$url = $self->image_url();
        if($url) {
            $video_attr = '';
            $thumb = '';
            $big_img = '';

            if($self->slider_opts['fs_image'] && $self->large_image_url() ) {
                $big_img = ' data-rsBigImg="'. $self->large_image_url() .'"';
            }
            if( method_exists($self,'video_url') && $self->video_url() ) {
            	$big_img .= ' data-rsVideo="' . $self->video_url() . '"';
            }

            $title = $self->title();
            $alt = strip_tags( $title );

            if($self->slider_opts['lazy_loading']) {
                $out = sprintf( '<a class="rsImg" href="%1$s"%2$s%3$s%4$s>%5$s</a>', $url, $video_attr, $thumb ,$big_img, $alt);
            } else {
                $out = sprintf( '<img class="rsImg" src="%1$s"%2$s%3$s%4$s alt="%5$s"/>', $url, $video_attr, $thumb ,$big_img, esc_attr($alt) );
            }
            return $out;
        }
	}



	

}

