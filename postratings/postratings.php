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
$wpdb->ratinglogs = $table_prefix . 'ratinglogs';


### Use Bars, Stars Or Squares Image For The Ratings
$ratings_image = 'stars'; // bars, stars or squares
$ratings_max = 5;


### Function: Display The Rating For The Post
function the_ratings($text_start = '<p>', $text_end = '</p>', $display_text = true, $display = true) {
	global $id, $ratings_image, $ratings_max;
	$post_ratings_users = the_ratings_users($id, false);
	$post_ratings_score = the_ratings_score($id, false);
	$post_ratings_average = the_ratings_average($id, false);
	if($post_ratings_score == 0 || $post_ratings_users == 0) {
		$post_ratings = 0;
		$post_ratings_average = 0;
	} else {
		$post_ratings = round($post_ratings_average, 1);
		
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
	if($display) {
		// No Ratings
		if($post_ratings == 0 && $post_ratings == 0) {
			for($i=1; $i <= $ratings_max; $i++) {
				echo '<img src="'.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_none.gif" alt="'.$post_ratings_users.__(' Votes | Average: ').$post_ratings_average.__(' out of ').$ratings_max.'" />';
			}
		} else {
			// Display Start Of Rating Image
			if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif')) {
				echo '<img src="'.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif" alt="" />';
			}
			// Display Rated Images
			for($i=1; $i <= $ratings_max; $i++) {
				if($i <= $post_ratings) {
					echo '<img src="'.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$post_ratings_users.__(' Votes | Average: ').$post_ratings_average.__(' out of ').$ratings_max.'" />';		
				} elseif($i == $insert_half) {
					echo '<img src="'.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$post_ratings_users.__(' Votes | Average: ').$post_ratings_average.__(' out of ').$ratings_max.'" />';
				} else {
					echo '<img src="'.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$post_ratings_users.__(' Votes | Average: ').$post_ratings_average.__(' out of ').$ratings_max.'" />';
				}
			}
			// Display End Of Rating Image
			if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif')) {
				echo '<img src="'.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif" alt="" />';
			}
		}
		if($display_text) {
			echo $text_start.'<b>'.$post_ratings_users.'</b>'.__(' votes, average: ').'<b>'.$post_ratings_average.'</b>'.__(' out of ').$ratings_max.$text_end;
		}
	} else {
		return $post_ratings;
	}
}


### Function: Displays Rating Vote Javascript
add_action('wp_head', 'the_ratings_vote_js');
function the_ratings_vote_js() {
	global $ratings_image, $ratings_max;
	echo '<script language="JavaScript" type="text/javascript">'."\n";
	echo 'function current_rating(rating) {'."\n";
	echo 'for(i = 1; i <= rating; i++) {'."\n";
	echo 'document.images[\'rating_\' + i].src = \''.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_on.gif\';'."\n";	
	echo '}}'."\n";
	echo 'function ratings_off() {'."\n";
	for($i=1; $i <= $ratings_max; $i++) {
		echo 'document.images[\'rating_'.$i.'\'].src = \''.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif\';'."\n";
	}
	echo '}'."\n";
	echo "</script>";
}


### Function: Display Ratings Vote
function the_ratings_vote() {
	global $id, $ratings_image, $ratings_max;
	$rated = check_rated($id);
	if(!$rated) {
		$rating_url = get_permalink();
		if(strpos($rating_url, '?') !== false) {
			$rating_url = $rating_url.'&amp;pid='.$id.'&amp;';
		} else {
			$rating_url = $rating_url.'?pid='.$id.'&amp;';
		}

		// Display Start Of Rating Image
		if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif')) {
			echo '<img src="'.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_start.gif" alt="" />';
		}
		// Display Rating Image
		for($i=1; $i <= $ratings_max; $i++) {
		echo '<a href="'.$rating_url.'rate='.$i.'" title="'.$i.' Stars"><img src="'.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$i.' Stars" border="0" name="rating_'.$i.'" onmouseover="current_rating('.$i.');" onmouseout="ratings_off();" /></a>';
		}
		// Display End Of Rating Image
		if(file_exists(ABSPATH.'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif')) {
			echo '<img src="'.get_settings('home').'/wp-content/plugins/postratings/images/'.$ratings_image.'/rating_end.gif" alt="" />';
		}
	} else {
		the_ratings();
	}
}


### Function: Process Ratings
add_action('init', 'process_ratings');
function process_ratings() {
	global $wpdb, $user_identity, $ratings_max;
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
				$post_ratings_users = the_ratings_users($post_id, false);
				$post_ratings_score = the_ratings_score($post_id, false);				
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
				$rate_log = $wpdb->query("INSERT INTO $wpdb->ratinglogs VALUES (0, $post_id, $rate,'".current_time('timestamp')."', '".get_ipaddress()."', '$rate_user')");
				// Add Cookie
				$rate_cookie = setcookie("rated_".$post_id, 1, time() + 30000000, COOKIEPATH);
			}
		}
	}
}


### Function: Displays The Total Users That Have Rated For The Post
function the_ratings_users($post_id, $display = true) {
	$post_ratings_users = intval(get_post_meta($post_id, 'ratings_users', true));
	if($display) {
		echo $post_ratings_users;
	} else {
		return $post_ratings_users;
	}
}


### Function: Displays The Total Score For The Post
function the_ratings_score($post_id, $display = true) {
	$post_ratings_score = intval(get_post_meta($post_id, 'ratings_score', true));
	if($display) {
		echo $post_ratings_score;
	} else {
		return $post_ratings_score;
	}
}


### Function: Displays The Average Score For The Post
function the_ratings_average($post_id, $display = true) {
	$post_ratings_average = get_post_meta($post_id, 'ratings_average', true);
	if($display) {
		echo $post_ratings_average;
	} else {
		return $post_ratings_average;
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
	$get_rated = $wpdb->get_var("SELECT rating_ip FROM $wpdb->ratinglogs WHERE rating_postid = $post_id AND rating_ip = '".get_ipaddress()."'");
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


### Function: Display Most Rated Page/Post
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
	$most_rated = $wpdb->get_results("SELECT wp_posts.ID, post_title, post_name, post_status, post_date, CAST(meta_value AS UNSIGNED) AS votes FROM wp_posts LEFT JOIN wp_postmeta ON wp_postmeta.post_id = wp_posts.ID WHERE post_date < '".current_time('mysql')."' AND $where AND meta_key = 'ratings_users' AND post_password = '' ORDER BY votes DESC LIMIT $limit");
	if($most_rated) {
		foreach ($most_rated as $post) {
				$post_title = htmlspecialchars(stripslashes($post->post_title));
				$post_views = intval($post->votes);
				echo "- <a href=\"".get_permalink()."\">$post_title</a> ($post_views ".__('Votes').")<br />";
		}
	} else {
		_e('N/A');
	}
}


### Function: Display Highest Rated Page/Post
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
	$most_rated = $wpdb->get_results("SELECT wp_posts.ID, post_title, post_name, post_status, post_date, (meta_value+0.00) AS highest FROM wp_posts LEFT JOIN wp_postmeta ON wp_postmeta.post_id = wp_posts.ID WHERE post_date < '".current_time('mysql')."' AND $where AND meta_key = 'ratings_average' AND post_password = '' ORDER BY highest DESC LIMIT $limit");
	if($most_rated) {
		foreach ($most_rated as $post) {
				$post_title = htmlspecialchars(stripslashes($post->post_title));
				$post_views = $post->highest;
				echo "- <a href=\"".get_permalink()."\">$post_title</a> ($post_views ".__('Score').")<br />";
		}
	} else {
		_e('N/A');
	}
}


### Function: Create Rating Logs Table
add_action('activate_postratings/postratings.php', 'create_ratinglogs_table');
function create_ratinglogs_table() {
	global $wpdb;
	include(ABSPATH.'/wp-admin/upgrade-functions.php');
	$create_ratinglogs_sql = "CREATE TABLE $wpdb->ratinglogs (".
			"rating_id INT(11) NOT NULL auto_increment,".
			"rating_postid INT(11) NOT NULL ,".
			"rating_rating INT(2) NOT NULL ,".
			"rating_timestamp VARCHAR(15) NOT NULL ,".
			"rating_ip VARCHAR(40) NOT NULL ,".
			"rating_username VARCHAR(50) NOT NULL,".
			"PRIMARY KEY (rating_id))";
	maybe_create_table($wpdb->ratinglogs, $create_ratinglogs_sql);
}
?>