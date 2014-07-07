<?php 
if( !defined('WPINC') ) exit('No direct access permitted');

class NewRoyalSliderNextGenSource {

	

	function __construct( $curr_options = null ) {
        add_filter('ngg_render_template', array(&$this, 'add_ng_template'), 10, 2); // add template
        add_action('load_nextgen_gallery_modules', array(&$this, 'load_product')); // add display_type
        register_deactivation_hook( NEW_ROYALSLIDER_FILE, array(&$this, 'deactivate') );
    }

	public function add_ng_template($path, $template_name = false) {
		if (strpos($template_name, 'gallery-royalslider') !== false) {
            $path = NEW_ROYALSLIDER_PLUGIN_PATH . 'classes/sources/nextgen/modules/nextgen_royalslider/templates/NextGen1Template.php';
        }
        return $path;
	}
    public function load_product(C_Component_Registry $registry)  {
       $registry->add_module_path(NEW_ROYALSLIDER_PLUGIN_PATH . 'classes/sources/nextgen', TRUE, TRUE);
    }
    public function deactivate() {
        delete_option('new_royalslider_ng_slider_id');
        if (class_exists('C_Photocrati_Installer')) {
            C_Photocrati_Installer::uninstall('ds-nextgen_royalslider');
            C_Photocrati_Installer::uninstall('ds-royalslider');
        }
    }

	public static function show_admin_options( $curr_options = null ) {
		?>
		<div class="rs-info">
			<p class="rs-awesome-paragraph"><?php _e('Here you may create a configuration of RoyalSlider that will be used for <a href="http://wordpress.org/plugins/nextgen-gallery/">NextGEN gallery</a>', 'new_royalslider'); ?></p>
			
            <div class="help-video"><a class="in-page-action" href="http://help.dimsemenov.com/kb/wordpress-royalslider-tutorials/wp-creating-royalslider-for-nextgen-gallery" target="_blank"><?php _e('View help video about how to create NextGen gallery with RoyalSlider', 'new_royalslider'); ?></a></div><br/>
            <style type="text/css">
            .rs-embed-to-site { display: none; }
            </style>
            <p><?php _e('Step-by-step instructions:', 'new_royalslider'); ?></p>
                        <ol>
                            <li><?php _e('Configure slider options at right side.', 'new_royalslider'); ?></li>
                            <li><?php _e('Click "Create(save) slider" button.', 'new_royalslider'); ?></li>
                            <li><?php _e('Insert NextGEN gallery to post or page as usually.', 'new_royalslider'); ?>
                                <ul>
                                    <li><strong><?php _e('NextGEN v2.0+ users:', 'new_royalslider'); ?></strong><br/><?php _e('RoyalSlider adds new NextGEN "display type", you can select it when you include NextGEN gallery to post, or define via shortcode: <code>[ngg_images gallery_ids="1" display_type="ds-nextgen_royalslider"]</code> (instead of "1", put the ID of NextGEN gallery that you wish to display).', 'new_royalslider'); 
                                    ?></li>
                                    <li><strong><?php _e('For NextGEN v1.x users:', 'new_royalslider'); ?></strong><br/><?php _e('RoyalSlider adds new NextGEN "template", to use it, simply add <code>template="royalslider"</code> attribute to shortcode, for example: <code>[nggallery id="2" template="royalslider"]</code>.', 'new_royalslider'); ?>
                                    </li>
                                </ul>
                            </li>
                            <li><?php 


                            $cid = get_option('new_royalslider_ng_slider_id');
                            if($cid) {
                                $cit = 'Insert_ID_of_RoyalSlider';
                            }
                            
                            printf( __('Include slider files to page, you can configure this on <a href="%s" target="_blank">RoyalSlider Global Settings page</a>. "NextGEN" type doesn\'t support auto-detection, so you need to check an option to preload files on all pages, or include files via PHP call only on pages that you need - <code>%s</code>  (in your theme functions.php).', 'new_royalslider'), get_admin_url() . "admin.php?page=new_royalslider_settings", 'register_new_royalslider_files(' . $cid . ')'); ?></li>
                        </ol>
                        <p><?php _e('Size of main images and thumbnails is controlled from NextGEN gallery settings.', 'new_royalslider'); ?></p>
        </div>
        <?php
	}
	public static function get_data($slides, $options, $type) {
		
	}
}


