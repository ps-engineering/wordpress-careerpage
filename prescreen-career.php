<?php
/**
* Plugin Name: Prescreen Career
* Plugin URI: https://careerpages.prescreen.io/
* Description: Prescreen Career
* Version: 1.36
* Author: Flo Hauck
* Author URI: http://www.flohauck.de
*/ 

require_once __DIR__ . "/includes/ps_career_options.php"; // Plugin options
require_once __DIR__ . "/includes/ps_career_showentries.php"; // Plugin Page Jobs - Show Entries
require_once __DIR__ . "/includes/ps_career_showapplications.php"; // Plugin Page Applications - Show Applications
require_once __DIR__ . "/includes/ps_career_application_custom_fields.php"; // Plugin Page Application Custom Fields
require_once __DIR__ . "/includes/ps_career_application_custom_field_form-template.php"; // Plugin Page Application Custom Fields
require_once __DIR__ . "/includes/ps_career_application_tags.php"; // Plugin Page Application Custom Fields
require_once __DIR__ . "/includes/ps_career_logging.php"; // Plugin Page Application Custom Fields
require_once __DIR__ . "/includes/ps_career_yoast.php"; // Functions for Yoast SEO
require_once __DIR__ . "/includes/ps_career_kununu.php"; // Functions for Kununu
require_once __DIR__ . "/includes/ps_career_helper.php"; // Helper Functions
require_once __DIR__ . "/includes/ps_career_sitemap.php"; // Own Sitemap for Jobs
require_once __DIR__ . "/includes/ps_career_google_indexing.php"; // Own Sitemap for Jobs
require_once __DIR__ . "/includes/hybridauth/autoload.php"; // OAuth
use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;


// Setup Menu in Backend
add_action( "admin_init", "prescreen_career_register_settings" ); // set options

function prescreen_career_setup_admin_menu(){
	add_menu_page('Prescreen Career', 'Prescreen Career', 'edit_pages', 'prescreen-career-settings', 'prescreen_career_settings_function');
	add_submenu_page( 'prescreen-career-settings', 'Jobs', 'Jobs', 'edit_pages', 'jobs', 'list_jobs_table_page' );
	add_submenu_page( 'prescreen-career-settings', 'Applications', 'Applications', 'edit_pages', 'applications', 'list_application_table_page' );
	add_submenu_page( 'prescreen-career-settings', 'All Forms','All Forms', 'edit_pages', '/edit.php?post_type=jobapplicationform', '');
	add_submenu_page( 'prescreen-career-settings', 'Add Form', 'Add Form', 'edit_pages', '/post-new.php?post_type=jobapplicationform', '');
	add_submenu_page( 'prescreen-career-settings', 'Custom Fields', 'Custom Fields', 'edit_pages', 'application_custom_fields', 'list_application_cf_table_page' );
	add_submenu_page( 'prescreen-career-settings', 'Formular Templates', 'Formular Templates', 'edit_pages', 'application_custom_field_form_template', 'list_application_cf_template_table_page' );
	add_submenu_page( 'prescreen-career-settings', 'Tags', 'Tags', 'edit_pages', 'application_tags', 'list_application_tags_table_page' );
	add_submenu_page( "prescreen-career-settings", "Settings", "Settings", "manage_options", "prescreen_career_settings", "prescreen_career_options_page");
	add_submenu_page( "prescreen-career-settings", "Logs", "Logs", "manage_options", "prescreen_career_logs", "list_logging_table_page");
	remove_submenu_page('prescreen-career-settings','prescreen-career-settings');
}
add_action('admin_menu', 'prescreen_career_setup_admin_menu');
 
function prescreen_career_settings_function(){
	
}

add_action('admin_head', 'my_column_width');

function my_column_width() {
    echo '<style type="text/css">';
    echo '.prescreen-career_page_jobs thead tr th:first-child { width: 30px; }';
	echo '.prescreen-career_page_jobs tbody tr td:first-child { vertical-align: middle; }';
    echo '.prescreen-career_page_jobs thead tr th:nth-child(2) { width: 30px; }';
	echo '.prescreen-career_page_jobs tbody tr td:nth-child(2) { vertical-align: middle; }';
	echo '.prescreen-career_page_jobs tbody tr td:nth-child(2) svg { width: 20px; height: auto; }';
    echo '</style>';
}


date_default_timezone_set('Europe/Berlin');


//Setup HTTP Login for Cron Jobs
if(defined('WP_CRON_CUSTOM_HTTP_BASIC_USERNAME') && defined('WP_CRON_CUSTOM_HTTP_BASIC_PASSWORD')) {
	function kb_http_basic_cron_request($cron_request) {
	$headers = array('Authorization' => sprintf('Basic %s', base64_encode(WP_CRON_CUSTOM_HTTP_BASIC_USERNAME . ':' . WP_CRON_CUSTOM_HTTP_BASIC_PASSWORD)));
	$cron_request['args']['headers'] = isset($cron_request['args']['headers']) ? array_merge($cron_request['args']['headers'], $headers) : $headers;
	return $cron_request;
	}
	add_filter('cron_request', 'kb_http_basic_cron_request');
}

global $jal_db_version;
$jal_db_version = '1.0';

function jal_install() {
	global $wpdb;
	global $jal_db_version;

	$table_prescreen_jobs = $wpdb->prefix . 'prescreen_jobs';
	$table_prescreen_jobs_last_update = $wpdb->prefix . 'prescreen_jobs_last_update';
	$table_prescreen_jobs_templates = $wpdb->prefix . 'prescreen_jobs_templates';
	$table_prescreen_jobs_departments = $wpdb->prefix . 'prescreen_jobs_departments';
	$table_prescreen_jobs_cities = $wpdb->prefix . 'prescreen_jobs_cities';
	$table_prescreen_jobs_positiontypes = $wpdb->prefix . 'prescreen_jobs_positiontypes';
	$table_prescreen_jobs_seniorities = $wpdb->prefix . 'prescreen_jobs_seniorities';
	$table_prescreen_jobs_instances = $wpdb->prefix . 'prescreen_jobs_instances';
	$table_prescreen_jobs_teams = $wpdb->prefix . 'prescreen_jobs_teams';
	$table_prescreen_jobs_custom_data_fields = $wpdb->prefix . 'prescreen_jobs_custom_data_fields';
	$table_prescreen_jobs_industries = $wpdb->prefix . 'prescreen_jobs_industries';
	$table_prescreen_candidates = $wpdb->prefix . 'prescreen_candidates';
	$table_prescreen_application_source = $wpdb->prefix . 'prescreen_application_source';
	$table_prescreen_application_custom_fields = $wpdb->prefix . 'prescreen_application_custom_fields';
	$table_prescreen_application_custom_field_form_template = $wpdb->prefix . 'prescreen_application_custom_field_form_template';
	$table_prescreen_application_tags = $wpdb->prefix . 'prescreen_application_tags';
	$table_prescreen_logging = $wpdb->prefix . 'prescreen_logging';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql_date_table = "CREATE TABLE $table_prescreen_jobs_last_update (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  lastupdate varchar(255),
	  PRIMARY KEY (id)
	) $charset_collate;";

	$sql_candidates_table = "CREATE TABLE $table_prescreen_candidates (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  data longtext,
	  email longtext,
	  candidatekey varchar(255),
	  userstate varchar(255),
	  userid bigint(20),
	  shorthandle longtext,
	  doubleoptin varchar(255),
	  created varchar(255),
	  PRIMARY KEY (id)
	) $charset_collate;";
	
	$sql_application_source_table = "CREATE TABLE $table_prescreen_application_source (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  title longtext,
	  language varchar(255),
	  sourceid bigint(20),
	  PRIMARY KEY (id)
	) $charset_collate;";
	
	$sql_application_custom_fields_table = "CREATE TABLE $table_prescreen_application_custom_fields (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  type longtext,
	  cfid varchar(255),
	  name longtext,
	  label longtext,
	  custom_field_values longtext,
	  is_job longtext,
	  is_candidate longtext,
	  is_mandatory longtext,
	  translations longtext,
	  is_active varchar(255),
	  PRIMARY KEY (id)
	) $charset_collate;";
	
	$sql_application_custom_field_form_template_table = "CREATE TABLE $table_prescreen_application_custom_field_form_template (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  cfid varchar(255),
	  value longtext,
	  PRIMARY KEY (id)
	) $charset_collate;";
	
	$sql_application_tags_table = "CREATE TABLE $table_prescreen_application_tags (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  tagid varchar(255),
	  name longtext,
	  PRIMARY KEY (id)
	) $charset_collate;";
	
	$sql_logging_table = "CREATE TABLE $table_prescreen_logging (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  description longtext,
	  created varchar(255),
	  PRIMARY KEY (id)
	) $charset_collate;";
	
	$sql_jobs_table = "CREATE TABLE $table_prescreen_jobs (
	  id bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  google_indexing longtext,
	  title longtext,
	  handle varchar(255),
	  shorthandle longtext,
	  showurl longtext,
	  applyurl longtext,
	  applyaddress longtext,
	  template longtext,
	  bannerurl longtext,
	  bannerfooterurl longtext,
	  department longtext,
	  city longtext,
	  positiontype longtext,
	  seniority longtext,
	  headcount longtext,
	  instance longtext,
	  team longtext,
	  custom_data_fields longtext,
	  simple_html_content longtext,
	  published_at longtext,
	  startofwork longtext,
	  industry longtext,
	  description longtext,
	  PRIMARY KEY (id)
	) $charset_collate;";
	
	$sql_jobs_templates_table = "CREATE TABLE $table_prescreen_jobs_templates (
	  primaryid bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  id bigint(20),
	  name longtext,
	  PRIMARY KEY (primaryid)
	) $charset_collate;";
	
	$sql_jobs_departments_table = "CREATE TABLE $table_prescreen_jobs_departments (
	  primaryid bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  id bigint(20),
	  title longtext,
	  PRIMARY KEY (primaryid)
	) $charset_collate;";
	
	$sql_jobs_cities_table = "CREATE TABLE $table_prescreen_jobs_cities (
	  primaryid bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  id bigint(20),
	  title longtext,
	  country longtext,
	  countrycode varchar(255),
	  lat text,
	  lng text,
	  distance text,
	  PRIMARY KEY (primaryid)
	) $charset_collate;";
	
	$sql_jobs_positiontypes_table = "CREATE TABLE $table_prescreen_jobs_positiontypes (
	  primaryid bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  id bigint(20),
	  title longtext,
	  PRIMARY KEY (primaryid)
	) $charset_collate;";
	
	$sql_jobs_seniorities_table = "CREATE TABLE $table_prescreen_jobs_seniorities (
	  primaryid bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  id bigint(20),
	  title longtext,
	  PRIMARY KEY (primaryid)
	) $charset_collate;";
	
	$sql_jobs_instances_table = "CREATE TABLE $table_prescreen_jobs_instances (
	  primaryid bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  id bigint(20),
	  is_legal_entity longtext,
	  name longtext,
	  handle longtext,
	  PRIMARY KEY (primaryid)
	) $charset_collate;";
	
	$sql_jobs_teams_table = "CREATE TABLE $table_prescreen_jobs_teams (
	  primaryid bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  id bigint(20),
	  name longtext,
	  PRIMARY KEY (primaryid)
	) $charset_collate;";
	
	$sql_jobs_custom_data_fields_table = "CREATE TABLE $table_prescreen_jobs_custom_data_fields (
	  primaryid bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  id bigint(20),
	  name longtext,
	  form_label longtext,
	  value_id bigint(20),
	  value longtext,
	  value_label longtext,
	  PRIMARY KEY (primaryid)
	) $charset_collate;";
	
	$sql_jobs_industries_table = "CREATE TABLE $table_prescreen_jobs_industries (
	  primaryid bigint(20) NOT NULL AUTO_INCREMENT,
	  status int(5) NOT NULL,
	  id bigint(20),
	  name longtext,
	  PRIMARY KEY (primaryid)
	) $charset_collate;";
	


	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql_date_table );
	dbDelta( $sql_jobs_table );
	dbDelta( $sql_candidates_table );
	dbDelta( $sql_application_source_table );
	dbDelta( $sql_application_custom_fields_table );
	dbDelta( $sql_application_custom_field_form_template_table );
	dbDelta( $sql_application_tags_table );
	dbDelta( $sql_logging_table );
	
	dbDelta( $sql_jobs_templates_table );
	dbDelta( $sql_jobs_departments_table );
	dbDelta( $sql_jobs_cities_table );
	dbDelta( $sql_jobs_positiontypes_table );
	dbDelta( $sql_jobs_seniorities_table );
	dbDelta( $sql_jobs_instances_table );
	dbDelta( $sql_jobs_teams_table );
	dbDelta( $sql_jobs_custom_data_fields_table );
	dbDelta( $sql_jobs_industries_table );

	add_option( 'jal_db_version', $jal_db_version );
}

function jal_install_data() {
	global $wpdb;

	date_default_timezone_set('Europe/Berlin');
	$date = date('d.m.Y H:i:s', time());
	
	$last_update = $wpdb->prefix . 'prescreen_jobs_last_update';
	$wpdb->insert( 
		$last_update, 
		array(  
			'lastupdate' => $date
		) 
	);
	
}

// Deactivating Plugin Remove Cron Job and Delete Tables

/*
register_deactivation_hook( __FILE__, 'cron_deactivation' );
function cron_deactivation() {
	wp_clear_scheduled_hook( 'schedule_job_import' );
}
register_deactivation_hook( __FILE__, 'prescreen_career_remove_database' );
function prescreen_career_remove_database() {
	 global $wpdb;
	 $sql = "DROP TABLE IF EXISTS ".$wpdb->prefix ."prescreen_jobs,".$wpdb->prefix ."prescreen_jobs_last_update,".$wpdb->prefix ."prescreen_jobs_templates,".$wpdb->prefix ."prescreen_jobs_departments,".$wpdb->prefix ."prescreen_jobs_cities,".$wpdb->prefix ."prescreen_jobs_positiontypes,".$wpdb->prefix ."prescreen_jobs_seniorities,".$wpdb->prefix ."prescreen_jobs_instances,".$wpdb->prefix ."prescreen_jobs_teams,".$wpdb->prefix ."prescreen_jobs_custom_data_fields,".$wpdb->prefix ."prescreen_jobs_industries" ;
	 $wpdb->query($sql);
	 delete_option("my_plugin_db_version");
}   
*/

register_activation_hook( __FILE__, 'shiba_activate' );
function shiba_activate($networkwide) {
	global $wpdb;
				 
	if (function_exists('is_multisite') && is_multisite()) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ($networkwide) {
			update_option( 'PS_career_googleindexing_interval', 'never' );
			update_option( 'PS_career_jobimport_interval', 'never' );
			$old_blog = $wpdb->blogid;
			// Get all blog ids
			$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				jal_install();    
				jal_install_data(); 
				schedule_job_import();  
				schedule_application_source_import();
				schedule_application_custom_fields_import();
				schedule_application_custom_fields_template_import();
				schedule_delete_old_candidates();
				//add_action( 'cron_activation', 'importJobs' ); 
				//restore_current_blog();
			}
			switch_to_blog($old_blog);
			//restore_current_blog();
			return;
		}   
	} else {
		update_option( 'PS_career_googleindexing_interval', 'never' );
		update_option( 'PS_career_jobimport_interval', 'never' );
		schedule_job_import();
		schedule_application_source_import();
		schedule_application_custom_fields_import();
		schedule_application_custom_fields_template_import();
		schedule_delete_old_candidates();
	}
	jal_install();    
	jal_install_data(); 
	

}



function custom_cron_job_recurrence( $schedules ) 
{
    if(!isset($schedules['30minutes']))
	{
		$schedules['30minutes'] = array(
			'display' => __( 'Every 30 Minutes', 'prescreen_career' ),
			'interval' => 1800,
		);
	}
	
    if(!isset($schedules['1hour']))
    {
        $schedules['1hour'] = array(
            'display' => __( 'Every Hour', 'prescreen_career' ),
            'interval' => 3600,
        );
    }
     
    if(!isset($schedules['2hours']))
    {
        $schedules['2hours'] = array(
        'display' => __( 'Every 2 Hours', 'prescreen_career' ),
        'interval' => 7200,
        );
    }
	
    if(!isset($schedules['3hours']))
	{
		$schedules['3hours'] = array(
		'display' => __( 'Every 3 Hours', 'prescreen_career' ),
		'interval' => 10800,
		);
	}
	
    if(!isset($schedules['twicedaily']))
	{
		$schedules['twicedaily'] = array(
		'display' => __( 'Twice per Day', 'prescreen_career' ),
		'interval' => 43200,
		);
	}
	
    if(!isset($schedules['daily']))
	{
		$schedules['daily'] = array(
		'display' => __( 'Once a Day', 'prescreen_career' ),
		'interval' => 86400,
		);
	}
     
    return $schedules;
}
add_filter( 'cron_schedules', 'custom_cron_job_recurrence' );

// Setup Cronjobs
add_action( 'schedule_job_import', 'importJobs' ); 
function schedule_job_import() {
	
	if(get_option('PS_career_jobimport_interval') != ''){
		$jobInterval = get_option('PS_career_jobimport_interval');
	} else {
		$jobInterval = 'hourly';
	}
	
	wp_schedule_event( time(), $jobInterval, 'schedule_job_import' );
	
}


add_action( 'schedule_google_indexing', 'updateGoogleIndex' ); 
function schedule_google_indexing() {
	
	if(get_option('PS_career_googleindexing_interval') != ''){
		$giInterval = get_option('PS_career_googleindexing_interval');
	} else {
		$giInterval = 'hourly';
	}
	
	if($giInterval != 'never'){
		wp_schedule_event( strtotime('06:00:00'), $giInterval, 'schedule_google_indexing' );
	} else {
		wp_clear_scheduled_hook( 'schedule_google_indexing' );
	}
}


add_action( 'schedule_application_source_import', 'importApplicationSource' ); 
function schedule_application_source_import() {
	
	$cronInterval = 'daily';
	
	wp_schedule_event( time(), $cronInterval, 'schedule_application_source_import' );
	
}

add_action( 'schedule_application_custom_fields_import', 'importApplicationCustomFields' ); 
function schedule_application_custom_fields_import() {
	
	$cronInterval = 'daily';
	
	wp_schedule_event( time(), $cronInterval, 'schedule_application_custom_fields_import' );
	
}

add_action( 'schedule_delete_old_candidates', 'deleteOldApplications' ); 
function schedule_delete_old_candidates() {
	
	$cronInterval = 'daily';
	
	wp_schedule_event( time(), $cronInterval, 'schedule_delete_old_candidates' );
	
}

add_action( 'schedule_application_custom_fields_template_import', 'importApplicationCustomFieldFormTemplates' ); 
function schedule_application_custom_fields_template_import() {
	
	$cronInterval = 'daily';
	
	wp_schedule_event( time(), $cronInterval, 'schedule_application_custom_fields_template_import' );
	
}

// Include Scripts in Backend
function prescreen_career_enqueue_script() {   
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
	wp_register_style( 'prescreen_career_admin_css', plugin_dir_url( __FILE__ ) . 'assets/css/career-backend.css' );
	wp_enqueue_style( 'prescreen_career_admin_css' );
	wp_enqueue_script( 'career_scripts', plugin_dir_url( __FILE__ ) . 'assets/js/career-scripts.js' );
    
}
add_action('admin_enqueue_scripts', 'prescreen_career_enqueue_script');

// Include Scripts in Frontend
function prescreen_career_enqueue_frontend_script(){
	wp_register_style( 'prescreen_career_css', plugin_dir_url( __FILE__ ) . 'assets/css/career-frontend.css' );
	wp_enqueue_style( 'prescreen_career_css' );
	wp_enqueue_script( 'prescreen_career_js', plugin_dir_url( __FILE__ ) . 'assets/js/career-frontend-min.js?ver=1.8', '', '', true );
	wp_localize_script( 'prescreen_career_js', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'prescreen_career_enqueue_frontend_script' );

function include_maps_api(){
	if(get_option('PS_career_googleapikey') != ''){
		echo '<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key='.get_option('PS_career_googleapikey').'"></script>';
	}
}
add_action('wp_footer', 'include_maps_api');

// Get Distance for Radius Search
function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000){
	// convert from degrees to radians
	$latFrom = deg2rad($latitudeFrom);
	$lonFrom = deg2rad($longitudeFrom);
	$latTo = deg2rad($latitudeTo);
	$lonTo = deg2rad($longitudeTo);
	
	$lonDelta = $lonTo - $lonFrom;
	$a = pow(cos($latTo) * sin($lonDelta), 2) +
	pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
	$b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
	
	$angle = atan2(sqrt($a), $b);
	return intval(($angle * $earthRadius) / 1000);
}

// Check Feed URL
function checkUrl($fetchURL){
	if(!$fetchURL || !is_string($fetchURL) || ! preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $fetchURL)){
		return false;
	} else {
		$url = $fetchURL;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
		curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpcode === 200){
			return true;
		} else {
			return false;
		}
	}
}

// Function for getting XML
function get_xml_from_url($url){
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
	
	$xmlstr = curl_exec($ch);

	return $xmlstr;
}

function googleIndexingIsActive(){
	$googleIndexing = get_option( "PS_career_googleindexing" );
	if( isset( $googleIndexing ) and $googleIndexing != "" ){
		return true;
	} else {
		return false;
	}
}


function writeLogging($log){
	global $wpdb;
	$currentDate = date('d.m.Y H:i:s', time());

	$wpdb->insert( 
		$wpdb->prefix.'prescreen_logging',
		array(  
			'created' => $currentDate,
			'description' => $log
		), 
		array( 
			'%s','%s'
		)  
	);

}

add_action('wp_ajax_manually_import_jobs', 'importJobs');

// Import Jobs
function importJobs() {
	
	global $wpdb;
	global $jal_db_version;
	
	//error_log("++++++++++++++ \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
	//error_log("[".date("j.m.Y, H:i")."] Job Import started \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
	
	writeLogging('Job Import started');
	
	// If Google Index is Active -> Make two Arrays for "New" and "Delete"
	if(googleIndexingIsActive()){
		//error_log("[".date("j.m.Y, H:i")."] Google Indexing is active - Make Empty Array for New and Delete \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
		writeLogging('Google Indexing is active - Make Empty Array for New and Delete');
		$googleIndexingNew = array();
		$googleIndexingDelete = array();
	}
	
	$returnInDatabase = 0;
	$returnNotInDatabase = 0;
	
	$returnGoogleIndexDel = 0;
	$returnGoogleIndexNew = 0;
	
	$jobsPerIteration = 50;
	$loopcounter = 0;
	
	$jobcount = 0;
	
	$fetchURL = get_option('PS_career_xmlurl').'&format=xml&max_results='.$jobsPerIteration.'&start_display='.$loopcounter;
	//$fetchURL = get_option('PS_career_xmlurl');
	
	// If positive Response from URL 
	if(checkUrl($fetchURL) === true || checkUrl($fetchURL) != ''){
		
		date_default_timezone_set('Europe/Berlin');
		$date = date('d.m.Y H:i:s', time());
		
		//error_log("[".date("j.m.Y, H:i")."] Positive Response from XML Feed \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
		writeLogging('Positive Response from XML Feed');
		
		// Set Date in Last Update Table
		$wpdb->query( 
			$wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_last_update SET lastupdate = %s WHERE id = 1',$date )
		); 
		
		
		// Set all Entries to status = 0
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs SET status = %d',0 ) ); 
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_templates SET status = %d',0 ) ); 
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_departments SET status = %d',0 ) ); 
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_cities SET status = %d',0 ) ); 
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_positiontypes SET status = %d',0 ) ); 
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_seniorities SET status = %d',0 ) ); 
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_instances SET status = %d',0 ) ); 
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_teams SET status = %d',0 ) ); 
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_custom_data_fields SET status = %d',0 ) ); 
		$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs_industries SET status = %d',0 ) ); 
		
		//error_log("[".date("j.m.Y, H:i")."] All Entries set to status = 0 \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
		writeLogging('All Entries set to status = 0');

		// Iterate through XML Feed and Update or Create Entries
		for ($i = 0; $i <= 100; $i++) {
			$xmlstr = get_xml_from_url(get_option('PS_career_xmlurl').'&format=xml&max_results='.$jobsPerIteration.'&start_display='.$loopcounter);
			
			//$xmlstr = get_xml_from_url(get_option('PS_career_xmlurl'));
			$xmlobj = new SimpleXMLElement($xmlstr);
			$xmlobj = (array)$xmlobj;//optional
			
			
			
			if(count($xmlobj['jobs']) > 0){
			
			//error_log("[".date("j.m.Y, H:i")."] Count Entries ".count($xmlobj['jobs'])." \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
			//error_log("[".date("j.m.Y, H:i")."] Start Iteration ".$i." \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
			writeLogging('Count Entries '.count($xmlobj['jobs']).'');
			writeLogging('Start Iteration '.$i.'');
	
			foreach ($xmlobj['jobs']->job as $value) {
				
				// Check if Entry with handle exists in Database
				$getHandle = $value->handle->__toString();
				$alreadyInDatabaseQuery = "SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE handle = '".$getHandle."'";
				$isAlreadyInDatabase = $wpdb->get_results($alreadyInDatabaseQuery);
				
				$jobcount++;
				
				$templates = array();
				foreach ($value->template as $template) { 
					if($template->id != 0){
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_templates WHERE id = ".$template->id."");
					if(empty($isInDatabase)){
						$wpdb->insert( 
							$wpdb->prefix.'prescreen_jobs_templates',
							array(  
								'id' => $template->id->__toString(), 
								'status' => 1,
								'name' => $template->name->__toString()
							), 
							array( 
								'%d','%d','%s'
							)  
						);
					} else {
						$wpdb->update( 
							$wpdb->prefix.'prescreen_jobs_templates',
							array(  
								'id' => $template->id->__toString(), 
								'status' => 1,
								'name' => $template->name->__toString()
							), 
							array( 'id' => $template->id->__toString() ),
							array( 
								'%d','%d','%s'
							),
							array( '%s' )
						);
					}
					//print_r($template);
					array_push($templates, $template->id); 
					}
				}
				$templates = implode(",", $templates);
				
				$departments = array();
				foreach ($value->department as $department) { 
					if($department->id != 0){
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_departments WHERE id = ".$department->id."");
					if(empty($isInDatabase)){
						$wpdb->insert( 
							$wpdb->prefix.'prescreen_jobs_departments',
							array(  
								'id' => $department->id->__toString(), 
								'status' => 1,
								'title' => $department->title->__toString()
							), 
							array( 
								'%d','%d','%s'
							)  
						);
					} else {
						$wpdb->update( 
							$wpdb->prefix.'prescreen_jobs_departments',
							array(  
								'id' => $department->id->__toString(), 
								'status' => 1,
								'title' => $department->title->__toString()
							), 
							array( 'id' => $department->id->__toString() ),
							array( 
								'%d','%d','%s'
							),
							array( '%s' )
						);
					}
					}
					//print_r($department);
					array_push($departments, $department->id); 
				}
				$departments = implode(",", $departments);
	
				$cities = array();
				foreach ($value->city as $city) { 
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_cities WHERE id = ".$city->id."");
					if(empty($isInDatabase)){
						$wpdb->insert( 
							$wpdb->prefix.'prescreen_jobs_cities',
							array(  
								'id' => $city->id->__toString(), 
								'status' => 1,
								'title' => $city->title->__toString(),
								'country' => $city->country->__toString(),
								'countrycode' => $city->countryCode->__toString(),
								'lat' => $city->lat->__toString(),
								'lng' => $city->long->__toString(),
								'distance' => $city->lat->__toString().','.$city->long->__toString()
							), 
							array( 
								'%d','%d','%s','%s','%s','%s','%s','%s'
							)  
						);
					} else {
						$wpdb->update( 
							$wpdb->prefix.'prescreen_jobs_cities',
							array(  
								'id' => $city->id->__toString(), 
								'status' => 1,
								'title' => $city->title->__toString(),
								'country' => $city->country->__toString(),
								'countrycode' => $city->countryCode->__toString(),
								'lat' => $city->lat->__toString(),
								'lng' => $city->long->__toString(),
								'distance' => $city->lat->__toString().','.$city->long->__toString()
							), 
							array( 'id' => $city->id->__toString() ),
							array( 
								'%d','%d','%s','%s','%s','%s','%s','%s'
							),
							array( '%s' )
						);
					}
					//print_r($city);
					array_push($cities, $city->id); 
				}
				$cities = implode(",", $cities);	
				
				$positiontypes = array();
				foreach ($value->positionType as $positiontype) { 
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_positiontypes WHERE id = ".$positiontype->id."");
					if(empty($isInDatabase)){
						$wpdb->insert( 
							$wpdb->prefix.'prescreen_jobs_positiontypes',
							array(  
								'id' => $positiontype->id->__toString(), 
								'status' => 1,
								'title' => $positiontype->title->__toString()
							), 
							array( 
								'%d','%d','%s'
							)  
						);
					} else {
						$wpdb->update( 
							$wpdb->prefix.'prescreen_jobs_positiontypes',
							array(  
								'id' => $positiontype->id->__toString(), 
								'status' => 1,
								'title' => $positiontype->title->__toString()
							), 
							array( 'id' => $positiontype->id->__toString() ),
							array( 
								'%d','%d','%s'
							),
							array( '%s' )
						);
					}
					//print_r($positiontype);
					array_push($positiontypes, $positiontype->id); 
				}
				$positiontypes = implode(",", $positiontypes);	
				
				$seniorities = array();
				foreach ($value->seniority as $seniority) { 
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_seniorities WHERE id = ".$seniority->id."");
					if(empty($isInDatabase)){
						$wpdb->insert( 
							$wpdb->prefix.'prescreen_jobs_seniorities',
							array(  
								'id' => $seniority->id->__toString(), 
								'status' => 1,
								'title' => $seniority->title->__toString()
							), 
							array( 
								'%d','%d','%s'
							)  
						);
					} else {
						$wpdb->update( 
							$wpdb->prefix.'prescreen_jobs_seniorities',
							array(  
								'id' => $seniority->id->__toString(), 
								'status' => 1,
								'title' => $seniority->title->__toString()
							), 
							array( 'id' => $seniority->id->__toString() ),
							array( 
								'%d','%d','%s'
							),
							array( '%s' )
						);
					}
					//print_r($seniority);
					array_push($seniorities, $seniority->id); 
				}
				$seniorities = implode(",", $seniorities);	
				
				$instances = array();
				foreach ($value->instance as $instance) { 
					if($instance->id != 0){
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_instances WHERE id = ".$instance->id."");
					if(empty($isInDatabase)){
						$wpdb->insert( 
							$wpdb->prefix.'prescreen_jobs_instances',
							array(  
								'id' => $instance->id->__toString(), 
								'status' => 1,
								'is_legal_entity' => $instance->is_legal_entity->__toString(),
								'name' => $instance->name->__toString(),
								'handle' => $instance->handle->__toString()
							), 
							array( 
								'%d','%d','%s','%s','%s'
							)  
						);
					} else {
						$wpdb->update( 
							$wpdb->prefix.'prescreen_jobs_instances',
							array(  
								'id' => $instance->id->__toString(), 
								'status' => 1,
								'is_legal_entity' => $instance->is_legal_entity->__toString(),
								'name' => $instance->name->__toString(),
								'handle' => $instance->handle->__toString()
							), 
							array( 'id' => $instance->id->__toString() ),
							array( 
								'%d','%d','%s','%s','%s'
							),
							array( '%s' )
						);
					}
					}
					//print_r($instance);
					array_push($instances, $instance->id); 
				}
				$instances = implode(",", $instances);	

				$teams = array();
				foreach ($value->team as $team) { 
					if($team->id != 0){
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_teams WHERE id = ".$team->id."");
					if(empty($isInDatabase)){
						$wpdb->insert( 
							$wpdb->prefix.'prescreen_jobs_teams',
							array(  
								'id' => $team->id->__toString(), 
								'status' => 1,
								'name' => $team->name->__toString()
							), 
							array( 
								'%d','%d','%s'
							)  
						);
					} else {
						$wpdb->update( 
							$wpdb->prefix.'prescreen_jobs_teams',
							array(  
								'id' => $team->id->__toString(), 
								'status' => 1,
								'name' => $team->name->__toString()
							), 
							array( 'id' => $team->id->__toString() ),
							array( 
								'%d','%d','%s'
							),
							array( '%s' )
						);
					}
					}
					//print_r($team);
					array_push($teams, $team->id); 
				}
				$teams = implode(",", $teams);
				
				$custom_data_fields = array();
				foreach ($value->custom_data_fields->custom_data_field as $custom_data_field) { 
					//print_r($custom_data_field);
					if($custom_data_field->value_id != ''){
						$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_custom_data_fields WHERE id = ".$custom_data_field->id." AND value_id = ".$custom_data_field->value_id."");
					} else {
						$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_custom_data_fields WHERE id = ".$custom_data_field->id."");
					}
					
					if(empty($isInDatabase)){
						$wpdb->insert( 
							$wpdb->prefix.'prescreen_jobs_custom_data_fields',
							array(  
								'id' => $custom_data_field->id->__toString(), 
								'status' => 1,
								'name' => $custom_data_field->name->__toString(),
								'form_label' => $custom_data_field->form_label->__toString(),
								'value_id' => $custom_data_field->value_id->__toString(),
								'value' => $custom_data_field->value->__toString(),
								'value_label' => $custom_data_field->value_label->__toString()
							), 
							array( 
								'%d','%d','%s','%s','%d','%s','%s'
							)  
						);
					} else {
						$wpdb->update( 
							$wpdb->prefix.'prescreen_jobs_custom_data_fields',
							array(  
								'id' => $custom_data_field->id->__toString(), 
								'status' => 1,
								'name' => $custom_data_field->name->__toString(),
								'form_label' => $custom_data_field->form_label->__toString(),
								'value_id' => $custom_data_field->value_id->__toString(),
								'value' => $custom_data_field->value->__toString(),
								'value_label' => $custom_data_field->value_label->__toString()
							), 
							array( 'id' => $custom_data_field->id->__toString(), 'value_id' => $custom_data_field->value_id->__toString() ),
							array( 
								'%d','%d','%s','%s','%d','%s','%s'
							),
							array( '%s','%s' )
						);
					}
					//print_r($custom_data_field);
					array_push($custom_data_fields, $custom_data_field->id); 
				}
				$custom_data_fields = implode(",", $custom_data_fields);	
				
				$industries = array();
				foreach ($value->industry as $industry) { 
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_industries WHERE id = ".$industry->id."");
					if(empty($isInDatabase)){
						$wpdb->insert( 
							$wpdb->prefix.'prescreen_jobs_industries',
							array(  
								'id' => $industry->id->__toString(), 
								'status' => 1,
								'name' => $industry->name->__toString()
							), 
							array( 
								'%d','%d','%s'
							)  
						);
					} else {
						$wpdb->update( 
							$wpdb->prefix.'prescreen_jobs_industries',
							array(  
								'id' => $industry->id->__toString(), 
								'status' => 1,
								'name' => $industry->name->__toString()
							), 
							array( 'id' => $industry->id->__toString() ),
							array( 
								'%d','%d','%s'
							),
							array( '%s' )
						);
					}
					//print_r($industry);
					array_push($industries, $industry->id); 
				}
				$industries = implode(",", $industries);	
				
				// If Entry exists in Database -> Update Entry
				if(!empty($isAlreadyInDatabase)){
					//$return .= 'is in database <br />';
					
					
					
					$returnInDatabase++;
					
					$wpdb->update( 
						$wpdb->prefix.'prescreen_jobs',
						array( 
							'status' => 1,
							'title' => $value->title->__toString(),
							'shorthandle' => $value->shortHandle->__toString(), 
							'showurl' => $value->showUrl->__toString(), 
							'applyurl' => $value->applyUrl->__toString(), 
							'applyaddress' => $value->applyAddress->__toString(),
							'template' => $templates,
							'bannerurl' => $value->bannerUrl->__toString(),
							'bannerfooterurl' => $value->bannerFooterUrl->__toString(),
							'department' => $departments,
							'city' => $cities,
							'positiontype' => $positiontypes,
							'seniority' => $seniorities,
							'headcount' => $value->headcount->__toString(),
							'instance' => $instances,
							'team' => $teams,
							'custom_data_fields' => json_encode($value->custom_data_fields),
							'simple_html_content' => $value->simple_html_content->__toString(),
							'published_at' => $value->published_at->__toString(),
							'startofwork' => $value->startOfWork->__toString(),
							'industry' => $industries,
							'description' => json_encode($value->description)
						), 
						array( 'handle' => $getHandle ), //Welcher Eintrag ist davon betroffen?
						array( 
							'%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
						),
						array( '%s' )
					);
					
					//error_log("[".date("j.m.Y, H:i")."] Entry with sh ".$value->shortHandle->__toString()." was updated and has status = 1 \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
					writeLogging('Entry with sh '.$value->shortHandle->__toString().' was updated and has status = 1');
					
					
				// If Entry doesn't exist in Database -> Create Entry
				} else {
					//$return .= 'is not in database <br />';
					
					
					$returnNotInDatabase++;
					
					// Set google_indexing = '' for new entries
					$googleIndexStatus = '';
					
					$wpdb->insert( 
						$wpdb->prefix.'prescreen_jobs',
						array(  
							'status' => 1, 
							'google_indexing' => $googleIndexStatus, 
							'title' => $value->title->__toString(), 
							'handle' => $value->handle->__toString(), 
							'shorthandle' => $value->shortHandle->__toString(), 
							'showurl' => $value->showUrl->__toString(), 
							'applyurl' => $value->applyUrl->__toString(), 
							'applyaddress' => $value->applyAddress->__toString(),
							'template' => $templates,
							'bannerurl' => $value->bannerUrl->__toString(),
							'bannerfooterurl' => $value->bannerFooterUrl->__toString(),
							'department' => $departments,
							'city' => $cities,
							'positiontype' => $positiontypes,
							'seniority' => $seniorities,
							'headcount' => $value->headcount->__toString(),
							'instance' => $instances,
							'team' => $teams,
							'custom_data_fields' => json_encode($value->custom_data_fields),
							'simple_html_content' => $value->simple_html_content->__toString(),
							'published_at' => $value->published_at->__toString(),
							'startofwork' => $value->startOfWork->__toString(),
							'industry' => $industries,
							'description' => json_encode($value->description)
						), 
						array( 
							'%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
						)  
					);
					
					//error_log("[".date("j.m.Y, H:i")."] Entry with sh ".$value->shortHandle->__toString()." was created and has status = 1 \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
					writeLogging('Entry with sh '.$value->shortHandle->__toString().' was created and has status = 1');
					
				}

			}
		
			//print_r($xmlobj);
			$loopcounter = $loopcounter + $jobsPerIteration;
			
			} else {
				continue;
			}
		}
		
		// Loop has finished
		
		// Check if Google Indexing is Active
		if(googleIndexingIsActive()){
			
			// Write Shorthandle from Job with Status != 1 in Array Delete -> and don't delete Jobs with Status < 1 immediately
			//error_log("[".date("j.m.Y, H:i")."] Google Indexing is active -> write Shorthandle with status != 1 in Array Delete \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
			writeLogging('Google Indexing is active -> write Shorthandle with status != 1 in Array Delete');
			
			$getJobsWithStatusZero = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE status != 1");
    		foreach ($getJobsWithStatusZero as $job) { 
				
				// Update google_indexing -> inactive
				/*
				$wpdb->update( $wpdb->prefix.'prescreen_jobs',
					array( 
						'google_indexing' => 'inactive'
					), 
					array( 'status' => 0 ), //Welcher Eintrag ist davon betroffen?
					array( 
						'%s'
					),
					array( '%d' )
				);
				*/
				// Push shorthandle to $googleIndexingDelete Array
				array_push($googleIndexingDelete, $job->shorthandle);
				//error_log("[".date("j.m.Y, H:i")."] Entry with sh ".$job->shorthandle." is in Array Delete \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
				writeLogging('Entry with sh '.$job->shorthandle.' is in Array Delete');
			}
			
			// Write Shorthandle from Job with google_indexing = '' in Array New
			$getJobsWithStatusOne = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE google_indexing = ''");
			
			//error_log("[".date("j.m.Y, H:i")."] Google Indexing is active -> write Shorthandle with google_indexing = '' in Array New \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
			writeLogging('Google Indexing is active -> write Shorthandle with google_indexing = "" in Array New');
			
			foreach ($getJobsWithStatusOne as $job) { 
				
				// Push shorthandle to $googleIndexingDelete Array
				array_push($googleIndexingNew, $job->shorthandle);
				//error_log("[".date("j.m.Y, H:i")."] Entry with sh ".$job->shorthandle." is in Array New \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
				writeLogging('Entry with sh '.$job->shorthandle.' is in Array New');
			}
			
		// If Google Indexing is not active -> Delete all Jobs with Status -1 or 0
		} else {
			$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs WHERE status != %d', 1) );
			//error_log("[".date("j.m.Y, H:i")."] Google Indexing is not active -> all Jobs with status != 1 were deleted \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
			writeLogging('Google Indexing is not active -> all Jobs with status != 1 were deleted');
		}

		$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs_templates WHERE status = %d', 0) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs_departments WHERE status = %d', 0) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs_cities WHERE status = %d', 0) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs_positiontypes WHERE status = %d', 0) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs_seniorities WHERE status = %d', 0) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs_instances WHERE status = %d', 0) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs_teams WHERE status = %d', 0) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs_custom_data_fields WHERE status = %d', 0) );
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs_industries WHERE status = %d', 0) );
		
		// If Google Indexing is active -> Iterate through New and Delete Array
		if(googleIndexingIsActive()){

			foreach ($googleIndexingDelete as $deleteShorthandle) {
				//echo $deleteShorthandle;
				if(googleIndexing($deleteShorthandle,'delete') === 200){
					//$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs WHERE shorthandle = %d', $deleteShorthandle) );
					//$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs SET google_indexing = %s WHERE shorthandle = '.$deleteShorthandle.'','inactive' ) ); 
					$wpdb->update( $wpdb->prefix.'prescreen_jobs',
						array( 
							'google_indexing' => 'inactive'
						), 
						array( 'shorthandle' => $deleteShorthandle ), //Welcher Eintrag ist davon betroffen?
						array( 
							'%s'
						),
						array( '%s' )
					);
					$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs WHERE shorthandle = %s', $deleteShorthandle) );
					$returnGoogleIndexDel++;
					
					//error_log("[".date("j.m.Y, H:i")."] Google Indexing is active -> Response-Status: 200 - Entry with ".$deleteShorthandle." was deleted from Google and Database \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
					writeLogging('Google Indexing is active -> Response-Status: 200 - Entry with '.$deleteShorthandle.' was deleted from Google and Database');
					
				} elseif(googleIndexing($deleteShorthandle,'delete') === 429){
					//error_log("[".date("j.m.Y, H:i")."] Google Indexing is active -> Response-Status: 429 - Stop everything at Entry with ".$deleteShorthandle." \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
					writeLogging('Google Indexing is active -> Response-Status: 429 - Stop everything at Entry with '.$deleteShorthandle.'');
					break;
				} else {
					//error_log("[".date("j.m.Y, H:i")."] Google Indexing is active -> Response-Status: ".googleIndexing($deleteShorthandle,'delete')." \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
					writeLogging('Google Indexing is active -> Response-Status: '.googleIndexing($deleteShorthandle,'delete').'');
					continue;
				}
			}

			foreach ($googleIndexingNew as $newShorthandle) {
				//echo $newShorthandle;
				//print_r(googleIndexing($newShorthandle,'new'));
				if(googleIndexing($newShorthandle,'new') === 200){
					$wpdb->update( $wpdb->prefix.'prescreen_jobs',
						array( 
							'google_indexing' => 'active'
						), 
						array( 'shorthandle' => $newShorthandle ), //Welcher Eintrag ist davon betroffen?
						array( 
							'%s'
						),
						array( '%s' )
					);
					$returnGoogleIndexNew++;
					//error_log("[".date("j.m.Y, H:i")."] Google Indexing is active -> Response-Status: 200 - Entry with ".$newShorthandle." was added to Google and google_indexing is active \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
					writeLogging('Google Indexing is active -> Response-Status: 200 - Entry with '.$newShorthandle.' was added to Google and google_indexing is active');
				} elseif(googleIndexing($newShorthandle,'new') === 429){
					//error_log("[".date("j.m.Y, H:i")."] Google Indexing is active -> Response-Status: 429 - Stop everything at Entry with ".$newShorthandle." \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
					writeLogging('Google Indexing is active -> Response-Status: 429 - Stop everything at Entry with '.$newShorthandle.'');
					break;
				} else {
					//error_log("[".date("j.m.Y, H:i")."] Google Indexing is active -> Response-Status: ".googleIndexing($newShorthandle,'new')." \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
					writeLogging('Google Indexing is active -> Response-Status: '.googleIndexing($newShorthandle,'new').'');
					continue;
				}
			}
			
		}
		
		if(googleIndexingIsActive()){
			//error_log("[".date("j.m.Y, H:i")."] ".$returnInDatabase." Jobs wurden aktualisiert - ".$returnNotInDatabase." Jobs wurden neu hinzugefügt (".$returnGoogleIndexDel." Jobs wurden aus dem Google Index entfernt - ".$returnGoogleIndexNew." Jobs wurden an den Google Index gesendet)"." \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
			writeLogging(''.$returnInDatabase.' Jobs wurden aktualisiert - '.$returnNotInDatabase.' Jobs wurden neu hinzugefügt ('.$returnGoogleIndexDel.' Jobs wurden aus dem Google Index entfernt - '.$returnGoogleIndexNew.' Jobs wurden an den Google Index gesendet)'.'');
			echo $returnInDatabase.' Jobs wurden aktualisiert - '.$returnNotInDatabase.' Jobs wurden neu hinzugefügt<br />'.$returnGoogleIndexDel.' Jobs wurden aus dem Google Index entfernt - '.$returnGoogleIndexNew.' Jobs wurden an den Google Index gesendet';
		} else {
			//error_log("[".date("j.m.Y, H:i")."] ".$returnInDatabase." Jobs wurden aktualisiert - ".$returnNotInDatabase." Jobs wurden neu hinzugefügt"." \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
			writeLogging(''.$returnInDatabase.' Jobs wurden aktualisiert - '.$returnNotInDatabase.' Jobs wurden neu hinzugefügt'.'');
			echo $returnInDatabase.' Jobs wurden aktualisiert - '.$returnNotInDatabase.' Jobs wurden neu hinzugefügt';
		}
		
		
		
	} else {
		//error_log("[".date("j.m.Y, H:i")."] Ein Fehler ist aufgetreten \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
		writeLogging('Ein Fehler ist aufgetreten');
		echo 'Ein Fehler ist aufgetreten.';
	}
	wp_die();
}

// Test Google Index via Button in Backend
add_action('wp_ajax_test_google_index', 'testGoogleIndexWithButton');

// Test Google Index via Button
function testGoogleIndexWithButton(){
	//error_log("[".date("j.m.Y, H:i")."] Test Google Indexing - ".testGoogleIndexing()." \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
	writeLogging('Test Google Indexing - '.testGoogleIndexing().'');
	echo testGoogleIndexing();
	wp_die();
}


// Update Google Index via Cron Job or via Button in Backend
add_action('wp_ajax_manually_update_google_index', 'updateGoogleIndexWithButton');

// Update Google Index via Button
function updateGoogleIndexWithButton(){
	global $wpdb;
	$googleIndexingNew = array();
	$googleIndexingDelete = array();
	
	$returnGoogleIndexDel = 0;
	$returnGoogleIndexNew = 0;
	
	$googleStatusCode = '';
	
	$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_jobs SET google_indexing = %s','' ) ); 
	
	echo 'Alle google_indexing Spalten auf "" gesetzt.<br />';
	
	$getJobsWithStatusZero = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE status != 1");
	foreach ($getJobsWithStatusZero as $job) { 
		
		// Update google_indexing -> inactive
		array_push($googleIndexingDelete, $job->shorthandle);
	}
	//print_r($googleIndexingDelete);
	echo count($googleIndexingDelete).' Jobs mit Status 0 in DELETE-Array geschrieben.<br />';
	
	$getJobsWithStatusOne = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE status = 1");
	foreach ($getJobsWithStatusOne as $job) { 
		
		// Push shorthandle to $googleIndexingDelete Array
		array_push($googleIndexingNew, $job->shorthandle);
	}
	echo count($googleIndexingNew).' Jobs mit Status 1 in NEW-Array geschrieben.<br />';
	
	
	foreach ($googleIndexingDelete as $deleteShorthandle) {
		//echo $deleteShorthandle;
		if(googleIndexing($deleteShorthandle,'delete') === 200){
			$googleStatusCode = googleIndexing($deleteShorthandle,'delete');
			$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs WHERE shorthandle = %d', $deleteShorthandle) );
			$returnGoogleIndexDel++;
		} elseif(googleIndexing($deleteShorthandle,'delete') === 429){
			$googleStatusCode = googleIndexing($deleteShorthandle,'delete');
			break;
		} else {
			$googleStatusCode = googleIndexing($deleteShorthandle,'delete');
			continue;
		}
	}
	
	foreach ($googleIndexingNew as $newShorthandle) {
		//echo $newShorthandle;
		//print_r(googleIndexing($newShorthandle,'new'));
		if(googleIndexing($newShorthandle,'new') === 200){
			$googleStatusCode = googleIndexing($newShorthandle,'new');
			$wpdb->update( $wpdb->prefix.'prescreen_jobs',
				array( 
					'google_indexing' => 'active'
				), 
				array( 'shorthandle' => $newShorthandle ), //Welcher Eintrag ist davon betroffen?
				array( 
					'%s'
				),
				array( '%s' )
			);
			$returnGoogleIndexNew++;
		} elseif(googleIndexing($newShorthandle,'new') === 429){
			$googleStatusCode = googleIndexing($newShorthandle,'new');
			break;
		} else {
			$googleStatusCode = googleIndexing($newShorthandle,'new');
			continue;
		}
	}
	
	
	
	echo $returnGoogleIndexDel.' Jobs wurden aus dem Google Index entfernt - '.$returnGoogleIndexNew.' Jobs wurden an den Google Index gesendet<br />Last Status Code: '.$googleStatusCode.'';
	wp_die();
}

// Update Google Index via Cronjob

function updateGoogleIndex(){
	global $wpdb;
	
	
	$googleIndexingNew = array();
	$googleIndexingDelete = array();
	
	//error_log("[".date("j.m.Y, H:i")."] CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Create New and Delete Array \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
	//writeLogging('CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Create New and Delete Array');
	
	$returnGoogleIndexDel = 0;
	$returnGoogleIndexNew = 0;
	
	$googleStatusCode = '';
	
	//error_log("[".date("j.m.Y, H:i")."] CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Write all Jobs where status != 1 in Delete Array \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
	//writeLogging('CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Write all Jobs where status != 1 in Delete Array');
	/*
	$getJobsWithStatusZero = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE status != 1");
	foreach ($getJobsWithStatusZero as $job) { 
		
		// Update google_indexing -> inactive
		array_push($googleIndexingDelete, $job->shorthandle);
	}
	//print_r($googleIndexingDelete);
	echo count($googleIndexingDelete).' Jobs mit Status 0 in DELETE-Array geschrieben.<br />';
	*/
	
	//error_log("[".date("j.m.Y, H:i")."] CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Write all Jobs where google_index = '' in New Array \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
	//writeLogging('CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Write all Jobs where google_index = '' in New Array');
	/*
	$getJobsWithStatusOne = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE google_indexing = ''");
	foreach ($getJobsWithStatusOne as $job) { 
		
		// Push shorthandle to $googleIndexingDelete Array
		array_push($googleIndexingNew, $job->shorthandle);
	}

	echo count($googleIndexingNew).' Jobs mit google_indexing = "" in NEW-Array geschrieben.<br />';
	*/
	
	
	//error_log("[".date("j.m.Y, H:i")."] CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Try to send all Jobs from Delete Array to Google \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
	//writeLogging('CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Try to send all Jobs from Delete Array to Google');
	/*
	foreach ($googleIndexingDelete as $deleteShorthandle) {
		//echo $deleteShorthandle;
		if(googleIndexing($deleteShorthandle,'delete') === 200){
			$googleStatusCode = googleIndexing($deleteShorthandle,'delete');
			$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_jobs WHERE shorthandle = %d', $deleteShorthandle) );
			$returnGoogleIndexDel++;
		} elseif(googleIndexing($deleteShorthandle,'delete') === 429){
			$googleStatusCode = googleIndexing($deleteShorthandle,'delete');
			break;
		} else {
			$googleStatusCode = googleIndexing($deleteShorthandle,'delete');
			continue;
		}
	}
	*/
	
	//error_log("[".date("j.m.Y, H:i")."] CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Try to send all Jobs from New Array to Google \n", 3, plugin_dir_path(__FILE__)."".date("Y_m_j")."_debug.log");
	//writeLogging('CRON JOB GOOGLE INDEX UPDATE (DRY RUN): Try to send all Jobs from New Array to Google');
	
	/*
	foreach ($googleIndexingNew as $newShorthandle) {
		//echo $newShorthandle;
		//print_r(googleIndexing($newShorthandle,'new'));
		if(googleIndexing($newShorthandle,'new') === 200){
			$googleStatusCode = googleIndexing($newShorthandle,'new');
			$wpdb->update( $wpdb->prefix.'prescreen_jobs',
				array( 
					'google_indexing' => 'active'
				), 
				array( 'shorthandle' => $newShorthandle ), //Welcher Eintrag ist davon betroffen?
				array( 
					'%s'
				),
				array( '%s' )
			);
			$returnGoogleIndexNew++;
		} elseif(googleIndexing($newShorthandle,'new') === 429){
			$googleStatusCode = googleIndexing($newShorthandle,'new');
			break;
		} else {
			$googleStatusCode = googleIndexing($newShorthandle,'new');
			continue;
		}
	}
	*/
	
	
	//echo $returnGoogleIndexDel.' Jobs wurden aus dem Google Index entfernt - '.$returnGoogleIndexNew.' Jobs wurden an den Google Index gesendet<br />Last Status Code: '.$googleStatusCode.'';
	wp_die();
}


function kh_update_media_seo() {
	importJobs();
}
add_action( 'admin_post_my_job_update', 'kh_update_media_seo' );


function show_job_xml_function($atts) {
	importJobs();
}
add_shortcode('show_job_xml', 'show_job_xml_function');



function job_snippet_function($atts) {
	global $wpdb;
	global $jal_db_version;
	
	extract(shortcode_atts(array(
		'type' => '',
		'jobcontent' => '',
		'show' => '',
		'format' => ''
	), $atts));
	
	if(isset($_GET['sh']) || $_COOKIE['job_sh'] ){
		if( isset( $_COOKIE['job_sh'] )) $shortHandle = $_COOKIE['job_sh'];
		if( isset( $_GET['sh'] )) $shortHandle = $_GET['sh'];
		$data = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE shorthandle = '".$shortHandle."'");
		
		
		if($type === 'template'){
			$returnArray = array();
			$array = explode(',', $data[0]->template);
			
			if($show === ''){
				$show = 'name';
			}
			
			foreach ($array as $value) { 
				$template = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_templates WHERE id = ".$value."");
				array_push($returnArray, $template[0]->$show); 
			}
			$returnArray = implode(", ", $returnArray);
			return $returnArray;
		} elseif($type === 'department'){
			$returnArray = array();
			$array = explode(',', $data[0]->department);
			
			if($show === ''){
				$show = 'title';
			}
			
			foreach ($array as $value) { 
				$template = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_departments WHERE id = ".$value."");
				array_push($returnArray, $template[0]->$show); 
			}
			$returnArray = implode(", ", $returnArray);
			return $returnArray;
		} elseif($type === 'city'){
			$returnArray = array();
			$array = explode(',', $data[0]->city);
			
			if($show === ''){
				$show = 'title';
			}

			foreach ($array as $value) { 
				$template = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_cities WHERE id = ".$value."");
				array_push($returnArray, $template[0]->$show); 
			}
			$returnArray = implode(", ", $returnArray);
			return $returnArray;
		} elseif($type === 'positiontype'){
			$returnArray = array();
			$array = explode(',', $data[0]->positiontype);
			
			if($show === ''){
				$show = 'title';
			}
			
			foreach ($array as $value) { 
				$template = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_positiontypes WHERE id = ".$value."");
				array_push($returnArray, $template[0]->$show); 
			}
			$returnArray = implode(", ", $returnArray);
			return $returnArray;
		} elseif($type === 'seniority'){
			$returnArray = array();
			$array = explode(',', $data[0]->seniority);
			
			if($show === ''){
				$show = 'title';
			}
			
			foreach ($array as $value) { 
				$template = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_seniorities WHERE id = ".$value."");
				array_push($returnArray, $template[0]->$show); 
			}
			$returnArray = implode(", ", $returnArray);
			return $returnArray;
		} elseif($type === 'instance'){
			$returnArray = array();
			$array = explode(',', $data[0]->instance);
			
			if($show === ''){
				$show = 'name';
			}
			
			foreach ($array as $value) { 
				if(isset($value) && $value != ''){
					$template = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_instances WHERE id = ".$value."");
					array_push($returnArray, $template[0]->$show); 
				}
			}
			
			
			$returnArray = implode(", ", $returnArray);
			return $returnArray;
			
		} elseif($type === 'team'){
			$returnArray = array();
			$array = explode(',', $data[0]->team);
			
			if($show === ''){
				$show = 'name';
			}
			
			foreach ($array as $value) { 
				$template = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_teams WHERE id = ".$value."");
				array_push($returnArray, $template[0]->$show); 
			}
			$returnArray = implode(", ", $returnArray);
			return $returnArray;
		} elseif(strpos($type, 'custom_data_fields') !== false){
			$returnArray = array();
			preg_match('#\((.*?)\)#', $type, $match);
			$keyword = 'custom_data_fields';
			$cdfID = $match[1];
			
			$template = $wpdb->get_results("SELECT custom_data_fields FROM ".$wpdb->prefix."prescreen_jobs WHERE shorthandle = '".$shortHandle."'");
			$template = json_decode($template[0]->custom_data_fields, true);
			
			if($show === ''){
				$show = 'value';
			}
			
			if(isset($template['custom_data_field'][0])){
				foreach ($template['custom_data_field'] as $cdf) { 
					
					if(isset($cdf['id']) && $cdf['id'] === $cdfID){
						if(!empty($cdf[$show])){
							array_push($returnArray, $cdf[$show]); 
						} else {
							array_push($returnArray, ''); 	
						}
					}
				}
			} else {
				$cdf = $template['custom_data_field'];
				if(isset($cdf['id']) && $cdf['id'] === $cdfID){
					if(!empty($cdf[$show])){
						array_push($returnArray, $cdf[$show]); 
					} else {
						array_push($returnArray, ''); 	
					}
				}
			}

			$returnArray = implode(", ", $returnArray);
			return $returnArray;
			
		} elseif($type === 'industry'){
			$returnArray = array();
			$array = explode(',', $data[0]->industry);
			
			if($show === ''){
				$show = 'name';
			}
			
			
			foreach ($array as $value) { 
				$template = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_industries WHERE id = ".$value."");
				array_push($returnArray, $template[0]->$show); 
			}
			$returnArray = implode(", ", $returnArray);
			return $returnArray;
		} elseif($type === 'description'){
			$arr = json_decode($data[0]->description);
			if($jobcontent != ''){
				//return $jobcontent;
				$jobcontent = 'job_content_'.$jobcontent;
				//return $arr->{$jobcontent};
				
				if(isset($arr->{$jobcontent})){
				
					$jobcontent = $arr->{$jobcontent};
					$jobcontent = strip_tags_content( $jobcontent, '<style>', TRUE );
					//return preg_replace("/\{.*\}/sU", "{}", $arr->{$jobcontent});
					return $jobcontent;
				
				}
				
			} else {
				return json_decode($data[0]->description)->job_content_1;
			}
		} elseif($type === 'published_at' || $type === 'startofwork'){
			if (DateTime::createFromFormat('Y-m-d', $data[0]->$type) !== FALSE) {
				if($format != ''){
					$newDate = date($format, strtotime($data[0]->$type));
					return $newDate;
				} else {
					return $data[0]->$type;
				}
			} else {
				return $data[0]->$type;
			}
		} else {
			return $data[0]->$type;
		}
	}

}

add_shortcode('job_snippet', 'job_snippet_function');


// If job_list or filter has Where Argument build SQL string
function getWhereSQL($where,$is,$isSelector){
	global $wpdb;
	global $post;
	
	
	if($is === 'default' && $where === 'default'){
		if(isset($_GET['is'])){
			$is = $_GET['is'];
			if(isset($_GET['where'])){
				$where = $_GET['where'];
			} else {
				$where = '';
			}
		} else if(isset($_GET['isnot'])){
			$is = $_GET['isnot'];
			if(isset($_GET['where'])){
				$where = $_GET['where'];
			} else {
				$where = '';
			}
		} else {
			$is = '';
			$where = '';
		}
	}
	
	if($where === ''){
		/*
		if(googleIndexingIsActive()){
			$whereSQL = 'WHERE google_indexing NOT LIKE "inactive"';
		} else {
			$whereSQL = '';
		}
		*/
		$whereSQL = 'WHERE '.$wpdb->prefix.'prescreen_jobs.status >= 0';
		return $whereSQL;
	} else {
		
		// If ISNOT Query
		if($isSelector === 'isnot'){
		
			// Check for multiple where arguments
			if (strpos($where, ',') !== false) {
				$whereArray = explode(',', $where);
				$isArray = explode(',', $is);
				
				$buildSQLString = '';
				foreach ($whereArray as $key=>$whereArgument) { 
					if(strpos($whereArgument, 'custom_data_fields') !== false) {
						$buildSQLString .= "custom_data_fields NOT LIKE '%".$isArray[$key]."%' AND ";
					} else {
						$buildSQLString .= $whereArgument." NOT LIKE '%".$isArray[$key]."%' AND ";
					}
				}
				$buildSQLString  = substr_replace($buildSQLString ,"", -4);
				$whereSQL = "WHERE ".$buildSQLString."";
				return $whereSQL.' AND '.$wpdb->prefix.'prescreen_jobs.status >= 0';
				/*
				if(googleIndexingIsActive()){
					return $whereSQL.' AND google_indexing NOT LIKE "inactive"';
				} else {
					return $whereSQL;
				}*/
			} else {
				// Check for Value in ()
				preg_match('/\(([^\)]*)\)/', $where, $matches);
				
				$is_array = explode (",", $is);
				//print_r($is_array);
				
				// If more than one IS value
				if (sizeof($is_array) > 1) {
					if(isset($matches[1])){
						$whereSQL = "WHERE ".$wpdb->prefix."prescreen_jobs.custom_data_fields NOT LIKE '%".$is_array[0]."%'";
						$count = 0;
						foreach ($is_array as $is_value) {
							
							if($count > 0){
						    $whereSQL .= "AND prescreen_jobs.custom_data_fields NOT LIKE '%".$is_array[$count]."%'";
							}
							$count++;
						}
					} else {
						$whereSQL = "WHERE ".$wpdb->prefix."prescreen_jobs.".$where." <> '".$is_array[0]."'";
						$count = 0;
						foreach ($is_array as $is_value) {
							if($count > 0){
						    	$whereSQL .= "AND ".$where." <> '".$is_array[$count]."'";
							}
							$count++;
						}
					}
				} else {
					if(isset($matches[1])){
						$whereSQL = "WHERE ".$wpdb->prefix."prescreen_jobs.custom_data_fields NOT LIKE '%".$is."%'";
					} else {
						$whereSQL = "WHERE ".$wpdb->prefix."prescreen_jobs.".$where." <> '".$is."'";
					}
				}
				//print_r($whereSQL);
				return $whereSQL.' AND '.$wpdb->prefix.'prescreen_jobs.status >= 0';
				/*
				if(googleIndexingIsActive()){
					return $whereSQL.' AND google_indexing NOT LIKE "inactive"';
				} else {
					return $whereSQL;
				}*/
			}
		//print_r($whereSQL);
		
		
			// If IS Query
			} else {
			
			// Check for multiple where arguments
			if (strpos($where, ',') !== false) {
				$whereArray = explode(',', $where);
				$isArray = explode(',', $is);
				
				$buildSQLString = '';
				foreach ($whereArray as $key=>$whereArgument) { 
					if(strpos($whereArgument, 'custom_data_fields') !== false) {
						$buildSQLString .= "custom_data_fields LIKE '%".$isArray[$key]."%' AND ";
					} else {
						$buildSQLString .= $whereArgument." LIKE '%".$isArray[$key]."%' AND ";
					}
				}
				$buildSQLString  = substr_replace($buildSQLString ,"", -4);
				$whereSQL = "WHERE ".$buildSQLString."";
				return $whereSQL.' AND '.$wpdb->prefix.'prescreen_jobs.status >= 0';
				/*
				if(googleIndexingIsActive()){
					return $whereSQL.' AND google_indexing NOT LIKE "inactive"';
				} else {
					return $whereSQL;
				}
				*/
			} else {
				// Check for Value in ()
				preg_match('/\(([^\)]*)\)/', $where, $matches);
				
				$is_array = explode (",", $is);
				//print_r($is_array);
				
				// If more than one IS value
				if (sizeof($is_array) > 1) {
					if(isset($matches[1])){
						$whereSQL = "WHERE ".$wpdb->prefix."prescreen_jobs.custom_data_fields LIKE '%".$is_array[0]."%'";
						$count = 0;
						foreach ($is_array as $is_value) {
							
							if($count > 0){
						    $whereSQL .= "OR prescreen_jobs.custom_data_fields LIKE '%".$is_array[$count]."%'";
							}
							$count++;
						}
					} else {
						$whereSQL = "WHERE ".$wpdb->prefix."prescreen_jobs.".$where." = '".$is_array[0]."'";
						$count = 0;
						foreach ($is_array as $is_value) {
							if($count > 0){
						    	$whereSQL .= "OR ".$where." = '".$is_array[$count]."'";
							}
							$count++;
						}
					}
				} else {
					if(isset($matches[1])){
						$whereSQL = "WHERE ".$wpdb->prefix."prescreen_jobs.custom_data_fields LIKE '%".$is."%'";
					} else {
						$whereSQL = "WHERE ".$wpdb->prefix."prescreen_jobs.".$where." = '".$is."'";
					}
				}
				//print_r($whereSQL);
				return $whereSQL.' AND '.$wpdb->prefix.'prescreen_jobs.status >= 0';
				/*
				if(googleIndexingIsActive()){
					return $whereSQL.' AND google_indexing NOT LIKE "inactive"';
				} else {
					return $whereSQL;
				}
				*/
			}
			
		}
	}
	
}




function job_list_function($atts) {
	global $wpdb;
	global $jal_db_version;
	global $wp;
	
	//$jobListTest = $wpdb->get_results("SELECT id,title FROM ".$wpdb->prefix."prescreen_jobs");
	//print_r($jobListTest);
	
	extract(shortcode_atts(array(
		'group' => '',
		'header' => '',
		'show' => '',
		'where' => '',
		'is' => '',
		'isnot' => '',
		'hide' => '',
		'type' => '',
		'debug' => 'false'
	), $atts));
	
	// Set Default Group
	if($group === ''){
		$group = 'jobs';
	}
	
	// Setup THEAD
	$jobListArray = array();
	if($hide != ''){
		array_push($jobListArray, '<style type="text/css">');
		$hide = explode(',', $hide);
		foreach ($hide as $column){
			array_push($jobListArray, '.job__list td:nth-child('.$column.'){ display: none !important; }');
		}
		array_push($jobListArray, '</style>');
	}
	if($header != ''){
		$headerClass = 'with-header';
	} else {
		$headerClass = '';
	}
	
	array_push($jobListArray, '<table class="job__list '.$type.' '.$headerClass.'">');
	
	if($header != ''){
		array_push($jobListArray, '<thead><tr>');
		$myHeaders = explode(',', $header);
		foreach ($myHeaders as $header){
			array_push($jobListArray, '<td>'.$header.'</td>');
		}
		array_push($jobListArray, '</tr></thead><tbody data-jplist-group="'.$group.'">');
	} else {
		array_push($jobListArray, '<tbody data-jplist-group="'.$group.'">');
	}
	
	//$showArray = explode(',', $show);
	
	// Setup TBODY
	$showArray = explode(',', $show);
	$buildSelect = '';
	$joinSelect = array();
	

	if($debug === 'true'){
		echo '<div>SHOW showARRAY</div><pre style="text-align: left;">';
		print_r($showArray);
		echo '</pre>';
	}
	
	foreach($showArray AS $showItem) {
		
		preg_match('/\(([^\)]*)\)/', $showItem, $matches);
		//print_r($matches);
		if(isset($matches[1])){
			$showItem = preg_replace("/\([^)]+\)/","",$showItem);
			$showField = $matches[1];
			
			if (strpos($matches[1], '|') !== false) {
				$showField = substr($matches[1], strpos($matches[1], "|") + 1); 
				$showValue = strtok($matches[1], '|');
				//$buildSelect .= $wpdb->prefix."prescreen_jobs_".$showItemNew.".".$showField." as ".$showItemNew."_".$showField.",";
				$buildSelect .= $wpdb->prefix."prescreen_jobs.custom_data_fields as custom_data_fields_".$showValue."_".$showField.",";
			} else {
				$showItemNew = makePluralTables($showItem);
				$buildSelect .= $wpdb->prefix."prescreen_jobs_".$showItemNew.".".$showField." as ".$showItemNew."_".$showField.",";
			}
		
			if($showItem != 'custom_data_fields'){
				array_push($joinSelect, "JOIN ".$wpdb->prefix."prescreen_jobs_".$showItemNew." ON ".$wpdb->prefix."prescreen_jobs".".".$showItem." = ".$wpdb->prefix."prescreen_jobs_".$showItemNew.".id ");
			}
		} else {
			$buildSelect .= $wpdb->prefix."prescreen_jobs.".$showItem.",";
			//echo '<br />';
		}
		
		
	}
	
	
	$buildSelect = rtrim($buildSelect, ",");

	if($debug === 'true'){
		echo '<div>SHOW buildSELECT</div><pre style="text-align: left;">';
		print_r($buildSelect);
		echo '</pre>';
	}
	
	$joinSelect = array_unique($joinSelect);
	$joinSelect = implode(' ', $joinSelect);
	
	
	if($debug === 'true'){
		echo '<div>SHOW joinSELECT</div><pre style="text-align: left;">';
		print_r($joinSelect);
		echo '</pre>';
	}
	
	if($is != '' && $isnot != ''){
		$isSelector = 'is';
		$isValues = $is;
	} else if($is != ''){
		$isSelector = 'is';
		$isValues = $is;
	} else if($isnot != ''){
		$isSelector = 'isnot';
		$isValues = $isnot;
	} else {
		$isSelector = 'is';
		$isValues = $is;
	}

	if($isValues === 'default'){
		if (strpos($where, 'custom_data_fields') !== false) {
			$defaultID = do_shortcode('[job_snippet type="'.$where.'" show="value_id" ]');
		} else {
			$defaultID = do_shortcode('[job_snippet type="'.$where.'" show="id" ]');
		}
		$isValues = intval($defaultID);
	}
	
	if($isValues === 0){
		$jobList = $wpdb->get_results("SELECT 
		".$wpdb->prefix."prescreen_jobs.shorthandle,".$buildSelect." 
		FROM ".$wpdb->prefix."prescreen_jobs
		".$joinSelect."");
		
		//DELETE LATER
		//if ( is_user_logged_in() ) {
		//	print_r('isValues = 0 <br /><br />');	
		//}
	} else {
		$jobList = $wpdb->get_results("SELECT 
		".$wpdb->prefix."prescreen_jobs.shorthandle,".$buildSelect." 
		FROM ".$wpdb->prefix."prescreen_jobs
		".$joinSelect." ".getWhereSQL($where,$isValues,$isSelector)."");
		
		//DELETE LATER
		//if ( is_user_logged_in() ) {
			
		//	print_r('isValues != 0 <br /><br />');
		//	print_r(getWhereSQL($where,$isValues,$isSelector).'<br /><br />');
		//}
	}
	
	//DELETE LATER
	//if ( is_user_logged_in() ) {
	//	print_r($buildSelect.'<br /><br />');
	//	print_r($joinSelect.'<br /><br />');
	//	print_r($jobList);
	//}

	if($debug === 'true'){
		echo '<div>SHOW jobLIST ARRAY</div><pre style="text-align: left;">';
		print_r($jobList);
		echo '</pre>';
	}
	//print_r($jobList);
	foreach ($jobList as $job) { 
		
		$jobInfoArray = array();
		$jobItemArray = '';
		
		//print_r($job->shorthandle);
		
		foreach ($job as $key => $jobInfo) { 
			$i = -1;
			

			$i++;
			
			
			
			if($key === 'shorthandle'){
				
			} elseif($key === 'title'){
				
				if(strpos(home_url( $wp->request ), 'job') !== false){
					$jobItemArray .= '<td class="'.$key.'"><a class="update-url" href="'.home_url( $wp->request ).'/?sh='.$job->shorthandle.'">'.$jobInfo.'</a></td>';
				} else {
					$jobItemArray .= '<td class="'.$key.'"><a class="update-url" href="'.home_url( $wp->request ).'/job/?sh='.$job->shorthandle.'">'.$jobInfo.'</a></td>';
				}

			} else {

				if(strpos($key, 'custom_data_fields') !== false) {
					//print_r('asdfasdf');
					$cdfElements = explode ("_", $key);  

					$databaseEntry = $wpdb->get_results("SELECT custom_data_fields FROM ".$wpdb->prefix."prescreen_jobs WHERE shorthandle = '".$job->shorthandle."'");
				    $databaseEntry = json_decode($databaseEntry[0]->custom_data_fields, true);
					
					$cdfID = $cdfElements[3];
					$cdfField = $cdfElements[4];

					if(isset($databaseEntry['custom_data_field'][0])){
						foreach ($databaseEntry['custom_data_field'] as $cdf) { 
	
							if(isset($cdf['id']) && $cdf['id'] === $cdfID){
								
								if(!empty($cdf[$cdfField])){
									$jobItemArray .= '<td class="'.preg_replace("/[^a-zA-Z]/", "", str_replace(' ', '', $cdf[$cdfField])).' custom_data_fields">'.$cdf[$cdfField].'</td>';
								} else {
									$jobItemArray .= '<td class="custom_data_fields"></td>';
								}
							}
						}
					} else {
						$cdf = $databaseEntry['custom_data_field'];
						if(isset($cdf['id']) && $cdf['id'] === $cdfID){
							if(!empty($cdf[$cdfField])){
								$jobItemArray .= '<td class="'.preg_replace("/[^a-zA-Z]/", "", str_replace(' ', '', $cdf[$cdfField])).' custom_data_fields">'.$cdf[$cdfField].'</td>';
							} else {
								$jobItemArray .= '<td class="custom_data_fields"></td>';
							}
						}
					}

					
				} elseif(strpos($key, 'distance') !== false) {
					
					$jobInfo = preg_split ("/\,/", $jobInfo);
					//print_r($jobInfo);
					if(isset($_GET["lat"]) && isset($_GET["lng"])){
						$jobItemArray .= '<td data-distance="'.vincentyGreatCircleDistance($_GET["lat"],$_GET["lng"],$jobInfo[0],$jobInfo[1]).'" class="distance" data-lat="'.$jobInfo[0].'" data-lng="'.$jobInfo[1].'">'.vincentyGreatCircleDistance($_GET["lat"],$_GET["lng"],$jobInfo[0],$jobInfo[1]).'</td>';
					} else {
						$jobItemArray .= '<td data-distance="" class="distance" data-lat="'.$jobInfo[0].'" data-lng="'.$jobInfo[1].'">0</td>';
					}	
				} else {
					$selector = explode ("_", $key);
					$selectorAddon = explode ("_", $key);
					//print_r($selector); 
					$selector = makeSingularTables($selector[1]);

					$jobItemArray .= '<td class="'.preg_replace("/[^a-zA-Z]/", "", str_replace(' ', '', $jobInfo)).' '.$selector.' '.$selectorAddon[0].'">'.$jobInfo.'</td>';
				}	
				
				
			}

		}
		$jobInfoArray = implode("", $jobInfoArray);
		array_push($jobListArray, '<tr data-jplist-item>'.$jobItemArray.'</tr>');

	}
	array_push($jobListArray, '</tbody>');
	array_push($jobListArray, '</table>');
	$jobListArray = implode("", $jobListArray);
	
	return $jobListArray;
	
}
add_shortcode('job_list', 'job_list_function');


function job_paging_function($atts) {
	global $wpdb;
	global $jal_db_version;
	global $wp;
	
	extract(shortcode_atts(array(
		'group' => ''
	), $atts));
	
	if($group === ''){
		$group = 'jobs';
	}
	
	return '
	<!-- pagination control -->
	<nav class="navigation pagination">
		<div class="nav-links" data-jplist-control="pagination"
			data-group="'.$group.'"
			data-items-per-page="'.get_option('PS_career_showposts').'"
			data-current-page="0"
			data-name="'.$group.'">
			<a class="prev page-numbers" href="#" data-type="prev"><i></i> </a>
			
		    <div class="jplist-holder" data-type="pages">
				<span class="page-numbers" data-type="page">{pageNumber}</span>
			</div>
			
			<a class="next page-numbers" href="#" data-type="next"> <i></i></a>
			
		    <div data-type="items-per-page-dd" class="jplist-dd">
		        <div data-type="panel" class="jplist-dd-panel">Items per page</div>
		
		        <div data-type="content" class="jplist-dd-content">
		            <div class="jplist-dd-item" data-value="'.get_option('PS_career_showposts').'">'.get_option('PS_career_showposts').' per page</div>
		            <div class="jplist-dd-item" data-value="0">View all</div>
		        </div>
		
		    </div>
		</div>
	</nav>
	';
	
}
add_shortcode('job_paging', 'job_paging_function');

function multi_unique($src){
     $output = array_map("unserialize",
     array_unique(array_map("serialize", $src)));
   return $output;
}


function job_filter_function($atts) {
	global $wpdb;
	global $jal_db_version;
	global $wp;
	
	extract(shortcode_atts(array(
		'type' => 'text',
		'by' => '',
		'placeholder' => '',
		'radius_search' => 'false',
		'group' => '',
		'default' => '',
		'where' => '',
		'is' => '',
		'isnot' => '',
		'field' => ''
	), $atts));
	
	if($group === ''){
		$group = 'jobs';
	}
	
	
	if($type === 'text'){
		return '
		<input
			 class="jplist-control-element" id="jplist-search" 
			 data-jplist-control="textbox-filter"
			 data-group="'.$group.'"
			 data-name="'.str_replace(' ', '', $by).'"
			 data-path=".'.str_replace(' ', '', $by).'"
			 data-clear-btn-id="title-clear-btn"
			 type="text"
			 value=""
			 placeholder="'.$placeholder.'"
			 data-id="'.str_replace(' ', '', $by).'"
		/>
		';
	} else if($type === 'reset'){
		return '
		<button type="button" class="reset_jplist">
		    '.$placeholder.'
		</button>
		';
	} else if($type === 'select'){
		$returnArray = array();

		
		$selector = makeSingularTables($by);

		
		if($radius_search === 'true'){
			$radiusSearchAddon = ",".$wpdb->prefix."prescreen_jobs_".$by.".lat,".$wpdb->prefix."prescreen_jobs_".$by.".lng";
		} else {
			$radiusSearchAddon = "";
		}
		
		if($is != '' && $isnot != ''){
			$isSelector = 'is';
			$isValues = $is;
			
			if($is === 'default'){
				if (strpos($where, 'custom_data_fields') !== false) {
					$defaultID = do_shortcode('[job_snippet type="'.$where.'" show="value_id" ]');
				} else {
					$defaultID = do_shortcode('[job_snippet type="'.$where.'" show="id" ]');
				}
				//print_r($defaultID);
				$isValues = intval($defaultID);
			}
			
		} else if($isnot != ''){
			$isSelector = 'isnot';
			$isValues = $isnot;
			
			if($isnot === 'default'){
				if (strpos($where, 'custom_data_fields') !== false) {
					$defaultID = do_shortcode('[job_snippet type="'.$where.'" show="value_id" ]');
				} else {
					$defaultID = do_shortcode('[job_snippet type="'.$where.'" show="id" ]');
				}
				//print_r($defaultID);
				$isValues = intval($defaultID);
			}
		} else if($is != ''){
			$isSelector = 'is';
			$isValues = $is;
			
			if($is === 'default'){
				if (strpos($where, 'custom_data_fields') !== false) {
					$defaultID = do_shortcode('[job_snippet type="'.$where.'" show="value_id" ]');
				} else {
					$defaultID = do_shortcode('[job_snippet type="'.$where.'" show="id" ]');
				}
				//print_r($defaultID);
				$isValues = intval($defaultID);
			}
		} else {
			$isSelector = 'is';
			$isValues = $is;
		}

		
		if(strpos($by, 'custom_data_fields') !== false) {
			preg_match('#\((.*?)\)#', $by, $match);
			$cdfID = $match[1];
			
			$template = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_custom_data_fields WHERE id = ".$cdfID." ORDER BY ".$field);
			//print_r($template);
		} else {
			$template = $wpdb->get_results("SELECT ".$wpdb->prefix."prescreen_jobs_".$by.".".$field."".$radiusSearchAddon." FROM ".$wpdb->prefix."prescreen_jobs JOIN ".$wpdb->prefix."prescreen_jobs_".$by." ON ".$wpdb->prefix."prescreen_jobs.".$selector." = ".$wpdb->prefix."prescreen_jobs_".$by.".id ".getWhereSQL($where,$isValues,$isSelector)." ORDER BY ".$field);
		}
		
		
		
		//$template = array_unique($template);
		$template = multi_unique($template);
		

		
		if($default === 'true'){
				
		} else {
			$defaultClass = '';	
		}
		
		if($default === 'true'){
			$selector = makeSingularTables($by);
			$defaultClass = '';	
			
			if(isset($_GET['sh']) || $_COOKIE['job_sh']){
				if( isset( $_COOKIE['job_sh'] )) $shortHandle = $_COOKIE['job_sh'];
				if( isset( $_GET['sh'] )) $shortHandle = $_GET['sh'];

				$data = $wpdb->get_results("SELECT $selector FROM ".$wpdb->prefix."prescreen_jobs WHERE shorthandle = '".$shortHandle."'");
				
				$defaultValue = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_".$by." WHERE id = ".$data[0]->$selector."");
				//print_r($by);
				if(isset($defaultValue[0]->title)){
					//$defaultValue = $defaultValue[0]->title;
					$defaultClass = 'data-default="'.$defaultValue[0]->title.'"';
				}
				if(isset($defaultValue[0]->name)){
					//$defaultValue = $defaultValue[0]->name;
					$defaultClass = 'data-default="'.$defaultValue[0]->name.'"';
				}				
			}
		} else {
			$defaultClass = '';	
		}
		
		
		$i = 0;
		foreach ($template as $value) { 
			$i++;
			//print_r($by);
			if($by === 'cities' && $field === 'title'){
				
				if(isset($value->title)){
					if(isset($_GET["city"]) && $_GET["city"] === $value->title){
						$selectedClass = 'selected';
					} else {
						$selectedClass = '';

					}
					
					array_push($returnArray, '<option '.$selectedClass.' data-cityname="'.$value->title.'" value="'.$i.'" data-lat="'.$value->lat.'" data-lng="'.$value->lng.'" data-path=".'.preg_replace("/[^a-zA-Z]/", "", str_replace(' ', '', $value->title)).'">'.$value->title.'</option>'); 
				}

			} else {
				if(isset($value->$field)){
					array_push($returnArray, '<option value="'.$i.'" data-path=".'.preg_replace("/[^a-zA-Z]/", "", str_replace(' ', '', $value->$field)).'">'.$value->$field.'</option>'); 
				}
			}

		}
		$returnArray = implode("", $returnArray);
		

		
		if($by === 'cities' && $field === 'title'){
			
			if($radius_search === 'true'){

					$radiusButtons = '';
					for ($i = 0; $i <= 1500; $i++) {
						if ($i % 10 == 0){
						$radiusButtons .= '<button class="radius-button-hide radius-'.$i.'" data-jplist-control="buttons-range-filter" data-path=".distance" data-group="'.$group.'" data-name="distance" data-selected="false" data-from="0" data-to="'.$i.'" data-id="radius-'.$i.'"></button>';
						}
					}
					$radiusButtons .= '<button class="radius-button-hide radius-all" data-jplist-control="buttons-range-filter" data-path=".distance" data-group="'.$group.'" data-name="distance" data-selected="false" data-from="0" data-id="radius-all"></button>';
					$radiusSearch = '<label><input id="city_radius" type="checkbox" value="" /><span class="wpcf7-list-item-label">Umkreissuche</span></label>
					<input id="umkreisinput" type="number" min="0" max="500" value="" placeholder="Kilometer" />
					'.$radiusButtons.'
					';
					return'
					<div class="cities_with_radius_search" data-error-message="'.get_option('PS_career_selectdefault').'">
					<select
						id="select_'.str_replace(' ', '', $by).'" 
						'.$defaultClass.' class="jplist-control-element" 
						data-jplist-control="select-filter"
						data-group="'.$group.'"
						data-id="'.str_replace(' ', '', $by).'"
						data-name="'.str_replace(' ', '', $by).'">
						
						
						<option
								value="0"
								data-path="default" selected>'.$placeholder.'</option>
						'.$returnArray.'
					</select>
					'.$radiusSearch.'
					</div>';

			} else {
				return'
				<select
					id="select_'.str_replace(' ', '', $by.'_'.$field).'" 
					'.$defaultClass.' class="jplist-control-element" 
					data-jplist-control="select-filter"
					data-group="'.$group.'"
					data-id="'.str_replace(' ', '', $by.'_'.$field).'"
					data-name="'.str_replace(' ', '', $by.'_'.$field).'">
					
					
					<option
							value="0"
							data-path="default" selected>'.$placeholder.'</option>
					'.$returnArray.'
				</select>
				';
			}
			
		} else if(strpos($by, 'custom_data_fields') !== false) {
			return'
			<select
				id="select_'.str_replace(' ', '', $by.'_'.$field).'" 
				'.$defaultClass.' class="jplist-control-element" 
			    data-jplist-control="select-filter"
			    data-group="'.$group.'"
				data-id="'.str_replace(' ', '', $by.'_'.$field).'"
			    data-name="'.str_replace(' ', '', $by.'_'.$field).'">
			
			    <option
			            value="0"
			            data-path="default" selected>'.$placeholder.'</option>
				'.$returnArray.'
			</select>
			';
		} else {
			return'
			<select
				id="select_'.str_replace(' ', '', $by.'_'.$field).'" 
				'.$defaultClass.' class="jplist-control-element" 
			    data-jplist-control="select-filter"
			    data-group="'.$group.'"
				data-id="'.str_replace(' ', '', $by.'_'.$field).'"
			    data-name="'.str_replace(' ', '', $by.'_'.$field).'">
			
			    <option
			            value="0"
			            data-path="default" selected>'.$placeholder.'</option>
				'.$returnArray.'
			</select>
			';
		}
	}
	
	
}
add_shortcode('job_filter', 'job_filter_function');


function job_apply_button_function($atts) {
	global $wpdb;
	global $jal_db_version;
	global $wp;
	
	extract(shortcode_atts(array(
		'title' => 'Jetzt bewerben',
		'background_color' => '',
		'color' => '',
		'target' => '',
		'rel' => '',
		'lang' => '',
		'class' => '',
		'type' => ''
	), $atts));
	
	
    $shortHandle = '';
	if( isset( $_COOKIE['job_sh'] )) $shortHandle = $_COOKIE['job_sh'];
	if( isset( $_GET['sh'] )) $shortHandle = $_GET['sh'];
	
	$getHandle = $wpdb->get_results("SELECT handle FROM ".$wpdb->prefix."prescreen_jobs WHERE shorthandle = '".$shortHandle."'");
	$handle = $getHandle[0]->handle;
	
	if($background_color != ''){
		$background_color_op = 'background-color: '.$background_color.';';
	} else {
		$background_color_op = '';
	}
	
	if($color != ''){
		$color_op = 'color: '.$color.';';
	} else {
		$color_op = '';
	}
	
	if($target != ''){
		$target_op = 'target="'.$target.'" ';
	} else {
		$target_op = '';
	}
	
	if($rel != ''){
		$rel_op = 'rel="'.$rel.'" ';
	} else {
		$rel_op = '';
	}	
	
	if($lang != ''){
		$lang = '&amp;lang='.$lang.'';
	} else {
		$lang = '';
	}	
	
	if($type === 'api'){
		return '<div class="job__button"><a href="application/?sh='.$shortHandle.'&amp;sa=1'.$lang.'" '.$target_op.' '.$rel_op.' class="'.$class.' button button--contained" style="'.$background_color_op.' '.$color_op.' margin-top: 1em;">'.$title.'</a></div>';
	} else {
		return '<div class="job__button"><a href="application/?sh='.$shortHandle.'&amp;jh='.$handle.'&amp;sa=1'.$lang.'" '.$target_op.' '.$rel_op.' class="'.$class.' button button--contained" style="'.$background_color_op.' '.$color_op.' margin-top: 1em;">'.$title.'</a></div>';
	}	
	
	
}
add_shortcode('job_apply_button', 'job_apply_button_function');



function job_application_function(){
 
	$shortHandle = '';
	if( isset( $_COOKIE['job_sh'] )) $shortHandle = $_COOKIE['job_sh'];
	if( isset( $_GET['sh'] )) $shortHandle = $_GET['sh'];
	
	$startedApplication = '';
	if( isset( $_GET['sa'] )) $startedApplication = $_GET['sa'];
	$handle = '';
	if( isset( $_COOKIE['job_jh'] )) $handle = $_COOKIE['job_jh'];
	if( isset( $_GET['jh'] )) $handle = $_GET['jh'];

	if (!$shortHandle) {
	   if( !isset( $_GET['rl'] )){ //prevent infinit reloading if param is not set in options
		   $parm = get_option( 'PS_prescreen_application_parm' );
		   wp_redirect( $_SERVER[ 'REQUEST_URI'].'?rl=1&'.$parm );
	   }
	   return;

	} else {
	   $shortcode = '';
	   $app_link = get_option( 'PS_career_applink' );
	   if ( $startedApplication == '1') { // application form
			if( empty( $app_link  )) return;
			
			$shortcode = '<div class="mw-col__10--12 col__12--12 column" id="psJobWidget"></div><script src="'.$app_link.'"></script>';
			return $shortcode;

	   }
	}
	return;
}
add_shortcode('job_application', 'job_application_function');

function googleIndexingTest(){
	print_r(googleIndexing('tnlp977w','delete'));
}
add_shortcode('google_indexing', 'googleIndexingTest');



function google4Jobs_function(){
	if(isset($_GET['sh'])){
			
			$g4jTitle = do_shortcode('[job_snippet type="title"]');
			$g4jTitle = str_replace( '"', "'",  $g4jTitle );
			$g4jDatePosted = do_shortcode('[job_snippet type="published_at"]');
			
			$g4jIndustry = do_shortcode('[job_snippet type="industry"]');
			$g4jIndustry = str_replace( '"', "'",  $g4jIndustry );
			
			//Google4Jobs
			$jsonld = PHP_EOL . '<script type="application/ld+json">' . PHP_EOL;
			$jsonld = $jsonld . '{' . PHP_EOL;
			$jsonld = $jsonld . '         "@context" : "schema.org",' . PHP_EOL;
			$jsonld = $jsonld . '         "@type" : "JobPosting"';
			$jsonld = $jsonld . ',' . PHP_EOL . '             "title" : "'. $g4jTitle . '"';
			$jsonld = $jsonld . ',' . PHP_EOL . '           "datePosted" : "'. $g4jDatePosted . '"';
			$jsonld = $jsonld . ',' . PHP_EOL . '     "industry" : "'. $g4jIndustry . '"';

			$positionType = intval(do_shortcode('[job_snippet type="positiontype" show="id"]'));
			if($positionType === 1){
				$positionType = '"FULL_TIME"';
			} else if($positionType === 2){
				$positionType = '"PART_TIME"';
			} else if($positionType === 3){
				$positionType = '"INTERN"';
			} else if($positionType === 4){
				$positionType = '"CONTRACTOR"';
			} else if($positionType === 8){
				$positionType = '"VOLUNTEER"';
			} else if($positionType === 9){
				$positionType = '["FULL_TIME", "PART_TIME"]';
			} else if($positionType === 11){
			   $positionType = '"TEMPORARY"';
			} else if($positionType === 12){
			   $positionType = '"VOLUNTEER"';
			} else if($positionType === 16){
			   $positionType = '"INTERN"';
			} else if($positionType === 17){
			   $positionType = '"TEMPORARY"';
			} else {
				$positionType = '"OTHER"';
			}

			$jsonld = $jsonld . ',' . PHP_EOL . '                "employmentType" : '. $positionType . '';
			
			

			$json_describiton = do_shortcode('[job_snippet type="simple_html_content"]');
			
			
			// remove html attributes and tags
			$json_describiton = strip_tags_content( $json_describiton, '<style>', TRUE );
			
			$json_describiton = strip_tags( $json_describiton, '<h1>,<h2>,<h3>,<br>,<p>,<li>,<ul>' );
			
			
			$json_describiton = htmlspecialchars_decode($json_describiton);
			$json_describiton = nl2p_html($json_describiton);
			
			$json_describiton = preg_replace('/<h1[^>]*>([\s\S]*?)<\/h1[^>]*>/', '', $json_describiton);
			
			$json_describiton = preg_replace("#(<h[1-6].*?>)<p.*?>#", '$1', $json_describiton);
			
			$json_describiton = preg_replace("#<\/p>(<\/h[1-6]>)#", '$1', $json_describiton);
			
			
			$json_describiton = str_replace( '<p><p>', '<p>',  $json_describiton );
			$json_describiton = str_replace( '</p></p>', '</p>',  $json_describiton );
			$json_describiton = str_replace( '<p>&nbsp;</p>', '',  $json_describiton );

			$json_describiton = preg_replace("#<\/p>(<\/h[1-6]>)#", '$1', $json_describiton);

			$json_describiton = str_replace( '<li><p>', '<li>',  $json_describiton );
			$json_describiton = str_replace( '</p></li>', '</li>',  $json_describiton );
			$json_describiton = str_replace( '&nbsp;', ' ',  $json_describiton );
			$json_describiton = str_replace( '</p><br><p>', '</p><p>',  $json_describiton );
			$json_describiton = str_replace( '</p><br><br><p>', '</p><p>',  $json_describiton );

			$json_describiton = preg_replace('/<h[1-6]>(.*?)<\/h[1-6]>/', '<p>$1</p>', $json_describiton);
			
			$json_describiton = str_replace( '<p><p>', '<p>',  $json_describiton );
			$json_describiton = str_replace( '<p></p>', '',  $json_describiton );
			$json_describiton = str_replace( '"', "'",  $json_describiton );

			If( $json_describiton ) $jsonld = $jsonld . ',' . PHP_EOL . '              "description" : "' . $json_describiton . '"';
			

			
			$jsonld = $jsonld . ',' . PHP_EOL;
			$jsonld = $jsonld . '         "hiringOrganization" : {' . PHP_EOL;
			$jsonld = $jsonld . '                         "@type" : "Organization",' . PHP_EOL;
			$jsonld = $jsonld . '                         "name" : "' . do_shortcode('[job_snippet type="instance"]') . '"' . PHP_EOL;
			$jsonld = $jsonld . '         }';
			
			
			$jsonld = $jsonld . ',' . PHP_EOL;
			$jsonld = $jsonld . '         "jobLocation" : {' . PHP_EOL;
			$jsonld = $jsonld . '         "@type" : "Place",' . PHP_EOL;
			$jsonld = $jsonld . '                         "address": {' . PHP_EOL;
			$jsonld = $jsonld . '                         "@type" : "PostalAddress",' . PHP_EOL;
			$jsonld = $jsonld . '                         "addressLocality" : "' . do_shortcode('[job_snippet type="city"]') . '",' . PHP_EOL;
			$jsonld = $jsonld . '                         "addressCountry" : "' . do_shortcode('[job_snippet type="city" show="countrycode"]') . '"' . PHP_EOL;
			$jsonld = $jsonld . '                         }' . PHP_EOL;
			$jsonld = $jsonld . '         }';
			
			$jsonld = $jsonld . '}' . PHP_EOL;
			$jsonld = $jsonld . '</script>' . PHP_EOL;
			
			return $jsonld;
			
		}
}
add_shortcode('google4Jobs', 'google4Jobs_function');

function standalone_search_function($atts) {
  extract(shortcode_atts(array(
    'placeholder' => 'Suchbegriff eingeben',
    'button_text' => 'Suchen',
	'search_url' => ''
  ), $atts));
  
  return '<form action="'.$search_url.'" class="standalone_search_holder" method="get"><div class="standalone_search"><input name="searchquery" type="text" value="" placeholder="'.$placeholder.'"><input class="button button--contained" type="submit" value="'.$button_text.'"></div></form>';
  //print_r($out);
}
   
add_shortcode('standalone_search', 'standalone_search_function');

function show_map_function($atts) {
  extract(shortcode_atts(array(
    'center_lat' => '',
    'center_lng' => '',
	'zoom' => '5',
	'height' => '400px',
	'hovertext' => '',
	'city_url' => ''
  ), $atts));
  
  return '<div id="careermap" class="map" data-cityurl="'.$city_url.'" data-hovertext="'.$hovertext.'" data-center-lat="'.$center_lat.'" data-center-lng="'.$center_lng.'" data-zoom="'.$zoom.'" style="height: '.$height.';"></div>';
  //print_r($out);
}
   
add_shortcode('show_map', 'show_map_function');

// register custom post type form Builder
function post_type_jobapplicationform() {
	register_post_type(
		'jobapplicationform',
		array(
		    'label' => __('Job Application Forms'),
		    'public' => false,
		    'publicly_queryable' => true,
		    'show_ui' => true,
		    'exclude_from_search' => true,
		    'rewrite' => false,
			'menu_position' => 99,
		    'supports' => array(
				'title',
		    	'revisions'
			)
		)
	);
}
add_action('init', 'post_type_jobapplicationform');

add_action( 'admin_menu', 'remove_post_type_jobapplicationform' );

function remove_post_type_jobapplicationform() {
	remove_menu_page( 'edit.php?post_type=jobapplicationform' );
}

function show_job_application_form_function($atts){
	
	define('DONOTCACHEPAGE', true);
	define('DONOTCACHEDB', true);
	define('DONOTMINIFY', true);
	define('DONOTCACHEOBJECT', true);
	
	// Get Application Form ID from Job
	
	$showApplicationFormID = '';
	$applicationformID = do_shortcode('[job_snippet type="custom_data_fields('.get_option('PS_job_applications_form_custom_field_value').')" show="value_id"]');
	

	//print_r('hs '.$applicationformID);
	
	// Get POST ID from Correct Form
	if(!isset($applicationformID) || $applicationformID === ''){
		$applicationformID = get_option('PS_job_applications_form_default_id');
		if(get_option('PS_job_applications_debug')){
			print_r('Take Default Template Form ID '.$applicationformID);
			echo '<br />';
		}
	} else {
		if(get_option('PS_job_applications_debug')){
			print_r('Application Template Form ID '.$applicationformID);
			echo '<br />';
		}
	}


		
	$ja_args = array(
		'post_type' => 'jobapplicationform',
		'posts_per_page' => -1
	);
	$ja_loop = new WP_Query($ja_args);
	while ( $ja_loop->have_posts() ) : $ja_loop->the_post(); 
		//print_r(get_field('job_application_form_id'));
		
		if(get_field('job_application_form_id') === $applicationformID){
			$showApplicationFormID = get_the_ID();
		}
	
	endwhile;
	wp_reset_postdata(); 


	
	$cbid = intval($showApplicationFormID);

	if(get_option('PS_job_applications_debug')){
		
		print_r('Careerpage Form ID '.$showApplicationFormID);
		echo '<br />';
	}
	
	if(isset( $cbid ) && $cbid != ''){
		$cb_args = array(
			'p'         => $cbid, // ID of a page, post, or custom type
			'post_type' => 'jobapplicationform'
		);
		$cb_loop = new WP_Query($cb_args);
		
		if(get_option('PS_job_applications_debug')){
			echo '<pre style="text-align: left;">';
			print_r($cb_loop);	
			echo '</pre>';
		}
		
		while ( $cb_loop->have_posts() ) : $cb_loop->the_post(); 
			ob_start();

			include('includes/ps_career_formbuilder.php');

			$output = ob_get_clean();
		endwhile;
		

	}
	
	
	wp_reset_postdata(); 
	
	
	return $output;

}
add_shortcode('show_job_application_form', 'show_job_application_form_function');


function importApplicationSource(){
	global $wpdb;
	if(get_option('PS_api_key') != ''){
		$apikey = get_option('PS_api_key');
	} else {
		$apikey = '';
	}
	
	if($apikey != ''){
		$requestAppSource = 'https://api.prescreenapp.io/v1/application_source';
		$curl_appSource = curl_init($requestAppSource.'?is_selectable_by_candidates=1&page_size=100');
		curl_setopt($curl_appSource, CURLOPT_HTTPHEADER,
		    array(
		        'apikey: '.$apikey.'',
		    )
		);
		curl_setopt($curl_appSource, CURLOPT_RETURNTRANSFER, true);
		$response_appSource = json_decode(curl_exec($curl_appSource));
		$array_appSource = get_object_vars($response_appSource);
		
		if(isset($array_appSource) && $array_appSource != ''){
			$delete = $wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."prescreen_application_source`");
			foreach ($array_appSource['data'] as &$appSource) {
				$appSourceId = $appSource->id;
				foreach ($appSource->translations as &$appSourceTranslations) {
					$appSourceLanguage = $appSourceTranslations->locale;
					$appSourceTitle = $appSourceTranslations->title;

					$wpdb->insert( 
						$wpdb->prefix.'prescreen_application_source',
						array(  
							'title' => $appSourceTitle,
							'language' => $appSourceLanguage,
							'sourceid' => $appSourceId
						), 
						array( 
							'%s','%s','%d'
						)  
					);
				}
				
			}
		
		}
	}
}


add_action('wp_ajax_manually_import_application_custom_fields', 'importApplicationCustomFields');
function importApplicationCustomFields(){
	global $wpdb;
	if(get_option('PS_api_key') != ''){
		$apikey = get_option('PS_api_key');
	} else {
		$apikey = '';
	}
	
	if($apikey != ''){
		$requestAppSource = 'https://api.prescreenapp.io/v1/custom_field';
		$curl_appSource = curl_init($requestAppSource.'?page_size=100&is_candidate=1');
		curl_setopt($curl_appSource, CURLOPT_HTTPHEADER,
		    array(
		        'apikey: '.$apikey.'',
		    )
		);
		curl_setopt($curl_appSource, CURLOPT_RETURNTRANSFER, true);
		$response_appSource = json_decode(curl_exec($curl_appSource));
		$array_appSource = get_object_vars($response_appSource);
		
		
		
		if(isset($array_appSource) && $array_appSource != ''){
			//$delete = $wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."prescreen_application_custom_fields`");
			
			$countEntries = 0;
			
			// Set All Entries to false
			$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_application_custom_fields SET is_active = %d','false' ) ); 
			
			
			foreach ($array_appSource['data'] as &$appSource) {

				
				if(isset($appSource->translations)){
					$translations = json_encode($appSource->translations);
				} else {
					$translations = '';
				}
				if(isset($appSource->custom_field_values)){
					$customFieldValues = json_encode($appSource->custom_field_values);
				} else {
					$customFieldValues = '';
				}
				
				// Check if Custom Field is already in Database -> if yes, delete entry
				$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_custom_fields WHERE cfid = ".$appSource->id."");
				if(!empty($isInDatabase)){
					$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_application_custom_fields WHERE cfid = %s', $appSource->id) );
				}
				
				// If Type is not file insert in Database
				if($appSource->type != 'file'){
					$wpdb->insert( 
						$wpdb->prefix.'prescreen_application_custom_fields',
						
						array(  
							'type' => $appSource->type,
							'cfid' => $appSource->id,
							'name' => $appSource->name,
							'label' => $appSource->label,
							'custom_field_values' => $customFieldValues,
							'is_job' => $appSource->is_job,
							'is_candidate' => $appSource->is_candidate,
							'is_mandatory' => $appSource->is_mandatory,
							'translations' => $translations,
							'is_active' => 'true'
						), 
						array( 
							'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'
						)  
					);	
					$countEntries++;
				}	
				
				
			}
			
			echo $countEntries.' Custom Fields wurden importiert.';
		
		}
	} else {
		echo 'Es ist kein API Key hinterlegt';
	}
	wp_die();
}




//Form Template

function acf_load_job_application_form_id_field_choices( $field ) {
    global $wpdb;
    // reset choices
    $field['choices'] = array();

	$records = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_custom_field_form_template");
	foreach($records as $customfield){

			$field['choices'][ $customfield->cfid ] = $customfield->value;

	}

    // return the field
    return $field;
    
}

add_filter('acf/load_field/name=job_application_form_id', 'acf_load_job_application_form_id_field_choices');


function acf_load_job_default_application_source_field_choices( $field ) {
    global $wpdb;
    // reset choices
    $field['choices'] = array();
	$currentLanguage = 'de';
						
	$applicationSource = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_source WHERE language = '".$currentLanguage."'");
	foreach($applicationSource as $singleApplicationSource){

			$field['choices'][ $singleApplicationSource->sourceid ] = $singleApplicationSource->title;

	}

    // return the field
    return $field;
    
}

add_filter('acf/load_field/name=job_default_application_source', 'acf_load_job_default_application_source_field_choices');





function acf_load_job_application_tags_field_choices( $field ) {
    global $wpdb;
    // reset choices
    $field['choices'] = array();
						
	$tags = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_tags");
	foreach($tags as $tag){

			$field['choices'][ $tag->tagid ] = $tag->name;

	}

    // return the field
    return $field;
    
}

add_filter('acf/load_field/name=job_application_tags', 'acf_load_job_application_tags_field_choices');



add_action('wp_ajax_manually_import_application_custom_field_form_templates', 'importApplicationCustomFieldFormTemplates');
function importApplicationCustomFieldFormTemplates(){
	global $wpdb;
	if(get_option('PS_api_key') != ''){
		$apikey = get_option('PS_api_key');
	} else {
		$apikey = '';
	}
	if(get_option('PS_job_applications_form_custom_field_value') != ''){
		$customFieldId = intval(get_option('PS_job_applications_form_custom_field_value'));
	} else {
		$customFieldId = '';
	}
	
	
	
	if($apikey != '' && $customFieldId != ''){
		$requestAppSource = 'https://api.prescreenapp.io/v1/custom_field/'.$customFieldId.'';
		$curl_appSource = curl_init($requestAppSource);
		curl_setopt($curl_appSource, CURLOPT_HTTPHEADER,
		    array(
		        'apikey: '.$apikey.'',
		    )
		);
		curl_setopt($curl_appSource, CURLOPT_RETURNTRANSFER, true);
		$response_appSource = json_decode(curl_exec($curl_appSource));
		$array_appSource = get_object_vars($response_appSource);
		
		
		
		if(isset($array_appSource) && $array_appSource != ''){
			//$delete = $wpdb->query("TRUNCATE TABLE `".$wpdb->prefix."prescreen_application_custom_field_form_template`");

			$appSource = $array_appSource;

			//echo '<pre>';
			//print_r($appSource['custom_field_values']);
			//echo '</pre>';
			
			
			if(isset($appSource['custom_field_values'])){
				$countEntries = 0;
				foreach ($appSource['custom_field_values'] as &$customFieldValue) {
					$cfid = $customFieldValue->id;
					$value =  $customFieldValue->value;
					
					
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_custom_field_form_template WHERE cfid = ".$cfid."");
					if(!empty($isInDatabase)){
						$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_application_custom_field_form_template WHERE cfid = %s', $cfid) );
					}
					$wpdb->insert( 
						$wpdb->prefix.'prescreen_application_custom_field_form_template',
						
						array(  
							'cfid' => $cfid,
							'value' => $value
						), 
						array( 
							'%s','%s'
						)  
					);	
					$countEntries++;

				}
				echo $countEntries.' Formular Templates wurden importiert.';
			}

		
		}
	} else {
		echo 'Es ist kein API Key oder keine Formular Custom Field ID hinterlegt.';
	}
	wp_die();
}




add_action('wp_ajax_manually_import_application_tags', 'importApplicationTags');
function importApplicationTags(){
	global $wpdb;
	if(get_option('PS_api_key') != ''){
		$apikey = get_option('PS_api_key');
	} else {
		$apikey = '';
	}

	
	if($apikey != ''){
		$requestAppSource = 'https://api.prescreenapp.io/v1/candidate_tag?page_size=1000';
		$curl_appSource = curl_init($requestAppSource);
		curl_setopt($curl_appSource, CURLOPT_HTTPHEADER,
		    array(
		        'apikey: '.$apikey.'',
		    )
		);
		curl_setopt($curl_appSource, CURLOPT_RETURNTRANSFER, true);
		$response_appSource = json_decode(curl_exec($curl_appSource));
		$array_appSource = get_object_vars($response_appSource);
		
		//print_r($array_appSource);
		
		
		if(isset($array_appSource) && $array_appSource != ''){

			$appSource = $array_appSource;
			
			//print_r($appSource['data']);
			
			if(isset($appSource['data'])){
				$countEntries = 0;
				foreach ($appSource['data'] as &$singleTag) {
					$tagid = $singleTag->id;
					$tagname =  $singleTag->name;
					
					
					$isInDatabase = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_tags WHERE tagid = ".$tagid."");
					if(!empty($isInDatabase)){
						$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_application_tags WHERE tagid = %s', $tagid) );
					}
					$wpdb->insert( 
						$wpdb->prefix.'prescreen_application_tags',
						
						array(  
							'tagid' => $tagid,
							'name' => $tagname
						), 
						array( 
							'%s','%s'
						)  
					);	
					$countEntries++;

				}
				echo $countEntries.' Tags wurden importiert.';
			}
			
			

		
		}
		
		
	} else {
		echo 'Es ist kein API Key hinterlegt.';
	}
	wp_die();
}





function deleteOldApplications(){
	global $wpdb;
	$currentDate = date('Ymd', time());
	$currentDate = date('Ymd', strtotime($currentDate.'- 7 days'));
	
	//$wpdb->query( $wpdb->prepare( 'UPDATE '.$wpdb->prefix.'prescreen_candidates SET test = %s',$currentDate ) ); 
	$wpdb->query( $wpdb->prepare( 'DELETE FROM '.$wpdb->prefix.'prescreen_candidates WHERE created < %s', $currentDate) );
	//wp_die();
}


function acf_manipulate_form_builder_custom_field( $field ) {
    global $wpdb;
    // reset choices
    $field['choices'] = array();

	$getCustomFieldValues = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_custom_fields");
	foreach ($getCustomFieldValues as $getCustomFieldValue) { 
		
		// Update google_indexing -> inactive
		/*
		$wpdb->update( $wpdb->prefix.'prescreen_jobs',
			array( 
				'google_indexing' => 'inactive'
			), 
			array( 'status' => 0 ), //Welcher Eintrag ist davon betroffen?
			array( 
				'%s'
			),
			array( '%d' )
		);
		*/
		// Push shorthandle to $googleIndexingDelete Array
		if($getCustomFieldValue->is_active === 'true'){
			$field['choices'][ $getCustomFieldValue->cfid.'_'.$getCustomFieldValue->type ] = $getCustomFieldValue->name;
		} else {
			$field['choices'][ $getCustomFieldValue->cfid.'_'.$getCustomFieldValue->type ] = $getCustomFieldValue->name.' [INACTIVE]';
		}
		
	}

    // if has rows
	/*
    if( have_rows('my_select_values', 'option') ) {
        
        // while has rows
        while( have_rows('my_select_values', 'option') ) {
            
            // instantiate row
            the_row();
            
            
            // vars
            $value = get_sub_field('value');
            $label = get_sub_field('label');

            
            // append to choices
            $field['choices'][ $value ] = $label;
            
        }
        
    }
	*/


    // return the field
    return $field;
    
}

add_filter('acf/load_field/name=job_application_form_custom_field', 'acf_manipulate_form_builder_custom_field');



// Send Job Application Form
add_action('wp_ajax_nopriv_send_application_form', 'sendApplicationForm');
add_action('wp_ajax_send_application_form', 'sendApplicationForm');

function random_strings($length_of_string){ 
    // String of all alphanumeric character 
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; 
    // Shufle the $str_result and returns substring 
    // of specified length 
    return substr(str_shuffle($str_result), 0, $length_of_string); 
} 

function saveCandidateToDatabase(array $jsonData,$candidatekey,$userstate,$application_url,$psUserId,$shorthandle,$doubleoptin){
	global $wpdb;
	
	date_default_timezone_set('Europe/Berlin');
	$date = date('Ymd', time());
	
	$wpdb->insert( 
		$wpdb->prefix.'prescreen_candidates',
		array(  
			'data' => json_encode($jsonData),
			'email' => $jsonData['email'],
			'candidatekey' => $candidatekey,
			'userstate' => $userstate,
			'userid' => $psUserId, 
			'shorthandle' => $shorthandle,
			'created' => $date,
			'doubleoptin' => $doubleoptin
		), 
		array( 
			'%s','%s','%s','%s','%d','%s','%s','%s'
		)  
	);
	
	writeLogging('APPLICATIONS: "'.$jsonData['email'].'" inserted into database');
	
	//sendDoubleOptinMail($jsonData['profile']['firstname'],$jsonData['profile']['lastname'],$jsonData['email'],$candidatekey,$userstate,$application_url);
	
}



function sendDoubleOptinMail($firstname,$lastname,$email,$candidatekey,$userstate,$application_url){
	
	$finishApplicationLink = $application_url."&email=".$email."&candidatekey=".$candidatekey;
	
	if(get_option('PS_job_applications_form_mail') != ''){
		$mailText = get_option('PS_job_applications_form_mail');
		$mailText = str_replace("[firstname]",$firstname,$mailText);
		$mailText = str_replace("[lastname]",$lastname,$mailText);
		$mailText = str_replace("[finish_application_link]",$finishApplicationLink,$mailText);
	} else {
		$mailText = 'Bitte klicken Sie hier: '.$finishApplicationLink.'';
	}
	
	//$mailText = "Hallo ".$firstname." ".$lastname.", \r\nVielen Dank für deine Bewerbung. \r\nBitte klicke auf folgenden Link um die Bewerbung abzuschließen: ".$application_url."&email=".$email."&candidatekey=".$candidatekey. " \r\nDanke und Grüße!";
	
	writeLogging('APPLICATIONS: "'.$email.'" double opt-in mail sent');
	
	$to = $email;
	if(get_option('PS_job_applications_mail_subject') != ''){
		$subject = get_option('PS_job_applications_mail_subject');
	} else {
		$subject = 'Bewerbung';
	}
	if(get_option('PS_job_applications_mail_sender') != ''){
		$mailsender = get_option('PS_job_applications_mail_sender');
	} else {
		$mailsender = 'info@prescreen.io';
	}
	
	$body = $mailText;
	$headers = array('From: '.$mailsender.'');
	 
	wp_mail( $to, $subject, $body, $headers );
}

function is_success($input_line) {
    if(is_string($input_line)){
        preg_match('/^\{(\s+|\n+)*(\"(.*)(\n+|\s+)*)*\}$|^\[(\s+|\n+)*\{(\s+|\n+)*(\"(.*)(\n+|\s+)*)*\}(\s+|\n)*\]$/', $input_line, $output_array);
        if (  isset($output_array) || !empty($output_array)) {
            return true;
        }
    }
	return false;
}
	

function sendApplicationForm(){
	
	$requestURL = 'https://api.prescreenapp.io/v1/job';
	$requestURLCandidate = 'https://api.prescreenapp.io/v1/candidate';
	$requestURLApplication = 'https://api.prescreenapp.io/v1/application';
	
	if(get_option('PS_api_key') != ''){
		$apikey = get_option('PS_api_key');
	} else {
		$apikey = '';
	}
	
	if($apikey != ''){
	
		$shorthandle = $_POST['shorthandle'];
		$application_url = $_POST['application_url'];
		$source_id_default = $_POST['source_id_default'];
		
		//print_r($shorthandle);
		
		if(isset($shorthandle)){
			
			
			$firstname = $_POST['firstname'];
			$lastname = $_POST['lastname'];
			$email = $_POST['email'];
			

			$jsonData = array();
			
			$jsonDataUploads = array();
			$profile = array();
			
			
			if(isset($_POST['email'])){
				$jsonData['email'] = $_POST['email'];
			}
			
			if(isset($_POST['language'])){
				$jsonData['language'] = $_POST['language'];
			}
			
			if(isset($_POST['send_email'])){
				$jsonData['send_email'] = true;
			} else {
				$jsonData['send_email'] = false;
			}
			
			if(isset($_POST['firstname'])){
				$jsonData['profile']['firstname'] = $_POST['firstname'];
			}

			if(isset($_POST['lastname'])){
				$jsonData['profile']['lastname'] = $_POST['lastname'];
			}
			
			if(isset($_POST['birthday'])){
				$birthday = new DateTime($_POST['birthday']);
				$jsonData['profile']['birthday'] = $birthday->format('c');
			}

			if(isset($_POST['gender'])){
				$jsonData['profile']['gender'] = $_POST['gender'];
			}
		
			if(isset($_POST['city_name'])){
				$jsonData['profile']['city_name'] = $_POST['city_name'];
			}

			if(isset($_POST['street'])){
				$jsonData['profile']['street'] = $_POST['street'];
			}

			if(isset($_POST['zip_code'])){
				$jsonData['profile']['zip_code'] = $_POST['zip_code'];
			}

			
			if(isset($_POST['country_of_residence'])){
				$jsonData['profile']['country_of_residence'] = strtoupper($_POST['country_of_residence']);
			}

			if(isset($_POST['nationality'])){
				$jsonData['profile']['nationality'] = strtoupper($_POST['nationality']);
			}

			if(isset($_POST['phone'])){
				$jsonData['profile']['phone'] = $_POST['phone'];
			}
			
			if(isset($_POST['has_extended_data_persistence'])){
				$jsonData['has_extended_data_persistence'] = true;
			} else {
				$jsonData['has_extended_data_persistence'] = false;
			}
			
			if(isset($_POST['source_id'])){
				$jsonData['source_id'] = intval($_POST['source_id']);
			} else {
				$jsonData['source_id'] = intval($source_id_default);
			}
			
			if(isset($_POST['candidate_tag_ids'])){
				//$jsonData['source_id'] = intval($_POST['source_id']);
				//print_r($_POST['candidate_tag_ids']);
				//print_r( json_decode( html_entity_decode( stripslashes ($_POST['candidate_tag_ids']) ) ) );
				foreach(json_decode( html_entity_decode( stripslashes ($_POST['candidate_tag_ids']) ) ) as $key => $value) {
				    //print "$key => $value\n";
					$jsonData['candidate_tag_ids'][$key] = $value;
				}
			} else {
				//$jsonData['source_id'] = intval($source_id_default);
			}
			
			/*
			if(isset($_POST['data_privacy'])){
				$jsonData['has_extended_data_persistence'] = $_POST['data_privacy'];
			}*/
			

			if(isset($_POST['motivational_letter'])){
				$jsonData['motivational_letter'] = $_POST['motivational_letter'];
			}
			
			if(isset($_FILES['avatar']) || isset($_POST['avatarExternal'])){
				//$jsonData['profile']['avatar'] = $_FILES['avatar'];
				
				//echo json_encode($avatar_filetype, JSON_UNESCAPED_SLASHES);
				
				if(isset($_POST['avatarExternal'])){
					$avatar_data_upload = file_get_contents($_POST['avatarExternal']);
					$avatar_filename = 'test.jpg';
					$avatar_filetype = 'image/jpeg';
					//$avatar_file_size = $_FILES['avatar']['size'];
				    //$avatar_file_tmp = $_FILES['avatar']['tmp_name'];
					
					$avatar_base64 = base64_encode(file_get_contents($avatar_data_upload));
				
				} else {
					$avatar_filename = $_FILES['avatar']['name'];
					$avatar_filetype = $_FILES['avatar']['type'];
					//$avatar_file_size = $_FILES['avatar']['size'];
				    $avatar_file_tmp = $_FILES['avatar']['tmp_name'];
					
					$avatar_type = pathinfo($avatar_file_tmp, PATHINFO_EXTENSION);
					
	
					$avatar_data_upload = file_get_contents($avatar_file_tmp);
				    $avatar_base64 = base64_encode($avatar_data_upload);
				}	
				

				
				$img = $avatar_base64;
				
				$avatar_filetype = substr($avatar_filetype, strpos($avatar_filetype, '/') + 1);
				$avatar_filetype = str_replace("jpeg","jpg",$avatar_filetype);
				
				// Crop Image
			    if ($img) {
			    	$percent = 1.0;
			    
			    	// Content type
			    	header('Content-Type: '.$avatar_filetype.'');
			    
			    	$data = base64_decode($img);
			    	$im = imagecreatefromstring($data);
			    	$width = imagesx($im);
			    	$height = imagesy($im);
					if($width > $height){
						$newwidth = $height;
						$newheight = $height;
					} else {
						$newwidth = $width;
						$newheight = $width;
					}
			    	$thumb = imagecreatetruecolor($newwidth, $newheight);
			    
			    	// Resize
			    	imagecopyresized($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			    
			    	// Output
					ob_start (); 
						if($avatar_filetype === 'png'){
							imagepng($thumb);
						} else {
							imagejpeg($thumb);
						}
					  
					  $image_data = ob_get_contents(); 
					
					ob_end_clean (); 
			    	$avatar_base64 = base64_encode($image_data);
			    }
				

				//$avatar_base64_final = 'data:image/' . $type . ';base64,' . $avatar_base64;
				
				$jsonData['profile']['avatar']['filename'] = $avatar_filename;
				$jsonData['profile']['avatar']['file_type'] = $avatar_filetype;
				//$jsonData['profile']['avatar']['base64_content'] = $avatar_base64;
				$jsonData['profile']['avatar']['base64_content'] = $avatar_base64;
				

			}
			
			if(isset($_FILES['cv_file'])){
				$cv_file_filename = $_FILES['cv_file']['name'];
				$cv_file_filetype = $_FILES['cv_file']['type'];
				$cv_file_file_size = $_FILES['cv_file']['size'];
			    $cv_file_file_tmp = $_FILES['cv_file']['tmp_name'];
				
			    $cv_file_data_upload = file_get_contents($cv_file_file_tmp);
			    $cv_file_base64 = base64_encode($cv_file_data_upload);
				
				$cv_file_filetype = substr($cv_file_filetype, strpos($cv_file_filetype, '/') + 1);
				$cv_file_filetype = str_replace("jpeg","jpg",$cv_file_filetype);
				
				$jsonData['cv_file']['filename'] = $cv_file_filename;
				$jsonData['cv_file']['source'] = 'candidate';
				$jsonData['cv_file']['file_type'] = $cv_file_filetype;
				$jsonData['cv_file']['base64_content'] = $cv_file_base64;
			}
			
	
			for($i = 0; $i < 50; ++$i) {
				if(isset($_FILES['job_applications_'.$i.''])){
					//$jsonData['job_applications'] = $_FILES['job_applications'];
					//$job_applications = $_FILES['job_applications_'.$i.''];
					$job_applications_filename = $_FILES['job_applications_'.$i.'']['name'];
					$job_applications_filetype = $_FILES['job_applications_'.$i.'']['type'];
					$job_applications_file_size = $_FILES['job_applications_'.$i.'']['size'];
					$job_applications_file_tmp = $_FILES['job_applications_'.$i.'']['tmp_name'];
					
				    $job_applications_data_upload = file_get_contents($job_applications_file_tmp);
				    $job_applications_base64 = base64_encode($job_applications_data_upload);
						
					$job_applications_filetype = substr($job_applications_filetype, strpos($job_applications_filetype, '/') + 1);
					$job_applications_filetype = str_replace("jpeg","jpg",$job_applications_filetype);

					$jsonData['job_applications']['recruiterFiles'][$i]['filename'] =  $job_applications_filename;
					$jsonData['job_applications']['recruiterFiles'][$i]['source'] = 'candidate';
					$jsonData['job_applications']['recruiterFiles'][$i]['file_type'] =  $job_applications_filetype;
					$jsonData['job_applications']['recruiterFiles'][$i]['base64_content'] =  $job_applications_base64;
					
					$jsonDataUploads['recruiter_files'][$i]['filename'] =  $job_applications_filename;
					$jsonDataUploads['recruiter_files'][$i]['source'] = 'candidate';
					$jsonDataUploads['recruiter_files'][$i]['file_type'] =  $job_applications_filetype;
					$jsonDataUploads['recruiter_files'][$i]['base64_content'] =  $job_applications_base64;
					
				}
			}
			
			
			for($i = 0; $i < 50; ++$i) {
				if(isset($_POST['custom_field_'.$i.''])){
					//echo json_decode($_POST['custom_field_'.$i.'']);
					//print_r(json_decode($_POST['custom_field_'.$i.'']));
					//$jsonData['custom_field'] = $_POST['custom_field_'.$i.''];
					//$customFieldData = json_decode($_POST['custom_field_'.$i.'']);
					$customFieldData = $_POST['custom_field_'.$i.''];
					$customFieldData = json_decode( html_entity_decode( stripslashes ($customFieldData ) ) );
					$customFieldData = $customFieldData[0];
					
					$jsonData['custom_fields'][$i]['custom_field_id'] =  $customFieldData->custom_field_id;
					
					$countSubArray = 0;
					foreach ($customFieldData->values as &$value) {
						
						
						$jsonData['custom_fields'][$i]['values'][$countSubArray]['id'] = $value->id;
						$jsonData['custom_fields'][$i]['values'][$countSubArray]['value'] = $value->value;
						
						$countSubArray++;
					}
					
					
					//print_r($jsonData);
					
				}
			}
			
			
			//$cv_file = $_POST['cv_file'];
			
			$job_applications = $_POST['job_applications'];
			$profile = $_POST['profile'];
			$source_id = $_POST['source_id'];
			
			
			
			// Get JOB ID from API
			$curl_jobid = curl_init($requestURL.'?short_handle='.$shorthandle.'');
			curl_setopt($curl_jobid, CURLOPT_HTTPHEADER,
			    array(
			        'apikey: '.$apikey.'',
			    )
			);
			curl_setopt($curl_jobid, CURLOPT_RETURNTRANSFER, true);
			$response_jobid = json_decode(curl_exec($curl_jobid));
			$array_jobid = get_object_vars($response_jobid);
			$jobid = $array_jobid['data'][0]->id;
			
		

			
			
			// Send Request to Prescreen and check if User already exists

			$curl_candidate = curl_init($requestURLCandidate.'?email='.$email.'');
			curl_setopt($curl_candidate, CURLOPT_HTTPHEADER,
			    array(
			        'apikey: '.$apikey.'',
			    )
			);
			curl_setopt($curl_candidate, CURLOPT_RETURNTRANSFER, true);
			$response_candidate = json_decode(curl_exec($curl_candidate));
			$array_candidate = get_object_vars($response_candidate);

			if(isset($jobid)){
				$jsonData['job_id'] = $jobid;
			}

			
			// Candidate Exists already
			if(isset($array_candidate['data'][0]->id) && $array_candidate['data'][0]->id != ''){
				$result['status'] = 'success';
				$result['message'] = get_option('PS_job_applications_success_message_already_registered');
				
				// SEND DOUBLE OPTIN MAIL
				$candidatekey = random_strings(9);
				saveCandidateToDatabase($jsonData,$candidatekey,'exists',$application_url,$array_candidate['data'][0]->id,$shorthandle,'open');
				sendDoubleOptinMail($jsonData['profile']['firstname'],$jsonData['profile']['lastname'],$jsonData['email'],$candidatekey,$userstate,$application_url);
				
			// Candidate doesn't exist
			} else {
				
				// Check if Double Optin is always activated
				if( get_option('PS_job_applications_form_always_double_opt_in') != ''){
					
					$result['status'] = 'success';
					$result['message'] = get_option('PS_job_applications_success_message_new_user_doubleoptin');
					// SEND DOUBLE OPTIN MAIL
					$candidatekey = random_strings(9);
					saveCandidateToDatabase($jsonData,$candidatekey,'new',$application_url,'',$shorthandle,'open');
					sendDoubleOptinMail($jsonData['profile']['firstname'],$jsonData['profile']['lastname'],$jsonData['email'],$candidatekey,$userstate,$application_url);
					
				} else {
					
					// SEND EVERYTHING TO PRESCREEN
					
					//echo json_encode($jsonData,JSON_UNESCAPED_SLASHES);
					
					saveCandidateToDatabase($jsonData,random_strings(9),'new',$application_url,'',$shorthandle,'unnecessary');
					
					$curl_sendjob = curl_init($requestURLCandidate);
					$jsonDataEncoded = json_encode($jsonData);
					curl_setopt($curl_sendjob, CURLOPT_POST, 1);
					curl_setopt($curl_sendjob, CURLOPT_POSTFIELDS, $jsonDataEncoded);
					curl_setopt($curl_sendjob, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl_sendjob, CURLOPT_HTTPHEADER,
					    array(
							'Content-Type: application/json',
					        'apikey: '.$apikey.'',
					    )
					);

					$response = curl_exec($curl_sendjob);
					
					// Uploads not empty -> Check User Id and upload Files
					if (!empty($jsonData['job_applications']['recruiterFiles'])) {
						
						$callback = get_object_vars(json_decode($response));
						
						$appId = $callback['job_applications'][0]->id;
						
						//print_r($appId);
						// If new User Id -> Insert Documents
						if(isset($appId) && $appId != ''){
							

							$sendApplication = 'https://api.prescreenapp.io/v1/application/'.$appId.'';
							$curl_sendapplication = curl_init($sendApplication);
							$jsonDataEncodedApplication = json_encode($jsonDataUploads);
							
							curl_setopt($curl_sendapplication, CURLOPT_POST, 1);
							curl_setopt($curl_sendapplication, CURLOPT_POSTFIELDS, $jsonDataEncodedApplication);
							curl_setopt($curl_sendapplication, CURLOPT_CUSTOMREQUEST, 'PATCH');
							curl_setopt($curl_sendapplication, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($curl_sendapplication, CURLOPT_HTTPHEADER,
							    array(
									'Content-Type: application/json',
							        'apikey: '.$apikey.'',
							    )
							);
							curl_exec($curl_sendapplication);
						}
						
					
					}
					
					
					
					
					if (strpos($response, 'updated_at') !== false) {
						$result['status'] = 'success';
						$result['message'] = get_option('PS_job_applications_success_message');
					} else {
						$result['status'] = 'error';
						$result['message'] = $response;
					}
					
					
					/*
					if(is_success($result)){
						$result['status'] = 'success';
						$result['message'] = 'Vielen Dank für Ihre Bewerbungsd.';
					} else {
						echo $result;
					}
					*/
					
					//$result['status'] = 'success';
					//$result['message'] = 'Vielen Dank für Ihre Bewerbung.';
					//echo json_encode($result);
					
				}
				

			}
			//print_r($array_candidate);
			echo json_encode($result);
			
		} else {
			$result['status'] = 'error';
			$result['message'] = 'no valid shorthandle';
			echo json_encode($result);
		}
		
	} else {
		$result['status'] = 'error';
		$result['message'] = 'no api key';
		echo json_encode($result);
	}
	
	
	wp_die();
	
}

function currentUrl($server){
    //Figure out whether we are using http or https.
    $http = 'http';
    //If HTTPS is present in our $_SERVER array, the URL should
    //start with https:// instead of http://
    if(isset($server['HTTPS'])){
        $http = 'https';
    }
    //Get the HTTP_HOST.
    $host = $server['HTTP_HOST'];
    //Get the REQUEST_URI. i.e. The Uniform Resource Identifier.
    $requestUri = $server['REQUEST_URI'];
    //Finally, construct the full URL.
    //Use the function htmlentities to prevent XSS attacks.
    return $http . '://' . htmlentities($host) . '' . htmlentities($requestUri);
}

// Send Job Application Form
//add_action('wp_ajax_nopriv_login_with_linked_in', 'loginWithLinkedIn');
//add_action('wp_ajax_login_with_linked_in', 'loginWithLinkedIn');
function loginWithLinkedIn(){
	
	
	if(get_option('PS_job_applications_linkedin_client_id') && get_option('PS_job_applications_linkedin_client_secret')){
	
	    $http = 'http';
	    if(isset($_SERVER['HTTPS'])){
	        $http = 'https';
	    }
	    $host = $_SERVER['HTTP_HOST'];
	    $requestUri = $_SERVER['REQUEST_URI'];
		
		if(isset($_GET['sh'])){
			setcookie("job_sh", $_GET['sh'], time()+3600); 
			//setcookie("job_jh", $_GET['jh'], time()+3600); 
		}
	
		$currentUrl = currentUrl($_SERVER);
	
		
		if(get_option('PS_job_applications_linkedin_callback_url')){
			$callbackLink = get_option('PS_job_applications_linkedin_callback_url');
		} else {
			$callbackLink = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
		}
		
		$hybridauthConfig = [
			'callback' => $http . '://' . htmlentities($host) .$callbackLink,
			'providers' => [
				'LinkedIn' => [
					'enabled' => true,
					'keys' => [
						'id' => get_option('PS_job_applications_linkedin_client_id'),
						'secret' => get_option('PS_job_applications_linkedin_client_secret'),
					],
				]
			],
		];
		
		
		$hybridauth = new Hybridauth($hybridauthConfig);
		$adapters = $hybridauth->getConnectedAdapters();
		
		$isLoggedIn = false;
		
	
		try {
			$userInfoLinkedIn = $adapters['LinkedIn']->getUserProfile();
			$tokens = $adapters['LinkedIn']->getAccessToken();
		
			// if we can get here, toggle to being logged in
			$isLoggedIn = true;
			
		} catch(Throwable $e) {
			#print "Exception: <p> $e";
		}
		
		
		if ($isLoggedIn == true) :
	
				$userInfoLinkedIn = $adapters['LinkedIn']->getUserProfile();
	
				if(isset($userInfoLinkedIn->photoURL) && $userInfoLinkedIn->photoURL != ''){
					return '<script>
					jQuery(document).ready(function($) {
						$(\'<input type="hidden" name="avatarExternal" value="'.$userInfoLinkedIn->photoURL.'"></input>\').insertBefore(\'[name="avatar"]\');
						$(\'<img src="'.$userInfoLinkedIn->photoURL.'" alt="" />\').insertBefore(\'[name="avatar"]\');
						$(\'[name="avatar"]\').hide();
						
						if($(\'[name="email"]\').length){
							$(\'[name="email"]\').val(\''.$userInfoLinkedIn->email.'\');
						}
						if($(\'[name="firstname"]\').length){
							$(\'[name="firstname"]\').val(\''.$userInfoLinkedIn->firstName.'\');
						}
						if($(\'[name="lastname"]\').length){
							$(\'[name="lastname"]\').val(\''.$userInfoLinkedIn->lastName.'\');
						}
					});
					</script>';
				} else {
					return '<script>
					jQuery(document).ready(function($) {
						if($(\'[name="email"]\').length){
							$(\'[name="email"]\').val(\''.$userInfoLinkedIn->email.'\');
						}
						if($(\'[name="firstname"]\').length){
							$(\'[name="firstname"]\').val(\''.$userInfoLinkedIn->firstName.'\');
						}
						if($(\'[name="lastname"]\').length){
							$(\'[name="lastname"]\').val(\''.$userInfoLinkedIn->lastName.'\');
						}
					});
					</script>';
				}
	
			
		else :
	
			try {
			 
			    $hybridauth = new Hybridauth($hybridauthConfig);
			
	
				if (isset($_GET['logout'])) {
					$adapter = $hybridauth->getAdapter('LinkedIn');
					$adapter->disconnect();
			
					$goTo = currentUrl($_SERVER)."&userLoggedOut=true";
					
					// if the user revoked the token, the trick is to log them out of HybridAuth then re-sign in
					// redirect them back to this page rather than the "you logged out" page
					if ( isset($_GET['relogin']) ) {
						$goTo = $hybridauthConfig['callback'];
					}
			
					header("Location: $goTo");
					exit();
				}
			
				// Gracefully handle if a user declines on the prompt to log in
				if (isset($_GET['error']) && ($_GET['error'] == 'user_cancelled_login' || $_GET['error'] == 'user_cancelled_authorize')) {
					header("Location: ".currentUrl($_SERVER)."&cancel=" .  $_GET['error']);
					exit();
				}
			
			    $hybridauth->authenticate('LinkedIn');
			
			    $adapters = $hybridauth->getConnectedAdapters();
			   
			
	
			    $userInfoLinkedIn = $adapters['LinkedIn']->getUserProfile();
	
			    HttpClient\Util::redirect(currentUrl($_SERVER));
			} catch (Exception $e) {
			  
	
			    if ( strpos($e->getMessage(), "65601") > 0) {       
			       header("Location: " . $hybridauthConfig['callback'] . "?logout=true&relogin=true");
			       exit();
			    }
			
			    echo $e->getMessage();
			
			}
	    
		endif;
	
	} else {
		return '<div style="width: 100%; text-align: left;">Es ist ein Fehler aufgetreten. Bitte informieren Sie einen Administrator um die Zugangsdaten zu überprüfen.</div>';
	}

}

add_shortcode('login_with_linkedin', 'loginWithLinkedIn');



function loginWithXing(){
	
	/*
	if(get_option('PS_job_applications_xing_consumer_key') && get_option('PS_job_applications_xing_consumer_secret')){
	
	    $http = 'http';
	    if(isset($_SERVER['HTTPS'])){
	        $http = 'https';
	    }
	    $host = $_SERVER['HTTP_HOST'];
	    $requestUri = $_SERVER['REQUEST_URI'];
		
		if(isset($_GET['sh'])){
			setcookie("job_sh", $_GET['sh'], time()+3600); 
		}
		
		$currentUrl = currentUrl($_SERVER);
	
		$callbackLink = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

		define("CALLBACK_URL", $http.'://'.$host.$callbackLink);
		define("AUTH_URL", "https://api.xing.com/auth/oauth2/authorize");
		define("ACCESS_TOKEN_URL", "https://example/oauth2/token");
		define("CLIENT_ID", get_option('PS_job_applications_xing_consumer_key'));
		define("CLIENT_SECRET", get_option('PS_job_applications_xing_consumer_secret'));
		define("SCOPE", ""); // optional
		
		$url = AUTH_URL."?"
		   ."response_type=code"
		   ."&client_id=". urlencode(CLIENT_ID)
		   ."&scope=". urlencode(SCOPE)
		   ."&redirect_uri=". urlencode(CALLBACK_URL)
		;
		
		print_r($url);
	
	} else {
		return '<div style="width: 100%; text-align: left;">Es ist ein Fehler aufgetreten. Bitte informieren Sie einen Administrator um die Zugangsdaten zu überprüfen.</div>';
	}
	*/
	
}
add_shortcode('login_with_xing', 'loginWithXing');



/**
 * Add/Remove appropriate CSS classes to Menu so Submenu displays open and the Menu link is styled appropriately.
 */
function pscareer_correct_current_menu(){
	$screen = get_current_screen();
	if ( $screen->id == 'jobapplicationform' || $screen->id == 'edit-jobapplicationform' ) {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#toplevel_page_prescreen-career-settings').addClass('wp-has-current-submenu wp-menu-open menu-top menu-top-first').removeClass('wp-not-current-submenu');
		$('#toplevel_page_prescreen-career-settings > a').addClass('wp-has-current-submenu').removeClass('wp-not-current-submenu');
	});
	</script>
	<?php
	}

}

add_action('admin_head', 'pscareer_correct_current_menu', 50);

function current_date_shortcode_function($atts) {
	extract(shortcode_atts(array(
		'addmonths' => 12,
	), $atts));
	
	$currentDate = date('d.m.Y', strtotime('+'.$addmonths.' months'));
	
	return $currentDate;
}

add_shortcode('current_date', 'current_date_shortcode_function');
