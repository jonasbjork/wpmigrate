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

$blog	= $argv[1];

// TODO: Hur hanterar jag enklast plugins:en?

$title	= jb_get_title( $blog );
$url	= jb_get_url( $blog );
$new_id	= jb_create_blog( $title, $url );

if ( $new_id > 0 ) {
	printf( "%d) %s - %s\n", $new_id, $title, $url );
	

	if ( $new_id > 0 ) {
		foreach ( $wpcore as $w ) {
			jb_table_migrate( $blog, $new_id, $w, $schema[$w] );
		}

//		jb_plugin_create_ngg($blog, $new_id);
//		jb_plugin_create_poll($blog, $new_id);
		// fix_users skall köras i varje migrate!
//		jb_fix_users($blog, $new_id);
	//	_jb_fix_users($blog, $new_id, 'posts', $schema);

	} else {
		printf("===> [ERROR]: Blog '%s' already exists!\n", $b);
	}	
	
}


