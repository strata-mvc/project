<?php
/**
 * Plugin Name: Iprospect Bootstraper
 * Plugin URI: https://iprospect.com
 * Description: A bootstraper that preloads Iprospect tools
 * Version: 1.0.0
 * Author: Francois Faubert
 * Author URI: http://www.francoisfaubert.com/
 * License: Apache 2 License
 */

if (!is_blog_installed()) { return; }

function auto_initialize_ip_modules()
{
    $loader = \Strata\Strata::app()->getLoader();
    foreach($loader->getPrefixesPsr4() as $prefix => $path) {
        if (strstr($prefix, "IP\\")) {
            $className = $prefix . "PluginInitializer";
            $initializer = new $className();
            $initializer::initialize();
        }
    }
}
add_action("plugins_loaded", "auto_initialize_ip_modules");
