<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.1 Plugin: WP-PostRatings 1.11								|
|	Copyright (c) 2007 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://www.lesterchan.net													|
|																							|
|	File Information:																	|
|	- Post Ratings Javascript File													|
|	- wp-content/plugins/postratings/postratings-js.php					|
|																							|
+----------------------------------------------------------------+
*/


### Include wp-config.php
@require('../../../wp-config.php');
cache_javascript_headers();

### Determine postratings.php Path
$ratings_ajax_url = dirname($_SERVER['PHP_SELF']);
if(substr($ratings_ajax_url, -1) == '/') {
	$ratings_ajax_url  = substr($ratings_ajax_url, 0, -1);
}

### Get Ratings AJAX Style
$postratings_ajax_style = get_option('postratings_ajax_style');
?>

// Variables
var site_url = "<?php echo get_option('siteurl'); ?>";
var ratings_ajax_url = "<?php echo $ratings_ajax_url; ?>/postratings.php";
var ratings_text_wait = "<?php _e('Please rate only 1 post at a time.', 'wp-postratings'); ?>";
var ratings_image = "<?php echo get_option('postratings_image'); ?>";
var ratings_max = "<?php echo intval(get_option('postratings_max')); ?>";
var ratings_mouseover_image = new Image();
ratings_mouseover_image.src = site_url + "/wp-content/plugins/postratings/images/" + ratings_image + "/rating_over.gif";
var ratings = new sack(ratings_ajax_url);
var post_id = 0;
var post_rating = 0;
var rate_fadein_opacity = 0;
var rate_fadeout_opacity = 100;
var ratings_show_loading = <?php echo intval($postratings_ajax_style['loading']); ?>;
var ratings_show_fading = <?php echo intval($postratings_ajax_style['fading']); ?>;
var is_ie = (document.all && document.getElementById);
var is_moz = (!document.all && document.getElementById);
var is_opera = (navigator.userAgent.indexOf("Opera") > -1);
var is_being_rated = false;


// Post Ratings Fade In Text
function rade_fadein_text() {
	if(rate_fadein_opacity < 100) {
		rate_fadein_opacity += 10;
		if(is_opera)  {
			rate_fadein_opacity = 100;
		} else	 if(is_ie) {
			if(ratings_show_fading) {
				document.getElementById('post-ratings-' + post_id).filters.alpha.opacity = rate_fadein_opacity;
			} else {
				rate_fadein_opacity = 100;
			}
		} else	 if(is_moz) {
			if(ratings_show_fading) {
				document.getElementById('post-ratings-' + post_id).style.MozOpacity = (rate_fadein_opacity/100);
			} else {
				rate_fadein_opacity = 100;
			}
		}
		setTimeout("rade_fadein_text()", 100); 
	} else {
		rate_fadein_opacity = 100;
		rate_unloading_text();
		is_being_rated = false;
	}
}


// When User Mouse Over Ratings
function current_rating(id, rating, rating_text) {
	if(!is_being_rated) {
		post_id = id;
		post_rating = rating;
		for(i = 1; i <= rating; i++) {
			document.images['rating_' + post_id + '_' + i].src = eval("ratings_mouseover_image.src");
		}
		if(document.getElementById('ratings_' + post_id + '_text')) {
			document.getElementById('ratings_' + post_id + '_text').style.display = 'inline';
			document.getElementById('ratings_' + post_id + '_text').innerHTML = rating_text;
		}
	}
}


// When User Mouse Out Ratings
function ratings_off(rating_score, insert_half) {
	if(!is_being_rated) {
		for(i = 1; i <= ratings_max; i++) {
			if(i <= rating_score) {
				document.images['rating_' + post_id + '_' + i].src = site_url + '/wp-content/plugins/postratings/images/' + ratings_image + '/rating_on.gif';
			} else if(i == insert_half) {
				document.images['rating_' + post_id + '_' + i].src = site_url + '/wp-content/plugins/postratings/images/' + ratings_image + '/rating_half.gif';
			} else {
				document.images['rating_' + post_id + '_' + i].src = site_url + '/wp-content/plugins/postratings/images/' + ratings_image + '/rating_off.gif';
			}
		}
		if(document.getElementById('ratings_' + post_id + '_text')) {
			document.getElementById('ratings_' + post_id + '_text').style.display = 'none';
			document.getElementById('ratings_' + post_id + '_text').innerHTML = '';
		}
	}
}


// Post Ratings Loading Text
function rate_loading_text() {
	if(ratings_show_loading) {
		document.getElementById('post-ratings-' + post_id + '-loading').style.display = 'block';
	}
}


// Post Ratings Finish Loading Text
function rate_unloading_text() {
	if(ratings_show_loading) {
		document.getElementById('post-ratings-' + post_id + '-loading').style.display = 'none';
	}
}


// Process Post Ratings
function rate_post() {	
	if(!is_being_rated) {
		is_being_rated = true;
		rate_loading_text();
		rate_process();		
	} else {		
		alert(ratings_text_wait);
	}
}


// Process Post Ratings
function rate_process() {
	if(rate_fadeout_opacity > 0) {
		rate_fadeout_opacity -= 10;
		if(is_opera) {
			rate_fadein_opacity = 0;
		} else if(is_ie) {
			if(ratings_show_fading) {
				document.getElementById('post-ratings-' + post_id).filters.alpha.opacity = rate_fadeout_opacity;
			} else {
				rate_fadein_opacity = 0;
			}
		} else if(is_moz) {
			if(ratings_show_fading) {
				document.getElementById('post-ratings-' + post_id).style.MozOpacity = (rate_fadeout_opacity/100);
			} else {
				rate_fadein_opacity = 0;
			}
		}
		setTimeout("rate_process()", 100); 
	} else {
		rate_fadeout_opacity = 0;
		ratings.reset();
		ratings.setVar("pid", post_id);
		ratings.setVar("rate", post_rating);
		ratings.method = 'GET';
		ratings.element = 'post-ratings-' + post_id;
		ratings.onCompletion = rade_fadein_text;
		ratings.runAJAX();
		rate_fadein_opacity = 0;
		rate_fadeout_opacity = 100;
	}
}