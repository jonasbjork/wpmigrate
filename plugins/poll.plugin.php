<?php

/**
 * Look for the table in the old db
 *
 * @param string $old The old database prefix.
 * @return boolean
 * @author Jonas Björk
 */
function jb_plugin_poll_find( $old ) {
	global $wp;
	$sql = sprintf( "SHOW TABLES LIKE '%s_pollsa'", $old );
	if ( mysql_num_rows( mysql_query( $sql, $wp ) ) == 1 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * This function creates the databasetable in the new database.
 *
 * @return boolean
 * @author Jonas Björk
 */
function jb_plugin_poll_create( $new ) {
	global $wpmu;
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_pollsa` (
	  `polla_aid` int(10) NOT NULL auto_increment,
	  `polla_qid` int(10) NOT NULL default '0',
	  `polla_answers` varchar(200) NOT NULL default '',
	  `polla_votes` int(10) NOT NULL default '0',
	  PRIMARY KEY  (`polla_aid`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new);
	
	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_pollsa\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_pollsip` (
	  `pollip_id` int(10) NOT NULL auto_increment,
	  `pollip_qid` varchar(10) NOT NULL default '',
	  `pollip_aid` varchar(10) NOT NULL default '',
	  `pollip_ip` varchar(100) NOT NULL default '',
	  `pollip_host` varchar(200) NOT NULL default '',
	  `pollip_timestamp` varchar(20) NOT NULL default '0000-00-00 00:00:00',
	  `pollip_user` tinytext NOT NULL,
	  `pollip_userid` int(10) NOT NULL default '0',
	  PRIMARY KEY  (`pollip_id`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new);
	
	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_pollip\n", $new );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	
	$sql = sprintf("CREATE TABLE IF NOT EXISTS `wp_%d_pollsq` (
	  `pollq_id` int(10) NOT NULL auto_increment,
	  `pollq_question` varchar(200) NOT NULL default '',
	  `pollq_timestamp` varchar(20) NOT NULL default '',
	  `pollq_totalvotes` int(10) NOT NULL default '0',
	  `pollq_active` tinyint(1) NOT NULL default '1',
	  `pollq_expiry` varchar(20) NOT NULL default '',
	  `pollq_multiple` tinyint(3) NOT NULL default '0',
	  `pollq_totalvoters` int(10) NOT NULL default '0',
	  PRIMARY KEY  (`pollq_id`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $new);

	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_pollsq\n", $new );
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
function jb_plugin_poll_schema() {
	$schema = array();
	$schema['pollsa']['fields'] = array('polla_aid', 'polla_qid', 'polla_answers', 'polla_votes');
	$schema['pollsa']['userid'] = array();

	$schema['pollsip']['fields'] = array('pollip_id', 'pollip_qid', 'pollip_aid', 'pollip_ip', 'pollip_host', 'pollip_timestamp', 'pollip_user', 'pollip_userid');
	$schema['pollsip']['userid'] = array( 'pollip_userid' );

	$schema['pollsq']['fields'] = array('pollq_id', 'pollq_question', 'pollq_timestamp', 'pollq_totalvotes', 'pollq_active', 'pollq_expiry', 'pollq_multiple', 'pollq_totalvoters');
	$schema['pollsq']['userid'] = array();

	return $schema;
}

/**
 * Migrates the table.
 *
 * @return void
 * @author Jonas Björk
 */
function jb_plugin_poll_migrate( $old, $new ) {
	$schema = jb_plugin_poll_schema();
	jb_table_migrate( $old, $new, 'pollsa', $schema['pollsa'] );
	jb_table_migrate( $old, $new, 'pollsip', $schema['pollsip'] );
	jb_table_migrate( $old, $new, 'pollsq', $schema['pollsq'] );
}


