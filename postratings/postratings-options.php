<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.0 Plugin: WP-PostRatings 1.00								|
|	Copyright (c) 2006 Lester "GaMerZ" Chan									|
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
	$postratings_template_vote = trim($_POST['postratings_template_vote']);
	$postratings_template_text = trim($_POST['postratings_template_text']);
	$postratings_template_none = trim($_POST['postratings_template_none']);
	$update_ratings_queries = array();
	$update_ratings_text = array();
	$update_ratings_queries[] = update_option('postratings_image', $postratings_image);
	$update_ratings_queries[] = update_option('postratings_max', $postratings_max);
	$update_ratings_queries[] = update_option('postratings_template_vote', $postratings_template_vote);
	$update_ratings_queries[] = update_option('postratings_template_text', $postratings_template_text);
	$update_ratings_queries[] = update_option('postratings_template_none', $postratings_template_none);
	$update_ratings_text[] = __('Ratings Image');
	$update_ratings_text[] = __('Max Ratings');
	$update_ratings_text[] = __('Ratings Template Vote');
	$update_ratings_text[] = __('Ratings Template Text');
	$update_ratings_text[] = __('Ratings Template For No Ratings');
	$i=0;
	$text = '';
	foreach($update_ratings_queries as $update_ratings_query) {
		if($update_ratings_query) {
			$text .= '<font color="green">'.$update_ratings_text[$i].' '.__('Updated').'</font><br />';
		}
		$i++;
	}
	if(empty($text)) {
		$text = '<font color="red">'.__('No Ratings Option Updated').'</font>';
	}
}
?>
<script language="JavaScript" type="text/javascript">
function ratings_default_templates(template) {
	var default_template;
	switch(template) {
		case "vote":
			default_template = "<p>%RATINGS_IMAGES_VOTE%</p>";
			break;
		case "text":
			default_template = "<p>%RATINGS_IMAGES% (<b>%RATINGS_USERS%</b> votes, average: <b>%RATINGS_AVERAGE%</b> out of %RATINGS_MAX%)</p>";
			break;
		case "none":
			default_template = "No Ratings Yet";
			break;
	}
	document.getElementById("postratings_template_" + template).value = default_template;
}
</script>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<div class="wrap"> 
	<h2><?php _e('Post Rating Options'); ?></h2> 
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
		<fieldset class="options">
			<legend><?php _e('Ratings Settings'); ?></legend>
			<table width="100%"  border="0" cellspacing="3" cellpadding="3">
				 <tr valign="top">
					<th align="left" width="20%"><?php _e('Ratings Image:'); ?></th>
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
												echo '<input type="radio" id="postratings_image" name="postratings_image" value="'.$filename.'" checked="checked" />';												
											} else {
												echo '<input type="radio" id="postratings_image" name="postratings_image" value="'.$filename.'" />';
											}
											echo '&nbsp;&nbsp;&nbsp;';
											if(file_exists($postratings_path.'/'.$filename.'/rating_start.gif')) {
												echo '<img src="'.$postratings_url.'/'.$filename.'/rating_start.gif" alt="rating_start.gif" />';
											}
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_on.gif" alt="rating_on.gif" />';
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_on.gif" alt="rating_on.gif" />';
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_on.gif" alt="rating_on.gif" />';
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_half.gif" alt="rating_half.gif" />';
											echo '<img src="'.$postratings_url.'/'.$filename.'/rating_off.gif" alt="rating_off.gif" />';
											if(file_exists($postratings_path.'/'.$filename.'/rating_end.gif')) {
												echo '<img src="'.$postratings_url.'/'.$filename.'/rating_end.gif" alt="rating_end.gif" />';
											}
											echo '&nbsp;&nbsp;&nbsp;('.$filename.')';
											echo '<br /><br />'."\n";
										}
									} 
								} 
								closedir($handle);
							}
						?>
					</td>
				</tr>
				<tr valign="top">
					<th align="left" width="20%"><?php _e('Max Ratings:'); ?></th>
					<td align="left"><input type="text" name="postratings_max" value="<?php echo get_settings('postratings_max'); ?>" size="3" /></td>
				</tr>
			</table>
			</fieldset>
			<fieldset class="options">
				<legend><?php _e('Template Variables'); ?></legend>
				<table width="100%"  border="0" cellspacing="3" cellpadding="3">
					<tr>
						<td><b>%RATINGS_IMAGES%</b> - <?php _e('Display the ratings images'); ?></td>
						<td><b>%RATINGS_IMAGES_VOTE%</b> - <?php _e('Display the ratings voting image'); ?></td>
					</tr>
					<tr>
						<td><b>%RATINGS_AVERAGE%</b> - <?php _e('Display the average ratings'); ?></td>
						<td><b>%RATINGS_USERS%</b> - <?php _e('Display the total number of users rated for the post'); ?></td>						
					</tr>
					<tr>
						<td><b>%RATINGS_MAX%</b> - <?php _e('Display the max number of ratings'); ?></td>
						<td><b>%RATINGS_PERCENTAGE%</b> - <?php _e('Display the ratings percentage'); ?></td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="options">
				<legend><?php _e('Ratings Templates'); ?></legend>
				<table width="100%"  border="0" cellspacing="3" cellpadding="3">
					 <tr valign="top">
						<td align="left" width="30%">
							<b><?php _e('Ratings Vote Text:'); ?></b><br /><br />
							<?php _e('Allowed Variables:'); ?><br />
							- %RATINGS_IMAGES_VOTE%<br />
							- %RATINGS_MAX%<br /><br />
							<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template'); ?>" onclick="javascript: ratings_default_templates('vote');" class="button" />
						</td>
						<td align="left"><textarea cols="80" rows="10" id="postratings_template_vote" name="postratings_template_vote"><?php echo stripslashes(get_settings('postratings_template_vote')); ?></textarea></td>
					</tr>
					 <tr valign="top">
						<td align="left" width="30%">
							<b><?php _e('Ratings Text:'); ?></b><br /><br />
							<?php _e('Allowed Variables:'); ?><br />
							- %RATINGS_IMAGES%<br />
							- %RATINGS_MAX%<br />
							- %RATINGS_USERS%<br />							
							- %RATINGS_AVERAGE%<br />
							- %RATINGS_PERCENTAGE%<br /><br />
							<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template'); ?>" onclick="javascript: ratings_default_templates('text');" class="button" />
						</td>
						<td align="left"><textarea cols="80" rows="10" id="postratings_template_text" name="postratings_template_text"><?php echo stripslashes(get_settings('postratings_template_text')); ?></textarea></td>
					</tr>
					 <tr valign="top">
						<td align="left" width="30%">
							<b><?php _e('Ratings None:'); ?></b><br /><br />
							<?php _e('Allowed Variables:'); ?><br />
							- %RATINGS_MAX%<br />
							- %RATINGS_USERS%<br />							
							- %RATINGS_AVERAGE%<br />
							- %RATINGS_PERCENTAGE%<br /><br />
							<input type="button" name="RestoreDefault" value="<?php _e('Restore Default Template'); ?>" onclick="javascript: ratings_default_templates('none');" class="button" />
						</td>
						<td align="left"><textarea cols="80" rows="10" id="postratings_template_none" name="postratings_template_none"><?php echo stripslashes(get_settings('postratings_template_none')); ?></textarea></td>
					</tr>
				</table>
			</fieldset>
			<div align="center">
				<input type="submit" name="Submit" class="button" value="<?php _e('Update Options'); ?>" />&nbsp;&nbsp;<input type="button" name="cancel" Value="<?php _e('Cancel'); ?>" class="button" onclick="javascript:history.go(-1)" />
			</div>
	</form>
</div>