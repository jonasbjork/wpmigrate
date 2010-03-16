<?php
define('DEBUG', true);
if($argc == 1) { die("[Error] asa, dan, jen, lot, som, vim, wp !\nSyntax: ./$argv[0] \n\n"); }
$blogs = array( $argv[1] );

$host = '172.16.33.128';
$_SERVER['HTTP_HOST'] = $host;
define( 'WPPATH', '/srv/www/' );
require_once ( WPPATH.'wp-load.php' );
require_once ('/srv/www/wp-includes/plugin.php');
require_once ('/srv/www/wp-includes/formatting.php');
require_once ('/srv/www/wp-includes/wpmu-functions.php');

$wp = mysql_connect('localhost', 'root', 'root', true);
$wpmu = mysql_connect('localhost', 'root', 'root', true);
mysql_select_db('svenskdam', $wp);
mysql_select_db('wpmu', $wpmu);


function jb_create_blog($domain, $title) {
	global $wpdb;
	$wpdb->db_connect();
	$domain = $domain.".".$host;
	$n = wpmu_create_blog( $domain, '/', $title, '1', array( 'public' => 1 ), '1' );
	if( !is_int($n) ) {
		if(DEBUG) printf("Could not create blog ".$domain."!");
		return -1;
	}
	if(DEBUG) printf("Created blog number: %d\n", $n);
	return $n;
}

function jb_get_title($table) {
	global $wp;
	$sql = sprintf("SELECT option_value FROM %s_options WHERE option_name='blogname' LIMIT 1", $table);
	$q = mysql_query($sql, $wp);
	if(mysql_num_rows($q) == 0) {
		if(DEBUG) printf("Could not get blogname for table: ".$table);
		return -1;
	} 
	$r = mysql_fetch_array($q);
	return utf8_encode($r['option_value']);
}

function jb_get_url($table) {
	global $wp;
	$sql = sprintf("SELECT option_value FROM %s_options WHERE option_name='siteurl' LIMIT 1", $table);
	$q = mysql_query($sql, $wp);
	if(mysql_num_rows($q) == 0) {
		if(DEBUG) printf("Could not get siteurl for table: ".$table);
		return -1;
	}
	$r = mysql_fetch_array($q);
	$url = $r['option_value'];
	$url = preg_match('@^(?:http://)?([^/]+)@i', $url, $matches);
	$url = preg_match('/(\w+)\./', $matches[1], $matches);
	return $matches[1];
}

function jb_table_comments($oldname, $newname) {
	global $wp, $wpmu;
	$sql = sprintf("SELECT * FROM %s_comments", $oldname);
	$q = mysql_query($sql, $wp);
	if(mysql_num_rows($q) == 0) {
		if(DEBUG) print("Darn! No _comments");
	} else {
		while($r = mysql_fetch_array($q)) {
			$sql = sprintf("INSERT INTO wp_%d_comments VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')", $newname, $r['comment_ID'], $r['comment_post_ID'], $r['comment_author'], $r['comment_author_email'], $r['comment_author_url'], $r['comment_author_IP'], $r['comment_date'], $r['comment_date_gmt'], $r['comment_content'], $r['comment_karma'], $r['comment_approved'], $r['comment_agent'], $r['comment_type'], $r['comment_parent'], $r['user_id']);
			mysql_query($sql, $wpmu);
			if(DEBUG) print '.';
		}
		print "\n";
	}
}

function jb_fields($fields) {
	$start = 0;
        foreach( $fields as &$fk) {
        	if($start > 0) { $f .= ","; }
                $f .= $fk;
                $start++;
        }
	return $f;
}

function jb_table_migrate($oldname, $newname, $table, $fields) {
	global $wp, $wpmu;

	$sql = sprintf("TRUNCATE wp_%d_%s", $newname, $table);
	mysql_query($sql);

	$f = jb_fields($fields);
	printf("===> [INFO]: f=%s\n", $f);
	$sql = sprintf("SELECT %s FROM %s_%s", $f, $oldname, $table);
	$q = mysql_query($sql, $wp);
	if(mysql_num_rows($q) == 0) {
		printf("===> [ERROR]: Darn, no content in  _%s!\n", $table);
	} else {
		while($r = mysql_fetch_array($q)) {
			if(DEBUG) print '.';
			$count = 0;
			$sql = sprintf("INSERT INTO wp_%d_%s(%s) VALUES(", $newname, $table, $f);
			foreach( $fields as &$k ) {
				$sql .= sprintf("'%s'", mysql_real_escape_string($r[$k]));
				if($count != (count($fields)-1)) { $sql .= ","; }
				$count++;
			}
			$sql .= ")";
			$sql = utf8_encode($sql);
			if(!mysql_query($sql, $wpmu)) {
				printf("===> [ERROR]: Darn, error when inserting %s\n", $sql);
			};
		}
		if(DEBUG) print "\n";
	}
}

function jb_fix_users($oldname, $newname) {
	global $wp, $wpmu;
	$sql = sprintf("SELECT DISTINCT(post_author), user_email FROM %s_posts INNER JOIN %s_users ON %s_posts.post_author=%s_users.ID", $oldname, $oldname, $oldname, $oldname);
	$q = mysql_query($sql, $wp);
	if(mysql_num_rows($q) > 0) {
		while($r = mysql_fetch_array($q)) {
			$uid = jb_create_user($oldname, $r['user_email']); // kolla om användaren redan finns
			$s2 = sprintf("UPDATE wp_%s_posts SET post_author='%d' WHERE post_author='%d'", $newname, $uid, $r['post_author']);
			mysql_query($s2, $wpmu);
			if(DEBUG) printf("%s(%s) = %s\n", $uid, $r['post_author'], $r['user_email']);	
		}
	} else {
		printf("===> [INFO]: No users found in posts.\n");
	}

	$sql = sprintf("SELECT DISTINCT(link_owner), user_email FROM %s_links INNER JOIN %s_users ON %s_links.link_owner=%s_users.ID", $oldname, $oldname, $oldname, $oldname);
	$q = mysql_query($sql, $wp);
	if(mysql_num_rows($q) > 0) {
		while($r = mysql_fetch_array($q)) {
			$uid = jb_create_user($oldname, $r['user_email']);
			$s2 = sprintf("UPDATE wp_%s_links SET link_owner='%d' WHERE link_owner='%d'", $newname, $uid, $r['link_owner']);
			mysql_query($s2, $wpmu);
			if(DEBUG) printf("%s(%s) = %s\n", $uid, $r['link_owner'], $r['user_email']);
		}
	} else {
		printf("===> [INFO]: No users found in links.\n");
	}

	$sql = sprintf("SELECT DISTINCT(user_id), user_email FROM %s_comments INNER JOIN %s_users ON %s_comments.user_id=%s_users.ID", $oldname, $oldname, $oldname, $oldname);
	$q = mysql_query($sql, $wp);
	if(mysql_num_rows($q) > 0) {
		while ($r = mysql_fetch_array($q)) {
			$uid = jb_create_user($oldname, $r['user_email']);
			$s2 = sprintf("UPDATE wp_%s_comments SET user_id='%d' WHERE user_id='%d'", $newname, $uid, $r['user_id']);
			mysql_query($s2, $wpmu);
			if(DEBUG) printf("%s(%s) = %s\n", $uid, $r['user_id'], $r['user_email']);
		}
	} else {
		printf("===> [INFO]: No users found in comments.\n");
	}	

}

function jb_create_user($oldname, $mail) {
	global $wp, $wpmu;
	$sql = sprintf("SELECT ID FROM wp_users WHERE user_email='%s' LIMIT 1", $mail);
	$q = mysql_query($sql, $wpmu);
	if(mysql_num_rows($q) == 0) {
		// hittade ingen användare
		$s1 = sprintf("SELECT * FROM %s_users WHERE user_email='%s' LIMIT 1", $oldname, $mail);
		$q1 = mysql_query($s1, $wp);
		if(mysql_num_rows($q1) == 0) {
			printf("===> [ERROR]: No such user: %s\n", $mail);
		} else {
			$ri = mysql_fetch_array($q1);
			$s2 = sprintf("INSERT INTO wp_users(`user_login`,`user_pass`,`user_nicename`,`user_email`,`user_url`,`user_registered`,`user_activation_key`,`user_status`,`display_name`) VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s')",
			 $ri['user_login'], $ri['user_pass'], $ri['user_nicename'],
			 $ri['user_email'],
			 $ri['user_url'],
			 $ri['user_registered'],
			 $ri['user_activation_key'], $ri['user_status'], $ri['display_name']
			);
			mysql_query($s2, $wpmu);
			return mysql_insert_id($wpmu);
		}
	} else {
		$r = mysql_fetch_array($q);
		return $r['ID'];
	}	
}

function jb_create_ngg($oldname, $newname) {

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

function jb_create_poll($oldname, $newname) {

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

$arr_comments = array('comment_ID', 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP', 'comment_date', 'comment_date_gmt', 'comment_content', 'comment_karma', 'comment_approved', 'comment_agent', 'comment_type', 'comment_parent', 'user_id');
$arr_commentmeta = array('meta_id', 'comment_id', 'meta_key', 'meta_value');
$arr_links = array('link_id', 'link_url', 'link_name', 'link_image', 'link_target', 'link_description', 'link_visible', 'link_owner', 'link_rating', 'link_updated', 'link_rel', 'link_notes', 'link_rss');
$arr_options = array('option_id', 'blog_id', 'option_name', 'option_value', 'autoload');
$arr_postmeta = array('meta_id', 'post_id', 'meta_key', 'meta_value');
$arr_posts = array('ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count');
$arr_term_relation = array('object_id', 'term_taxonomy_id', 'term_order');
$arr_term_tax = array('term_taxonomy_id', 'term_id', 'taxonomy', 'description', 'parent', 'count');
$arr_terms = array('term_id', 'name', 'slug', 'term_group');


$blog = array();

foreach( $blogs as &$b ) {
	$title = jb_get_title($b);
	$url = jb_get_url($b);
	printf("%s - %s\n", $title, $url);
	$new_id = jb_create_blog($title, $url);

	if($new_id > 0 ) {
		jb_table_migrate($b, $new_id, 'comments', $arr_comments);
		//jb_table_migrate($b, $new_id, 'commentmeta', $arr_commentmeta);
		jb_table_migrate($b, $new_id, 'links', $arr_links);
		jb_table_migrate($b, $new_id, 'options', $arr_options);
		jb_table_migrate($b, $new_id, 'postmeta', $arr_postmeta);
		jb_table_migrate($b, $new_id, 'posts', $arr_posts);
		jb_table_migrate($b, $new_id, 'term_relationships', $arr_term_relation);
		jb_table_migrate($b, $new_id, 'term_taxonomy', $arr_term_tax);
		jb_table_migrate($b, $new_id, 'terms', $arr_terms);

		jb_create_ngg($b, $new_id);
		jb_create_poll($b, $new_id);
		jb_fix_users($b, $new_id);
	} else {
		printf("===> [ERROR]: Blog '%s' already exists!\n", $b);
	}	
	
}


