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
|	- Manage Post Ratings Logs													|
|	- wp-content/plugins/postratings/postratings-manager.php			|
|																							|
+----------------------------------------------------------------+
*/


### Check Whether User Can Manage Ratings
if(!current_user_can('manage_ratings')) {
	die('Access Denied');
}


### Ratings Variables
$base_name = plugin_basename('postratings/postratings-manager.php');
$base_page = 'admin.php?page='.$base_name;
$postratings_page = intval($_GET['ratingpage']);
$postratings_sortby = trim($_GET['by']);
$postratings_sortby_text = '';
$postratings_sortorder = trim($_GET['order']);
$postratings_sortorder_text = '';
$postratings_log_perpage = intval($_GET['perpage']);
$postratings_sort_url = '';


### Form Sorting URL
if(!empty($postratings_sortby)) {
	$postratings_sort_url .= '&amp;by='.$postratings_sortby;
}
if(!empty($postratings_sortorder)) {
	$postratings_sort_url .= '&amp;order='.$postratings_sortorder;
}
if(!empty($postratings_log_perpage)) {
	$postratings_sort_url .= '&amp;perpage='.$postratings_log_perpage;
}


### Get Order By
switch($postratings_sortby) {
	case 'id':
		$postratings_sortby = 'rating_id';
		$postratings_sortby_text = 'ID';
		break;
	case 'username':
		$postratings_sortby = 'rating_username';
		$postratings_sortby_text = 'Username';
		break;
	case 'rating':
		$postratings_sortby = 'rating_rating';
		$postratings_sortby_text = 'Rating';
		break;
	case 'postid':
		$postratings_sortby = 'rating_postid';
		$postratings_sortby_text = 'Post ID';
		break;
	case 'posttitle':
		$postratings_sortby = 'rating_posttitle';
		$postratings_sortby_text = 'Post Title';
		break;
	case 'ip':
		$postratings_sortby = 'rating_ip';
		$postratings_sortby_text = 'IP';
		break;
	case 'host':
		$postratings_sortby = 'rating_host';
		$postratings_sortby_text = 'Host';
		break;
	case 'date':
	default:
		$postratings_sortby = 'rating_timestamp';
		$postratings_sortby_text = 'Date';
}


### Get Sort Order
switch($postratings_sortorder) {
	case 'asc':
		$postratings_sortorder = 'ASC';
		$postratings_sortorder_text = 'Ascending';
		break;
	case 'desc':
	default:
		$postratings_sortorder = 'DESC';
		$postratings_sortorder_text = 'Descending';
}


### Form Processing 
if(!empty($_POST['delete_logs'])) {
	if(trim($_POST['delete_logs_yes']) == 'yes') {
		$delete_logs = $wpdb->query("DELETE FROM $wpdb->ratings");
		if($delete_logs) {
			$text = '<font color="green">All Post Ratings Logs Have Been Deleted.</font>';
		} else {
			$text = '<font color="red">An Error Has Occured While Deleting All Post Ratings Logs.</font>';
		}
	}
}


### Get Post Ratings Logs Data
$total_ratings = $wpdb->get_var("SELECT COUNT(rating_id) FROM $wpdb->ratings");
$total_users = $wpdb->get_var("SELECT SUM(CAST(meta_value AS UNSIGNED)) FROM $wpdb->postmeta WHERE meta_key = 'ratings_users'");
$total_score = $wpdb->get_var("SELECT SUM(CAST(meta_value AS UNSIGNED)) FROM $wpdb->postmeta WHERE meta_key = 'ratings_score'");
if($total_users == 0) { 
	$total_average = 0;
} else {
	$total_average = $total_score/$total_users;
}


### Checking $postratings_page and $offset
if(empty($postratings_page) || $postratings_page == 0) { $postratings_page = 1; }
if(empty($offset)) { $offset = 0; }
if(empty($postratings_log_perpage) || $postratings_log_perpage == 0) { $postratings_log_perpage = 20; }


### Determin $offset
$offset = ($postratings_page-1) * $postratings_log_perpage;


### Determine Max Number Of Polls To Display On Page
if(($offset + $postratings_log_perpage) > $total_ratings) { 
	$max_on_page = $total_ratings; 
} else { 
	$max_on_page = ($offset + $postratings_log_perpage); 
}


### Determine Number Of Polls To Display On Page
if (($offset + 1) > ($total_ratings)) { 
	$display_on_page = $total_ratings; 
} else { 
	$display_on_page = ($offset + 1); 
}


### Determing Total Amount Of Pages
$total_pages = ceil($total_ratings / $postratings_log_perpage);


### Get The Logs
$postratings_logs = $wpdb->get_results("SELECT * FROM $wpdb->ratings ORDER BY $postratings_sortby $postratings_sortorder LIMIT $offset, $postratings_log_perpage");
?>
<?php if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<!-- Manage Post Ratings -->
<div class="wrap">
	<h2><?php _e('Post Ratings Logs'); ?></h2>
	<p><?php _e('Displaying'); ?> <b><?php echo $display_on_page;?></b> <?php _e('To'); ?> <b><?php echo $max_on_page; ?></b> <?php _e('Of'); ?> <b><?php echo $total_ratings; ?></b> <?php _e('Post Ratings Logs'); ?></p>
	<p><?php _e('Sorted By'); ?> <b><?php echo $postratings_sortby_text;?></b> <?php _e('In'); ?> <b><?php echo $postratings_sortorder_text;?></b> <?php _e('Order'); ?></p>
	<table width="100%"  border="0" cellspacing="3" cellpadding="3">
	<tr>
		<th width="2%"><?php _e('ID'); ?></th>
		<th width="20%"><?php _e('Username'); ?></th>
		<th width="10%"><?php _e('Rating'); ?></th>
		<th width="28%"><?php _e('Post Title'); ?></th>	
		<th width="20%"><?php _e('Date / Time'); ?></th>
		<th width="20%"><?php _e('IP / Host'); ?></th>			
	</tr>
	<?php
		if($postratings_logs) {
			$i = 0;
			foreach($postratings_logs as $postratings_log) {
				if($i%2 == 0) {
					$style = 'style=\'background-color: #eee\'';
				}  else {
					$style = 'style=\'background-color: none\'';
				}
				$postratings_id = intval($postratings_log->rating_id);
				$postratings_username = stripslashes($postratings_log->rating_username);
				$postratings_rating = stripslashes($postratings_log->rating_rating);
				$postratings_postid = intval($postratings_log->rating_postid);
				$postratings_posttitle = stripslashes($postratings_log->rating_posttitle);
				$postratings_date = gmdate("jS F Y", $postratings_log->rating_timestamp);
				$postratings_time = gmdate("H:i", $postratings_log->rating_timestamp);
				$postratings_ip = $postratings_log->rating_ip;
				$postratings_host = $postratings_log->rating_host;				
				echo "<tr $style>\n";
				echo "<td>$postratings_id</td>\n";
				echo "<td>$postratings_username</td>\n";
				echo "<td>$postratings_rating Star(s)</td>\n";
				echo "<td>$postratings_posttitle</td>\n";
				echo "<td>$postratings_date, $postratings_time</td>\n";
				echo "<td>$postratings_ip / $postratings_host</td>\n";
				echo '</tr>';
				$i++;
			}
		} else {
			echo '<tr><td colspan="8" align="center"><b>'.__('No Post Ratings Logs Found').'</b></td></tr>';
		}
	?>
	</table>
		<!-- <Paging> -->
		<?php
			if($total_pages > 1) {
		?>
		<br />
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td align="left" width="50%">
					<?php
						if($postratings_page > 1 && ((($postratings_page*$postratings_log_perpage)-($postratings_log_perpage-1)) <= $total_ratings)) {
							echo '<b>&laquo;</b> <a href="'.$base_page.'&amp;ratingpage='.($postratings_page-1).'" title="&laquo; '.__('Previous Page').'">'.__('Previous Page').'</a>';
						} else {
							echo '&nbsp;';
						}
					?>
				</td>
				<td align="right" width="50%">
					<?php
						if($postratings_page >= 1 && ((($postratings_page*$postratings_log_perpage)+1) <=  $total_ratings)) {
							echo '<a href="'.$base_page.'&amp;ratingpage='.($postratings_page+1).'" title="'.__('Next Page').' &raquo;">'.__('Next Page').'</a> <b>&raquo;</b>';
						} else {
							echo '&nbsp;';
						}
					?>
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<?php _e('Pages'); ?> (<?php echo $total_pages; ?>) :
					<?php
						if ($postratings_page >= 4) {
							echo '<b><a href="'.$base_page.'&amp;ratingpage=1'.$postratings_sort_url.$postratings_sort_url.'" title="'.__('Go to First Page').'">&laquo; '.__('First').'</a></b> ... ';
						}
						if($postratings_page > 1) {
							echo ' <b><a href="'.$base_page.'&amp;ratingpage='.($postratings_page-1).$postratings_sort_url.'" title="&laquo; '.__('Go to Page').' '.($postratings_page-1).'">&laquo;</a></b> ';
						}
						for($i = $postratings_page - 2 ; $i  <= $postratings_page +2; $i++) {
							if ($i >= 1 && $i <= $total_pages) {
								if($i == $postratings_page) {
									echo "<b>[$i]</b> ";
								} else {
									echo '<a href="'.$base_page.'&amp;ratingpage='.($i).$postratings_sort_url.'" title="'.__('Page').' '.$i.'">'.$i.'</a> ';
								}
							}
						}
						if($postratings_page < $total_pages) {
							echo ' <b><a href="'.$base_page.'&amp;ratingpage='.($postratings_page+1).$postratings_sort_url.'" title="'.__('Go to Page').' '.($postratings_page+1).' &raquo;">&raquo;</a></b> ';
						}
						if (($postratings_page+2) < $total_pages) {
							echo ' ... <b><a href="'.$base_page.'&amp;ratingpage='.($total_pages).$postratings_sort_url.'" title="'.__('Go to Last Page').'">'.__('Last').' &raquo;</a></b>';
						}
					?>
				</td>
			</tr>
		</table>	
		<!-- </Paging> -->
		<?php
			}
		?>
	<br />
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
		<input type="hidden" name="page" value="<?php echo $base_name; ?>">
		Sort Options:&nbsp;&nbsp;&nbsp;
		<select name="by" size="1">
			<option value="id"<?php if($postratings_sortby == 'rating_id') { echo ' selected="selected"'; }?>>ID</option>
			<option value="username"<?php if($postratings_sortby == 'rating_username') { echo ' selected="selected"'; }?>>UserName</option>
			<option value="rating"<?php if($postratings_sortby == 'rating_rating') { echo ' selected="selected"'; }?>>Rating</option>
			<option value="postid"<?php if($postratings_sortby == 'rating_postid') { echo ' selected="selected"'; }?>>Post ID</option>
			<option value="posttitle"<?php if($postratings_sortby == 'rating_posttitle') { echo ' selected="selected"'; }?>>Post Title</option>
			<option value="date"<?php if($postratings_sortby == 'rating_timestamp') { echo ' selected="selected"'; }?>>Date</option>
			<option value="ip"<?php if($postratings_sortby == 'rating_ip') { echo ' selected="selected"'; }?>>IP</option>
			<option value="host"<?php if($postratings_sortby == 'rating_host') { echo ' selected="selected"'; }?>>Host</option>
		</select>
		&nbsp;&nbsp;&nbsp;
		<select name="order" size="1">
			<option value="asc"<?php if($postratings_sortorder == 'ASC') { echo ' selected="selected"'; }?>>Ascending</option>
			<option value="desc"<?php if($postratings_sortorder == 'DESC') { echo ' selected="selected"'; } ?>>Descending</option>
		</select>
		&nbsp;&nbsp;&nbsp;
		<select name="perpage" size="1">
		<?php
			for($i=10; $i <= 100; $i+=10) {
				if($postratings_log_perpage == $i) {
					echo "<option value=\"$i\" selected=\"selected\">Per Page: $i</option>\n";
				} else {
					echo "<option value=\"$i\">Per Page: $i</option>\n";
				}
			}
		?>
		</select>
		<input type="submit" value="Sort" class="button" />
	</form>
</div>

<!-- Post Ratings Stats -->
<div class="wrap">
	<h2><?php _e('Post Ratings Logs Stats'); ?></h2>
	<table border="0" cellspacing="3" cellpadding="3">
	<tr>
		<th align="left"><?php _e('Total Users Voted:'); ?></th>
		<td align="left"><?php echo number_format($total_users); ?></td>
	</tr>
	<tr>
		<th align="left"><?php _e('Total Score:'); ?></th>
		<td align="left"><?php echo number_format($total_score); ?></td>
	</tr>
	<tr>
		<th align="left"><?php _e('Total Average:'); ?></th>
		<td align="left"><?php echo number_format($total_average, 2); ?></td>
	</tr>
	</table>
</div>

<!-- Delete Post Ratings Logs -->
<div class="wrap">
	<h2><?php _e('Post Ratings Logs'); ?></h2>
	<div align="center">
		<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
			<b>Are You Sure You Want To Delete All Post Ratings Logs?</b><br /><br />
			<input type="checkbox" name="delete_logs_yes" value="yes" />&nbsp;Yes<br /><br />
			<input type="submit" name="delete_logs" value="Delete" class="button" onclick="return confirm('You Are About To Delete All Post Ratings\nThis Action Is Not Reversible.\n\n Choose \'Cancel\' to stop, \'OK\' to delete.')" />
		</form>
	</div>
</div>