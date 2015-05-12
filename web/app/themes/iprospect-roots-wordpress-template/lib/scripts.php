<?php
/**
 * Enqueue scripts and stylesheets
 *
 * Enqueue stylesheets in the following order:
 * 1. /theme/assets/css/main.min.css
 *
 * Enqueue scripts in the following order:
 * 1. jquery-1.11.0.min.js via Google CDN
 * 2. /theme/assets/js/vendor/modernizr-2.7.0.min.js
 * 3. /theme/assets/js/main.min.js (in footer)
 */
function roots_scripts() {
  wp_enqueue_style('roots_main', get_template_directory_uri() . '/assets/css/main.min.css', false, '5356e10c30ebf428272c39af9ca280b8');

  // jQuery is loaded using the same method from HTML5 Boilerplate:
  // Grab Google CDN's latest jQuery with a protocol relative URL; fallback to local if offline
  // It's kept in the header instead of footer to avoid conflicts with plugins.
  if (!is_admin() && current_theme_supports('jquery-cdn')) {
    wp_deregister_script('jquery');
    wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js', array(), null, false);
    add_filter('script_loader_src', 'roots_jquery_local_fallback', 10, 2);
  }

  if (is_single() && comments_open() && get_option('thread_comments')) {
    wp_enqueue_script('comment-reply');
  }

  // Register the modernizr load script 
  wp_register_script('modernizr', get_template_directory_uri() . '/assets/js/vendor/modernizr.min.js', array(), null, false);
  wp_register_script('yepnope', get_template_directory_uri() . '/assets/js/bower_components/yepnope/yepnope.1.5.4-min.js', array(), null, false);
  wp_register_script('script-loader', get_template_directory_uri() . '/assets/js/script-loader.js', array('yepnope'), null, false);

  // Add javascript config variables to modernizr load script
  $config = array(
    'bower' => get_template_directory_uri() . '/assets/js/bower_components/',
    'plugins' => get_template_directory_uri() . '/assets/js/plugins/',
    'js' => get_template_directory_uri() . '/assets/js/',
    'lang' => ICL_LANGUAGE_CODE,
    'ajaxurl' => admin_url('admin-ajax.php'),
    'security' => wp_create_nonce(AJAX_NONCE_KEY)
  );
  wp_localize_script('script-loader', 'WpConfig', $config);

  // Weinre switch for mobile development
  if(defined('WP_DEV') && WP_DEV && defined('WEINRE_ADDRESS')){
    wp_register_script('weinre', 'http://' . WEINRE_ADDRESS .'/target/target-script-min.js#' . get_bloginfo('wpurl'), array(),null, false);
    wp_enqueue_script('weinre');
  } 
  
  // Enqueue all scripts
  wp_enqueue_script('modernizr');
  wp_enqueue_script('jquery');
  wp_enqueue_script('yepnope');
  wp_enqueue_script('script-loader');
}
add_action('wp_enqueue_scripts', 'roots_scripts', 100);

// http://wordpress.stackexchange.com/a/12450
function roots_jquery_local_fallback($src, $handle = null) {
  static $add_jquery_fallback = false;

  if ($add_jquery_fallback) {
    echo '<script>window.jQuery || document.write(\'<script src="' . get_template_directory_uri() . '/assets/js/bower_components/jquery/dist/jquery.min.js"><\/script>\')</script>' . "\n";
    $add_jquery_fallback = false;
  }

  if ($handle === 'jquery') {
    $add_jquery_fallback = true;
  }

  return $src;
}
add_action('wp_head', 'roots_jquery_local_fallback');
