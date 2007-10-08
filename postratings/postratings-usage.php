<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.1 Plugin: WP-PostRatings 1.21								|
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
	<h2><?php _e('General Usage', 'wp-postratings'); ?></h2>
	<ol>
		<li>
			<?php _e('Open ', 'wp-postratings'); ?><strong>wp-content/themes/&lt;<?php _e('YOUR THEME NAME', 'wp-postratings'); ?>&gt;/index.php</strong><br /><?php _e('You may place it in <strong>single.php</strong>, <strong>post.php</strong> or <strong>page.php</strong> also.'); ?>
		</li>
		<li>
			<?php _e('Find:', 'wp-postratings'); ?>
			<blockquote>
				<pre class="wp-postratings-usage-pre">&lt;?php while (have_posts()) : the_post(); ?&gt;</pre>
			</blockquote>
			<?php _e('Add anywhere below it (the place you want the ratings to show):', 'wp-postratings'); ?>
			<blockquote><pre class="wp-postratings-usage-pre">&lt;?php if(function_exists('the_ratings')) { the_ratings(); } ?&gt;</pre></blockquote>
		</li>
		<li>
			<?php printf(__('If you DO NOT want the ratings to appear in every post, DO NOT use the code above. Just type in <strong>%s</strong> into the selected post content and it will embed ratings into that post only.', 'wp-postratings'), '[ratings]'); ?>
		</li>
	</ol>
</div>
<div class="wrap"> 
	<h2><?php _e('Ratings Stats (With Widgets)', 'wp-postratings'); ?></h2> 
	<ol>
		<li>
			<?php _e('<strong>Activate</strong> WP-PostRatings Widget Plugin', 'wp-postratings'); ?>
		</li>
		<li>
			<?php _e('Go to \'<strong>WP-Admin -> Presentation -> Widgets</strong>\'', 'wp-postratings'); ?>
		</li>
		<li>
			<?php _e('To Display <strong>Highest Rated Post</strong>', 'wp-postratings'); ?>
		</li>
		<li>
			<?php _e('<strong>Drag</strong> the Highest Rated Widget to your sidebar', 'wp-postratings'); ?>
		</li>
		<li>
			<?php _e('You can <strong>configure</strong> the Highest Rated Widget by clicking on the configure icon', 'wp-postratings'); ?>
		</li>
		<li>
			<?php _e('To Display <strong>Most Rated Post</strong>', 'wp-postratings'); ?>
		</li>
		<li>
			<?php _e('<strong>Drag</strong> the Most Rated Widget to your sidebar', 'wp-postratings'); ?>
		</li>
		<li>
			<?php _e('You can <strong>configure</strong> the Most Rated Widget by clicking on the configure icon', 'wp-postratings'); ?>
		</li>
		<li>
			<?php _e('Click \'Save changes\'', 'wp-postratings'); ?>
		</li>
	</ol>
</div>
<div class="wrap"> 
	<h2><?php _e('Ratings Stats (Outside WP Loop)', 'wp-postratings'); ?></h2>
	<h3><?php _e('To Display Lowest Rated Post', 'wp-postratings'); ?></h3>
	<blockquote>
		<pre class="wp-postratings-usage-pre">&lt;?php if (function_exists('get_lowest_rated')): ?&gt;
&nbsp;&nbsp;&nbsp;&lt;ul&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;?php get_lowest_rated(); ?&gt;
&nbsp;&nbsp;&nbsp;&lt;/ul&gt;
&lt;?php endif; ?&gt;</pre>
	</blockquote>
	<p><?php _e('Default: get_lowest_rated(\'both\', 10)', 'wp-postratings'); ?></p>
	<p><?php _e('The value <strong>\'both\'</strong> will display both the lowest rated posts and pages.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the lowest rated posts only, replace \'both\' with <strong>\'post\'</strong>.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the lowest rated pages only, replace \'both\' with <strong>\'page\'</strong>.', 'wp-postratings'); ?></p>
	<p><?php _e('The value <strong>10</strong> will display only the top 10 lowest rated posts/pages.', 'wp-postratings'); ?></p>
	<h3><?php _e('To Display Highest Rated Post', 'wp-postratings'); ?></h3>
	<blockquote>
		<pre class="wp-postratings-usage-pre">&lt;?php if (function_exists('get_highest_rated')): ?&gt;
&nbsp;&nbsp;&nbsp;&lt;ul&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;?php get_highest_rated(); ?&gt;
&nbsp;&nbsp;&nbsp;&lt;/ul&gt;
&lt;?php endif; ?&gt;</pre>
	</blockquote>
	<p><?php _e('Default: get_highest_rated(\'both\', 10)', 'wp-postratings'); ?></p>
	<p><?php _e('The value <strong>\'both\'</strong> will display both the highest rated posts and pages.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the highest rated posts only, replace \'both\' with <strong>\'post\'</strong>.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the highest rated pages only, replace \'both\' with <strong>\'page\'</strong>.', 'wp-postratings'); ?></p>
	<p><?php _e('The value <strong>10</strong> will display only the top 10 highest rated posts/pages.', 'wp-postratings'); ?></p>
	<h3><?php _e('To Display Most Rated Post', 'wp-postratings'); ?></h3>
	<blockquote>
		<pre class="wp-postratings-usage-pre">&lt;?php if (function_exists('get_most_rated')): ?&gt;
&nbsp;&nbsp;&nbsp;&lt;ul&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;?php get_most_rated(); ?&gt;
&nbsp;&nbsp;&nbsp;&lt;/ul&gt;
&lt;?php endif; ?&gt;	</pre>
	</blockquote>
	<p><?php _e('Default: get_most_rated(\'both\', 10)', 'wp-postratings'); ?></p>
	<p><?php _e('The value <strong>\'both\'</strong> will display both the most rated posts and pages.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the most rated posts only, replace \'both\' with <strong>\'post\'</strong>.', 'wp-postratings'); ?></p>
	<p><?php _e('If you want to display the most rated pages only, replace \'both\' with <strong>\'page\'</strong>.', 'wp-postratings'); ?></p>
	<p><?php _e('The value <strong>10</strong> will display only the top 10 most rated posts/pages.', 'wp-postratings'); ?></p>
</div>
<div class="wrap"> 
	<h2><?php _e('Note', 'wp-postratings'); ?></h2>
	<ul>
		<li>
			<?php _e('In IE, some of the ratings\'s text may appear jagged (this is normal in IE). To solve this issue,', 'wp-postratings'); ?>
			<ol>
				<li>
					<?php _e('Open <strong>postratings-css.css</strong>', 'wp-postratings'); ?>
				</li>
				<li>
					<?php _e('Find:', 'wp-postratings'); ?>
					<blockquote><pre class="wp-postratings-usage-pre">/* background-color: #ffffff; */</pre></blockquote>
				</li>
				<li>
					<?php _e('Replace:', 'wp-postratings'); ?>
					<blockquote><pre class="wp-postratings-usage-pre">background-color: #ffffff;</pre></blockquote>
					<?php _e('Where <strong>#ffffff</strong> should be your background color for the ratings.', 'wp-postratings'); ?>
				</li>
			</ol>
		</li>
	</ul>
</div>