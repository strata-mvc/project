<?php
# Database Configuration

//define('WEINRE_ADDRESS', '184.107.217.250:8080');

if( stristr( $_SERVER['SERVER_NAME'], "dlamarre" ) ) {
 	# LOCAL (Dave Lamarre)
	define('DB_NAME','iprospect-roots');
	define('DB_USER','root');
	define('DB_PASSWORD','root');
	define('WP_HOME','http://www.roots.dlamarre.com');
	define('WP_SITEURL','http://www.roots.dlamarre.com');
} else if ( stristr( $_SERVER['SERVER_NAME'], "privard" ) ) {
 	# LOCAL (Philippe V Rivard)
	define('DB_NAME','iprospect-roots');
	define('DB_USER','root');
	define('DB_PASSWORD','root');
	define('WP_LOCAL', true);
	define('WP_HOME','http://www.iprospect-roots.privard.local');
	define('WP_SITEURL','http://www.iprospect-roots.privard.local');
}else if( stristr( $_SERVER['SERVER_NAME'], "thibault" ) ) {
 	# LOCAL (Dave Lamarre)
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
	define('WP_HOME','');
	define('WP_SITEURL','');
} else if( stristr( $_SERVER['SERVER_NAME'], "ffaubert" ) ) {
	define('DB_NAME','roots');
	define('DB_USER','root');
	define('DB_PASSWORD','nvi');
	define('WP_HOME','');
	define('WP_SITEURL','');
} else if( stristr( $_SERVER['SERVER_NAME'], "brancourt" ) ) {
 	# LOCAL (Dave Lamarre)
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
	define('WP_HOME','');
	define('WP_SITEURL','');
} else if( stristr( $_SERVER['SERVER_NAME'], "screpin" ) ) {
 	# LOCAL (Dave Lamarre)
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
	define('WP_HOME','');
	define('WP_SITEURL','');
}  elseif( stristr( $_SERVER['SERVER_NAME'], "test" ) ) {
	# TEST :
	define('DB_NAME','');
	define('DB_USER','');
	define('DB_PASSWORD','');
}elseif( stristr( $_SERVER['SERVER_NAME'], "staging" ) ) {
	# STAGING :
	define('DB_NAME','rootsnvi_bdstg');
	define('DB_USER','rootsnvi_usrstg');
	define('DB_PASSWORD','@}X{u=,tQXzi');
	define('WP_HOME','');
	define('WP_SITEURL','http://roots.nvistaging.com');
}
else {
	# PROD :
	define('WP_HOME','');
	define('WP_SITEURL','');
	define('WP_DEV', false);
}

if(!defined('WP_DEV')) define('WP_DEV', true);

define('DB_HOST','localhost');
define('DB_HOST_SLAVE','localhost');
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

define('WP_AUTO_UPDATE_CORE',false);

# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');
