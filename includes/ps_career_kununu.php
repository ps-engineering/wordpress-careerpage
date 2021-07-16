<?php
function kununu_score_function($atts) {
	return '<p>'.get_option('PS_career_kununu_score').'</p>';
}
add_shortcode('kununu-score', 'kununu_score_function');

function kununu_live_score_function($atts) {
	return '<div style="overflow: hidden; width: 300px; height: 85px; margin: 0 auto;"><iframe scrolling="no" src="'.get_option('PS_career_kununu_live_score').'" style="border:0; margin: 0; padding: 0; overflow: hidden; width: 300px; height: 85px;"></iframe></div>';
}
add_shortcode('kununu-live-score', 'kununu_live_score_function');
?>