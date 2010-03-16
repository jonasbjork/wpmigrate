<?php

function jb_plugin_create_ngg($oldname, $newname) {

	global $wp, $wpmu;

$sql = sprintf("CREATE TABLE `wp_%d_ngg_album` (
  `id` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `previewpic` bigint(20) NOT NULL default '0',
  `albumdesc` mediumtext,
  `sortorder` longtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8", $newname);

	$ngg_album = array('id', 'name', 'previewpic', 'albumdesc', 'sortorder');

	mysql_query($sql, $wpmu);
	printf("===> [INFO]: Created table wp_%d_ngg_album.\n", $newname);

$sql = sprintf("CREATE TABLE `wp_%d_ngg_gallery` (
  `gid` bigint(20) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `path` mediumtext,
  `title` mediumtext,
  `galdesc` mediumtext,
  `pageid` bigint(20) default '0',
  `previewpic` bigint(20) default '0',
  `author` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8", $newname);

$ngg_gallery = array('gid', 'name', 'path', 'title', 'galdesc', 'pageid', 'previewpic', 'author');

	mysql_query($sql, $wpmu);
	printf("===> [INFO]: Created table wp_%d_ngg_gallery.\n", $newname);

$sql = sprintf("CREATE TABLE `wp_%d_ngg_pictures` (
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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8", $newname);

$ngg_pictures = array('pid', 'post_id', 'galleryid', 'filename', 'description', 'alttext', 'imagedate', 'exclude', 'sortorder', 'meta_data');

	mysql_query($sql, $wpmu);
	printf("===> [INFO]: Created table wp_%d_ngg_pictures.\n", $newname);

	jb_table_migrate($oldname, $newname, 'ngg_album', $ngg_album);
	jb_table_migrate($oldname, $newname, 'ngg_gallery', $ngg_gallery);
	jb_table_migrate($oldname, $newname, 'ngg_pictures', $ngg_pictures);

}

