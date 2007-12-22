<?php
/*
Plugin Name: WP-PostRatings Widget
Plugin URI: http://lesterchan.net/portfolio/programming.php
Description: Adds a PostRatings Widget to display most rated and/or highest rated posts and/or pages on your sidebar. You will need to activate WP-PostRatings first.
Version: 1.30
Author: Lester 'GaMerZ' Chan
Author URI: http://lesterchan.net
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


### Function: Init WP-PostRatings Widget
function widget_ratings_init() {
	if (!function_exists('register_sidebar_widget')) {
		return;
	}

	### Function: WP-PostRatings Highest Rated Widget
	function widget_ratings_highest_rated($args) {
		extract($args);
		$options = get_option('widget_ratings_highest_rated');
		$title = htmlspecialchars($options['title']);		
		if (function_exists('get_highest_rated')) {
			echo $before_widget.$before_title.$title.$after_title;
			echo '<ul>'."\n";
			get_highest_rated($options['mode'], $options['min_votes'], $options['limit'], $options['chars']);
			echo '</ul>'."\n";
			echo $after_widget;
		}		
	}

	### Function: WP-PostRatings Most Rated Widget
	function widget_ratings_most_rated($args) {
		extract($args);
		$options = get_option('widget_ratings_most_rated');
		$title = htmlspecialchars($options['title']);		
		if (function_exists('get_highest_rated')) {
			echo $before_widget.$before_title.$title.$after_title;
			echo '<ul>'."\n";
			get_most_rated($options['mode'], $options['min_votes'], $options['limit'], $options['chars']);
			echo '</ul>'."\n";
			echo $after_widget;
		}		
	}

	### Function: WP-PostRatings Highest Rated Widget Options
	function widget_ratings_highest_rated_options() {
		$options = get_option('widget_ratings_highest_rated');
		if (!is_array($options)) {
			$options = array('title' => __('Highest Rated', 'wp-postratings'), 'mode' => 'post', 'limit' => 10, 'chars' => 200);
		}
		if ($_POST['highest_rated-submit']) {
			$options['title'] = strip_tags(addslashes($_POST['highest_rated-title']));
			$options['mode'] = strip_tags(addslashes($_POST['highest_rated-mode']));
			$options['min_votes'] = intval($_POST['highest_rated-min_votes']);
			$options['limit'] = intval($_POST['highest_rated-limit']);
			$options['chars'] = intval($_POST['highest_rated-chars']);
			update_option('widget_ratings_highest_rated', $options);
		}
		echo '<p style="text-align: left;"><label for="highest_rated-title">';
		_e('Title', 'wp-postratings');
		echo ': </label><input type="text" id="highest_rated-title" name="highest_rated-title" value="'.htmlspecialchars(stripslashes($options['title'])).'" /></p>'."\n";
		echo '<p style="text-align: left;"><label for="highest_rated-mode">';
		_e('Show Ratings For: ', 'wp-postratings');
		echo ' </label>'."\n";
		echo '<select id="highest_rated-mode" name="highest_rated-mode" size="1">'."\n";
		echo '<option value="both"';
		selected('both', $options['mode']);
		echo '>';
		_e('Post &amp Page', 'wp-postratings');
		echo '</option>'."\n";
		echo '<option value="post"';
		selected('post', $options['mode']);
		echo '>';
		_e('Post', 'wp-postratings');
		echo '</option>'."\n";
		echo '<option value="page"';
		selected('page', $options['mode']);
		echo '>';
		_e('Page', 'wp-postratings');
		echo '</option>'."\n";
		echo '</select>&nbsp;&nbsp;';
		_e('Only', 'wp-postratings');
		echo '</p>'."\n";
		echo '<p style="text-align: left;"><label for="highest_rated-min_votes">';
		_e('Minimum Votes', 'wp-postratings');
		echo ': </label><input type="text" id="highest_rated-min_votes" name="highest_rated-min_votes" value="'.intval($options['min_votes']).'" size="3" /></p>'."\n";
		echo '<p style="text-align: left;"><label for="highest_rated-limit">';
		_e('Limit', 'wp-postratings');
		echo ': </label><input type="text" id="highest_rated-limit" name="highest_rated-limit" value="'.intval($options['limit']).'" size="3" /></p>'."\n";
		echo '<p style="text-align: left;"><label for="highest_rated-chars">';
		_e('Post Title Length (Characters)', 'wp-postratings');
		echo ': </label><input type="text" id="highest_rated-chars" name="highest_rated-chars" value="'.intval($options['chars']).'" size="5" />&nbsp;&nbsp;'."\n";
		_e('(<strong>0</strong> to disable)', 'wp-postratings');
		echo '</p>'."\n";
		echo '<input type="hidden" id="highest_rated-submit" name="highest_rated-submit" value="1" />'."\n";
	}

	### Function: WP-PostRatings Most Rated Widget Options
	function widget_ratings_most_rated_options() {
		$options = get_option('widget_ratings_most_rated');
		if (!is_array($options)) {
			$options = array('title' => __('Most Rated', 'wp-postratings'), 'mode' => 'post', 'limit' => 10, 'chars' => 200);
		}
		if ($_POST['most_rated-submit']) {
			$options['title'] = strip_tags(addslashes($_POST['most_rated-title']));
			$options['mode'] = strip_tags(addslashes($_POST['most_rated-mode']));
			$options['min_votes'] = intval($_POST['most_rated-min_votes']);
			$options['limit'] = intval($_POST['most_rated-limit']);
			$options['chars'] = intval($_POST['most_rated-chars']);
			update_option('widget_ratings_most_rated', $options);
		}
		echo '<p style="text-align: left;"><label for="most_rated-title">';
		_e('Title', 'wp-postratings');
		echo ': </label><input type="text" id="most_rated-title" name="most_rated-title" value="'.htmlspecialchars(stripslashes($options['title'])).'" /></p>'."\n";
		echo '<p style="text-align: left;"><label for="most_rated-mode">';
		_e('Show Ratings For: ', 'wp-postratings');
		echo ' </label>'."\n";
		echo '<select id="most_rated-mode" name="most_rated-mode" size="1">'."\n";
		echo '<option value="both"';
		selected('both', $options['mode']);
		echo '>';
		_e('Post &amp Page', 'wp-postratings');
		echo '</option>'."\n";
		echo '<option value="post"';
		selected('post', $options['mode']);
		echo '>';
		_e('Post', 'wp-postratings');
		echo '</option>'."\n";
		echo '<option value="page"';
		selected('page', $options['mode']);
		echo '>';
		_e('Page', 'wp-postratings');
		echo '</option>'."\n";
		echo '</select>&nbsp;&nbsp;';
		_e('Only', 'wp-postratings');
		echo '</p>'."\n";
		echo '<p style="text-align: left;"><label for="most_rated-min_votes">';
		_e('Minimum Votes', 'wp-postratings');
		echo ': </label><input type="text" id="most_rated-min_votes" name="most_rated-min_votes" value="'.intval($options['min_votes']).'" size="3" /></p>'."\n";
		echo '<p style="text-align: left;"><label for="most_rated-limit">';
		_e('Limit', 'wp-postratings');
		echo ': </label><input type="text" id="most_rated-limit" name="most_rated-limit" value="'.intval($options['limit']).'" size="3" /></p>'."\n";
		echo '<p style="text-align: left;"><label for="most_rated-chars">';
		_e('Post Title Length (Characters)', 'wp-postratings');
		echo ': </label><input type="text" id="most_rated-chars" name="most_rated-chars" value="'.intval($options['chars']).'" size="5" />&nbsp;&nbsp;'."\n";
		_e('(<strong>0</strong> to disable)', 'wp-postratings');
		echo '</p>'."\n";
		echo '<input type="hidden" id="most_rated-submit" name="most_rated-submit" value="1" />'."\n";
	}

	// Register Widgets
	register_sidebar_widget(array('Highest Rated', 'wp-postratings'), 'widget_ratings_highest_rated');
	register_widget_control(array('Highest Rated', 'wp-postratings'), 'widget_ratings_highest_rated_options', 400, 200);
	register_sidebar_widget(array('Most Rated', 'wp-postratings'), 'widget_ratings_most_rated');
	register_widget_control(array('Most Rated', 'wp-postratings'), 'widget_ratings_most_rated_options', 400, 200);
}


### Function: Load The WP-PostRatings Widget
add_action('plugins_loaded', 'widget_ratings_init')
?>