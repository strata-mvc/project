<?php

ob_start();

    /**
     * Front to the WordPress application. This file doesn't do anything, but loads
     * wp-blog-header.php which does and tells WordPress to load the theme.
     *
     * @package WordPress
     */

    /**
     * Tells WordPress to load the WordPress theme and output it.
     *
     * @var bool
     */
    define('WP_USE_THEMES', false);

    /** Loads the WordPress Environment and Template */
    require( \Strata\Strata::getWordpressPath() . '/wp-blog-header.php' );

ob_end_clean();
