<?php
define( 'DEBUG', true );
define( 'WPMUPATH', '/srv/www/' );
define( 'PLUGINDIR', JBABSPATH.'plugins/' );
define( 'HOST', '172.16.33.128' );
define( 'DB_WP_HOST', 'localhost' );
define( 'DB_WP_USER', 'root' );
define( 'DB_WP_PASS', 'root' );
define( 'DB_WP_DB', 'svenskdam' );
define( 'DB_WPMU_HOST', 'localhost' );
define( 'DB_WPMU_USER', 'root' );
define( 'DB_WPMU_PASS', 'root' );
define( 'DB_WPMU_DB', 'wpmu' );

// Bootstrapping WPMU
$_SERVER['HTTP_HOST'] = HOST;
require_once ( WPMUPATH.'wp-load.php' );
require_once ( WPMUPATH.'wp-includes/plugin.php');
require_once ( WPMUPATH.'wp-includes/formatting.php');
require_once ( WPMUPATH.'wp-includes/wpmu-functions.php');

// Loading functions and schema
require_once ( 'functions.php' );
require_once ( 'schema.php' );

// Loading plugins
if ( is_dir( PLUGINDIR ) ) {
	if ( $d = opendir( PLUGINDIR ) ) {
		$plugins = array();
		while ( ( $p = readdir( $d ) ) !== false ) {
			if ( substr( $p, -10 ) == 'plugin.php' ) {
				$pl = PLUGINDIR.$p;
				$plugins[] = $pl;
			}
		}
		closedir( $d );
		foreach( $plugins as $plugg ) {
			include_once( $plugg );
			if ( DEBUG ) printf("===> [INFO]: Loaded plugin %s\n", $plugg);
		}

	}
}

// Connecting to the database(s)
$wp = mysql_connect( DB_WP_HOST, DB_WP_USER, DB_WP_PASS, true) or die("===> [ERROR]: Could not connect to (wp) MySQL-server: ".DB_WP_HOST."\n");
$wpmu = mysql_connect( DB_WPMU_HOST, DB_WPMU_USER, DB_WPMU_PASS, true) or die("===> [ERROR]: Could not connect to (wpmu) MySQL-server: ".DB_WPMU_HOST."\n");
mysql_select_db( DB_WP_DB, $wp) or die("===> [ERROR]: Could not use database: ".DB_WP_DB."\n");
mysql_select_db( DB_WPMU_DB, $wpmu) or die("===> [ERROR]: Could not use database: ".DB_WPMU_DB."\n");

