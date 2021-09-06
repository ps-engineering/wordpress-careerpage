<?php
//Setup Yoast Variables
add_action('wpseo_register_extra_replacements', 'register_custom_yoast_variables');
 
function register_custom_yoast_variables() {
	wpseo_register_var_replacement( '%%job_title%%', 'get_job_title', 'advanced', 'Retrieve job title from Prescreen' );
	wpseo_register_var_replacement( '%%job_description%%', 'get_job_description', 'advanced', 'Retrieve job describtion from Prescreen' );
}

 
function filter_wpseo_robots_index(){
	return 'index,follow';
}
 
function filter_wpseo_robots_noindex(){
	return 'noindex,nofollow';
}
 
function filter_wpseo_canonical_param(){
    if ( is_page_template( 'page_job.php' ) && isset($_GET['sh']) ) {
        $mainJobURL = get_option('PS_career_mainjoburl');
        $redirect_url = $mainJobURL.'?sh='.$_GET['sh'];
    } else {
		return ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
	}
	
}
 
function filter_wpseo_canonical_noparam(){
	return ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'] . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
}


function get_job_title(){
	global $wpdb;
	
	$blogtitle = get_bloginfo( 'name' );
	
	if(isset($_GET['sh'])){
		$shortHandle = $_GET['sh'];
		$data = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE shorthandle = '".$shortHandle."'");
		if (!empty($data)) {
			$title = $data[0]->title;
		} else {
			$title = '';
		}
	} else {
		$title = '';
	}
	
	$stringlength = 60 - 3 - strlen($blogtitle);
	
	
	if( $title == '' ){
	   //add_filter( 'wpseo_robots', 'filter_wpseo_robots_noindex' );
	   //add_filter( 'wpseo_canonical', 'filter_wpseo_canonical_noparam' );
	   return __( 'Prescreen Jobs', 'prescreen' ); //GENERISCHER TEXT FÜR DOCUMENT TITLE
	} else {
		//add_filter( 'wpseo_robots', 'filter_wpseo_robots_index' );
		//add_filter( 'wpseo_canonical', 'filter_wpseo_canonical_param' );
		
		if( strlen( $title ) > $stringlength ){
			//return substr( $title, 0, strpos( $title, ' ', 48 ));
			$title = substr($title, 0, $stringlength);
			if (rtrim($title) != $title) {
				// Whitespace at the end; Do nothing
				$title = $title;
			} else {
				$title = preg_replace('/\s+?(\S+)?$/', '', substr($title, 0, $stringlength));
			}
			return $title;
		} else {
			return $title;
		}
	}
	
}

function yoast_seo_canonical( $canonical ) {
	
    if ( is_page_template( 'page_job.php' ) && isset($_GET['sh']) ) {
        $mainJobURL = get_option('PS_career_mainjoburl');
        $canonical = $mainJobURL.'?sh='.$_GET['sh'];
    }

	//print_r($canonical);
    return $canonical;	
	
}

add_filter( 'wpseo_canonical', 'yoast_seo_canonical', 10, 1 );



function yoast_seo_robots( $robots ) {
	$actual_link = strtok((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", '?');
	
	//print_r($actual_link);
	//print_r(get_option('PS_career_mainjoburl'));
	
	if($actual_link === get_option('PS_career_mainjoburl')){
	    if ( is_page_template( 'page_job.php' ) && isset($_GET['sh']) ) {
	        $robots = 'index,follow';
	    }
	} else {
	    if ( is_page_template( 'page_job.php' ) && isset($_GET['sh']) ) {
	        $robots = 'noindex,follow';
	    }
	}
	

    return $robots;	
	
}

add_filter( 'wpseo_robots', 'yoast_seo_robots');

function get_job_description(){
	global $wpdb;
	if(isset($_GET['sh'])){
		$shortHandle = $_GET['sh'];
		$data = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE shorthandle = '".$shortHandle."'");
		if (is_object($data[0])){
			$description = $data[0]->simple_html_content;
			$description = preg_replace('/<h1[^>]*>([\s\S]*?)<\/h1[^>]*>/', '', $description);
			$description = strip_tags($description);
		}
	} else {
		$description = '';
	}
	
	if( $description == ''){
		return '';
	} else {
		if( strlen( $description ) > 155 ){
			return substr( $description, 0, strpos( $description, ' ', 155 ));
		} else {
			return $description;
		}
	}
}
?>