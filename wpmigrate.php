<?php
define( 'JBABSPATH', dirname(__FILE__).'/');
require_once( 'include/config.php' );

if ( $argc == 1 ) {
	$wps = jb_find_wp();
	$i = 0;
	$error = "\nYou didn't select any database to migrate, choose one!\n";
	$error .= "Syntax: $argv[0] [ ";
	foreach ( $wps as $w ) {
		$error .= $w." ";
		if( !((count($wps)-1) == $i ) ) $error .= "| ";
		$i++;
	}
	$error .= "]\n";
	die( $error );
}

$blogs = array( $argv[1] );
$blog = array();

foreach( $blogs as &$b ) {
	$title = jb_get_title($b);
	$url = jb_get_url($b);
	printf("%s - %s\n", $title, $url);
	$new_id = jb_create_blog($title, $url);

	if($new_id > 0 ) {
		jb_table_migrate($b, $new_id, 'comments', $schema['comments']['fields'] );
		//jb_table_migrate($b, $new_id, 'commentmeta', $schema['commentmeta']);
		jb_table_migrate($b, $new_id, 'links', $schema['links']['fields']);
		jb_table_migrate($b, $new_id, 'options', $schema['options']['fields']);
		jb_table_migrate($b, $new_id, 'postmeta', $schema['postmeta']['fields']);
		jb_table_migrate($b, $new_id, 'posts', $schema['posts']['fields']);
		jb_table_migrate($b, $new_id, 'term_relationships', $schema['term_relationships']['fields']);
		jb_table_migrate($b, $new_id, 'term_taxonomy', $schema['term_taxonomy']['fields']);
		jb_table_migrate($b, $new_id, 'terms', $schema['terms']['fields']);

		jb_plugin_create_ngg($b, $new_id);
		jb_plugin_create_poll($b, $new_id);
		jb_fix_users($b, $new_id);
	} else {
		printf("===> [ERROR]: Blog '%s' already exists!\n", $b);
	}	
	
}


