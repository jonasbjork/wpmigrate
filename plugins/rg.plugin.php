<?php

/**
 * Look for the table in the old db
 *
 * @param string $old The old database prefix.
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_rg_find( $old ) {
		global $wp;
		$sql = sprintf( "SHOW TABLES LIKE '%s_rg_form'", $old );
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
function jb_plugin_rg_create( $new ) {
	global $wpmu;
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_rg_form` (
	  `id` mediumint(8) unsigned NOT NULL auto_increment,
	  `title` varchar(150) NOT NULL,
	  `date_created` datetime NOT NULL,
	  `is_active` tinyint(1) NOT NULL default '1',
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new);
	
	printf("[SQL] %s\n", $sql);

	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_rg_form\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_rg_form_meta` (
	  `form_id` mediumint(8) unsigned NOT NULL,
	  `display_meta` longtext,
	  `entries_grid_meta` longtext,
	  KEY `form_key` (`form_id`),
	  CONSTRAINT `wp_%d_rg_form_meta_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `wp_%d_rg_form` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8", $new, $new, $new);

	printf("[SQL] %s\n", $sql);
	
	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_rg_form_meta\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_rg_form_view` (
	  `id` bigint(20) unsigned NOT NULL auto_increment,
	  `form_id` mediumint(8) unsigned NOT NULL,
	  `date_created` datetime NOT NULL,
	  `ip` char(15) default NULL,
	  `count` mediumint(8) unsigned NOT NULL default '1',
	  PRIMARY KEY  (`id`),
	  KEY `form_key` (`form_id`),
	  CONSTRAINT `wp_%d_rg_form_view_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `wp_%d_rg_form` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new, $new, $new);

printf("[SQL] %s\n", $sql);

	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_rg_form_view\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}

	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_rg_lead` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `form_id` mediumint(8) unsigned NOT NULL,
	  `post_id` bigint(20) unsigned default NULL,
	  `date_created` datetime NOT NULL,
	  `is_starred` tinyint(1) NOT NULL default '0',
	  `is_read` tinyint(1) NOT NULL default '0',
	  `ip` char(15) NOT NULL,
	  `source_url` varchar(200) NOT NULL default '',
	  `user_agent` varchar(250) NOT NULL default '',
	  PRIMARY KEY  (`id`),
	  KEY `form_key` (`form_id`),
	  CONSTRAINT `wp_%d_rg_lead_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `wp_%d_rg_form` (`id`)
	) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new, $new, $new);

printf("[SQL] %s\n", $sql);

	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_rg_lead\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}	

	$sql = sprintf("CREATE TABLE `wp_%d_rg_lead_detail` (
	  `id` bigint(20) unsigned NOT NULL auto_increment,
	  `lead_id` int(10) unsigned NOT NULL,
	  `form_id` mediumint(8) unsigned NOT NULL,
	  `field_number` float unsigned NOT NULL,
	  `value` varchar(200) default NULL,
	  PRIMARY KEY  (`id`),
	  KEY `form_key` (`form_id`),
	  KEY `lead_key` (`lead_id`),
	  CONSTRAINT `wp_%d_rg_lead_detail_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `wp_%d_rg_lead` (`id`) ON DELETE CASCADE,
	  CONSTRAINT `wp_%d_rg_lead_detail_ibfk_2` FOREIGN KEY (`form_id`) REFERENCES `wp_%d_rg_form` (`id`) ON DELETE CASCADE
	) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new, $new, $new, $new, $new);

printf("[SQL] %s\n", $sql);

	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_rg_lead_detail\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}	

	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_rg_lead_detail_long` (
	  `lead_detail_id` bigint(20) unsigned NOT NULL, `value` longtext, KEY `lead_detail_key` (`lead_detail_id`),
	  CONSTRAINT `wp_%d_rg_lead_detail_long_ibfk_1` FOREIGN KEY (`lead_detail_id`) REFERENCES `wp_%d_rg_lead_detail` (`id`) ON DELETE CASCADE ) ENGINE=InnoDB DEFAULT CHARSET=utf8", $new, $new, $new);

	printf("[SQL] %s\n", $sql);

	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_rg_lead_detail_long\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}

	$sql = sprintf("CREATE TABLE `wp_%d_rg_lead_notes` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `lead_id` int(10) unsigned NOT NULL,
	  `user_name` varchar(250) default NULL,
	  `user_id` bigint(20) default NULL,
	  `date_created` datetime NOT NULL,
	  `value` longtext,
	  PRIMARY KEY  (`id`),
	  KEY `lead_key` (`lead_id`),
	  KEY `lead_user_key` (`lead_id`,`user_id`),
	  CONSTRAINT `wp_%d_rg_lead_notes_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `wp_%d_rg_lead` (`id`) ON DELETE CASCADE
	) ENGINE=InnoDB DEFAULT CHARSET=utf8", $new, $new, $new);

printf("[SQL] %s\n", $sql);

	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_rg_lead_detail_long\n", $new );
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
function jb_plugin_rg_schema() {
	$schema = array();
	$schema['rg_form']['fields'] = array( 'id', 'title', 'date_created', 'is_active' );
	$schema['rg_form']['userid'] = array();

	$schema['rg_form_meta']['fields'] = array( 'form_id', 'display_meta', 'entries_grid_meta' );
	$schema['rg_form_meta']['userid'] = array();

	$schema['rg_form_view']['fields'] = array( 'id', 'form_id', 'date_created', 'ip', 'count' );
	$schema['rg_form_view']['userid'] = array();

	$schema['rg_lead']['fields'] = array( 'id', 'form_id', 'post_id', 'date_created', 'is_starred', 'is_read', 'ip', 'source_url', 'user_agent' );
	$schema['rg_lead']['userid'] = array();
	
	$schema['rg_lead_detail']['fields'] = array( 'id', 'lead_id', 'form_id', 'field_number', 'value' );
	$schema['rg_lead_detail']['userid'] = array();
	
	$schema['rg_lead_detail_long']['fields'] = array( 'lead_detail_id', 'value' );
 	$schema['rg_lead_detail_long']['userid'] = array();

	$schema['rg_lead_notes']['fields'] = array( 'id', 'lead_id', 'user_name', 'user_id', 'date_created', 'value' );
	$schema['rg_lead_notes']['userid'] = array( 'user_id' );
	
	return $schema;	
}

/**
 * Migrates the table.
 *
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_rg_migrate( $old, $new ) {
	$schema = jb_plugin_rg_schema();
	jb_table_migrate( $old, $new, 'rg_form', $schema['rg_form'] );
	jb_table_migrate( $old, $new, 'rg_form_meta', $schema['rg_form_meta'] );
	jb_table_migrate( $old, $new, 'rg_form_view', $schema['rg_form_view'] );
	jb_table_migrate( $old, $new, 'rg_lead', $schema['rg_lead'] );
	jb_table_migrate( $old, $new, 'rg_lead_detail', $schema['rg_lead_detail'] );
	jb_table_migrate( $old, $new, 'rg_lead_detail_long', $schema['rg_lead_detail_long'] );
	jb_table_migrate( $old, $new, 'rg_lead_notes', $schema['rg_lead_notes'] );
}