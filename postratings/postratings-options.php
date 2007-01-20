<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.1 Plugin: WP-PostRatings 1.10								|
|	Copyright (c) 2007 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://www.lesterchan.net													|
|																							|
|	File Information:																	|
|	- Configure Post Ratings Options												|
|	- wp-content/plugins/postratings/postratings-options.php			|
|																							|
+----------------------------------------------------------------+
*/


### Check Whether User Can Manage Ratings
if(!current_user_can('manage_ratings')) {
	die('Access Denied');
}


### Ratings Variables
$base_name = plugin_basename('postratings/postratings-options.php');
$base_page = 'admin.php?page='.$base_name;


### If Form Is Submitted
if($_POST['Submit']) {
	$postratings_image = strip_tags(trim($_POST['postratings_image']));
	$postratings_max = intval($_POST['postratings_max']);
	$postratings_ratingstext_array = $_POST['postratings_ratingstext'];
	$postratings_ratingstext = array();
	foreach($postratings_ratingstext_array as $ratingstext) {
		$postratings_ratingstext[] = trim(addslashes($ratingstext));
	}
	$postratings_template_vote = trim($_POST['postratings_template_vote']);
	$postratings_template_text = trim($_POST['postratings_template_text']);
	$postratings_template_none = trim($_POST['postratings_template_none']);
	$postratings_template_highestrated = trim($_POST['postratings_template_highestrated']);
	$postratings_logging_method = intval($_POST['postratings_logging_method']);
	$postratings_allowtorate = intval($_POST['postratings_allowtorate']);
	$update_ratings_queries = array();
	$update_ratings_text = array();
	$update_ratings_queries[] = update_option('postratings_image', $postratings_image);
	$update_ratings_queries[] = update_option('postratings_max', $postratings_max);
	$update_ratings_queries[] = update_option('postratings_ratingstext', $postratings_ratingstext);
	$update_ratings_queries[] = update_option('postratings_template_vote', $postratings_template_vote);
	$update_ratings_queries[] = update_option('postratings_template_text', $postratings_template_text);
	$update_ratings_queries[] = update_option('postratings_template_none', $postratings_template_none);
	$update_ratings_queries[] = update_option('postratings_template_highestrated', $postratings_template_highestrated);
	$update_ratings_queries[] = update_option('postratings_logging_method', $postratings_logging_method);
	$update_ratings_queries[] = update_option('postratings_allowtorate', $postratings_allowtorate);
	$update_ratings_text[] = __('Ratings Image', 'wp-postratings');
	$update_ratings_text[] = __('Max Ratings', 'wp-postratings');
	$update_ratings_text[] = __('Individual Rating Text', 'wp-postratings');
	$update_ratings_text[] = __('Ratings Template Vote', 'wp-postratings');
	$update_ratings_text[] = __('Ratings Template Text', 'wp-postratings');
	$update_ratings_text[] = __('Ratings Template For No Ratings', 'wp-postratings');
	$update_ratings_text[] = __('Ratings Template For Highest Rated', 'wp-postratings');
	$update_ratings_text[] = __('Logging Method', 'wp-postratings');
	$update_ratings_text[] = __('Allow To Vote Option', 'wp-postratings');
	$i=0;
	$text = '';
	foreach($update_ratings_queries as $update_ratings_query) {
		if($update_ratings_query) {
			$text .= '<font color="green">'.$update_ratings_text[$i].' '.__('Updated', 'wp-postratings').'</font><br />';
		}
		$i++;
	}
	if(empty($text)) {
		$text = '<font color="red">'.__('No Ratings Option Updated', 'wp-postratings').'</font>';
	}
}
?>
<script language="JavaScript" type="text/javascript">
/* <![CDATA[*/
	function ratings_default_templates(template) {
		var default_template;
		switch(template) {
			case "vote":
				default_template = "%RATINGS_IMAGES_VOTE% (<strong>%RATINGS_USERS%</strong> <?php _e('votes', 'wp-postratings'); ?>, <?php _e('average', 'wp-postratings'); ?>: <strong>%RATINGS_AVERAGE%</strong> <?php _e('out of', 'wp-postratings'); ?> %RATINGS_MAX%)<br />%RATINGS_TEXT%";
				break;
			case "text":
				default_template = "%RATINGS_IMAGES% (<strong>%RATINGS_USERS%</strong> <?php _e('votes', 'wp-postratings'); ?>, <?php _e('average', 'wp-postratings'); ?>: <strong>%RATINGS_AVERAGE%</strong> <?php _e('out of', 'wp-postratings'); ?> %RATINGS_MAX%)";
				break;
			case "none":
				default_template = "%RATINGS_IMAGES_VOTE% (<?php _e('No Ratings Yet', 'wp-postratings'); ?>)<br />%RATINGS_TEXT%";
				break;
			case "highestrated":
				default_template = "<li><a href=\"%POST_URL%\">%POST_TITLE%</a> %RATINGS_IMAGES% (%RATINGS_AVERAGE% <?php _e('out of', 'wp-postratings'); ?> %RATINGS_MAX%)</li>";
				break;
		}
		document.getElementById("postratings_template_" + template).value = default_template;
	}
/* ]]> */
</script>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<div class="wrap"> 
	<h2><?php _e('Post Rating Options', 'wp-postratings'); ?></h2> 
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
		<fieldset class="options">
			<legend><?php _e('Ratings Settings', 'wp-postratings'); ?></legend>
			<table width="100%"  border="0" cellspacing="3" cellpadding="3">
				 <tr valign="top">
					<th align="left" width="20%"><?php _e('Ratings Image:', 'wp-postratings'); ?></th>
					<td align="left">
						<?php
							$postratings_url = get_settings('siteurl').'/wp-content/plugins/postratings/images';
							$postratings_path = ABSPATH.'/wp-content/plugins/postratings/images';
							$postratings_image = get_settings('postratings_image');
							if($handle = @opendir(ABSPATH.'/wp-content/plugins/postratings/images')) {     
								while (false !== ($filename = readdir($handle))) {  
									if ($filename != '.' && $filename != '..') {
										if(is_dir($postratings_path.'/'.$filename)) {
											if($postratings_image == $filename) {
												echo '<input type="radio" name="postratings_image" value="'.$filename.'" checked="checked" />';											
											} else {
												echo '<input type="radio" name="postratings_image" value="'.$filename.'" />';
											}
											echo '&nbsp;&nbsp;&nbsp;';
											if(file_exists($postratings_path.'/'.$filename.'/rating_start.gif')) {
												echo '<img src="'.$postratings_url.'/'.$filename.'/rating_start.gif" alt="rating_start.gif" class="post-ratings-image" />';
											}
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_over.gif" alt="rating_over.gif" class="post-ratings-image" />';
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_on.gif" alt="rating_on.gif" class="post-ratings-image" />';
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_on.gif" alt="rating_on.gif" class="post-ratings-image" />';
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_half.gif" alt="rating_half.gif" class="post-ratings-image" />';
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_off.gif" alt="rating_off.gif" class="post-ratings-image" />';
											if(file_exists($postratings_path.'/'.$filename.'/rating_end.gif')) {
												echo '<img src="'.$postratings_url.'/'.$filename.'/rating_end.gif" alt="rating_end.gif" class="post-ratings-image" />';
											}
											echo '&nbsp;&nbsp;&nbsp;('.$filename.')';
											echo '<br /><br />'."\n";
										}
									} 
								} 
								closedir($handle);
							}
							$postratings_max = get_settings('postratings_max');
						?>
					</td>
				</tr>
				<tr valign="top">
					<th align="left" width="20%"><?php _e('Max Ratings:', 'wp-postratings'); ?></th>
					<td align="left"><input type="text" name="postratings_max" value="<?php echo $postratings_max; ?>" size="3" /></td>
				</tr>
				<tr valign="top">
					<th align="left" width="20%"><?php _e('Individual Rating Text:', 'wp-postratings'); ?></th>
					<td align="left">
						<table width="50%"  border="0" cellspacing="3" cellpadding="3">						
							<?php
								$postratings_ratingstext = get_settings('postratings_ratingstext');
								for($i = 1; $i <= $postratings_max; $i++) {
									echo '<tr>'."\n";
									echo '<td>'."\n";
									if(file_exists($postratings_path.'/'.$postratings_image.'/rating_start.gif')) {
										echo '<img src="'.$postratings_url.'/'.$postratings_image.'/rating_start.gif" alt="rating_start.gif" class="post-ratings-image" />';
									}
									for($j = 1; $j < ($i+1); $j++) {
										echo '<img src="'.$postratings_url.'/'.$postratings_image.'/rating_on.gif" alt="rating_on.gif" class="post-ratings-image" />';
									}
									if(file_exists($postratings_path.'/'.$postratings_image.'/rating_end.gif')) {
										echo '<img src="'.$postratings_url.'/'.$postratings_image.'/rating_end.gif" alt="rating_end.gif" class="post-ratings-image" />';
									}
									echo '</td>'."\n";
									echo '<td>'."\n";
									echo '<input type="text" name="postratings_ratingstext[]" value="'.stripslashes($postratings_ratingstext[$i-1]).'" size="20" maxlength="50" />'."\n";
									echo '</td>'."\n";
									echo '</tr>'."\n";
								}								
							?>
						</table>
					</td>
				</tr>
			</table>
		</fieldset>
		<fieldset class="options">
		<legend><?php _e('Allow To Rate', 'wp-postratings'); ?></legend>
		<table width="100%"  border="0" cellspacing="3" cellpadding="3">
			 <tr valign="top">
				<th align="left" width="30%"><?php _e('Who Is Allowed To Rate?', 'wp-postratings'); ?></th>
				<td align="left">
					<select name="postratings_allowtorate" size="1">
						<option value="0"<?php selected('0', get_settings('postratings_allowtorate')); ?>><?php _e('Guests Only', 'wp-postratings'); ?></option>
						<option value="1"<?php selected('1', get_settings('postratings_allowtorate')); ?>><?php _e('Registered Users Only', 'wp-postratings'); ?></option>
						<option value="2"<?php selected('2', get_settings('postratings_allowtorate')); ?>><?php _e('Registered Users And Guests', 'wp-postratings'); ?></option>
					</select>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="options">
		<legend><?php _e('Logging Method', 'wp-postratings'); ?></legend>
		<table width="100%"  border="0" cellspacing="3" cellpadding="3">
			 <tr valign="top">
				<th align="left" width="30%"><?php _e('Ratings Logging Method:', 'wp-postratings'); ?></th>
				<td align="left">
					<select name="postratings_logging_method" size="1">
						<option value="0"<?php selected('0', get_settings('postratings_logging_method')); ?>><?php _e('Do Not Log', 'wp-postratings'); ?></option>
						<option value="1"<?php selected('1', get_settings('postratings_logging_method')); ?>><?php _e('Logged By Cookie', 'wp-postratings'); ?></option>
						<option value="2"<?php selected('2', get_settings('postratings_logging_method')); ?>><?php _e('Logged By IP', 'wp-postratings'); ?></option>
						<option value="3"<?php selected('3', get_settings('postratings_logging_method')); ?>><?php _e('Logged By Cookie And IP', 'wp-postratings'); ?></option>
						<option value="4"<?php selected('4', get_settings('postratings_logging_method')); ?>><?php _e('Logged By Username', 'wp-postratings'); ?></option>
					</select>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="options">
		<legend><?php _e('Template Variables', 'wp-postratings'); ?></legend>
		<table width="100%"  border="0" cellspacing="3" cellpadding="3">
			<tr>
				<td><strong>%RATINGS_IMAGES%</strong> - <?php _e('Display the ratings images', 'wp-postratings'); ?></td>
				<td><strong>%RATINGS_IMAGES_VOTE%</strong> - <?php _e('Display the ratings voting image', 'wp-postratings'); ?></td>
			</tr>
			<tr>
				<td><strong>%RATINGS_AVERAGE%</strong> - <?php _e('Display the average ratings', 'wp-postratings'); ?></td>
				<td><strong>%RATINGS_USERS%</strong> - <?php _e('Display the total number of users rated for the post', 'wp-postratings'); ?></td>						
			</tr>
			<tr>
				<td><strong>%RATINGS_MAX%</strong> - <?php _e('Display the max number of ratings', 'wp-postratings'); ?></td>
				<td><strong>%RATINGS_PERCENTAGE%</strong> - <?php _e('Display the ratings percentage', 'wp-postratings'); ?></td>
			</tr>
			<tr>
				<td><strong>%RATINGS_SCORE%</strong> - <?php _e('Display the total score of the ratings', 'wp-postratings'); ?></td>
				<td><strong>%RATINGS_TEXT%</strong> - <?php _e('Display the individual rating text. Eg: 1 Star, 2 Stars, etc', 'wp-postratings'); ?></td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="options">
		<legend><?php _e('Ratings Templates', 'wp-postratings'); ?></legend>
		<table width="100%"  border="0" cellspacing="3" cellpadding="3">
			 <tr valign="top">
				<td align="left" width="30%">
					<strong><?php _e('Ratings Vote Text:', 'wp-postratings'); ?></strong><br /><br />
					<?php _e('Allowed Variables:', 'wp-postratings'); ?><br />
					- %RATINGS_IMAGES_VOTE%<br />
					- %RATINGS_MAX%<br />
					- %RATINGS_SCORE%<br />
					- %RATINGS_TEXT%<br />
					- %RATINGS_USERS%<br />							
					- %RATINGS_AVERAGE%<br />
					- %RATINGS_PERCENTAGE%<br /><br />
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template', 'wp-postratings'); ?>" onclick="javascript: ratings_default_templates('vote');" class="button" />
				</td>
				<td align="left"><textarea cols="80" rows="10" id="postratings_template_vote" name="postratings_template_vote"><?php echo htmlspecialchars(stripslashes(get_settings('postratings_template_vote'))); ?></textarea></td>
			</tr>
			 <tr valign="top">
				<td align="left" width="30%">
					<strong><?php _e('Ratings Text:', 'wp-postratings'); ?></strong><br /><br />
					<?php _e('Allowed Variables:', 'wp-postratings'); ?><br />
					- %RATINGS_IMAGES%<br />
					- %RATINGS_MAX%<br />
					- %RATINGS_SCORE%<br />
					- %RATINGS_USERS%<br />							
					- %RATINGS_AVERAGE%<br />
					- %RATINGS_PERCENTAGE%<br /><br />
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template', 'wp-postratings'); ?>" onclick="javascript: ratings_default_templates('text');" class="button" />
				</td>
				<td align="left"><textarea cols="80" rows="10" id="postratings_template_text" name="postratings_template_text"><?php echo htmlspecialchars(stripslashes(get_settings('postratings_template_text'))); ?></textarea></td>
			</tr>
			 <tr valign="top">
				<td align="left" width="30%">
					<strong><?php _e('Ratings None:', 'wp-postratings'); ?></strong><br /><br />
					<?php _e('Allowed Variables:', 'wp-postratings'); ?><br />
					- %RATINGS_IMAGES_VOTE%<br />
					- %RATINGS_MAX%<br />
					- %RATINGS_SCORE%<br />
					- %RATINGS_TEXT%<br />
					- %RATINGS_USERS%<br />							
					- %RATINGS_AVERAGE%<br />
					- %RATINGS_PERCENTAGE%<br /><br />
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template', 'wp-postratings'); ?>" onclick="javascript: ratings_default_templates('none');" class="button" />
				</td>
				<td align="left"><textarea cols="80" rows="10" id="postratings_template_none" name="postratings_template_none"><?php echo htmlspecialchars(stripslashes(get_settings('postratings_template_none'))); ?></textarea></td>
			</tr>
			 <tr valign="top">
				<td align="left" width="30%">
					<strong><?php _e('Highest Rated:', 'wp-postratings'); ?></strong><br /><br />
					<?php _e('Allowed Variables:', 'wp-postratings'); ?><br />
					- %RATINGS_IMAGES<br />
					- %RATINGS_MAX%<br />
					- %RATINGS_USERS%<br />							
					- %RATINGS_AVERAGE%<br />
					- %POST_TITLE%<br />
					- %POST_URL%<br /><br />
					<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template', 'wp-postratings'); ?>" onclick="javascript: ratings_default_templates('highestrated');" class="button" />
				</td>
				<td align="left"><textarea cols="80" rows="10" id="postratings_template_highestrated" name="postratings_template_highestrated"><?php echo htmlspecialchars(stripslashes(get_settings('postratings_template_highestrated'))); ?></textarea></td>
			</tr>
		</table>
	</fieldset>
	<div align="center">
		<input type="submit" name="Submit" class="button" value="<?php _e('Update Options', 'wp-postratings'); ?>" />&nbsp;&nbsp;<input type="button" name="cancel" value="<?php _e('Cancel', 'wp-postratings'); ?>" class="button" onclick="javascript:history.go(-1)" />
	</div>
	</form>
</div>