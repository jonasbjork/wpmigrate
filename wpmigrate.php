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

$blog	= $argv[1]; // TODO: fixa kontroll av detta!

if( DB_USE )	$url	= jb_get_url( $blog );
if( DB_USE )	$new_id	= jb_create_blog( $blog, $url );
if( !DB_USE )	$new_id = 7676;

if ( $new_id > 0 ) {
	printf( "%d) %s - %s\n", $new_id, $blog, $url );
	
	if ( $new_id > 0 ) {
		foreach ( $wpcore as $w ) {
			if( DB_USE ) jb_table_migrate( $blog, $new_id, $w, $schema[$w] );
		}
		
	// some plugin handling
	var_dump( $plugins );
	
	foreach( $plugins as $p ) {
		$f_find			= sprintf( "jb_plugin_%s_find", $p['name'] );
		$f_create		= sprintf( "jb_plugin_%s_create", $p['name'] );
		$f_migrate	= sprintf( "jb_plugin_%s_migrate", $p['name'] );

		if ( function_exists( $f_find ) ) {
			if ( $f_find( $blog ) ) {
				if ( function_exists( $f_create ) ) {
					if ( $f_create( $new_id ) ) {
						if ( function_exists( $f_migrate ) ) {
							$f_migrate( $blog, $new_id );
						} else {
							printf( "Could not find function: %s\n", $f_migrate );
						}
					} else {
						printf( "DEBUG: Couldn't create %s in %s\n", $p['name'], $new_id );
					}
				} else {
					printf( "Could not find function: %s\n", $f_create );
				}
			} else {
				printf( "DEBUG: Couldn't find %s in %s\n", $p['name'], $blog );
			}
		} else {
			printf( "Could not find function: %s\n", $f_find );
		}
	}

//jb_plugin_poll_migrate();

//		jb_plugin_create_ngg($blog, $new_id);
//		jb_plugin_create_poll($blog, $new_id);

	} else {
		printf("===> [ERROR]: Blog '%s' already exists!\n", $b);
	}	
	
}

	
