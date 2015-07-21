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
    $app = \Strata\Strata::app();

    // Configure the custom logger
    $logger = new \Strata\Logger\Logger();
    $logger->color = "\e[0;33m";
    $app->setConfig("IPLogger", $logger);

    $loader = $app->getLoader();
    $initializedPlugins = array();
    foreach($loader->getPrefixesPsr4() as $prefix => $path) {
        if (strstr($prefix, "IP\\")) {
            $initializedPlugins[] = $prefix;
            $className = $prefix . "PluginInitializer";
            $initializer = new $className();
            $initializer::initialize();
        }
    }

    $logger->log(sprintf("Found %s custom post types : %s", count($initializedPlugins), implode(", ", $initializedPlugins)), "[IP::plugins]");
}

add_action("plugins_loaded", "auto_initialize_ip_modules");



add_filter('acf/settings/save_json', 'ip_auto_save_acf');
function ip_auto_save_acf($path)
{
    $path = \Strata\Strata::getConfigurationPath() . 'acf';
    if (!is_dir($path)) {
        mkdir($path);
    }

    return $path;
}

add_filter('acf/settings/load_json', 'ip_auto_load_acf');
function ip_auto_load_acf($paths)
{
    unset($paths[0]);
    $paths[] = \Strata\Strata::getConfigurationPath() . 'acf';
    return $paths;
}
