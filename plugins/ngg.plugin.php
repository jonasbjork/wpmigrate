<?php

/**
 * Look for the table in the old db
 *
 * @param string $old The old database prefix.
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_ngg_find( $old ) {
	global $wp;
	$sql = sprintf( "SHOW TABLES LIKE '%s_ngg_album'", $old );
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
function jb_plugin_ngg_create( $new ) {
	global $wpmu;
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_ngg_album` (
	  `id` bigint(20) NOT NULL auto_increment,
	  `name` varchar(255) NOT NULL,
	  `previewpic` bigint(20) NOT NULL default '0',
	  `albumdesc` mediumtext,
	  `sortorder` longtext NOT NULL,
	  PRIMARY KEY  (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8", $new);
		
	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_ngg_album\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_ngg_gallery` (
	  `gid` bigint(20) NOT NULL auto_increment,
	  `name` varchar(255) NOT NULL,
	  `path` mediumtext,
	  `title` mediumtext,
	  `galdesc` mediumtext,
	  `pageid` bigint(20) default '0',
	  `previewpic` bigint(20) default '0',
	  `author` bigint(20) NOT NULL default '0',
	  PRIMARY KEY  (`gid`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new);
	
	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_ngg_gallery\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_ngg_pictures` (
	  `pid` bigint(20) NOT NULL auto_increment,
	  `post_id` bigint(20) NOT NULL default '0',
	  `galleryid` bigint(20) NOT NULL default '0',
	  `filename` varchar(255) NOT NULL,
	  `description` mediumtext,
	  `alttext` mediumtext,
	  `imagedate` datetime NOT NULL default '0000-00-00 00:00:00',
	  `exclude` tinyint(4) default '0',
	  `sortorder` bigint(20) NOT NULL default '0',
	  `meta_data` longtext,
	  PRIMARY KEY  (`pid`),
	  KEY `post_id` (`post_id`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new);
	
	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_ngg_pictures\n", $new );
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
function jb_plugin_ngg_schema() {
	$schema = array();
	$schema['ngg_album']['fields'] = array('id', 'name', 'previewpic', 'albumdesc', 'sortorder');
	$schema['ngg_album']['userid'] = array();

	$schema['ngg_gallery']['fields'] = array('gid', 'name', 'path', 'title', 'galdesc', 'pageid', 'previewpic', 'author');
	$schema['ngg_gallery']['userid'] = array('author');

	$schema['ngg_pictures']['fields'] = array('pid', 'post_id', 'galleryid', 'filename', 'description', 'alttext', 'imagedate', 'exclude', 'sortorder', 'meta_data');
	$schema['ngg_pictures']['userid'] = array();

	return $schema;
}

/**
 * Migrates the table.
 *
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_ngg_migrate( $old, $new ) {
	$schema = jb_plugin_ngg_schema();
	jb_table_migrate( $old, $new, 'ngg_album', $schema['ngg_album'] );
	jb_table_migrate( $old, $new, 'ngg_gallery', $schema['ngg_gallery'] );
	jb_table_migrate( $old, $new, 'ngg_pictures', $schema['ngg_pictures'] );
}

