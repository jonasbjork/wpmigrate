<?php
/**
	This file holds the databaseinfo for wp tables.
*/

$schema = array();

$schema['comments'] = array('comment_ID', 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP', 'comment_date', 'comment_date_gmt', 'comment_content', 'comment_karma', 'comment_approved', 'comment_agent', 'comment_type', 'comment_parent', 'user_id');

$schema['commentmeta'] = array('meta_id', 'comment_id', 'meta_key', 'meta_value');

$schema['links'] = array('link_id', 'link_url', 'link_name', 'link_image', 'link_target', 'link_description', 'link_visible', 'link_owner', 'link_rating', 'link_updated', 'link_rel', 'link_notes', 'link_rss');

$schema['options'] = array('option_id', 'blog_id', 'option_name', 'option_value', 'autoload');

$schema['postmeta'] = array('meta_id', 'post_id', 'meta_key', 'meta_value');

$schema['posts'] = array('ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count');

$schema['term_relationships'] = array('object_id', 'term_taxonomy_id', 'term_order');

$schema['term_taxonomy'] = array('term_taxonomy_id', 'term_id', 'taxonomy', 'description', 'parent', 'count');

$schema['terms'] = array('term_id', 'name', 'slug', 'term_group');

