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

### Variables
$wp_cache_key = '';
$using_wp_cache = "false";
$postratings_max = intval(get_option('postratings_max'));
$postratings_custom = intval(get_option('postratings_customrating'));
$postratings_ajax_style = get_option('postratings_ajax_style');
if(defined(WP_CACHE)) {
	if(wp_cache_enable()) {
		include_once(ABSPATH.'/wp-content/plugins/wp-cache/wp-cache-phase1.php');  
		$wp_cache_key = md5($_SERVER['SERVER_NAME'].preg_replace('/#.*$/', '', $_SERVER['REQUEST_URI']).wp_cache_get_cookies_values());
		$using_wp_cache = "true";
	}
}
?>

// Variables
var using_wp_cache = <?php echo $using_wp_cache; ?>;
var site_url = "<?php echo get_option('siteurl'); ?>";
var ratings_ajax_url = "<?php echo $ratings_ajax_url; ?>/postratings.php";
var ratings_text_wait = "<?php _e('Please rate only 1 post at a time.', 'wp-postratings'); ?>";
var ratings_image = "<?php echo get_option('postratings_image'); ?>";
var ratings_max = "<?php echo $postratings_max; ?>";
var ratings_page_hash = "<?php echo $wp_cache_key; ?>";
<?php
if($postratings_custom) {
	for($i = 1; $i <= $postratings_max; $i++) {
		echo 'var ratings_'.$i.'_mouseover_image = new Image();'."\n";
		echo 'ratings_'.$i.'_mouseover_image.src = site_url + "/wp-content/plugins/postratings/images/" + ratings_image + "/rating_'.$i.'_over.gif";'."\n";
	}
} else {
	echo 'var ratings_mouseover_image = new Image();';
	echo 'ratings_mouseover_image.src = site_url + "/wp-content/plugins/postratings/images/" + ratings_image + "/rating_over.gif";'."\n";
}
?>
var ratings = new sack(ratings_ajax_url);
var post_id = 0;
var post_rating = 0;
var rate_fadein_opacity = 0;
var rate_fadeout_opacity = 100;
var ratings_show_loading = <?php echo intval($postratings_ajax_style['loading']); ?>;
var ratings_show_fading = <?php echo intval($postratings_ajax_style['fading']); ?>;
var ratings_custom = <?php echo $postratings_custom; ?>;
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
		if(ratings_custom && ratings_max == 2) {
			document.images['rating_' + post_id + '_' + rating].src = eval("ratings_" + rating + "_mouseover_image.src");
		} else {
			for(i = 1; i <= rating; i++) {
				<?php
					if($postratings_custom) {
						echo "document.images['rating_' + post_id + '_' + i].src = eval(\"ratings_\" + i + \"_mouseover_image.src\");\n";
					} else {
						echo "document.images['rating_' + post_id + '_' + i].src = eval(\"ratings_mouseover_image.src\");\n";
					}
				?>
			}
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
				<?php
					if($postratings_custom) {
						echo  "document.images['rating_' + post_id + '_' + i].src = site_url + '/wp-content/plugins/postratings/images/' + ratings_image + '/rating_' + i + '_on.gif';\n";
					} else {
						echo "document.images['rating_' + post_id + '_' + i].src = site_url + '/wp-content/plugins/postratings/images/' + ratings_image + '/rating_on.gif';\n";
					}
				?>
			} else if(i == insert_half) {
				<?php
					if($postratings_custom) {
						echo   "document.images['rating_' + post_id + '_' + i].src = site_url + '/wp-content/plugins/postratings/images/' + ratings_image + '/rating_' + i + '_half.gif';\n";
					} else {
						echo "document.images['rating_' + post_id + '_' + i].src = site_url + '/wp-content/plugins/postratings/images/' + ratings_image + '/rating_half.gif';\n";
					}
				?>
			} else {
				<?php
					if($postratings_custom) {
						echo "document.images['rating_' + post_id + '_' + i].src = site_url + '/wp-content/plugins/postratings/images/' + ratings_image + '/rating_' + i + '_off.gif';\n";
					} else {
						echo "document.images['rating_' + post_id + '_' + i].src = site_url + '/wp-content/plugins/postratings/images/' + ratings_image + '/rating_off.gif';\n";
					}
				?>
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
		if(using_wp_cache) {
			ratings.setVar("page_hash", ratings_page_hash);
		}
		ratings.method = 'GET';
		ratings.element = 'post-ratings-' + post_id;
		ratings.onCompletion = rade_fadein_text;
		ratings.runAJAX();
		rate_fadein_opacity = 0;
		rate_fadeout_opacity = 100;
	}
}