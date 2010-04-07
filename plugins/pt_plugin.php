<?php

/**
 * Look for the table in the old db
 *
 * @param string $old The old database prefix.
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_pt_find( $old ) {
		global $wp;
		$sql = sprintf( "SHOW TABLES LIKE '%s_pt_templates'", $old );
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
function jb_plugin_pt_create( $new ) {
	global $wpmu;
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_pt_templates` (
	  `template_id` bigint(20) NOT NULL auto_increment,
	  `type` enum('page','post') NOT NULL,
	  `title` text NOT NULL,
	  `name` varchar(200) NOT NULL,
	  `content` longtext NOT NULL,
	  `excerpt` text NOT NULL,
	  `categories` text NOT NULL,
	  `tags` text NOT NULL,
	  `password` varchar(20) NOT NULL,
	  `comment_status` enum('open','closed','registered_only') NOT NULL,
	  `ping_status` enum('open','closed') NOT NULL,
	  `to_ping` text NOT NULL,
	  `parent` bigint(20) NOT NULL,
	  PRIMARY KEY  (`template_id`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new);

	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_pt_templates\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}

}	
/**
 * Holds the schema for the table.
 *
 * @return array
 * @author Jonas Björk
 */
function jb_plugin_pt_schema() {
	$schema = array();
	$schema['pt_templates']['fields'] = array( 'template_id', 'type', 'title', 'name', 'content', 'excerpt', 'categories', 'tags', 'password', 'comment_status', 'ping_status', 'to_ping', 'parent' );
	$schema['pt_templates']['userid'] = array();
	
	return $schema;	
}

/**
 * Migrates the table.
 *
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_pt_migrate( $old, $new ) {
	$schema = jb_plugin_pt_schema();
	jb_table_migrate( $old, $new, 'pt_template', $schema['pt_template'] );
}