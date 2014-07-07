<?php 
if( !defined('WPINC') ) exit('No direct access permitted');

class NewRoyalSliderInstagramRenderer {
    
    private $options;
    private $slider_opts;
    private $slide_data;



	function __construct( $slide_data, $slider_opts, $options ) {
        $this->slide_data = $slide_data;
        $this->options = $options;
        $this->slider_opts = $slider_opts;
	}

    public function media_element() {
        return $this->slide_data['original_obj'];
    }
    public function debug_media_element() {
        return '<pre>'.htmlspecialchars(print_r($this->slide_data['original_obj'], true)).'</pre>';
    }


    public function image_url() {
        return $this->slide_data['image'];
    }
    public function title() {
        return htmlentities($this->slide_data['title']);
    }

    public function url() {
        return $this->slide_data['original_obj']['link'];
    }
    public function date() {
        return gmdate(get_option('date_format'), $this->slide_data['original_obj']['created_time']);
    }
    public function time() {
        return gmdate(get_option('time_format'), $this->slide_data['original_obj']['created_time']);
    }
    

    public function large_image_url() {
        if(isset($this->slide_data['large_image'])) {
            return $this->slide_data['large_image'];
        }
        return null;
    }
    public function thumbnail_url() {
        return $this->slide_data['thumbnail'];
    }
    
    public function thumbnail() {
        if($this->slider_opts['thumb_type'] == 'title') {
            return '<div class="rsTmb">' . $this->title() . '</div>';
        } else  if($this->slider_opts['thumb_type'] == 'image') {
            return '<div class="rsTmb"><img src="'. $this->thumbnail_url() .'"/></div>';
        } else {
            return '';
        }
    }
    public function image_tag() {
        $url = $this->image_url();
        if($url) {
            $video_attr = '';
            $thumb = '';
            $big_img = '';

            if($this->slider_opts['fs_image']) {
                $big_img = ' data-rsBigImg="'. $this->large_image_url() .'"';
            }

            $title = $this->title();

            if($this->slider_opts['lazy_loading']) {
                return sprintf( '<a class="rsImg" href="%1$s"%2$s%3$s%4$s>%5$s</a>', $url, $video_attr, $thumb ,$big_img, $title);
            } else {
                return sprintf( '<img class="rsImg" src="%1$s"%2$s%3$s%4$s alt="%5$s"/>', $url, $video_attr, $thumb ,$big_img, esc_attr($title) );
            }
        }
    }
}


