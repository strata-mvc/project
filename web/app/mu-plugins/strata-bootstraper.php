<?php
/**
 * Plugin Name: Strata Bootstraper
 * Plugin URI: https://github.com/strata-mvc/strata/
 * Description: A bootstraper that hooks Strata into the project.
 * Version: 1.0.1
 * Author: Francois Faubert
 * Author URI: http://www.strata-framework.com/
 * License: Apache 2 License
 */

if (!is_blog_installed()) { return; }

Strata\Strata::app()->run();

