<?php

/**
 * Look for the table in the old db
 *
 * @param string $old The old database prefix.
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_popularpostsdata_find( $old ) {
	global $wp;
	$sql = sprintf( "SHOW TABLES LIKE '%s_popularpostsdata'", $old );
	if ( mysql_num_rows( mysql_query( $sql, $wp ) ) == 1 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * This function creates the databasetable in the new database.
 *
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_popularpostsdata_create( $new ) {
	global $wpmu;
	
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_popularpostsdata` (
	  `postid` int(10) NOT NULL,
	  `day` datetime NOT NULL default '0000-00-00 00:00:00',
	  `pageviews` int(10) default '1',
	  UNIQUE KEY `id` (`postid`,`day`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8", $new);
		
	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_popularpostsdata\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	return true;
}

/**
 * Holds the schema for the table.
 *
 * @return array
 * @author Jonas Björk
 */
function jb_plugin_popularpostsdata_schema() {
	$schema = array();
	$schema['popularpostsdata']['fields'] = array('postid', 'day', 'pageviews');
	$schema['popularpostsdata']['userid'] = array();
	
	return $schema;
}

/**
 * Migrates the table.
 *
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_popularpostsdata_migrate( $old, $new ) {
	$schema = jb_plugin_popularpostsdata_schema();
	jb_table_migrate( $old, $new, 'popularpostsdata', $schema['popularpostsdata'] );
}
