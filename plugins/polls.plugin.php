<?php

function jb_plugin_create_poll($oldname, $newname) {

	global $wp, $wpmu;

$sql = sprintf("CREATE TABLE `wp_%d_pollsa` (
  `polla_aid` int(10) NOT NULL auto_increment,
  `polla_qid` int(10) NOT NULL default '0',
  `polla_answers` varchar(200) NOT NULL default '',
  `polla_votes` int(10) NOT NULL default '0',
  PRIMARY KEY  (`polla_aid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8", $newname);

	$pollsa = array('polla_aid','polla_qid','polla_answers','polla_votes');
	mysql_query($sql, $wpmu);
	printf("===> [INFO]: Created table wp_%d_pollsa.\n", $newname);
	jb_table_migrate($oldname, $newname, 'pollsa', $pollsa);

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8", $newname);

	$pollsip = array('pollip_id', 'pollip_qid', 'pollip_aid', 'pollip_ip', 'pollip_host', 'pollip_timestamp', 'pollip_user', 'pollip_userid');
        mysql_query($sql, $wpmu);
	printf("===> [INFO]: Created table wp_%d_pollsip.\n", $newname);
        jb_table_migrate($oldname, $newname, 'pollsip', $pollsip);

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8", $newname);

	$pollsq = array('pollq_id', 'pollq_question', 'pollq_timestamp', 'pollq_totalvotes', 'pollq_active', 'pollq_expiry', 'pollq_multiple', 'pollq_totalvoters');
	mysql_query($sql, $wpmu);
	printf("===> [INFO]: Created table wp_%d_pollsq.\n", $newname);
	jb_table_migrate($oldname, $newname, 'pollsq', $pollsq);

}

