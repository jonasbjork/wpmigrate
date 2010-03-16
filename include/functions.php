<?php

function jb_find_wp() {
	global $wp;
	$blogs = array();
	$q = mysql_query( 'SHOW TABLES', $wp );
	if ( mysql_num_rows( $q ) > 0 ) {
		while ( $r = mysql_fetch_array( $q ) ) {
			if ( preg_match( '/^(\w+)_posts$/', $r[0] ) ) {
				preg_match('/^(\w+)\_/', $r[0], $wptable);
				$wpblog = $wptable[1];
				array_push($blogs, $wpblog);
			} 
		}
		if( count($blogs) > 0 ) {
			return $blogs;
		} else {
			return -1;
		}
	} else {
		return -1;
	}
}

function jb_create_blog($domain, $title) {
	global $wpdb;
	$wpdb->db_connect();
	$domain = $domain.".".HOST;
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

function jb_table_migrate($oldname, $newname, $table, $f) {
	global $wp, $wpmu;
	
	$fields = $f['fields'];

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

