-> Installation Instructions
------------------------------------------------------------------
// Drop previous wp-postratings table (wp_ratingslogs) using phpmyadmin

Note:
------------------------------------------------------------------
Only if you are using the previous version of wp-postratings
------------------------------------------------------------------


// Open wp-content/plugins folder

Put:
------------------------------------------------------------------
Folder: postratings
------------------------------------------------------------------


// Activate WP-PostRatings plugin





-> Usage Instructions
------------------------------------------------------------------
// Open wp-content/themes/<YOUR THEME NAME>/index.php

Find:
------------------------------------------------------------------
<?php while (have_posts()) : the_post(); ?>
------------------------------------------------------------------
Add Anywhere Below It:
------------------------------------------------------------------
<?php if(function_exists('the_ratings')) { the_ratings(); } ?>
------------------------------------------------------------------
Note:
------------------------------------------------------------------
- This will display the ratings of the post and the voting image if user has not voted yet.
- To embed the ratings into your post, use [ratings].
- the_ratings_result(); will display the ratings of the post.
- the_ratings_vote(); will display the voting images.
------------------------------------------------------------------


// Post Ratings Stats (You can place it anywhere outside the WP Loop)

// To Get Highest Rated Post

Use:
------------------------------------------------------------------
<?php if (function_exists('get_highest_rated')): ?>
	<?php get_highest_rated(); ?>
<?php endif; ?>
------------------------------------------------------------------
Note:
------------------------------------------------------------------
The first value you pass in is what you want to get, 'post', 'page' or 'both'.
The second value you pass in is the number of post you want to get.
Default: get_highest_rated('both', 10);
------------------------------------------------------------------

// To Get Most Rated Post

Use:
------------------------------------------------------------------
<?php if (function_exists('get_most_rated')): ?>
	<?php get_most_rated(); ?>
<?php endif; ?>
------------------------------------------------------------------
Note:
------------------------------------------------------------------
The first value you pass in is what you want to get, 'post', 'page' or 'both'.
The second value you pass in is the number of post you want to get.
Default: get_most_rated('both', 10);
------------------------------------------------------------------