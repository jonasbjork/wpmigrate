<?php
define( 'DEBUG', true );
define( 'WPMUPATH', '/srv/www/' );
define( 'PLUGINDIR', dirname(__FILE__).'/plugins/' );
define( 'HOST', '172.16.33.128' );
define( 'DB_WP_HOST', 'localhost' );
define( 'DB_WP_USER', 'root' );
define( 'DB_WP_PASS', 'root' );
define( 'DB_WP_DB', 'svenskdam' );
define( 'DB_WPMU_HOST', 'localhost' );
define( 'DB_WPMU_USER', 'root' );
define( 'DB_WPMU_PASS', 'root' );
define( 'DB_WPMU_DB', 'wpmu' );

if($argc == 1) { die("[Error] asa, dan, jen, lot, som, vim, wp !\nSyntax: ./$argv[0] \n\n"); }
$blogs = array( $argv[1] );

// Bootstrapping WPMU
$_SERVER['HTTP_HOST'] = HOST;
require_once ( WPMUPATH.'wp-load.php' );
require_once ( WPMUPATH.'wp-includes/plugin.php');
require_once ( WPMUPATH.'wp-includes/formatting.php');
require_once ( WPMUPATH.'wp-includes/wpmu-functions.php');

// Loading functions and schema
require_once ( 'include/functions.php' );
require_once ( 'include/schema.php' );

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

$blog = array();

foreach( $blogs as &$b ) {
	$title = jb_get_title($b);
	$url = jb_get_url($b);
	printf("%s - %s\n", $title, $url);
	$new_id = jb_create_blog($title, $url);

	if($new_id > 0 ) {
		jb_table_migrate($b, $new_id, 'comments', $schema['comments'] );
		//jb_table_migrate($b, $new_id, 'commentmeta', $schema['commentmeta']);
		jb_table_migrate($b, $new_id, 'links', $schema['links']);
		jb_table_migrate($b, $new_id, 'options', $schema['options']);
		jb_table_migrate($b, $new_id, 'postmeta', $schema['postmeta']);
		jb_table_migrate($b, $new_id, 'posts', $schema['posts']);
		jb_table_migrate($b, $new_id, 'term_relationships', $schema['term_relationships']);
		jb_table_migrate($b, $new_id, 'term_taxonomy', $schema['term_taxonomy']);
		jb_table_migrate($b, $new_id, 'terms', $schema['terms']);

		jb_plugin_create_ngg($b, $new_id);
		jb_plugin_create_poll($b, $new_id);
		jb_fix_users($b, $new_id);
	} else {
		printf("===> [ERROR]: Blog '%s' already exists!\n", $b);
	}	
	
}


