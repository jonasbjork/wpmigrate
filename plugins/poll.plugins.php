<?php



function jb_plugin_poll_create( $newname ) {
	global $wpmu;
	
	$sql = sprintf("CREATE TABLE `wp_%d_pollsa` (
	  `polla_aid` int(10) NOT NULL auto_increment,
	  `polla_qid` int(10) NOT NULL default '0',
	  `polla_answers` varchar(200) NOT NULL default '',
	  `polla_votes` int(10) NOT NULL default '0',
	  PRIMARY KEY  (`polla_aid`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $newname);
	
	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_pollsa\n", $newname );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	
	$sql = sprintf("CREATE TABLE `wp_%d_pollsip` (
	  `pollip_id` int(10) NOT NULL auto_increment,
	  `pollip_qid` varchar(10) NOT NULL default '',
	  `pollip_aid` varchar(10) NOT NULL default '',
	  `pollip_ip` varchar(100) NOT NULL default '',
	  `pollip_host` varchar(200) NOT NULL default '',
	  `pollip_timestamp` varchar(20) NOT NULL default '0000-00-00 00:00:00',
	  `pollip_user` tinytext NOT NULL,
	  `pollip_userid` int(10) NOT NULL default '0',
	  PRIMARY KEY  (`pollip_id`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $newname);
	
	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_pollip\n", $newname );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	
	$sql = sprintf("CREATE TABLE `wp_%d_pollsq` (
	  `pollq_id` int(10) NOT NULL auto_increment,
	  `pollq_question` varchar(200) NOT NULL default '',
	  `pollq_timestamp` varchar(20) NOT NULL default '',
	  `pollq_totalvotes` int(10) NOT NULL default '0',
	  `pollq_active` tinyint(1) NOT NULL default '1',
	  `pollq_expiry` varchar(20) NOT NULL default '',
	  `pollq_multiple` tinyint(3) NOT NULL default '0',
	  `pollq_totalvoters` int(10) NOT NULL default '0',
	  PRIMARY KEY  (`pollq_id`)
	) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8", $newname);

	if ( mysql_query( $sql, $wpmu ) ) {
		if ( DEBUG ) printf( "===> [INFO]: Created table wp_%d_pollsq\n", $newname );
	} else {
		printf( "===> [ERROR]: Could not create table: %s\n", mysql_error( $wpmu ) );
		return false;
	}
	
	return true;
}

function jb_plugin_poll_schema() {
	$schema = array();
	$schema['pollsa']['fields'] = array('polla_aid', 'polla_qid', 'polla_answers', 'polla_votes');
// TODO $schema['pollsa']['userid']
	
	$schema['pollsip']['fields'] = array('pollip_id', 'pollip_qid', 'pollip_aid', 'pollip_ip', 'pollip_host', 'pollip_timestamp', 'pollip_user', 'pollip_userid');
// TODO $schema['pollsip']['userid']
	
	$schema['pollsq']['fields'] = array('pollq_id', 'pollq_question', 'pollq_timestamp', 'pollq_totalvotes', 'pollq_active', 'pollq_expiry', 'pollq_multiple', 'pollq_totalvotes');
// TODO $schema['pollsq']['userid']
	
	return $schema;
}

function jb_plugin_poll_migrate() {
	$schema = jb_plugin_poll_schema();
	var_dump( $schema );
	foreach ( $schema as $s ) {
		printf( "%s , ", $s[1] );
	}
}
