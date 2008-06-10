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
|	- Post Ratings Admin Javascript File											|
|	- wp-content/plugins/wp-postratings/postratings-admin-js.php		|
|																							|
+----------------------------------------------------------------+
*/


### Include wp-config.php
@require('../../../wp-config.php');
cache_javascript_headers();


### Determine postratings-admin-ajax.php Path
$postratings_admin_ajax_url = dirname($_SERVER['PHP_SELF']);
if(substr($postratings_admin_ajax_url, -1) == '/') {
	$postratings_admin_ajax_url  = substr($postratings_admin_ajax_url, 0, -1);
}
?>
// Variables
var postratings_admin_ajax_url = "<?php echo $postratings_admin_ajax_url; ?>/postratings-admin-ajax.php";
var postratings_admin = new sack(postratings_admin_ajax_url);


// Function: Hide PostRatings Loading
function postratings_admin_hide_loading() {
	document.getElementById('postratings_loading').style.display = 'none';
}


// Function: Update Rating Text, Rating Value
function update_rating_text_value() {
	image_form = document.getElementsByName('postratings_image');
	for (var i=0; i < image_form.length; i++) {
		if (image_form[i].checked) {
			image = image_form[i].value;
		}
	}
	max = document.getElementById('postratings_max').value;
	custom = document.getElementById('postratings_customrating').value;
	document.getElementById('postratings_loading').style.display = 'block';
	postratings_admin.reset();
	postratings_admin.setVar("custom", custom);
	postratings_admin.setVar("image", image);
	postratings_admin.setVar("max", max);
	postratings_admin.method = 'GET';
	postratings_admin.element = 'rating_text_value';
	postratings_admin.onCompletion = postratings_admin_hide_loading;
	postratings_admin.runAJAX();
}