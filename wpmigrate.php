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

// Loading functions
require_once ( 'include/functions.php' );
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

$wp = mysql_connect( DB_WP_HOST, DB_WP_USER, DB_WP_PASS, true) or die("===> [ERROR]: Could not connect to (wp) MySQL-server: ".DB_WP_HOST."\n");
$wpmu = mysql_connect( DB_WPMU_HOST, DB_WPMU_USER, DB_WPMU_PASS, true) or die("===> [ERROR]: Could not connect to (wpmu) MySQL-server: ".DB_WPMU_HOST."\n");
mysql_select_db( DB_WP_DB, $wp) or die("===> [ERROR]: Could not use database: ".DB_WP_DB."\n");
mysql_select_db( DB_WPMU_DB, $wpmu) or die("===> [ERROR]: Could not use database: ".DB_WPMU_DB."\n");

$arr_comments = array('comment_ID', 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP', 'comment_date', 'comment_date_gmt', 'comment_content', 'comment_karma', 'comment_approved', 'comment_agent', 'comment_type', 'comment_parent', 'user_id');
$arr_commentmeta = array('meta_id', 'comment_id', 'meta_key', 'meta_value');
$arr_links = array('link_id', 'link_url', 'link_name', 'link_image', 'link_target', 'link_description', 'link_visible', 'link_owner', 'link_rating', 'link_updated', 'link_rel', 'link_notes', 'link_rss');
$arr_options = array('option_id', 'blog_id', 'option_name', 'option_value', 'autoload');
$arr_postmeta = array('meta_id', 'post_id', 'meta_key', 'meta_value');
$arr_posts = array('ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count');
$arr_term_relation = array('object_id', 'term_taxonomy_id', 'term_order');
$arr_term_tax = array('term_taxonomy_id', 'term_id', 'taxonomy', 'description', 'parent', 'count');
$arr_terms = array('term_id', 'name', 'slug', 'term_group');


$blog = array();

foreach( $blogs as &$b ) {
	$title = jb_get_title($b);
	$url = jb_get_url($b);
	printf("%s - %s\n", $title, $url);
	$new_id = jb_create_blog($title, $url);

	if($new_id > 0 ) {
		jb_table_migrate($b, $new_id, 'comments', $arr_comments);
		//jb_table_migrate($b, $new_id, 'commentmeta', $arr_commentmeta);
		jb_table_migrate($b, $new_id, 'links', $arr_links);
		jb_table_migrate($b, $new_id, 'options', $arr_options);
		jb_table_migrate($b, $new_id, 'postmeta', $arr_postmeta);
		jb_table_migrate($b, $new_id, 'posts', $arr_posts);
		jb_table_migrate($b, $new_id, 'term_relationships', $arr_term_relation);
		jb_table_migrate($b, $new_id, 'term_taxonomy', $arr_term_tax);
		jb_table_migrate($b, $new_id, 'terms', $arr_terms);

		jb_plugin_create_ngg($b, $new_id);
		jb_plugin_create_poll($b, $new_id);
		jb_fix_users($b, $new_id);
	} else {
		printf("===> [ERROR]: Blog '%s' already exists!\n", $b);
	}	
	
}


