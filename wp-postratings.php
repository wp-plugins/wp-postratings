<?php
/*
Plugin Name: WP-PostRatings
Plugin URI: http://lesterchan.net/portfolio/programming/php/
Description: Adds an AJAX rating system for your WordPress blog's post/page.
Version: 1.40
Author: Lester 'GaMerZ' Chan
Author URI: http://lesterchan.net
*/


/* 
	Copyright 2008  Lester Chan  (email : lesterchan@gmail.com)

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
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}


### Create Text Domain For Translations
add_action('init', 'postratings_textdomain');
function postratings_textdomain() {
	load_plugin_textdomain('wp-postratings', false, 'wp-postratings');
}


### Rating Logs Table Name
global $wpdb;
$wpdb->ratings = $wpdb->prefix.'ratings';


### Function: Ratings Administration Menu
add_action('admin_menu', 'ratings_menu');
function ratings_menu() {
	if (function_exists('add_menu_page')) {
		add_menu_page(__('Ratings', 'wp-postratings'), __('Ratings', 'wp-postratings'), 'manage_ratings', 'wp-postratings/postratings-manager.php');
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('wp-postratings/postratings-manager.php', __('Manage Ratings', 'wp-postratings'), __('Manage Ratings', 'wp-postratings'), 'manage_ratings', 'wp-postratings/postratings-manager.php');
		add_submenu_page('wp-postratings/postratings-manager.php', __('Ratings Options', 'wp-postratings'), __('Ratings Options', 'wp-postratings'),  'manage_ratings', 'wp-postratings/postratings-options.php');
		add_submenu_page('wp-postratings/postratings-manager.php', __('Ratings Templates', 'wp-postratings'), __('Ratings Templates', 'wp-postratings'),  'manage_ratings', 'wp-postratings/postratings-templates.php');
		add_submenu_page('wp-postratings/postratings-manager.php', __('Uninstall WP-PostRatings', 'wp-postratings'), __('Uninstall WP-PostRatings', 'wp-postratings'), 'manage_ratings', 'wp-postratings/postratings-uninstall.php');
	}
}


### Function: Display The Rating For The Post
function the_ratings($start_tag = 'div', $custom_id = 0, $display = true) {
	global $id;
	// Allow Custom ID
	if(intval($custom_id) > 0) {
		$id = $custom_id;
	}
	// Loading Style
	$postratings_ajax_style = get_option('postratings_ajax_style');
	if(intval($postratings_ajax_style['loading']) == 1) {
		$loading = "\n<$start_tag id=\"post-ratings-$id-loading\"  class=\"post-ratings-loading\"><img src=\"".plugins_url('wp-postratings/images/loading.gif')."\" width=\"16\" height=\"16\" alt=\"".__('Loading', 'wp-postratings')." ...\" title=\"".__('Loading', 'wp-postratings')." ...\" class=\"post-ratings-image\" />&nbsp;".__('Loading', 'wp-postratings')." ...</".$start_tag.">\n";
	} else {
		$loading = '';
	}
	// Check To See Whether User Has Voted
	$user_voted = check_rated($id);
	// If User Voted Or Is Not Allowed To Rate
	if($user_voted) {
		if(!$display) {
			return "<$start_tag id=\"post-ratings-$id\" class=\"post-ratings\">".the_ratings_results($id).'</'.$start_tag.'>'.$loading;
		} else {
			echo "<$start_tag id=\"post-ratings-$id\" class=\"post-ratings\">".the_ratings_results($id).'</'.$start_tag.'>'.$loading;
			return;
		}
	// If User Is Not Allowed To Rate
	} else if(!check_allowtorate()) {
		if(!$display) {
			return "<$start_tag id=\"post-ratings-$id\" class=\"post-ratings\">".the_ratings_results($id, 0, 0, 0, 1).'</'.$start_tag.'>'.$loading;
		} else {
			echo "<$start_tag id=\"post-ratings-$id\" class=\"post-ratings\">".the_ratings_results($id, 0, 0, 0, 1).'</'.$start_tag.'>'.$loading;
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


### Function: Register Scripts And Styles
add_action('init', 'ratings_register_scriptstyle');
function ratings_register_scriptstyle() {
	wp_register_script('wp-postratings', plugins_url('wp-postratings/postratings-js-packed.js'), false, '1.40');
	wp_register_script('wp-postratings-admin', plugins_url('wp-postratings/postratings-admin-js-packed.js'), false, '1.40');
	if(@file_exists(TEMPLATEPATH.'/postratings-css.css')) {
		wp_register_style('wp-postratings', get_stylesheet_directory_uri().'/postratings-css.css', false, '1.40', 'all');
	} else {
		wp_register_style('wp-postratings', plugins_url('wp-postratings/postratings-css.css'), false, '1.40', 'all');
	}	
}


### Function: Displays Rating Header
add_action('wp_head', 'the_ratings_header');
function the_ratings_header() {
	$postratings_max = intval(get_option('postratings_max'));
	$postratings_custom = intval(get_option('postratings_customrating'));
	$postratings_ajax_style = get_option('postratings_ajax_style');	
	echo "\n".'<!-- Start Of Script Generated By WP-PostRatings 1.40 -->'."\n";
	echo '<script type="text/javascript">'."\n";
	echo '/* <![CDATA[ */'."\n";
	echo "\t".'var ratings_plugin_url = \''.plugins_url('wp-postratings')."';\n";
	echo "\t".'var ratings_ajax_url = \''.plugins_url('wp-postratings/wp-postratings.php')."';\n";
	echo "\t".'var ratings_text_wait = \''.js_escape(__('Please rate only 1 post at a time.', 'wp-postratings'))."';\n";
	echo "\t".'var ratings_image = \''.get_option('postratings_image')."';\n";
	echo "\t".'var ratings_max = '.$postratings_max.";\n";
	if($postratings_custom) {
		for($i = 1; $i <= $postratings_max; $i++) {
			echo "\t".'var ratings_'.$i.'_mouseover_image = new Image();'."\n";
			echo "\t".'ratings_'.$i.'_mouseover_image.src = ratings_plugin_url + "/images/" + ratings_image + "/rating_'.$i.'_over.gif";'."\n";
		}
	} else {
		echo "\t".'var ratings_mouseover_image = new Image();'."\n";
		echo "\t".'ratings_mouseover_image.src = ratings_plugin_url + "/images/" + ratings_image + "/rating_over.gif";'."\n";
	}
	echo "\t".'var ratings_show_loading = '.intval($postratings_ajax_style['loading']).";\n";
	echo "\t".'var ratings_show_fading = '.intval($postratings_ajax_style['fading']).";\n";
	echo "\t".'var ratings_custom = '.$postratings_custom.";\n";
	echo '/* ]]> */'."\n";
	echo '</script>'."\n";
	wp_print_scripts(array('sack', 'wp-postratings'));
	wp_print_styles('wp-postratings');
	echo '<!-- End Of Script Generated By WP-PostRatings 1.40 -->'."\n";
}


### Function: Displays Ratings Header In WP-Admin
add_action('admin_head', 'ratings_header_admin');
function ratings_header_admin() {	
	echo "\n".'<!-- Start Of Script Generated By WP-PostRatings 1.40 -->'."\n";
	echo '<script type="text/javascript">'."\n";
	echo '/* <![CDATA[ */'."\n";
	echo "\t".'var postratings_admin_ajax_url = \''.plugins_url('wp-postratings/postratings-admin-ajax.php')."';\n";
	echo '/* ]]> */'."\n";
	echo '</script>'."\n";
	wp_print_scripts(array('sack', 'wp-postratings-admin'));
	wp_print_styles('wp-postratings');
	echo '<!-- End Of Script Generated By WP-PostRatings 1.40 -->'."\n";
}


### Function: Display Ratings Results 
function the_ratings_results($post_id, $new_user = 0, $new_score = 0, $new_average = 0, $type = 0) {
	$ratings_image = get_option('postratings_image');
	$ratings_max = intval(get_option('postratings_max'));
	$postratings_custom = intval(get_option('postratings_customrating'));
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
	if(file_exists(WP_PLUGIN_DIR.'/wp-postratings/images/'.$ratings_image.'/rating_start.gif')) {
		$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_start.gif').'" alt="" class="post-ratings-image" />';
	}
	// Display Rated Images
	$post_ratings_score = intval($post_ratings_score);
	if($postratings_custom && $ratings_max == 2) {
		if($post_ratings_score > 0) {
			$post_ratings_score = '+'.$post_ratings_score;
		}
		$image_alt = sprintf(__ngettext('%s rating', '%s rating', $post_ratings_score, 'wp-postratings'), $post_ratings_score).', '.sprintf(__ngettext('%s vote', '%s votes', number_format_i18n($post_ratings_users), 'wp-postratings'), number_format_i18n($post_ratings_users));
	} else {
		$image_alt = sprintf(__ngettext('%s vote', '%s votes', number_format_i18n($post_ratings_users), 'wp-postratings'), number_format_i18n($post_ratings_users)).', '.__('average', 'wp-postratings').': '.$post_ratings_average.' '.__('out of', 'wp-postratings').' '.$ratings_max;
	}
	if($postratings_custom) {
		for($i=1; $i <= $ratings_max; $i++) {
			if($i <= $post_ratings) {
				$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_on.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
			} elseif($i == $insert_half) {
				$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_half.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
			} else {
				$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_off.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
			}
		}
	} else {
		for($i=1; $i <= $ratings_max; $i++) {
			if($i <= $post_ratings) {
				$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_on.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
			} elseif($i == $insert_half) {
				$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_half.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
			} else {
				$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_off.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
			}
		}
	}
	// Display End Of Rating Image
	if(file_exists(WP_PLUGIN_DIR.'/wp-postratings/images/'.$ratings_image.'/rating_end.gif')) {
		$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_end.gif').'" alt="" class="post-ratings-image" />';
	}
	// Display The Contents
	if($type == 1) {
		$template_postratings_text = stripslashes(get_option('postratings_template_permission'));
	} else {
		$template_postratings_text = stripslashes(get_option('postratings_template_text'));
	}
	$template_postratings_text = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_MAX%", $ratings_max, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_SCORE%", $post_ratings_score, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_PERCENTAGE%", $post_ratings_percentage, $template_postratings_text);
	$template_postratings_text = str_replace("%RATINGS_USERS%", number_format_i18n($post_ratings_users), $template_postratings_text);
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
	$postratings_custom = intval(get_option('postratings_customrating'));
	// Display Start Of Rating Image
	if(file_exists(WP_PLUGIN_DIR.'/wp-postratings/images/'.$ratings_image.'/rating_start.gif')) {
		$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_start.gif').'" alt="" class="post-ratings-image" />';
	}
	// Display Rated Images
	$post_ratings_score = intval($post_ratings_score);
	if($postratings_custom && $ratings_max == 2) {
		if($post_ratings_score > 0) {
			$post_ratings_score = '+'.$post_ratings_score;
		}
	}
	if($postratings_custom) {
		for($i=1; $i <= $ratings_max; $i++) {
			$ratings_text = stripslashes($postratings_ratingstext[$i-1]);
			if($i <= $post_ratings) {
				$post_ratings_images .= '<img id="rating_'.$post_id.'_'.$i.'" src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_on.gif').'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$post_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$post_ratings.', '.$insert_half.');" onclick="rate_post();" onkeypress="rate_post();" style="cursor: pointer; border: 0px;" />';		
			} elseif($i == $insert_half) {
				$post_ratings_images .= '<img id="rating_'.$post_id.'_'.$i.'" src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_half.gif').'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$post_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$post_ratings.', '.$insert_half.');" onclick="rate_post();" onkeypress="rate_post();" style="cursor: pointer; border: 0px;" />';
			} else {
				$post_ratings_images .= '<img id="rating_'.$post_id.'_'.$i.'" src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_off.gif').'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$post_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$post_ratings.', '.$insert_half.');" onclick="rate_post();" onkeypress="rate_post();" style="cursor: pointer; border: 0px;" />';
			}
		}
	} else {
		for($i=1; $i <= $ratings_max; $i++) {
			$ratings_text = stripslashes($postratings_ratingstext[$i-1]);
			if($i <= $post_ratings) {
				$post_ratings_images .= '<img id="rating_'.$post_id.'_'.$i.'" src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_on.gif').'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$post_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$post_ratings.', '.$insert_half.');" onclick="rate_post();" onkeypress="rate_post();" style="cursor: pointer; border: 0px;" />';		
			} elseif($i == $insert_half) {
				$post_ratings_images .= '<img id="rating_'.$post_id.'_'.$i.'" src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_half.gif').'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$post_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$post_ratings.', '.$insert_half.');" onclick="rate_post();" onkeypress="rate_post();" style="cursor: pointer; border: 0px;" />';
			} else {
				$post_ratings_images .= '<img id="rating_'.$post_id.'_'.$i.'" src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_off.gif').'" alt="'.$ratings_text.'" title="'.$ratings_text.'" onmouseover="current_rating('.$post_id.', '.$i.', \''.$ratings_text.'\');" onmouseout="ratings_off('.$post_ratings.', '.$insert_half.');" onclick="rate_post();" onkeypress="rate_post();" style="cursor: pointer; border: 0px;" />';
			}
		}
	}
	// Display End Of Rating Image
	if(file_exists(WP_PLUGIN_DIR.'/wp-postratings/images/'.$ratings_image.'/rating_end.gif')) {
		$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_end.gif').'" alt="" class="post-ratings-image" />';
	}
	// Individual Post Ratings Text
	$post_ratings_text = '<span class="post-ratings-text" id="ratings_'.$post_id.'_text"></span>';

	// If No Ratings, Return No Ratings templae
	if($post_ratings_users == 0) {
		$template_postratings_none = stripslashes(get_option('postratings_template_none'));
		$template_postratings_none = str_replace("%RATINGS_IMAGES_VOTE%", $post_ratings_images, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_MAX%", $ratings_max, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_SCORE%", $post_ratings_score, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_TEXT%", $post_ratings_text, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_PERCENTAGE%", $post_ratings_percentage, $template_postratings_none);
		$template_postratings_none = str_replace("%RATINGS_USERS%", number_format_i18n($post_ratings_users), $template_postratings_none);
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
		$template_postratings_vote = str_replace("%RATINGS_USERS%", number_format_i18n($post_ratings_users), $template_postratings_vote);
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
	if(isset($_COOKIE["rated_$post_id"])) {
		return true;
	} else {
		return false;
	}
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


### Function: Get Comment Authors Ratings
add_action('loop_start', 'get_comment_authors_ratings');
function get_comment_authors_ratings() {
	global $wpdb, $id, $comment_authors_ratings;
	$comment_authors_ratings = array();
	$comment_authors_ratings_results = $wpdb->get_results("SELECT rating_username, rating_rating, rating_ip FROM $wpdb->ratings WHERE rating_postid = $id");
	if($comment_authors_ratings_results) {
		foreach($comment_authors_ratings_results as $comment_authors_ratings_result) {
			$comment_author = stripslashes($comment_authors_ratings_result->rating_username);
			$comment_authors_ratings[$comment_author] = $comment_authors_ratings_result->rating_rating;
			$comment_authors_ratings[$comment_authors_ratings_result->rating_ip] = $comment_authors_ratings_result->rating_rating;
		}
	}
}


### Function: Comment Author Ratings
function comment_author_ratings($comment_author_specific = '', $display = true) {
	global $comment_authors_ratings;
	$post_ratings_images = '';
	$ratings_image = get_option('postratings_image');
	$ratings_max = intval(get_option('postratings_max'));
	$postratings_custom = intval(get_option('postratings_customrating'));
	if(empty($comment_author_specific)) {
		$comment_author = get_comment_author();
	} else {
		$comment_author = $comment_author_specific;
	}
	$comment_author_rating = intval($comment_authors_ratings[$comment_author]);	
	if($comment_author_rating == 0) {
		$comment_author_rating = intval($comment_authors_ratings[get_comment_author_IP()]);
	}	
	if($comment_author_rating != 0) {
		// Display Start Of Rating Image
		if(file_exists(WP_PLUGIN_DIR.'/wp-postratings/images/'.$ratings_image.'/rating_start.gif')) {
			$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_start.gif').'" alt="" class="post-ratings-image" />';
		}
		// Display Rated Images
		if($postratings_custom && $ratings_max == 2) {
			if($comment_author_rating > 0) {
				$comment_author_rating = '+'.$comment_author_rating;
			}		
		}
		$image_alt = sprintf(__('%s gives a rating of %s', 'wp-postratings'), $comment_author, $comment_author_rating);
		// Display 
		if($postratings_custom && $ratings_max == 2) {
			if($comment_author_rating > 0) {
				$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_2_on.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
			} else {
				$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_1_on.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
			}
		} elseif($postratings_custom) {
			for($i=1; $i <= $ratings_max; $i++) {
				if($i <= $comment_author_rating) {
					$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_on.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
				} else {
					$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_off.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
				}
			}
		} else {
			for($i=1; $i <= $ratings_max; $i++) {
				if($i <= $comment_author_rating) {
					$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_on.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
				} else {
					$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_off.gif').'" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
				}
			}
		}
		// Display End Of Rating Image
		if(file_exists(WP_PLUGIN_DIR.'/wp-postratings/images/'.$ratings_image.'/rating_end.gif')) {
			$post_ratings_images .= '<img src="'.plugins_url('wp-postratings/images/'.$ratings_image.'/rating_end.gif').'" alt="" class="post-ratings-image" />';
		}
	}
	if($display) {
		echo $post_ratings_images;
	} else {
		return $post_ratings_images;
	}
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


### Function: Return All Images From A Rating Image Folder
function ratings_images_folder($folder_name) {
	$normal_images = array('rating_over.gif', 'rating_on.gif', 'rating_half.gif', 'rating_off.gif');
	$postratings_path = WP_PLUGIN_DIR.'/wp-postratings/images/'.$folder_name;
	$images_count_temp = 1;
	$images_count = 1;
	$count = 0;
	$rating['max'] = 0;
	$rating['custom'] = 0;
	$rating['images'] = array();
	if(is_dir($postratings_path)) {
		if($handle = @opendir($postratings_path)) {
			while (false !== ($filename = readdir($handle))) {  
				if ($filename != '.' && $filename != '..') {
					if(in_array($filename, $normal_images)) {
						$count++;
					} elseif(intval(substr($filename,7, -7)) > $rating['max']) {
						$rating['max'] = intval(substr($filename,7, -7));
					}
					$rating['images'][] = $filename;
					$images_count++;
				}
			}
			closedir($handle);
		}
	}
	if($count != sizeof($normal_images)) {
		$rating['custom'] = 1;
	}
	if($rating['max'] == 0) {
		$rating['max'] = intval(get_option('postratings_max'));
	}
	return $rating;
}


### Function: Add PostRatings To Post/Page Automatically
//add_action('the_content', 'add_ratings_to_content');
function add_ratings_to_content($content) {
	if (!is_feed() && get_option('postratings_add')) {
		$content .= the_ratings('div', 0, false);
	}
	return $content;
}


### Function: Short Code For Inserting Ratings Into Posts
add_shortcode('ratings', 'ratings_shortcode');
function ratings_shortcode($atts) {
	extract(shortcode_atts(array('id' => '0', 'results' => false), $atts));
	if(!is_feed()) {
		if($results) {
			return the_ratings_results($id);
		} else {
			return the_ratings('span', $id, false);
		}
	} else {
		return __('Note: There is a rating embedded within this post, please visit this post to rate it.', 'wp-postratings');
	}
}


### Function: Snippet Text
if(!function_exists('snippet_text')) {
	function snippet_text($text, $length = 0) {
		$text = html_entity_decode($text, ENT_QUOTES, get_option('blog_charset'));
		 if (strlen($text) > $length) {
			return htmlentities(substr($text,0,$length), ENT_COMPAT, get_option('blog_charset')).'...';
		 } else {
			return htmlentities($text, ENT_COMPAT, get_option('blog_charset'));
		 }
	}
}


### Function: Process Post Excerpt, For Some Reasons, The Default get_post_excerpt() Does Not Work As Expected
function ratings_post_excerpt($post_excerpt, $post_content, $post_password) {
	if(!empty($post_password)) {
		if(!isset($_COOKIE['wp-postpass_'.COOKIEHASH]) || $_COOKIE['wp-postpass_'.COOKIEHASH] != $post_password) {
			return __('There is no excerpt because this is a protected post.', 'wp-postratings');
		}
	}
	if(empty($post_excerpt)) {
		return snippet_text($post_content, 200);
	} else {
		return $post_excerpt;
	}
}


### Function: Add Rating Custom Fields
add_action('publish_post', 'add_ratings_fields');
function add_ratings_fields($post_ID) {
	global $wpdb;
	add_post_meta($post_ID, 'ratings_users', 0, true);
	add_post_meta($post_ID, 'ratings_score', 0, true);
	add_post_meta($post_ID, 'ratings_average', 0, true);	
}


### Function:Delete Rating Custom Fields
add_action('delete_post', 'delete_ratings_fields');
function delete_ratings_fields($post_ID) {
	global $wpdb;
	delete_post_meta($post_ID, 'ratings_users');
	delete_post_meta($post_ID, 'ratings_score');
	delete_post_meta($post_ID, 'ratings_average');	
}


### Function: Process Ratings
process_ratings();
function process_ratings() {
	global $wpdb, $user_identity, $user_ID;
	$ratings_max = intval(get_option('postratings_max'));
	$ratings_custom = intval(get_option('postratings_customrating'));
	$ratings_value = get_option('postratings_ratingsvalue');
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
				$post_ratings_users = ($post_ratings_users+1);
				$post_ratings_score = ($post_ratings_score+intval($ratings_value[$rate-1]));
				$post_ratings_average = round($post_ratings_score/$post_ratings_users, 2);
				if (!update_post_meta($post_id, 'ratings_users', $post_ratings_users)) {
					add_post_meta($post_id, 'ratings_users', $post_ratings_users, true);
				}
				if(!update_post_meta($post_id, 'ratings_score', $post_ratings_score)) {
					add_post_meta($post_id, 'ratings_score', $post_ratings_score, true);
				}
				if(!update_post_meta($post_id, 'ratings_average', $post_ratings_average)) {
					add_post_meta($post_id, 'ratings_average',$post_ratings_average, true);	
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
					$rate_cookie = setcookie("rated_".$post_id, $ratings_value[$rate-1], time() + 30000000, COOKIEPATH);
				}
				// Log Ratings No Matter What
				$rate_log = $wpdb->query("INSERT INTO $wpdb->ratings VALUES (0, $post_id, '$post_title', ".$ratings_value[$rate-1].",'".current_time('timestamp')."', '".get_ipaddress()."', '".@gethostbyaddr(get_ipaddress())."' ,'$rate_user', $rate_userid)");
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
	$orderby = trim(addslashes(get_query_var('r_orderby')));
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
	$ratings_max = intval(get_option('postratings_max'));
	$ratings_custom = intval(get_option('postratings_customrating'));
	if($ratings_custom && $ratings_max == 2) {
		$content .= " AND t1.meta_key = 'ratings_score' AND t2.meta_key = 'ratings_users'";
	} else {
		$content .= " AND t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users'";
	}	
	return $content;
}
function ratings_highest_orderby($content) {
	$orderby = trim(addslashes(get_query_var('r_orderby')));
	if(empty($orderby) || ($orderby != 'asc' && $orderby != 'desc')) {
		$orderby = 'desc';
	}
	$content = " ratings_average $orderby, ratings_users $orderby";
	return $content;
}


### Function: Ratings Public Variables
add_filter('query_vars', 'ratings_variables');
function ratings_variables($public_query_vars) {
	$public_query_vars[] = 'r_sortby';
	$public_query_vars[] = 'r_orderby';
	return $public_query_vars;
}


### Function: Sort Ratings Posts
add_action('pre_get_posts', 'ratings_sorting');
function ratings_sorting() {
	if(get_query_var('r_sortby') == 'most_rated') {
		add_filter('posts_fields', 'ratings_most_fields');
		add_filter('posts_join', 'ratings_most_join');
		add_filter('posts_where', 'ratings_most_where');
		add_filter('posts_orderby', 'ratings_most_orderby');
	} elseif(get_query_var('r_sortby') == 'highest_rated') {
		add_filter('posts_fields', 'ratings_highest_fields');
		add_filter('posts_join', 'ratings_highest_join');
		add_filter('posts_where', 'ratings_highest_where');
		add_filter('posts_orderby', 'ratings_highest_orderby');
	}
}


### Function: Plug Into WP-Stats
if(strpos(get_option('stats_url'), $_SERVER['REQUEST_URI']) || strpos($_SERVER['REQUEST_URI'], 'stats-options.php') || strpos($_SERVER['REQUEST_URI'], 'wp-stats/wp-stats.php')) {
	add_filter('wp_stats_page_admin_plugins', 'postratings_page_admin_general_stats');
	add_filter('wp_stats_page_admin_most', 'postratings_page_admin_most_stats');
	add_filter('wp_stats_page_plugins', 'postratings_page_general_stats');
	add_filter('wp_stats_page_most', 'postratings_page_most_stats');
}


### Function: Add WP-PostRatings General Stats To WP-Stats Page Options
function postratings_page_admin_general_stats($content) {
	$stats_display = get_option('stats_display');
	if($stats_display['ratings'] == 1) {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_ratings" value="ratings" checked="checked" />&nbsp;&nbsp;<label for="wpstats_ratings">'.__('WP-PostRatings', 'wp-postratings').'</label><br />'."\n";
	} else {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_ratings" value="ratings" />&nbsp;&nbsp;<label for="wpstats_ratings">'.__('WP-PostRatings', 'wp-postratings').'</label><br />'."\n";
	}
	return $content;
}


### Function: Add WP-PostRatings Top Most/Highest Stats To WP-Stats Page Options
function postratings_page_admin_most_stats($content) {
	$stats_display = get_option('stats_display');
	$stats_mostlimit = intval(get_option('stats_mostlimit'));
	if($stats_display['rated_highest'] == 1) {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_rated_highest" value="rated_highest" checked="checked" />&nbsp;&nbsp;<label for="wpstats_rated_highest">'.sprintf(__ngettext('%s Highest Rated Post', '%s Highest Rated Posts', $stats_mostlimit, 'wp-postratings'), $stats_mostlimit).'</label><br />'."\n";
	} else {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_rated_highest" value="rated_highest" />&nbsp;&nbsp;<label for="wpstats_rated_highest">'.sprintf(__ngettext('%s Highest Rated Post', '%s Highest Rated Posts', $stats_mostlimit, 'wp-postratings'), $stats_mostlimit).'</label><br />'."\n";
	}
	if($stats_display['rated_most'] == 1) {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_rated_most" value="rated_most" checked="checked" />&nbsp;&nbsp;<label for="wpstats_rated_most">'.sprintf(__ngettext('%s Most Rated Post', '%s Most Rated Posts', $stats_mostlimit, 'wp-postratings'), $stats_mostlimit).'</label><br />'."\n";
	} else {
		$content .= '<input type="checkbox" name="stats_display[]" id="wpstats_rated_most" value="rated_most" />&nbsp;&nbsp;<label for="wpstats_rated_most">'.sprintf(__ngettext('%s Most Rated Post', '%s Most Rated Posts', $stats_mostlimit, 'wp-postratings'), $stats_mostlimit).'</label><br />'."\n";
	}
	return $content;
}


### Function: Add WP-PostRatings General Stats To WP-Stats Page
function postratings_page_general_stats($content) {
	$stats_display = get_option('stats_display');
	if($stats_display['ratings'] == 1) {
		$content .= '<p><strong>'.__('WP-PostRatings', 'wp-postratings').'</strong></p>'."\n";
		$content .= '<ul>'."\n";
		$content .= '<li>'.sprintf(__ngettext('<strong>%s</strong> user casted his vote.', '<strong>%s</strong> users casted their vote.', get_ratings_users(false), 'wp-postratings'), get_ratings_users(false)).'</li>'."\n";
		$content .= '</ul>'."\n";
	}
	return $content;
}


### Function: Add WP-PostRatings Top Most/Highest Stats To WP-Stats Page
function postratings_page_most_stats($content) {
	$stats_display = get_option('stats_display');
	$stats_mostlimit = intval(get_option('stats_mostlimit'));
	if($stats_display['rated_highest'] == 1) {
		$content .= '<p><strong>'.sprintf(__ngettext('%s Highest Rated Post', '%s Highest Rated Posts', $stats_mostlimit, 'wp-postratings'), $stats_mostlimit).'</strong></p>'."\n";
		$content .= '<ul>'."\n";
		$content .= get_highest_rated('post', 0, $stats_mostlimit, 0, false);
		$content .= '</ul>'."\n";
	}
	if($stats_display['rated_most'] == 1) {
		$content .= '<p><strong>'.sprintf(__ngettext('%s Most Rated Post', '%s Most Rated Posts', $stats_mostlimit, 'wp-postratings'), $stats_mostlimit).'</strong></p>'."\n";
		$content .= '<ul>'."\n";
		$content .= get_most_rated('post', 0, $stats_mostlimit, 0, false);
		$content .= '</ul>'."\n";
	}
	return $content;
}


### Function: Create Rating Logs Table
add_action('activate_wp-postratings/wp-postratings.php', 'create_ratinglogs_table');
function create_ratinglogs_table() {
	global $wpdb;
	if(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} else {
		die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
	}
	$charset_collate = '';
	if($wpdb->supports_collation()) {
		if(!empty($wpdb->charset)) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if(!empty($wpdb->collate)) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}
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
			"PRIMARY KEY (rating_id)) $charset_collate;";
	maybe_create_table($wpdb->ratings, $create_ratinglogs_sql);
	// Add In Options (4 Records)
	add_option('postratings_image', 'stars', 'Your Ratings Image');
	add_option('postratings_max', '5', 'Your Max Ratings');
	add_option('postratings_template_vote', '%RATINGS_IMAGES_VOTE% (<strong>%RATINGS_USERS%</strong> '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': <strong>%RATINGS_AVERAGE%</strong> '.__('out of', 'wp-postratings').' %RATINGS_MAX%)<br />%RATINGS_TEXT%', 'Ratings Vote Template Text');
	add_option('postratings_template_text', '%RATINGS_IMAGES% (<em><strong>%RATINGS_USERS%</strong> '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': <strong>%RATINGS_AVERAGE%</strong> '.__('out of', 'wp-postratings').' %RATINGS_MAX%, <strong>'.__('rated', 'wp-postratings').'</strong></em>)', 'Ratings Template Text');
	add_option('postratings_template_none', '%RATINGS_IMAGES_VOTE% ('.__('No Ratings Yet', 'wp-postratings').')<br />%RATINGS_TEXT%', 'Ratings Template For No Ratings');
	// Database Upgrade For WP-PostRatings 1.02
	add_option('postratings_logging_method', '3', 'Logging Method Of User Rated\'s Answer');
	add_option('postratings_allowtorate', '2', 'Who Is Allowed To Rate');
	// Database Uprade For WP-PostRatings 1.04	
	maybe_add_column($wpdb->ratings, 'rating_userid', "ALTER TABLE $wpdb->ratings ADD rating_userid INT( 10 ) NOT NULL DEFAULT '0';");
	// Database Uprade For WP-PostRatings 1.05
	add_option('postratings_ratingstext', array(__('1 Star', 'wp-postratings'), __('2 Stars', 'wp-postratings'), __('3 Stars', 'wp-postratings'), __('4 Stars', 'wp-postratings'), __('5 Stars', 'wp-postratings')), 'Individual Post Rating Text');
	add_option('postratings_template_highestrated', '<li><a href="%POST_URL%" title="%POST_TITLE%">%POST_TITLE%</a> %RATINGS_IMAGES% (%RATINGS_AVERAGE% '.__('out of', 'wp-postratings').' %RATINGS_MAX%)</li>', 'Template For Highest Rated');
	// Database Upgrade For WP-PostRatings 1.11
	add_option('postratings_ajax_style', array('loading' => 1, 'fading' => 1), 'Ratings AJAX Style');
	// Database Upgrade For WP-PostRatings 1.20
	add_option('postratings_ratingsvalue', array(1,2,3,4,5), 'Individual Post Rating Value');
	add_option('postratings_customrating', 0, 'Use Custom Ratings');
	add_option('postratings_template_permission', '%RATINGS_IMAGES% (<em><strong>%RATINGS_USERS%</strong> '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': <strong>%RATINGS_AVERAGE%</strong> '.__('out of', 'wp-postratings').' %RATINGS_MAX%</em>)<br /><em>'.__('You need to be a registered member to rate this post.', 'wp-postratings').'</em>', 'Ratings Template Text');
	// Database Upgrade For WP-PostRatings 1.30
	add_option('postratings_template_mostrated', '<li><a href="%POST_URL%"  title="%POST_TITLE%">%POST_TITLE%</a> - %RATINGS_USERS% '.__('votes', 'wp-postratings').'</li>', 'Most Rated Template Text');
	// Set 'manage_ratings' Capabilities To Administrator	
	$role = get_role('administrator');
	if(!$role->has_cap('manage_ratings')) {
		$role->add_cap('manage_ratings');
	}
}


### Seperate PostRatings Stats For Readability
require_once('postratings-stats.php');
?>