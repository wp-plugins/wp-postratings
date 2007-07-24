<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.1 Plugin: WP-PostRatings 1.20								|
|	Copyright (c) 2007 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- How To Use WP-PostRatings												|
|	- wp-content/plugins/postratings/postratings-usage.php				|
|																							|
+----------------------------------------------------------------+
*/


### Check Whether User Can Manage Ratings
if(!current_user_can('manage_ratings')) {
	die('Access Denied');
}
?>
<div class="wrap"> 
	<h2><?php _e("General Usage", 'wp-postratings'); ?></h2>
	<ol>
		<li>
			<?php _e("Open ", 'wp-postratings'); ?><b>wp-content/themes/&lt;<?php _e("YOUR THEME NAME", 'wp-postratings'); ?>&gt;/index.php</b><br /><?php _e('You may place it in <b>single.php</b>, <b>post.php</b> or <b>page.php</b> also.'); ?>
		</li>
		<li>
			<?php _e("Find:", 'wp-postratings'); ?>
			<blockquote>
				<pre class="wp-postratings-usage-pre">&lt;?php while (have_posts()) : the_post(); ?&gt;</pre>
			</blockquote>
			<?php _e("Add anywhere below it (the place you want the ratings to show):", 'wp-postratings'); ?>
			<blockquote><pre class="wp-postratings-usage-pre">&lt;?php if(function_exists('the_ratings')) { the_ratings(); } ?&gt;</pre></blockquote>
		</li>
		<li>
			<?php _e("If you DO NOT want the ratings to appear in every post, DO NOT use the code above. Just type in <b>[ratings]</b> into the selected post content and it will embed ratings into that post only.", 'wp-postratings'); ?>
		</li>
	</ol>
</div>
<div class="wrap"> 
	<h2><?php _e('Ratings Stats (With Widgets)', 'wp-postratings'); ?></h2> 
	<ol>
		<li>
			<strong>Activate</strong> WP-PostRatings Widget Plugin
		</li>
		<li>
			Go to '<strong>WP-Admin -> Presentation -> Widgets</strong>'
		</li>
		<li>
			To Display <strong>Highest Rated Post</strong>
		</li>
		<li>
			<strong>Drag</strong> the Highest Rated Widget to your sidebar
		</li>
		<li>
			You can <strong>configure</strong> the Highest Rated Widget by clicking on the configure icon
		</li>
		<li>
			To Display <strong>Most Rated Post</strong>
		</li>
		<li>
			<strong>Drag</strong> the Most Rated Widget to your sidebar
		</li>
		<li>
			You can <strong>configure</strong> the Most Rated Widget by clicking on the configure icon
		</li>
		<li>
			Click 'Save changes'
		</li>
	</ol>
</div>
<div class="wrap"> 
	<h2><?php _e('Ratings Stats (Outside WP Loop)', 'wp-postratings'); ?></h2> 
	<h3><?php _e('To Display Highest Rated Post', 'wp-postratings'); ?></h3>
	<blockquote>
		<pre class="wp-postratings-usage-pre">&lt;?php if (function_exists('get_highest_rated')): ?&gt;
&nbsp;&nbsp;&nbsp;&lt;ul&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;?php get_highest_rated(); ?&gt;
&nbsp;&nbsp;&nbsp;&lt;/ul&gt;
&lt;?php endif; ?&gt;</pre>
	</blockquote>
	<p><?php _e('Default: get_highest_rated(\'both\', 10)', 'wp-postratings'); ?></p>
	<p><?php _e('The value <b>\'both\'</b> will display both the highest rated posts and pages.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the highest rated posts only, replace \'both\' with <b>\'post\'</b>.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the highest rated pages only, replace \'both\' with <b>\'page\'</b>.', 'wp-postratings'); ?></p>
	<p><?php _e('The value <b>10</b> will display only the top 10 highest rated posts/pages.', 'wp-postratings'); ?></p>
	<h3><?php _e('To Display Most Rated Post', 'wp-postratings'); ?></h3>
	<blockquote>
		<pre class="wp-postratings-usage-pre">&lt;?php if (function_exists('get_most_rated')): ?&gt;
&nbsp;&nbsp;&nbsp;&lt;ul&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;?php get_most_rated(); ?&gt;
&nbsp;&nbsp;&nbsp;&lt;/ul&gt;
&lt;?php endif; ?&gt;	</pre>
	</blockquote>
	<p><?php _e('Default: get_most_rated(\'both\', 10)', 'wp-postratings'); ?></p>
	<p><?php _e('The value <b>\'both\'</b> will display both the most rated posts and pages.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the most rated posts only, replace \'both\' with <b>\'post\'</b>.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the most rated pages only, replace \'both\' with <b>\'page\'</b>.', 'wp-postratings'); ?></p>
	<p><?php _e('The value <b>10</b> will display only the top 10 most rated posts/pages.', 'wp-postratings'); ?></p>
</div>
<div class="wrap"> 
	<h2><?php _e("Note", 'wp-postratings'); ?></h2>
	<ul>
		<li>
			<?php _e("In IE, some of the ratings's text may appear jagged (this is normal in IE). To solve this issue,", 'wp-postratings'); ?>
			<ol>
				<li>
					<?php _e("Open <b>postratings-css.css</b>", 'wp-postratings'); ?>
				</li>
				<li>
					<?php _e("Find:", 'wp-postratings'); ?>
					<blockquote><pre class="wp-postratings-usage-pre">/* background-color: #ffffff; */</pre></blockquote>
				</li>
				<li>
					<?php _e("Replace:", 'wp-postratings'); ?>
					<blockquote><pre class="wp-postratings-usage-pre">background-color: #ffffff;</pre></blockquote>
					<?php _e("Where <b>#ffffff</b> should be your background color for the ratings.", 'wp-postratings'); ?>
				</li>
			</ol>
		</li>
	</ul>
</div>