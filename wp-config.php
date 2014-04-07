<?php
# Database Configuration

if( stristr( $_SERVER['SERVER_NAME'], "dlamarre" ) ) {
 	# LOCAL (Dave Lamarre) 
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
} else if ( stristr( $_SERVER['SERVER_NAME'], "privard" ) ) {
 	# LOCAL (Philippe V Rivard) 
	define('DB_NAME','iprospect-roots');
	define('DB_USER','root');
	define('DB_PASSWORD','root');
	define('WP_LOCAL', true);
} else if( stristr( $_SERVER['SERVER_NAME'], "ajourquin" ) ) {
 	# LOCAL (Dave Lamarre) 
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
}else if( stristr( $_SERVER['SERVER_NAME'], "thibault" ) ) {
 	# LOCAL (Dave Lamarre) 
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
} else if( stristr( $_SERVER['SERVER_NAME'], "ffaubert" ) ) {
 	# LOCAL (Dave Lamarre) 
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
} else if( stristr( $_SERVER['SERVER_NAME'], "brancourt" ) ) {
 	# LOCAL (Dave Lamarre) 
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
} else if( stristr( $_SERVER['SERVER_NAME'], "ssamson" ) ) {
 	# LOCAL (Dave Lamarre) 
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
}  elseif( stristr( $_SERVER['SERVER_NAME'], "test" ) ) {
	# TEST :
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
}elseif( stristr( $_SERVER['SERVER_NAME'], "staging" ) ) {
	# STAGING :
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
}
else {
	# PROD :
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
}
define('DB_HOST','localhost');
define('DB_HOST_SLAVE','localhost');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_unicode_ci');

define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

// DEBUG
define('WP_DEBUG', false);

$table_prefix = 'wp_';

# Security Salts, Keys, Etc
define('AUTH_KEY',         '');
define('SECURE_AUTH_KEY',  '');
define('LOGGED_IN_KEY',    '');
define('NONCE_KEY',        '');
define('AUTH_SALT',        '');
define('SECURE_AUTH_SALT', '');
define('LOGGED_IN_SALT',   '');
define('NONCE_SALT',       '');


# Localized Language Stuff

#define('WP_CACHE',TRUE);

define('PWP_NAME','mdacc');

define('FS_METHOD','direct');

define('FS_CHMOD_DIR',0775);

define('FS_CHMOD_FILE',0664);

define('PWP_ROOT_DIR','/nas/wp');

define('WPE_APIKEY','af1e7188444441666aa13ef2232df8b3061aae9c');

define('WPE_FOOTER_HTML',"");

define('WPE_CLUSTER_ID','1639');

define('WPE_CLUSTER_TYPE','pod');

define('WPE_ISP',true);

define('WPE_BPOD',false);

define('WPE_RO_FILESYSTEM',false);

define('WPE_LARGEFS_BUCKET','largefs.wpengine');

define('WPE_CDN_DISABLE_ALLOWED',false);

define('DISALLOW_FILE_EDIT',FALSE);

define('DISALLOW_FILE_MODS',FALSE);

define('DISABLE_WP_CRON',false);

define('WPE_FORCE_SSL_LOGIN',false);

define('FORCE_SSL_LOGIN',false);

/*SSLSTART*/ if ( isset($_SERVER['HTTP_X_WPE_SSL']) && $_SERVER['HTTP_X_WPE_SSL'] ) $_SERVER['HTTPS'] = 'on'; /*SSLEND*/

define('WPE_EXTERNAL_URL',false);

define('WP_POST_REVISIONS',FALSE);

define('WPE_WHITELABEL','wpengine');

define('WP_TURN_OFF_ADMIN_BAR',false);

define('WPE_BETA_TESTER',false);

umask(0002);

$wpe_cdn_uris=array ();

$wpe_no_cdn_uris=array ();

$wpe_content_regexs=array ();

$wpe_all_domains=array (  0 => '',);

$wpe_varnish_servers=array (  0 => 'pod-1639',);

$wpe_ec_servers=array ();

$wpe_largefs=array ();

$wpe_netdna_domains=array ();

$wpe_netdna_push_domains=array ();

$wpe_domain_mappings=array ();

$memcached_servers=array (  'default' =>   array (    0 => 'unix:///tmp/memcached.sock',  ),);

define('WP_AUTO_UPDATE_CORE',false);
define('WPLANG','');

# WP Engine ID

# WP Engine Settings

define( 'WP_ALLOW_MULTISITE', false );
define( 'MULTISITE', false );
define( 'SUBDOMAIN_INSTALL', false );
$base = '/';
define( 'DOMAIN_CURRENT_SITE', '' );
define( 'PATH_CURRENT_SITE','/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );


# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');

$_wpe_preamble_path = null; if(false){}