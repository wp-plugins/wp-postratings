<?php
/*
Plugin Name: WP-PostRatings
Plugin URI: http://www.lesterchan.net/portfolio/programming.php
Description: Enables You To Have A Rating System For Your Post
Version: 1.00
Author: GaMerZ
Author URI: http://www.lesterchan.net
*/


/*  Copyright 2005  Lester Chan  (email : gamerz84@hotmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


### Rating Logs Table Name
$wpdb->ratings = $table_prefix . 'ratings';


### Function: Ratings Administration Menu
add_action('admin_menu', 'ratings_menu');
function ratings_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('Ratings'), __('Ratings'), 'manage_ratings', 'postratings/postratings-manager.php');
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('postratings/postratings-manager.php', __('Manage Ratings'), __('Manage Ratings'), 'manage_ratings', 'postratings/postratings-manager.php');
		add_submenu_page('postratings/postratings-manager.php', __('Ratings Options'), __('Ratings Options'),  'manage_ratings', 'postratings/postratings-options.php');
	}
}


### Function: Display The Rating For The Post
function the_ratings($display = true) {
	global $id;
	// Check To See Whether User Has Voted
	$user_voted = check_rated($id);
	// If User Voted
	if($user_voted) {
		if(!$display) {
			return the_ratings_results(false);
		} else {
			the_ratings_results();
		}
	// If User Has Not Voted
	} else {
		if(!$display) {
			return the_ratings_vote(false)."\n".the_ratings_results(false);
		} else {
			the_ratings_vote();
			the_ratings_results();
		}
	}
}


### Function: Displays Rating Vote Javascript
add_action('wp_head', 'the_ratings_vote_js');
function the_ratings_vote_js() {
	$ratings_image = get_settings('postratings_image');
	$ratings_max = intval(get_settings('postratings_max'));
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo 'function current_rating(id, rating) {'."\n";
	echo 'for(i = 1; i <= rating; i++) {'."\n";
	echo 'document.images[\'rating_\' + id + \'_\' + i].src = \''.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_on.gif\';'."\n";	
	echo '}}'."\n";
	echo 'function ratings_off(id) {'."\n";
	for($i=1; $i <= $ratings_max; $i++) {
		echo 'document.images[\'rating_\' + id + \'_'.$i.'\'].src = \''.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif\';'."\n";
	}
	echo '}'."\n";
	echo "</script>";
}


### Function: Display Ratings Results 
function the_ratings_results($display = true) {
	global $id;
	$ratings_image = get_settings('postratings_image');
	$ratings_max = intval(get_settings('postratings_max'));
	$post_ratings = get_post_custom();
	$post_ratings_users = $post_ratings['ratings_users'][0];
	$post_ratings_score = $post_ratings['ratings_score'][0];
	$post_ratings_average = $post_ratings['ratings_average'][0];
	$post_ratings_images = '';
	if($post_ratings_score == 0 || $post_ratings_users == 0) {
		$post_ratings = 0;
		$post_ratings_average = 0;
		$post_ratings_percentage = 0;
	} else {
		$post_ratings = round($post_ratings_average, 1);
		$post_ratings_percentage = round((($post_ratings_score/$post_ratings_users)/$ratings_max) * 100, 2);		
	}
	// Check For Half Star
	$insert_half = 0;
	$average_diff = abs(floor($post_ratings_average)-$post_ratings);
	if($average_diff >= 0.25 && $average_diff <= 0.75) {
		$insert_half = ceil($post_ratings_average);
	} elseif($average_diff > 0.75) {
		$insert_half = ceil($post_ratings);
	}	
	$post_ratings = intval($post_ratings);
	// No Ratings
	if($post_ratings == 0) {
		$template_postratings_none = stripslashes(get_settings('postratings_template_none'));
		$template_postratings_none = str_replace("%RATINGS_MAX%", $ratings_max, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_PERCENTAGE%", $post_ratings_percentage, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_USERS%", $post_ratings_users, $template_postratings_none);
		if($display) {
			echo $template_postratings_none;
		} else {
			return $template_postratings_none;
		}
	} else {
		// Display Start Of Rating Image
		if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif')) {
			$post_ratings_images .= '<img src="'.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif" alt="" />';
		}
		// Display Rated Images
		for($i=1; $i <= $ratings_max; $i++) {
			if($i <= $post_ratings) {
				$post_ratings_images .= '<img src="'.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$post_ratings_users.__(' Votes | Average: ').$post_ratings_average.__(' out of ').$ratings_max.'" />';		
			} elseif($i == $insert_half) {
				$post_ratings_images .= '<img src="'.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$post_ratings_users.__(' Votes | Average: ').$post_ratings_average.__(' out of ').$ratings_max.'" />';
			} else {
				$post_ratings_images .= '<img src="'.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$post_ratings_users.__(' Votes | Average: ').$post_ratings_average.__(' out of ').$ratings_max.'" />';
			}
		}
		// Display End Of Rating Image
		if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif')) {
			$post_ratings_images .= '<img src="'.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif" alt="" />';
		}
		// Display The Contents
		$template_postratings_text = stripslashes(get_settings('postratings_template_text'));
		$template_postratings_text = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $template_postratings_text);
		$template_postratings_text = str_replace("%RATINGS_MAX%", $ratings_max, $template_postratings_text);
		$template_postratings_text = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $template_postratings_text);
		$template_postratings_text = str_replace("%RATINGS_PERCENTAGE%", $post_ratings_percentage, $template_postratings_text);
		$template_postratings_text = str_replace("%RATINGS_USERS%", number_format($post_ratings_users), $template_postratings_text);		
		if($display) {
			echo $template_postratings_text;
		} else {
			return $template_postratings_text;
		}
	}
}


### Function: Display Ratings Vote
function the_ratings_vote($display = true) {
	global $id;
	$post_ratings_image_vote = '';
	$ratings_image = get_settings('postratings_image');
	$ratings_max = intval(get_settings('postratings_max'));
	$rating_url = get_permalink();
	if(strpos($rating_url, '?') !== false) {
		$rating_url = $rating_url.'&amp;pid='.$id.'&amp;';
	} else {
		$rating_url = $rating_url.'?pid='.$id.'&amp;';
	}
	// Display Start Of Rating Image
	if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif')) {
		$post_ratings_image_vote = '<img src="'.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif" alt="" />';
	}
	// Display Rating Image
	for($i=1; $i <= $ratings_max; $i++) {
	$post_ratings_image_vote .= '<a href="'.$rating_url.'rate='.$i.'" title="'.$i.' Stars"><img src="'.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$i.' Stars" border="0" name="rating_'.$id.'_'.$i.'" onmouseover="current_rating('.$id.', '.$i.');" onmouseout="ratings_off('.$id.');" /></a>';
	}
	// Display End Of Rating Image
	if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif')) {
		$post_ratings_image_vote .= '<img src="'.get_settings('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif" alt="" />';
	}
	// Display The Contents
	$template_postratings_vote = stripslashes(get_settings('postratings_template_vote'));
	$template_postratings_vote = str_replace("%RATINGS_IMAGES_VOTE%", $post_ratings_image_vote, $template_postratings_vote);
	$template_postratings_vote = str_replace("%RATINGS_MAX%", $ratings_max, $template_postratings_vote);
	if($display) {
		echo $template_postratings_vote;
	} else {
		return $template_postratings_vote;
	}
}


### Function: Process Ratings
add_action('init', 'process_ratings');
function process_ratings() {
	global $wpdb, $user_identity;
	$ratings_max = intval(get_settings('postratings_max'));
	$rate = intval($_GET['rate']);
	$post_id = intval($_GET['pid']);
	if($rate && $post_id) {
		$rated = check_rated($post_id);
		// Check Whether Post Has Been Rated By User
		if(!$rated) {
			// Check Whether Is There A Valid Post
			$post = get_post($post_id);
			// If Valid Post Then We Rate It
			if($post) {
				$post_title = addslashes($post->post_title);
				$post_ratings = get_post_custom($post_id);
				$post_ratings_users = $post_ratings['ratings_users'][0];
				$post_ratings_score = $post_ratings['ratings_score'][0];	
				// Check For Ratings Lesser Than 1 And Greater Than $ratings_max
				if($rate < 1 || $rate > $ratings_max) {
					$rate = 0;
				}
				// Add Ratings
				if($post_ratings_users == 0 && $post_ratings_score == 0) {
					$post_ratings_average = round($rate/1, 2);
					add_post_meta($post_id, 'ratings_users', 1);
					add_post_meta($post_id, 'ratings_score', $rate);
					add_post_meta($post_id, 'ratings_average',$post_ratings_average);	
				// Update Ratings
				} else {
					$post_ratings_average = round(($post_ratings_score+$rate)/($post_ratings_users+1), 2);
					update_post_meta($post_id, 'ratings_users', ($post_ratings_users+1));	
					update_post_meta($post_id, 'ratings_score', ($post_ratings_score+$rate));
					update_post_meta($post_id, 'ratings_average', $post_ratings_average);
				}
				// Add Log
				if(!empty($user_identity)) {
					$rate_user = addslashes($user_identity);
				} elseif(!empty($_COOKIE['comment_author_'.COOKIEHASH])) {
					$rate_user = addslashes($_COOKIE['comment_author_'.COOKIEHASH]);
				} else {
					$rate_user = 'Guest';
				}
				$rate_log = $wpdb->query("INSERT INTO $wpdb->ratings VALUES (0, $post_id, '$post_title', $rate,'".current_time('timestamp')."', '".get_ipaddress()."', '".gethostbyaddr(get_ipaddress())."' ,'$rate_user')");
				// Add Cookie
				$rate_cookie = setcookie("rated_".$post_id, 1, time() + 30000000, COOKIEPATH);
			}
		}
	}
}


### Function: Check Whether User Have Rated For The Post
function check_rated($post_id) {
	// Check Cookie First
	$rated_cookie = check_rated_cookie($post_id);
	if($rated_cookie > 0) {
		return true;
	// Check IP If Cookie Cannot Be Found
	} else {
		return check_rated_ip($post_id);
	}
	return false;	
}


### Function: Check Rated By Cookie
function check_rated_cookie($post_id) {
	// 0: False | > 0: True
	return intval($_COOKIE["rated_$post_id"]);
}


### Function: Check Rated By IP
function check_rated_ip($post_id) {
	global $wpdb;
	// Check IP From IP Logging Database
	$get_rated = $wpdb->get_var("SELECT rating_ip FROM $wpdb->ratings WHERE rating_postid = $post_id AND rating_ip = '".get_ipaddress()."'");
	// 0: False | > 0: True
	return intval($get_rated);
}


### Function: Get IP Address
if(!function_exists('get_ipaddress')) {
	function get_ipaddress() {
		if (empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip_address = $_SERVER["REMOTE_ADDR"];
		} else {
			$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		if(strpos($ip_address, ',') !== false) {
			$ip_address = explode(',', $ip_address);
			$ip_address = $ip_address[0];
		}
		return $ip_address;
	}
}


### Function: Place Rating In Content
add_filter('the_content', 'place_ratings', 12);
function place_ratings($content){
    $content = preg_replace( "/\[ratings\]/ise", "the_ratings(false)", $content);
	return $content;
}


### Function: Display Most Rated Page/Post
if(!function_exists('get_most_rated')) {
	function get_most_rated($mode = '', $limit = 10) {
		global $wpdb, $post;
		$where = '';
		if($mode == 'post') {
			$where = 'post_status = \'publish\'';
		} elseif($mode == 'page') {
			$where = 'post_status = \'static\'';
		} else {
			$where = '(post_status = \'publish\' OR post_status = \'static\')';
		}
		$most_rated = $wpdb->get_results("SELECT wp_posts.ID, post_title, post_name, post_status, post_date, CAST(meta_value AS UNSIGNED) AS votes FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_date < '".current_time('mysql')."' AND $where AND meta_key = 'ratings_users' AND post_password = '' ORDER BY votes DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $post) {
				$post_title = htmlspecialchars(stripslashes($post->post_title));
				$post_views = intval($post->votes);
				echo "<li><a href=\"".get_permalink()."\">$post_title</a> ($post_views ".__('Votes').")</li>";
			}
		} else {
			echo '<li>'.__('N/A').'</li>';
		}
	}
}


### Function: Display Highest Rated Page/Post
if(!function_exists('get_highest_rated')) {
	function get_highest_rated($mode = '', $limit = 10) {
		global $wpdb, $post;
		$where = '';
		if($mode == 'post') {
			$where = 'post_status = \'publish\'';
		} elseif($mode == 'page') {
			$where = 'post_status = \'static\'';
		} else {
			$where = '(post_status = \'publish\' OR post_status = \'static\')';
		}
		$most_rated = $wpdb->get_results("SELECT wp_posts.ID, post_title, post_name, post_status, post_date, (meta_value+0.00) AS highest FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_date < '".current_time('mysql')."' AND $where AND meta_key = 'ratings_average' AND post_password = '' ORDER BY highest DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $post) {
				$post_title = htmlspecialchars(stripslashes($post->post_title));
				$post_views = $post->highest;
				echo "<li><a href=\"".get_permalink()."\">$post_title</a> ($post_views ".__('Stars').")</li>";
			}
		} else {
			echo '<li>'.__('N/A').'</li>';
		}
	}
}


### Function: Create Rating Logs Table
add_action('activate_postratings/postratings.php', 'create_ratinglogs_table');
function create_ratinglogs_table() {
	global $wpdb;
	include(ABSPATH.'/wp-admin/upgrade-functions.php');
	// Create Post Ratings Table
	$create_ratinglogs_sql = "CREATE TABLE $wpdb->ratings (".
			"rating_id INT(11) NOT NULL auto_increment,".
			"rating_postid INT(11) NOT NULL ,".
			"rating_posttitle TEXT NOT NULL,".
			"rating_rating INT(2) NOT NULL ,".
			"rating_timestamp VARCHAR(15) NOT NULL ,".
			"rating_ip VARCHAR(40) NOT NULL ,".
			"rating_host VARCHAR(200) NOT NULL,".
			"rating_username VARCHAR(50) NOT NULL,".
			"PRIMARY KEY (rating_id))";
	maybe_create_table($wpdb->ratings, $create_ratinglogs_sql);
	// Add In Options (4 Records)
	add_option('postratings_image', 'stars', 'Your Ratings Image');
	add_option('postratings_max', 5, 'Your Max Ratings');
	add_option('postratings_template_vote', '<p>%RATINGS_IMAGES_VOTE%</p>', 'Ratings Vote Template Text');
	add_option('postratings_template_text', '<p>%RATINGS_IMAGES% (<b>%RATINGS_USERS%</b> votes, average: <b>%RATINGS_AVERAGE%</b> out of %RATINGS_MAX%)</p>', 'Ratings Template Text');
	add_option('postratings_template_none', 'No Ratings Yet', 'Ratings Template For No Ratings');
	// Set 'manage_ratings' Capabilities To Administrator	
	$role = get_role('administrator');
	if(!$role->has_cap('manage_ratings')) {
		$role->add_cap('manage_ratings');
	}
}
?>