/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.7 Plugin: WP-PostRatings 1.40								|
|	Copyright (c) 2008 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- Post Ratings Javascript File													|
|	- wp-content/plugins/wp-postratings/postratings-js.php				|
|																							|
+----------------------------------------------------------------+
*/


// Variables
var ratings = new sack(ratings_ajax_url);
var post_id = 0;
var post_rating = 0;
var rate_fadein_opacity = 0;
var rate_fadeout_opacity = 100;
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
				document.getElementById('post-ratings-' + post_id).style.filter = 'alpha(opacity=' + rate_fadein_opacity + ')';
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
				if(ratings_custom) {
					document.images['rating_' + post_id + '_' + i].src = eval("ratings_" + i + "_mouseover_image.src");
				} else {
					document.images['rating_' + post_id + '_' + i].src = eval("ratings_mouseover_image.src");
				}
			}
		}
		if(document.getElementById('ratings_' + post_id + '_text')) {
			document.getElementById('ratings_' + post_id + '_text').style.display = 'inline';
			document.getElementById('ratings_' + post_id + '_text').innerHTML = rating_text;
		}
	}
}


// When User Mouse Out Ratings
function ratings_off(rating_score, insert_half, half_rtl) {
	if(!is_being_rated) {
		for(i = 1; i <= ratings_max; i++) {
			if(i <= rating_score) {
				if(ratings_custom) {
					document.images['rating_' + post_id + '_' + i].src = ratings_plugin_url + '/images/' + ratings_image + '/rating_' + i + '_on.' + ratings_image_ext;
				} else {
					document.images['rating_' + post_id + '_' + i].src = ratings_plugin_url + '/images/' + ratings_image + '/rating_on.' + ratings_image_ext;
				}
			} else if(i == insert_half) {
				if(ratings_custom) {
					document.images['rating_' + post_id + '_' + i].src = ratings_plugin_url + '/images/' + ratings_image + '/rating_' + i + '_half' + (half_rtl ? '-rtl' : '') + '.' + ratings_image_ext;
				} else {
					document.images['rating_' + post_id + '_' + i].src = ratings_plugin_url + '/images/' + ratings_image + '/rating_half' + (half_rtl ? '-rtl' : '') + '.' + ratings_image_ext;
				}
			} else {
				if(ratings_custom) {
					document.images['rating_' + post_id + '_' + i].src = ratings_plugin_url + '/images/' + ratings_image + '/rating_' + i + '_off.' + ratings_image_ext;
				} else {
					document.images['rating_' + post_id + '_' + i].src = ratings_plugin_url + '/images/' + ratings_image + '/rating_off.' + ratings_image_ext;
				}
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
				document.getElementById('post-ratings-' + post_id).style.filter = 'alpha(opacity=' + rate_fadeout_opacity + ')';
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