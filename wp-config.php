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
 	# LOCAL (Sylvie Crepin)
	define('DB_NAME','iprospect-roots');
	define('DB_USER','root');
	define('DB_PASSWORD','root');
	define('WP_LOCAL', true);
	define('WP_HOME','http://www.iprospect-roots.screpin.local');
	define('WP_SITEURL','http://www.iprospect-roots.screpin.local');
} else if( stristr( $_SERVER['SERVER_NAME'], "pbonneau" ) ) {
	# LOCAL (Pat Bonneau)
	define('DB_NAME','iprospect-roots-wordpress-template');
	define('DB_USER','root');
	define('DB_PASSWORD','');
	define('WP_LOCAL', true);
	define('WP_HOME','http://www.iprospect-roots.pbonneau.local');
	define('WP_SITEURL','http://www.iprospect-roots.pbonneau.local');
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
define('AUTH_KEY',         '>,KCuFZdCs(WOX]EdlOrJR-gGexaISkV#r+Apxs0:O=XAgPusr)9x~~T&=5UHzZQ');
define('SECURE_AUTH_KEY',  '-P|1BfH(/88=,gmD@$9eI+g#+~q>s _&jjIM YVJ#pl~RVjdQ&j=~}2|$+&c(!+|');
define('LOGGED_IN_KEY',    '-OZS,A]U}A-4Rf-R$tv4I.$~X:+2R b}|*de@dhvLux~sF|<(t:g!,6M/CvYP^|?');
define('NONCE_KEY',        ' FRtXZM 8|4ewtt9pFyjqqMe~A~EkRgR]_;Zy;m1d2DX;-Md@6BP+fn22$8{~%/f');
define('AUTH_SALT',        '6Z(bH>w>rpiMw0Cno.QTaQ`t rALg.a##f)2bwNXF5_iw)VZ*},:Bo(rt|p^Q_Lf');
define('SECURE_AUTH_SALT', 'wF-fq0M`dT+U[c$L^:y!`M7.Lgt._N)SO,-+/e}IB7;[>%R6`6)(h+C2P#f6:+bM');
define('LOGGED_IN_SALT',   'JBpeZH<D;}_xLJ l-R-0K|Z[,|=Y1A_UP-d:&-6>8XJb.Y:m7e`,_1!;A)^jF8^c');
define('NONCE_SALT',       'E{f9`##qTAOmv>,tsdb0/O,?CO/z*=N,1+|xH{*^c!N:2./^%d+28ppBZ>B5.CW6');

define('WP_AUTO_UPDATE_CORE',false);

# That's It. Pencils down
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');
