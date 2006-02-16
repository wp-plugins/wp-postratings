-> Installation Instructions
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
This will display the ratings of the post.
------------------------------------------------------------------


// Open wp-content/themes/<YOUR THEME NAME>/single.php OR page.php

Find:
------------------------------------------------------------------
<?php while (have_posts()) : the_post(); ?>
------------------------------------------------------------------
Add Anywhere Below It:
------------------------------------------------------------------
<?php if(function_exists('the_ratings')) { the_ratings_vote(); } ?> 
------------------------------------------------------------------
Note:
------------------------------------------------------------------
This will display the image which will allow users to rate. If the 
user has rated the post before, it will just display the ratings for
the post.
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