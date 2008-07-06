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
|	- Post Ratings AJAX For Admin Backend									|
|	- wp-content/plugins/wp-postratings/postratings-admin-ajax.php	|
|																							|
+----------------------------------------------------------------+
*/


### Include wp-config.php
$wp_root = '../../..';
if (file_exists($wp_root.'/wp-load.php')) {
	require_once($wp_root.'/wp-load.php');
} else {
	require_once($wp_root.'/wp-config.php');
}


### Check Whether User Can Manage Ratings
if(!current_user_can('manage_ratings')) {
	die('Access Denied');
}


### Variables
$postratings_url = WP_PLUGIN_URL.'/wp-postratings/images';
$postratings_path = WP_PLUGIN_DIR.'/wp-postratings/images';
$postratings_ratingstext = get_option('postratings_ratingstext');
$postratings_ratingsvalue = get_option('postratings_ratingsvalue');


### Form Processing
$postratings_customrating = intval($_GET['custom']);
$postratings_image = trim($_GET['image']);
$postratings_max = intval($_GET['max']);


### If It Is A Up/Down Rating
if($postratings_customrating && $postratings_max == 2) {
	$postratings_ratingsvalue[0] = -1;
	$postratings_ratingsvalue[1] = 1;
	$postratings_ratingstext[0] = __('Vote This Post Down', 'wp-postratings');
	$postratings_ratingstext[1] = __('Vote This Post Up', 'wp-postratings');
} else {
	for($i = 0; $i < $postratings_max; $i++) {
		if($i > 0) {
			$postratings_ratingstext[$i] = sprintf(__('%s Stars', 'wp-postratings'), $i+1);
		} else {
			$postratings_ratingstext[$i] = sprintf(__('%s Star', 'wp-postratings'), $i+1);
		}
		$postratings_ratingsvalue[$i] = $i+1;
	}
}
?>
<table class="form-table">
	<thead>
		<tr>
			<th><?php _e('Rating Image', 'wp-postratings'); ?></th>
			<th><?php _e('Rating Text', 'wp-postratings'); ?></th>
			<th><?php _e('Rating Value', 'wp-postratings'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
			for($i = 1; $i <= $postratings_max; $i++) {
				$postratings_text = stripslashes($postratings_ratingstext[$i-1]);
				$postratings_value = $postratings_ratingsvalue[$i-1];
				if($postratings_value > 0) {
					$postratings_value = '+'.$postratings_value;
				}
				echo '<tr>'."\n";
				echo '<td>'."\n";
				if(file_exists($postratings_path.'/'.$postratings_image.'/rating_start.gif')) {
					echo '<img src="'.$postratings_url.'/'.$postratings_image.'/rating_start.gif" alt="rating_start.gif" class="post-ratings-image" />';
				}
				if($postratings_customrating) {
					if($postratings_max == 2) {
						echo '<img src="'.$postratings_url.'/'.$postratings_image.'/rating_'.$i.'_on.gif" alt="rating_'.$i.'_on.gif" class="post-ratings-image" />';
					} else {
						for($j = 1; $j < ($i+1); $j++) {
							echo '<img src="'.$postratings_url.'/'.$postratings_image.'/rating_'.$j.'_on.gif" alt="rating_on.gif" class="post-ratings-image" />';
						}
					}
				} else {
					for($j = 1; $j < ($i+1); $j++) {
						echo '<img src="'.$postratings_url.'/'.$postratings_image.'/rating_on.gif" alt="rating_on.gif" class="post-ratings-image" />';
					}
				}
				if(file_exists($postratings_path.'/'.$postratings_image.'/rating_end.gif')) {
					echo '<img src="'.$postratings_url.'/'.$postratings_image.'/rating_end.gif" alt="rating_end.gif" class="post-ratings-image" />';
				}
				echo '</td>'."\n";
				echo '<td>'."\n";
				echo '<input type="text" id="postratings_ratingstext_'.$i.'" name="postratings_ratingstext[]" value="'.$postratings_text.'" size="20" maxlength="50" />'."\n";
				echo '</td>'."\n";
				echo '<td>'."\n";
				echo '<input type="text" id="postratings_ratingsvalue_'.$i.'" name="postratings_ratingsvalue[]" value="'.$postratings_value.'" size="2" maxlength="2" />'."\n";
				echo '</td>'."\n";
				echo '</tr>'."\n";
			}								
		?>
	</tbody>
</table>
<?php exit(); ?>