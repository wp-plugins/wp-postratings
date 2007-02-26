<?php
/*
Plugin Name: WP-PostRatings
Plugin URI: http://www.lesterchan.net/portfolio/programming.php
Description: Adds an AJAX rating system for your WordPress blog's post/page.
Version: 1.11
Author: GaMerZ
Author URI: http://www.lesterchan.net
*/


/* 
	Copyright 2007  Lester Chan  (email : gamerz84@hotmail.com)

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


### Load WP-Config File If This File Is Called Directly
if (!function_exists('add_action')) {
	require_once('../../../wp-config.php');
}


### Create Text Domain For Translations
load_plugin_textdomain('wp-postratings', 'wp-content/plugins/postratings');


### Rating Logs Table Name
$wpdb->ratings = $table_prefix . 'ratings';


### Function: Ratings Administration Menu
add_action('admin_menu', 'ratings_menu');
function ratings_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('Ratings', 'wp-postratings'), __('Ratings', 'wp-postratings'), 'manage_ratings', 'postratings/postratings-manager.php');
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('postratings/postratings-manager.php', __('Manage Ratings', 'wp-postratings'), __('Manage Ratings', 'wp-postratings'), 'manage_ratings', 'postratings/postratings-manager.php');
		add_submenu_page('postratings/postratings-manager.php', __('Ratings Options', 'wp-postratings'), __('Ratings Options', 'wp-postratings'),  'manage_ratings', 'postratings/postratings-options.php');
		add_submenu_page('postratings/postratings-manager.php', __('Ratings Usage', 'wp-postratings'), __('Ratings Usage', 'wp-postratings'), 'manage_ratings', 'postratings/postratings-usage.php');
	}
}


### Function: Display The Rating For The Post
function the_ratings($start_tag = 'div', $display = true) {
	global $id;
	// Loading Style
	$postratings_ajax_style = get_option('postratings_ajax_style');
	if(intval($postratings_ajax_style['loading']) == 1) {
		$loading = "\n<$start_tag id=\"post-ratings-$id-loading\"  class=\"post-ratings-loading\"><img src=\"".get_option('siteurl')."/wp-content/plugins/postratings/images/loading.gif\" width=\"16\" height=\"16\" alt=\"".__('Loading', 'wp-postratings')." ...\" title=\"".__('Loading', 'wp-postratings')." ...\" class=\"post-ratings-image\" />&nbsp;".__('Loading', 'wp-postratings')." ...</".$start_tag.">\n";
	} else {
		$loading = '';
	}
	// Check To See Whether User Has Voted
	$user_voted = check_rated($id);
	// If User Voted Or Is Not Allowed To Rate
	if($user_voted || !check_allowtorate()) {
		if(!$display) {
			return "<$start_tag id=\"post-ratings-$id\" class=\"post-ratings\">".the_ratings_results($id).'</'.$start_tag.'>'.$loading;
		} else {
			echo "<$start_tag id=\"post-ratings-$id\" class=\"post-ratings\">".the_ratings_results($id).'</'.$start_tag.'>'.$loading;
			return;
		}
	// If User Has Not Voted
	} else {
		if(!$display) {
			return "<$start_tag id=\"post-ratings-$id\" class=\"post-ratings\">".the_ratings_vote($id).'</'.$start_tag.'>'.$loading;
		} else {
			echo "<$start_tag id=\"post-ratings-$id\" class=\"post-ratings\">".the_ratings_vote($id).'</'.$start_tag.'>'.$loading;
			return;
		}
	}
}


### Function: Displays Rating Header
add_action('wp_head', 'the_ratings_header');
function the_ratings_header() {
	echo "\n".'<!-- Start Of Script Generated By WP-PostRatings 1.10 -->'."\n";
	wp_register_script('wp-postratings', '/wp-content/plugins/postratings/postratings-js.php', false, '1.10');
	wp_print_scripts(array('sack', 'wp-postratings'));
	echo '<link rel="stylesheet" href="'.get_option('siteurl').'/wp-content/plugins/postratings/postratings-css.css" type="text/css" media="screen" />'."\n";
	echo '<!-- End Of Script Generated By WP-PostRatings 1.10 -->'."\n";
}


### Function: Displays Ratings Header In WP-Admin
add_action('admin_head', 'ratings_header_admin');
function ratings_header_admin() {
	echo '<link rel="stylesheet" href="'.get_option('siteurl').'/wp-content/plugins/postratings/postratings-css.css" type="text/css" media="screen" />'."\n";
}


### Function: Display Ratings Results 
function the_ratings_results($post_id, $new_user = 0, $new_score = 0, $new_average = 0) {
	$ratings_image = get_option('postratings_image');
	$ratings_max = intval(get_option('postratings_max'));
	if($new_user == 0 && $new_score == 0 && $new_average == 0) {
		$post_ratings = get_post_custom($post_id);
		$post_ratings_users = $post_ratings['ratings_users'][0];
		$post_ratings_score = $post_ratings['ratings_score'][0];
		$post_ratings_average = $post_ratings['ratings_average'][0];
	} else {
		$post_ratings_users = $new_user;
		$post_ratings_score = $new_score;
		$post_ratings_average = $new_average;
	}
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
	// Display Start Of Rating Image
	if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif')) {
		$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
	}
	// Display Rated Images
	$image_alt = $post_ratings_users.' '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': '.$post_ratings_average.' '.__('out of', 'wp-postratings').' '.$ratings_max;
	for($i=1; $i <= $ratings_max; $i++) {
		if($i <= $post_ratings) {
			$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
		} elseif($i == $insert_half) {
			$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
		} else {
			$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
		}
	}
	// Display End Of Rating Image
	if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif')) {
		$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
	}
	// Display User Rated Text
	$post_ratings_user_rated = '';
	// Display The Contents
	$template_postratings_text = stripslashes(get_option('postratings_template_text'));
	$template_postratings_text = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_MAX%", $ratings_max, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_SCORE%", $post_ratings_score, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_USER_RATED%", $post_ratings_user_rated, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_PERCENTAGE%", $post_ratings_percentage, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_USERS%", number_format($post_ratings_users), $template_postratings_text);
	// Return Post Ratings Template
	return $template_postratings_text;
}


### Function: Display Ratings Vote
function the_ratings_vote($post_id, $new_user = 0, $new_score = 0, $new_average = 0) {
	$ratings_image = get_option('postratings_image');
	$ratings_max = intval(get_option('postratings_max'));
	if($new_user == 0 && $new_score == 0 && $new_average == 0) {
		$post_ratings = get_post_custom($post_id);
		$post_ratings_users = $post_ratings['ratings_users'][0];
		$post_ratings_score = $post_ratings['ratings_score'][0];
		$post_ratings_average = $post_ratings['ratings_average'][0];
	} else {
		$post_ratings_users = $new_user;
		$post_ratings_score = $new_score;
		$post_ratings_average = $new_average;
	}
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
	$postratings_ratingstext = get_option('postratings_ratingstext');
	// Display Start Of Rating Image
	if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif')) {
		$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
	}
	// Display Rated Images
	for($i=1; $i <= $ratings_max; $i++) {
		$ratings_text = stripslashes($postratings_ratingstext[$i-1]);
		if($i <= $post_ratings) {
			$post_ratings_images .= '<img id="rating_'.$post_id.'_'.$i.'" src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$post_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$post_ratings.', '.$insert_half.');" onclick="rate_post();" onkeypress="rate_post();" style="cursor: pointer; border: 0px;" />';		
		} elseif($i == $insert_half) {
			$post_ratings_images .= '<img id="rating_'.$post_id.'_'.$i.'" src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$post_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$post_ratings.', '.$insert_half.');" onclick="rate_post();" onkeypress="rate_post();" style="cursor: pointer; border: 0px;" />';
		} else {
			$post_ratings_images .= '<img id="rating_'.$post_id.'_'.$i.'" src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$post_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$post_ratings.', '.$insert_half.');" onclick="rate_post();" onkeypress="rate_post();" style="cursor: pointer; border: 0px;" />';
		}
	}
	// Display End Of Rating Image
	if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif')) {
		$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
	}
	// Individual Post Ratings Text
	$post_ratings_text = '<span class="post-ratings-text" id="ratings_'.$post_id.'_text"></span>';

	// If No Ratings, Return No Ratings templae
	if($post_ratings == 0) {
		$template_postratings_none = stripslashes(get_option('postratings_template_none'));
		$template_postratings_none = str_replace("%RATINGS_IMAGES_VOTE%", $post_ratings_images, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_MAX%", $ratings_max, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_SCORE%", $post_ratings_score, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_TEXT%", $post_ratings_text, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_PERCENTAGE%", $post_ratings_percentage, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_USERS%", $post_ratings_users, $template_postratings_none);
		// Return Post Ratings Template
		return $template_postratings_none;
	} else {
		// Display The Contents
		$template_postratings_vote = stripslashes(get_option('postratings_template_vote'));
		$template_postratings_vote = str_replace("%RATINGS_IMAGES_VOTE%", $post_ratings_images, $template_postratings_vote);
		$template_postratings_vote = str_replace("%RATINGS_MAX%", $ratings_max, $template_postratings_vote);
		$template_postratings_vote = str_replace("%RATINGS_SCORE%", $post_ratings_score, $template_postratings_vote);
		$template_postratings_vote = str_replace("%RATINGS_TEXT%", $post_ratings_text, $template_postratings_vote);
		$template_postratings_vote = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $template_postratings_vote);
		$template_postratings_vote = str_replace("%RATINGS_PERCENTAGE%", $post_ratings_percentage, $template_postratings_vote);
		$template_postratings_vote = str_replace("%RATINGS_USERS%", number_format($post_ratings_users), $template_postratings_vote);
		// Return Post Ratings Voting Template
		return $template_postratings_vote;
	}
}


### Function: Check Who Is Allow To Rate
function check_allowtorate() {
	global $user_ID;
	$user_ID = intval($user_ID);
	$allow_to_vote = intval(get_option('postratings_allowtorate'));
	switch($allow_to_vote) {
		// Guests Only
		case 0:
			if($user_ID > 0) {
				return false;
			}
			return true;
			break;
		// Registered Users Only
		case 1:
			if($user_ID == 0) {
				return false;
			}
			return true;
			break;
		// Registered Users And Guests
		case 2:
		default:
			return true;
	}
}


### Function: Check Whether User Have Rated For The Post
function check_rated($post_id) {
	global $user_ID;
	$postratings_logging_method = intval(get_option('postratings_logging_method'));
	switch($postratings_logging_method) {
		// Do Not Log
		case 0:
			return false;
			break;
		// Logged By Cookie
		case 1:
			return check_rated_cookie($post_id);
			break;
		// Logged By IP
		case 2:
			return check_rated_ip($post_id);
			break;
		// Logged By Cookie And IP
		case 3:
			$rated_cookie = check_rated_cookie($post_id);
			if($rated_cookie > 0) {
				return true;
			} else {
				return check_rated_ip($post_id);
			}
			break;
		// Logged By Username
		case 4:
			return check_rated_username($post_id);
			break;
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


### Function: Check Rated By Username
function check_rated_username($post_id) {
	global $wpdb, $user_ID;
	$rating_userid = intval($user_ID);
	// Check User ID From IP Logging Database
	$get_rated = $wpdb->get_var("SELECT rating_userid FROM $wpdb->ratings WHERE rating_postid = $post_id AND rating_userid = $rating_userid");
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
add_filter('the_content', 'place_ratings', 7);
function place_ratings($content){
	if(!is_feed()) {
		 $content = preg_replace("/\[ratings\]/ise", "the_ratings('div', false)", $content);
	} else {
		$content = preg_replace("/\[ratings\]/ise", __('Note: You can rate this post by visiting the site.', 'wp-postratings'), $content);
	}   
	return $content;
}


### Function: Display Most Rated Page/Post
if(!function_exists('get_most_rated')) {
	function get_most_rated($mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$where = '';
		$temp = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$most_rated = $wpdb->get_results("SELECT $wpdb->posts.*, (meta_value+0) AS ratings_votes FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_date < '".current_time('mysql')."' AND $where AND post_status = 'publish' AND meta_key = 'ratings_users' AND post_password = '' ORDER BY ratings_votes DESC LIMIT $limit");
		if($most_rated) {
			if($chars > 0) {
				foreach ($most_rated as $post) {
					$post_title = get_the_title();
					$post_votes = intval($post->ratings_votes);
					$temp .= "<li><a href=\"".get_permalink()."\">".snippet_chars($post_title, $chars)."</a> - $post_votes ".__('Votes', 'wp-postratings')."</li>\n";
				}
			} else {
				foreach ($most_rated as $post) {
					$post_title = get_the_title();
					$post_votes = intval($post->ratings_votes);
					$temp .= "<li><a href=\"".get_permalink()."\">$post_title</a> - $post_votes ".__('Votes', 'wp-postratings')."</li>\n";
				}
			}
		} else {
			$temp = '<li>'.__('N/A', 'wp-postratings').'</li>'."\n";
		}
		if($display) {
			echo $temp;
		} else {
			return $temp;
		}
	}
}


### Function: Display Highest Rated Page/Post By Category ID
if(!function_exists('get_highest_rated_category')) {
	function get_highest_rated_category($category_id = 0, $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$ratings_image = get_option('postratings_image');
		$ratings_max = intval(get_option('postratings_max'));
		$where = '';
		$temp = '';
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$highest_rated = $wpdb->get_results("SELECT $wpdb->posts.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users FROM $wpdb->posts LEFT JOIN $wpdb->postmeta AS t1 ON t1.post_id = $wpdb->posts.ID LEFT JOIN $wpdb->postmeta As t2 ON t1.post_id = t2.post_id LEFT JOIN $wpdb->post2cat ON $wpdb->post2cat.post_id = $wpdb->posts.ID WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."'  AND $wpdb->posts.post_status = 'publish' AND $wpdb->post2cat.category_id = $category_id AND $where ORDER BY ratings_average DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $post) {
				// Variables
				$post_ratings_users = $post->ratings_users;
				$post_ratings_images = '';
				$post_title = get_the_title();
				$post_ratings_average = $post->ratings_average;
				$post_ratings_whole = intval($post_ratings_average);
				$post_ratings = floor($post_ratings_average);
				// Check For Half Star
				$insert_half = 0;
				$average_diff = $post_ratings_average-$post_ratings_whole;
				if($average_diff >= 0.25 && $average_diff <= 0.75) {
					$insert_half = $post_ratings_whole+1;
				} elseif($average_diff > 0.75) {
					$post_ratings = $post_ratings+1;
				}
				// Display Start Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
				}
				// Display Rated Images
				for($i=1; $i <= $ratings_max; $i++) {
					if($i <= $post_ratings) {
						$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" title="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" class="post-ratings-image" />';		
					} elseif($i == $insert_half) {						
						$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" title="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" class="post-ratings-image" />';
					} else {
						$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" title="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" class="post-ratings-image" />';
					}
				}
				// Display End Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
				}
				if($chars > 0) {
					$temp = "<li><a href=\"".get_permalink()."\">".snippet_chars($post_title, $chars)."</a> ".$post_ratings_images."</li>\n";
				} else {
					// Display The Contents
					$temp = stripslashes(get_option('postratings_template_highestrated'));
					$temp = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $temp);
					$temp = str_replace("%RATINGS_MAX%", $ratings_max, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_USERS%", number_format($post_ratings_users), $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_URL%", get_permalink(), $temp);
				}
				$output .= $temp;
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-postratings').'</li>'."\n";
		}
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Highest Rated Page/Post
if(!function_exists('get_highest_rated')) {
	function get_highest_rated($mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$temp_post = $post;
		$ratings_image = get_option('postratings_image');
		$ratings_max = intval(get_option('postratings_max'));
		$where = '';
		$temp = '';
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		$highest_rated = $wpdb->get_results("SELECT $wpdb->posts.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users FROM $wpdb->posts LEFT JOIN $wpdb->postmeta AS t1 ON t1.post_id = $wpdb->posts.ID LEFT JOIN $wpdb->postmeta As t2 ON t1.post_id = t2.post_id WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."' AND $wpdb->posts.post_status = 'publish' AND $where ORDER BY ratings_average DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $post) {
				// Variables
				$post_ratings_users = $post->ratings_users;
				$post_ratings_images = '';
				$post_title = get_the_title();
				$post_ratings_average = $post->ratings_average;
				$post_ratings_whole = intval($post_ratings_average);
				$post_ratings = floor($post_ratings_average);
				// Check For Half Star
				$insert_half = 0;
				$average_diff = $post_ratings_average-$post_ratings_whole;
				if($average_diff >= 0.25 && $average_diff <= 0.75) {
					$insert_half = $post_ratings_whole+1;
				} elseif($average_diff > 0.75) {
					$post_ratings = $post_ratings+1;
				}
				// Display Start Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
				}
				// Display Rated Images
				for($i=1; $i <= $ratings_max; $i++) {
					if($i <= $post_ratings) {
						$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" title="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" class="post-ratings-image" />';		
					} elseif($i == $insert_half) {						
						$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" title="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" class="post-ratings-image" />';
					} else {
						$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" title="'.__('Average: ', 'wp-postratings').$post_ratings_average.__(' out of ', 'wp-postratings').$ratings_max.'" class="post-ratings-image" />';
					}
				}
				// Display End Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
				}
				if($chars > 0) {
					$temp = "<li><a href=\"".get_permalink()."\">".snippet_chars($post_title, $chars)."</a> ".$post_ratings_images."</li>\n";
				} else {
					// Display The Contents
					$temp = stripslashes(get_option('postratings_template_highestrated'));
					$temp = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $temp);
					$temp = str_replace("%RATINGS_MAX%", $ratings_max, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_USERS%", number_format($post_ratings_users), $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_URL%", get_permalink(), $temp);
				}
				$output .= $temp;
			}
		} else {
			$output = '<li>'.__('N/A', 'wp-postratings').'</li>'."\n";
		}
		$post = $temp_post;
		if($display) {
			echo $output;
		} else {
			return $output;
		}
	}
}


### Function: Display Total Rating Votes
if(!function_exists('get_ratings_votes')) {
	function get_ratings_votes($display = true) {
		global $wpdb;
		$ratings_votes = $wpdb->get_var("SELECT SUM((meta_value+0.00)) FROM $wpdb->postmeta WHERE meta_key = 'ratings_score'");
		if($display) {
			echo number_format($ratings_votes);
		} else {
			return number_format($ratings_votes);
		}
	}
}


### Function: Display Total Rating Users
if(!function_exists('get_ratings_users')) {
	function get_ratings_users($display = true) {
		global $wpdb;
		$ratings_users = $wpdb->get_var("SELECT SUM((meta_value+0.00)) FROM $wpdb->postmeta WHERE meta_key = 'ratings_users'");
		if($display) {
			echo number_format($ratings_users);
		} else {
			return number_format($ratings_users);
		}
	}
}


### Function: Snippet Text
if(!function_exists('snippet_chars')) {
	function snippet_chars($text, $length = 0) {
		$text = htmlspecialchars_decode($text);
		 if (strlen($text) > $length){       
			return htmlspecialchars(substr($text,0,$length)).'...';             
		 } else {
			return htmlspecialchars($text);
		 }
	}
}


### Function: HTML Special Chars Decode
if (!function_exists('htmlspecialchars_decode')) {
   function htmlspecialchars_decode($text) {
       return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
   }
}


### Function: Process Ratings
process_ratings();
function process_ratings() {
	global $wpdb, $user_identity, $user_ID;
	$ratings_max = intval(get_option('postratings_max'));
	$rate = intval($_GET['rate']);
	$post_id = intval($_GET['pid']);
	header('Content-Type: text/html; charset='.get_option('blog_charset').'');
	if($rate > 0 && $post_id > 0 && check_allowtorate()) {		
		// Check For Bot
		$bots_useragent = array('googlebot', 'google', 'msnbot', 'ia_archiver', 'lycos', 'jeeves', 'scooter', 'fast-webcrawler', 'slurp@inktomi', 'turnitinbot', 'technorati', 'yahoo', 'findexa', 'findlinks', 'gaisbo', 'zyborg', 'surveybot', 'bloglines', 'blogsearch', 'ubsub', 'syndic8', 'userland', 'gigabot', 'become.com');
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		foreach ($bots_useragent as $bot) { 
			if (stristr($useragent, $bot) !== false) {
				return;
			} 
		}
		$rated = check_rated($post_id);
		// Check Whether Post Has Been Rated By User
		if(!$rated) {
			// Check Whether Is There A Valid Post
			$post = get_post($post_id);
			// If Valid Post Then We Rate It
			if($post) {
				$post_title = addslashes($post->post_title);
				$post_ratings = get_post_custom($post_id);
				$post_ratings_users = intval($post_ratings['ratings_users'][0]);
				$post_ratings_score = intval($post_ratings['ratings_score'][0]);	
				// Check For Ratings Lesser Than 1 And Greater Than $ratings_max
				if($rate < 1 || $rate > $ratings_max) {
					$rate = 0;
				}
				// Add Ratings
				if($post_ratings_users == 0 && $post_ratings_score == 0) {
					$post_ratings_users = 1;
					$post_ratings_score = $rate;
					$post_ratings_average = round($rate/1, 2);
					add_post_meta($post_id, 'ratings_users', 1);
					add_post_meta($post_id, 'ratings_score', $rate);
					add_post_meta($post_id, 'ratings_average',$post_ratings_average);	
				// Update Ratings
				} else {
					$post_ratings_users = ($post_ratings_users+1);
					$post_ratings_score = ($post_ratings_score+$rate);
					$post_ratings_average = round($post_ratings_score/$post_ratings_users, 2);					
					update_post_meta($post_id, 'ratings_users', $post_ratings_users);	
					update_post_meta($post_id, 'ratings_score', $post_ratings_score);
					update_post_meta($post_id, 'ratings_average', $post_ratings_average);
				}
				// Add Log
				if(!empty($user_identity)) {
					$rate_user = addslashes($user_identity);
				} elseif(!empty($_COOKIE['comment_author_'.COOKIEHASH])) {
					$rate_user = addslashes($_COOKIE['comment_author_'.COOKIEHASH]);
				} else {
					$rate_user = __('Guest', 'wp-postratings');
				}
				$rate_userid = intval($user_ID);
				// Only Create Cookie If User Choose Logging Method 1 Or 3
				$postratings_logging_method = intval(get_option('postratings_logging_method'));
				if($postratings_logging_method == 1 || $postratings_logging_method == 3) {
					$rate_cookie = setcookie("rated_".$post_id, 1, time() + 30000000, COOKIEPATH);
				}
				// Log Ratings No Matter What
				$rate_log = $wpdb->query("INSERT INTO $wpdb->ratings VALUES (0, $post_id, '$post_title', $rate,'".current_time('timestamp')."', '".get_ipaddress()."', '".gethostbyaddr(get_ipaddress())."' ,'$rate_user', $rate_userid)");
				// Output AJAX Result
				echo the_ratings_results($post_id, $post_ratings_users, $post_ratings_score, $post_ratings_average);
				exit();
			} else {
				printf(__('Invalid Post ID. Post ID #%s.', 'wp-postratings'), $post_id);
				exit();
			} // End if($post)
		} else {
			printf(__('You Had Already Rated This Post. Post ID #%s.', 'wp-postratings'), $post_id);
			exit();	
		}// End if(!$rated)
	} // End if($rate && $post_id && check_allowtorate())
}


### Function: Modify Default WordPress Listing To Make It Sorted By Most Rated
function ratings_most_fields($content) {
	global $wpdb;
	$content .= ", ($wpdb->postmeta.meta_value+0) AS ratings_votes";
	return $content;
}
function ratings_most_join($content) {
	global $wpdb;
	$content .= " LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID";
	return $content;
}
function ratings_most_where($content) {
	global $wpdb;
	$content .= " AND $wpdb->postmeta.meta_key = 'ratings_users'";
	return $content;
}
function ratings_most_orderby($content) {
	$orderby = trim(addslashes($_GET['orderby']));
	if(empty($orderby) && ($orderby != 'asc' || $orderby != 'desc')) {
		$orderby = 'desc';
	}
	$content = " ratings_votes $orderby";
	return $content;
}


### Function: Modify Default WordPress Listing To Make It Sorted By Highest Rated
function ratings_highest_fields($content) {
	$content .= ", (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users";
	return $content;
}
function ratings_highest_join($content) {
	global $wpdb;
	$content .= " LEFT JOIN $wpdb->postmeta AS t1 ON t1.post_id = $wpdb->posts.ID LEFT JOIN $wpdb->postmeta As t2 ON t1.post_id = t2.post_id";
	return $content;
}
function ratings_highest_where($content) {
	$content .= " AND t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users'";
	return $content;
}
function ratings_highest_orderby($content) {
	$orderby = trim(addslashes($_GET['orderby']));
	if(empty($orderby) && ($orderby != 'asc' || $orderby != 'desc')) {
		$orderby = 'desc';
	}
	$content = " ratings_average $orderby, ratings_users $orderby";
	return $content;
}


### Process The Sorting
/*
if($_GET['sortby'] == 'most_rated') {
	add_filter('posts_fields', 'ratings_most_fields');
	add_filter('posts_join', 'ratings_most_join');
	add_filter('posts_where', 'ratings_most_where');
	add_filter('posts_orderby', 'ratings_most_orderby');
} elseif($_GET['sortby'] == 'highest_rated') {
	add_filter('posts_fields', 'ratings_highest_fields');
	add_filter('posts_join', 'ratings_highest_join');
	add_filter('posts_where', 'ratings_highest_where');
	add_filter('posts_orderby', 'ratings_highest_orderby');
}
*/



### Function: Create Rating Logs Table
add_action('activate_postratings/postratings.php', 'create_ratinglogs_table');
function create_ratinglogs_table() {
	global $wpdb;
	include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
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
			"rating_userid int(10) NOT NULL default '0',".
			"PRIMARY KEY (rating_id))";
	maybe_create_table($wpdb->ratings, $create_ratinglogs_sql);
	// Add In Options (4 Records)
	add_option('postratings_image', 'stars', 'Your Ratings Image');
	add_option('postratings_max', '5', 'Your Max Ratings');
	add_option('postratings_template_vote', '%RATINGS_IMAGES_VOTE% (<b>%RATINGS_USERS%</b> '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': <b>%RATINGS_AVERAGE%</b> '.__('out of', 'wp-postratings').' %RATINGS_MAX%)<br />%RATINGS_TEXT%', 'Ratings Vote Template Text');
	add_option('postratings_template_text', '%RATINGS_IMAGES% (<b>%RATINGS_USERS%</b> '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': <b>%RATINGS_AVERAGE%</b> '.__('out of', 'wp-postratings').' %RATINGS_MAX%)', 'Ratings Template Text');
	add_option('postratings_template_none', '%RATINGS_IMAGES_VOTE% ('.__('No Ratings Yet', 'wp-postratings').')<br />%RATINGS_TEXT%', 'Ratings Template For No Ratings');
	// Database Upgrade For WP-PostRatings 1.02
	add_option('postratings_logging_method', '3', 'Logging Method Of User Rated\'s Answer');
	add_option('postratings_allowtorate', '2', 'Who Is Allowed To Rate');
	// Database Uprade For WP-PostRatings 1.04	
	maybe_add_column($wpdb->ratings, 'rating_userid', "ALTER TABLE $wpdb->ratings ADD rating_userid INT( 10 ) NOT NULL DEFAULT '0';");
	// Database Uprade For WP-PostRatings 1.05
	add_option('postratings_ratingstext', array(__('1 Star', 'wp-postratings'), __('2 Stars', 'wp-postratings'), __('3 Stars', 'wp-postratings'), __('4 Stars', 'wp-postratings'), __('5 Stars', 'wp-postratings')), 'Individual Post Rating Text');
	add_option('postratings_template_highestrated', '<li><a href="%POST_URL%">%POST_TITLE%</a> %RATINGS_IMAGES% (%RATINGS_AVERAGE% '.__('out of', 'wp-postratings').' %RATINGS_MAX%)</li>', 'Template For Highest Rated');
	// Database Uprade For WP-PostRatings 1.11
	add_option('postratings_ajax_style', array('loading' => 1, 'fading' => 1), 'Ratings AJAX Style');
	// Set 'manage_ratings' Capabilities To Administrator	
	$role = get_role('administrator');
	if(!$role->has_cap('manage_ratings')) {
		$role->add_cap('manage_ratings');
	}
}
?>