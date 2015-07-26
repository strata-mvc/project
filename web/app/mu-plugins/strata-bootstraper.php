<?php
/**
 * Plugin Name: Strata Bootstraper
 * Plugin URI: https://github.com/francoisfaubert/strata/
 * Description: A bootstraper that hooks Strata code into Bedrock.
 * Version: 1.0.0
 * Author: Francois Faubert
 * Author URI: http://www.francoisfaubert.com/
 * License: Apache 2 License
 */

if (!is_blog_installed()) { return; }

use \Strata\Strata;
$app = Strata::bootstrap(Strata::requireVendorAutoload());
$app->run();
