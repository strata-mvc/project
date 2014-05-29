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
  wp_enqueue_style('roots_main', get_template_directory_uri() . '/assets/css/main.min.css', false, 'ac86a4cae32899b61a4af848c35e7940');

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

  wp_register_script('modernizr', get_template_directory_uri() . '/assets/js/vendor/modernizr.min.js', array(), null, false);

  // Register the modernizr load script 
  wp_register_script('yepnope', get_template_directory_uri() . '/assets/js/bower_components/yepnope/yepnope.1.5.4-min.js', array(), null, false);
  wp_register_script('script-loader', get_template_directory_uri() . '/assets/js/script-loader.js', array('yepnope'), null, false);

  // Add javascript config variables to modernizr load script
  $config = array(
    'bower' => get_template_directory_uri() . '/assets/js/bower_components/',
    'plugins' => get_template_directory_uri() . '/assets/js/plugins/',
    'js' => get_template_directory_uri() . '/assets/js/'
  );
  wp_localize_script('script-loader', 'WpConfig', $config);

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

/**
 * Adds a Royal Slider theme stylesheet.
 * Don't forget to add the proper class to the slider in your HTML :
 * 
 * default      : rsDefault
 * default-inverted : rsDefaultInv
 * Minimal white  : rsMinW
 * Universal    : rsUni
 * 
 */
function royal_slider_theme(){

  $themes = array(
    "default" => "default/rs-default.css",
    "default-inverted" => "default-inverted/rs-default-inverted.css",
    "minimal-white" => "minimal-white/rs-minimal-white.css",
    "universal" => "universal/rs-universal.css"
  );

  wp_register_style(
    "royal_slider_theme", 
    get_template_directory_uri() . '/assets/css/royal-slider/skins/' . $themes['minimal-white'], 
    "child_main"
  );
  wp_enqueue_style("royal_slider_theme");
}
add_action('wp_enqueue_scripts', 'royal_slider_theme', 102);

function roots_google_analytics() { ?>
<script>
  (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
  function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
  e=o.createElement(i);r=o.getElementsByTagName(i)[0];
  e.src='//www.google-analytics.com/analytics.js';
  r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
  ga('create','<?php echo GOOGLE_ANALYTICS_ID; ?>');ga('send','pageview');
</script>

<?php }
if (GOOGLE_ANALYTICS_ID && !current_user_can('manage_options')) {
  add_action('wp_footer', 'roots_google_analytics', 20);
}
