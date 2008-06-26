<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.5 Plugin: WP-PostRatings 1.31								|
|	Copyright (c) 2008 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- Containts Post Rating Stats	 												|
|	- wp-content/plugins/wp-postratings/postratings-stats.php			|
|																							|
+----------------------------------------------------------------+
*/


### Function: Display Most Rated Page/Post
if(!function_exists('get_most_rated')) {
	function get_most_rated($mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$where = '';
		$temp = '';
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$most_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users FROM $wpdb->posts LEFT JOIN $wpdb->postmeta AS t1 ON t1.post_id = $wpdb->posts.ID LEFT JOIN $wpdb->postmeta As t2 ON t1.post_id = t2.post_id WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."' AND $wpdb->posts.post_status = 'publish' AND t2.meta_value >= $min_votes AND $where ORDER BY ratings_users DESC, $order_by DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $post) {
				$post_ratings_users = number_format_i18n($post->ratings_users);
				$post_ratings_average = $post->ratings_average;
				$post_title = get_the_title();
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				if($chars > 0) {
					$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> - $post_ratings_users ".__('votes', 'wp-postratings')."</li>\n";
				} else {
					$temp = stripslashes(get_option('postratings_template_mostrated'));
					$temp = str_replace("%RATINGS_USERS%", $post_ratings_users, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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


### Function: Display Most Rated Page/Post By Category ID
if(!function_exists('get_most_rated_category')) {
	function get_most_rated_category($category_id = 0, $mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$where = '';
		$temp = '';
		$output = '';
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$most_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users FROM $wpdb->posts LEFT JOIN $wpdb->postmeta AS t1 ON t1.post_id = $wpdb->posts.ID LEFT JOIN $wpdb->postmeta As t2 ON t1.post_id = t2.post_id INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."' AND $wpdb->posts.post_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND t2.meta_value >= $min_votes AND $where ORDER BY ratings_users DESC, $order_by DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $post) {
				$post_ratings_users = number_format_i18n($post->ratings_users);
				$post_ratings_average = $post->ratings_average;
				$post_title = get_the_title();
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				if($chars > 0) {
					$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> - $post_ratings_users ".__('votes', 'wp-postratings')."</li>\n";
				} else {
					$temp = stripslashes(get_option('postratings_template_mostrated'));
					$temp = str_replace("%RATINGS_USERS%", $post_ratings_users, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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


### Function: Display Most Rated Page/Post With Time Range
if(!function_exists('get_most_rated_range')) {
	function get_most_rated_range($time = '1 day', $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$where = '';
		$temp = '';
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$most_rated = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_postid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_postid))), 2) AS ratings_average, $wpdb->posts.* FROM $wpdb->posts LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_postid = $wpdb->posts.ID WHERE rating_timestamp >= $min_time AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."'  AND $wpdb->posts.post_status = 'publish' AND $where GROUP BY $wpdb->ratings.rating_postid ORDER BY ratings_users DESC, $order_by DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $post) {
				$post_ratings_users = number_format_i18n($post->ratings_users);
				$post_ratings_average = $post->ratings_average;
				$post_ratings_score = $post->ratings_score;
				$post_title = get_the_title();
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				if($chars > 0) {
					$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> - $post_ratings_users ".__('votes', 'wp-postratings')."</li>\n";
				} else {
					$temp = stripslashes(get_option('postratings_template_mostrated'));
					$temp = str_replace("%RATINGS_USERS%", $post_ratings_users, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_SCORE%", $post_ratings_score, $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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


### Function: Display Most Rated Page/Post With Time Range By Category ID
if(!function_exists('get_most_rated_range_category')) {
	function get_most_rated_range_category($time = '1 day', $category_id = 0, $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$where = '';
		$temp = '';
		$output = '';
		if(is_array($category_id)) {
			// There is a bug with multiple categoies. The number of votes will be multiplied by the number of categories passed in.
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$most_rated = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_postid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_postid))), 2) AS ratings_average, $wpdb->posts.* FROM $wpdb->posts LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_postid = $wpdb->posts.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE rating_timestamp >= $min_time AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."'  AND $wpdb->posts.post_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND $where GROUP BY $wpdb->ratings.rating_postid ORDER BY ratings_users DESC, $order_by DESC LIMIT $limit");
		if($most_rated) {
			foreach ($most_rated as $post) {
				$post_ratings_users = number_format_i18n($post->ratings_users);
				$post_ratings_average = $post->ratings_average;
				$post_ratings_score = $post->ratings_score;
				$post_title = get_the_title();
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				if($chars > 0) {
					$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> - $post_ratings_users ".__('votes', 'wp-postratings')."</li>\n";
				} else {
					$temp = stripslashes(get_option('postratings_template_mostrated'));
					$temp = str_replace("%RATINGS_USERS%", $post_ratings_users, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_SCORE%", $post_ratings_score, $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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
	function get_highest_rated($mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$temp_post = $post;
		$ratings_image = get_option('postratings_image');
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$where = '';
		$temp = '';
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$highest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->posts LEFT JOIN $wpdb->postmeta AS t1 ON t1.post_id = $wpdb->posts.ID LEFT JOIN $wpdb->postmeta As t2 ON t1.post_id = t2.post_id LEFT JOIN $wpdb->postmeta AS t3 ON t3.post_id = $wpdb->posts.ID WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."' AND $wpdb->posts.post_status = 'publish' AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $post) {
				// Variables
				$post_ratings_users = $post->ratings_users;
				$post_ratings_images = '';
				$post_title = get_the_title();
				$post_ratings_average = $post->ratings_average;
				$post_ratings_score = $post->ratings_score;
				$post_ratings_whole = intval($post_ratings_average);
				$post_ratings = floor($post_ratings_average);
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				// Check For Half Star
				$insert_half = 0;
				$average_diff = $post_ratings_average-$post_ratings_whole;
				if($average_diff >= 0.25 && $average_diff <= 0.75) {
					$insert_half = $post_ratings_whole+1;
				} elseif($average_diff > 0.75) {
					$post_ratings = $post_ratings+1;
				}
				if($ratings_custom && $ratings_max == 2) {
					if($post_ratings_score > 0) {
						$post_ratings_score = '+'.$post_ratings_score;
					}
					$image_alt = $post_ratings_score.' '.__('rating', 'wp-postratings');
				} else {
					$image_alt = $post_ratings_users.' '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': '.$post_ratings_average.' '.__('out of', 'wp-postratings').' '.$ratings_max;
				}
				// Display Start Of Rating Images
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
				}
				if(!$ratings_custom) { 
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				} else {
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				}
				// Display End Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
				}
				if($chars > 0) {
					if($ratings_custom && $ratings_max == 2) {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$image_alt."</li>\n";
					} else {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$post_ratings_images."</li>\n";
					}
				} else {
					// Display The Contents
					$temp = stripslashes(get_option('postratings_template_highestrated'));
					$temp = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $temp);
					$temp = str_replace("%RATINGS_MAX%", $ratings_max, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_SCORE%", $post_ratings_score, $temp);
					$temp = str_replace("%RATINGS_USERS%", number_format_i18n($post_ratings_users), $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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


### Function: Display Highest Rated Page/Post By Category ID
if(!function_exists('get_highest_rated_category')) {
	function get_highest_rated_category($category_id = 0, $mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$ratings_image = get_option('postratings_image');
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$where = '';
		$temp = '';
		$output = '';
		// Code By: Dirceu P. Junior (http://pomoti.com)
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$highest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->posts LEFT JOIN $wpdb->postmeta AS t1 ON t1.post_id = $wpdb->posts.ID LEFT JOIN $wpdb->postmeta AS t2 ON t1.post_id = t2.post_id LEFT JOIN $wpdb->postmeta AS t3 ON t3.post_id = $wpdb->posts.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."'  AND $wpdb->posts.post_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $post) {
				// Variables
				$post_ratings_users = $post->ratings_users;
				$post_ratings_images = '';
				$post_title = get_the_title();
				$post_ratings_average = $post->ratings_average;
				$post_ratings_score = $post->ratings_score;
				$post_ratings_whole = intval($post_ratings_average);
				$post_ratings = floor($post_ratings_average);
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				// Check For Half Star
				$insert_half = 0;
				$average_diff = $post_ratings_average-$post_ratings_whole;
				if($average_diff >= 0.25 && $average_diff <= 0.75) {
					$insert_half = $post_ratings_whole+1;
				} elseif($average_diff > 0.75) {
					$post_ratings = $post_ratings+1;
				}
				if($ratings_custom && $ratings_max == 2) {
					if($post_ratings_score > 0) {
						$post_ratings_score = '+'.$post_ratings_score;
					}
					$image_alt = $post_ratings_score.' '.__('rating', 'wp-postratings');
				} else {
					$image_alt = $post_ratings_users.' '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': '.$post_ratings_average.' '.__('out of', 'wp-postratings').' '.$ratings_max;
				}
				// Display Start Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
				}
				if(!$ratings_custom) { 
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				} else {
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				}
				// Display End Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
				}
				if($chars > 0) {
					if($ratings_custom && $ratings_max == 2) {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$image_alt."</li>\n";
					} else {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$post_ratings_images."</li>\n";
					}
				} else {
					// Display The Contents
					$temp = stripslashes(get_option('postratings_template_highestrated'));
					$temp = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $temp);
					$temp = str_replace("%RATINGS_MAX%", $ratings_max, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_SCORE%", $post_ratings_score, $temp);
					$temp = str_replace("%RATINGS_USERS%", number_format_i18n($post_ratings_users), $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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


### Function: Display Highest Rated Page/Post With Time Range
if(!function_exists('get_highest_rated_range')) {
	function get_highest_rated_range($time = '1 day', $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$temp_post = $post;
		$ratings_image = get_option('postratings_image');
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$where = '';
		$temp = '';
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$highest_rated = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_postid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_postid))), 2) AS ratings_average, $wpdb->posts.* FROM $wpdb->posts LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_postid = $wpdb->posts.ID WHERE rating_timestamp >= $min_time AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."'  AND $wpdb->posts.post_status = 'publish' AND $where GROUP BY $wpdb->ratings.rating_postid ORDER BY $order_by DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $post) {
				// Variables
				$post_ratings_users = $post->ratings_users;
				$post_ratings_images = '';
				$post_title = get_the_title();
				$post_ratings_average = $post->ratings_average;
				$post_ratings_score = $post->ratings_score;
				$post_ratings_whole = intval($post_ratings_average);
				$post_ratings = floor($post_ratings_average);
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				// Check For Half Star
				$insert_half = 0;
				$average_diff = $post_ratings_average-$post_ratings_whole;
				if($average_diff >= 0.25 && $average_diff <= 0.75) {
					$insert_half = $post_ratings_whole+1;
				} elseif($average_diff > 0.75) {
					$post_ratings = $post_ratings+1;
				}
				if($ratings_custom && $ratings_max == 2) {
					if($post_ratings_score > 0) {
						$post_ratings_score = '+'.$post_ratings_score;
					}
					$image_alt = $post_ratings_score.' '.__('rating', 'wp-postratings');
				} else {
					$image_alt = $post_ratings_users.' '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': '.$post_ratings_average.' '.__('out of', 'wp-postratings').' '.$ratings_max;
				}
				// Display Start Of Rating Images
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
				}
				if(!$ratings_custom) { 
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				} else {
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				}
				// Display End Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
				}
				if($chars > 0) {
					if($ratings_custom && $ratings_max == 2) {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$image_alt."</li>\n";
					} else {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$post_ratings_images."</li>\n";
					}
				} else {
					// Display The Contents
					$temp = stripslashes(get_option('postratings_template_highestrated'));
					$temp = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $temp);
					$temp = str_replace("%RATINGS_MAX%", $ratings_max, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_SCORE%", $post_ratings_score, $temp);
					$temp = str_replace("%RATINGS_USERS%", number_format_i18n($post_ratings_users), $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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


### Function: Display Highest Rated Page/Post With Time Range By Category ID
if(!function_exists('get_highest_rated_range_category')) {
	function get_highest_rated_range_category($time = '1 day', $category_id = 0, $mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$ratings_image = get_option('postratings_image');
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$min_time = strtotime('-'.$time, current_time('timestamp')); 
		$where = '';
		$temp = '';
		$output = '';
		// Code By: Dirceu P. Junior (http://pomoti.com)
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$highest_rated = $wpdb->get_results("SELECT COUNT($wpdb->ratings.rating_postid) AS ratings_users, SUM($wpdb->ratings.rating_rating) AS ratings_score, ROUND(((SUM($wpdb->ratings.rating_rating)/COUNT($wpdb->ratings.rating_postid))), 2) AS ratings_average, $wpdb->posts.* FROM $wpdb->posts LEFT JOIN $wpdb->ratings ON $wpdb->ratings.rating_postid = $wpdb->posts.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE rating_timestamp >= $min_time AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."'  AND $wpdb->posts.post_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND $where GROUP BY $wpdb->ratings.rating_postid ORDER BY $order_by DESC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $post) {
				// Variables
				$post_ratings_users = $post->ratings_users;
				$post_ratings_images = '';
				$post_title = get_the_title();
				$post_ratings_average = $post->ratings_average;
				$post_ratings_score = $post->ratings_score;
				$post_ratings_whole = intval($post_ratings_average);
				$post_ratings = floor($post_ratings_average);
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				// Check For Half Star
				$insert_half = 0;
				$average_diff = $post_ratings_average-$post_ratings_whole;
				if($average_diff >= 0.25 && $average_diff <= 0.75) {
					$insert_half = $post_ratings_whole+1;
				} elseif($average_diff > 0.75) {
					$post_ratings = $post_ratings+1;
				}
				if($ratings_custom && $ratings_max == 2) {
					if($post_ratings_score > 0) {
						$post_ratings_score = '+'.$post_ratings_score;
					}
					$image_alt = $post_ratings_score.' '.__('rating', 'wp-postratings');
				} else {
					$image_alt = $post_ratings_users.' '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': '.$post_ratings_average.' '.__('out of', 'wp-postratings').' '.$ratings_max;
				}
				// Display Start Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
				}
				if(!$ratings_custom) { 
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				} else {
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				}
				// Display End Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
				}
				if($chars > 0) {
					if($ratings_custom && $ratings_max == 2) {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$image_alt."</li>\n";
					} else {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$post_ratings_images."</li>\n";
					}
				} else {
					// Display The Contents
					$temp = stripslashes(get_option('postratings_template_highestrated'));
					$temp = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $temp);
					$temp = str_replace("%RATINGS_MAX%", $ratings_max, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_SCORE%", $post_ratings_score, $temp);
					$temp = str_replace("%RATINGS_USERS%", number_format_i18n($post_ratings_users), $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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


### Function: Display Lowest Rated Page/Post
if(!function_exists('get_lowest_rated')) {
	function get_lowest_rated($mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$temp_post = $post;
		$ratings_image = get_option('postratings_image');
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$where = '';
		$temp = '';
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$highest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->posts LEFT JOIN $wpdb->postmeta AS t1 ON t1.post_id = $wpdb->posts.ID LEFT JOIN $wpdb->postmeta As t2 ON t1.post_id = t2.post_id LEFT JOIN $wpdb->postmeta AS t3 ON t3.post_id = $wpdb->posts.ID WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."' AND $wpdb->posts.post_status = 'publish' AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by ASC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $post) {
				// Variables
				$post_ratings_users = $post->ratings_users;
				$post_ratings_images = '';
				$post_title = get_the_title();
				$post_ratings_average = $post->ratings_average;
				$post_ratings_score = $post->ratings_score;
				$post_ratings_whole = intval($post_ratings_average);
				$post_ratings = floor($post_ratings_average);
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				// Check For Half Star
				$insert_half = 0;
				$average_diff = $post_ratings_average-$post_ratings_whole;
				if($average_diff >= 0.25 && $average_diff <= 0.75) {
					$insert_half = $post_ratings_whole+1;
				} elseif($average_diff > 0.75) {
					$post_ratings = $post_ratings+1;
				}
				if($ratings_custom && $ratings_max == 2) {
					if($post_ratings_score > 0) {
						$post_ratings_score = '+'.$post_ratings_score;
					}
					$image_alt = $post_ratings_score.' '.__('rating', 'wp-postratings');
				} else {
					$image_alt = $post_ratings_users.' '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': '.$post_ratings_average.' '.__('out of', 'wp-postratings').' '.$ratings_max;
				}
				// Display Start Of Rating Images
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
				}
				if(!$ratings_custom) { 
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				} else {
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				}
				// Display End Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
				}
				if($chars > 0) {
					if($ratings_custom && $ratings_max == 2) {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$image_alt."</li>\n";
					} else {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$post_ratings_images."</li>\n";
					}
				} else {
					// Display The Contents
					$temp = stripslashes(get_option('postratings_template_highestrated'));
					$temp = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $temp);
					$temp = str_replace("%RATINGS_MAX%", $ratings_max, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_SCORE%", $post_ratings_score, $temp);
					$temp = str_replace("%RATINGS_USERS%", number_format_i18n($post_ratings_users), $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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


### Function: Display Lowest Rated Page/Post By Category ID
if(!function_exists('get_lowest_rated_category')) {
	function get_lowest_rated_category($category_id = 0, $mode = '', $min_votes = 0, $limit = 10, $chars = 0, $display = true) {
		global $wpdb, $post;
		$ratings_image = get_option('postratings_image');
		$ratings_max = intval(get_option('postratings_max'));
		$ratings_custom = intval(get_option('postratings_customrating'));
		$where = '';
		$temp = '';
		$output = '';
		// Code By: Dirceu P. Junior (http://pomoti.com)
		if(is_array($category_id)) {
			$category_sql = "$wpdb->term_taxonomy.term_id IN (".join(',', $category_id).')';
		} else {
			$category_sql = "$wpdb->term_taxonomy.term_id = $category_id";
		}
		if(!empty($mode) && $mode != 'both') {
			$where = "$wpdb->posts.post_type = '$mode'";
		} else {
			$where = '1=1';
		}
		if($ratings_custom && $ratings_max == 2) {
			$order_by = 'ratings_score';
		} else {
			$order_by = 'ratings_average';
		}
		$highest_rated = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (t1.meta_value+0.00) AS ratings_average, (t2.meta_value+0.00) AS ratings_users, (t3.meta_value+0.00) AS ratings_score FROM $wpdb->posts LEFT JOIN $wpdb->postmeta AS t1 ON t1.post_id = $wpdb->posts.ID LEFT JOIN $wpdb->postmeta AS t2 ON t1.post_id = t2.post_id LEFT JOIN $wpdb->postmeta AS t3 ON t3.post_id = $wpdb->posts.ID INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE t1.meta_key = 'ratings_average' AND t2.meta_key = 'ratings_users' AND t3.meta_key = 'ratings_score' AND $wpdb->posts.post_password = '' AND $wpdb->posts.post_date < '".current_time('mysql')."'  AND $wpdb->posts.post_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $category_sql AND t2.meta_value >= $min_votes AND $where ORDER BY $order_by ASC, ratings_users DESC LIMIT $limit");
		if($highest_rated) {
			foreach($highest_rated as $post) {
				// Variables
				$post_ratings_users = $post->ratings_users;
				$post_ratings_images = '';
				$post_title = get_the_title();
				$post_ratings_average = $post->ratings_average;
				$post_ratings_score = $post->ratings_score;
				$post_ratings_whole = intval($post_ratings_average);
				$post_ratings = floor($post_ratings_average);
				$post_excerpt = ratings_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password);
				$post_content = get_the_content();
				// Check For Half Star
				$insert_half = 0;
				$average_diff = $post_ratings_average-$post_ratings_whole;
				if($average_diff >= 0.25 && $average_diff <= 0.75) {
					$insert_half = $post_ratings_whole+1;
				} elseif($average_diff > 0.75) {
					$post_ratings = $post_ratings+1;
				}
				if($ratings_custom && $ratings_max == 2) {
					if($post_ratings_score > 0) {
						$post_ratings_score = '+'.$post_ratings_score;
					}
					$image_alt = $post_ratings_score.' '.__('rating', 'wp-postratings');
				} else {
					$image_alt = $post_ratings_users.' '.__('votes', 'wp-postratings').', '.__('average', 'wp-postratings').': '.$post_ratings_average.' '.__('out of', 'wp-postratings').' '.$ratings_max;
				}
				// Display Start Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_start.gif" alt="" class="post-ratings-image" />';
				}
				if(!$ratings_custom) { 
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				} else {
					for($i=1; $i <= $ratings_max; $i++) {
						if($i <= $post_ratings) {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_on.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';		
						} elseif($i == $insert_half) {						
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_half.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						} else {
							$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_'.$i.'_off.gif" alt="'.$image_alt.'" title="'.$image_alt.'" class="post-ratings-image" />';
						}
					}
				}
				// Display End Of Rating Image
				if(file_exists(ABSPATH.'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif')) {
					$post_ratings_images .= '<img src="'.get_option('siteurl').'/wp-content/plugins/wp-postratings/images/'.$ratings_image.'/rating_end.gif" alt="" class="post-ratings-image" />';
				}
				if($chars > 0) {
					if($ratings_custom && $ratings_max == 2) {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$image_alt."</li>\n";
					} else {
						$temp = "<li><a href=\"".get_permalink()."\">".snippet_text($post_title, $chars)."</a> ".$post_ratings_images."</li>\n";
					}
				} else {
					// Display The Contents
					$temp = stripslashes(get_option('postratings_template_highestrated'));
					$temp = str_replace("%RATINGS_IMAGES%", $post_ratings_images, $temp);
					$temp = str_replace("%RATINGS_MAX%", $ratings_max, $temp);
					$temp = str_replace("%RATINGS_AVERAGE%", $post_ratings_average, $temp);
					$temp = str_replace("%RATINGS_SCORE%", $post_ratings_score, $temp);
					$temp = str_replace("%RATINGS_USERS%", number_format_i18n($post_ratings_users), $temp);
					$temp = str_replace("%POST_TITLE%", $post_title, $temp);
					$temp = str_replace("%POST_EXCERPT%", $post_excerpt, $temp);
					$temp = str_replace("%POST_CONTENT%", $post_content, $temp);
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


### Function: Display Total Rating Users
if(!function_exists('get_ratings_users')) {
	function get_ratings_users($display = true) {
		global $wpdb;
		$ratings_users = $wpdb->get_var("SELECT SUM((meta_value+0.00)) FROM $wpdb->postmeta WHERE meta_key = 'ratings_users'");
		if($display) {
			echo number_format_i18n($ratings_users);
		} else {
			return number_format_i18n($ratings_users);
		}
	}
}
?>