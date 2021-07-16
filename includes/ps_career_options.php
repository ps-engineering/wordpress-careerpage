<?php

function prescreen_career_register_settings() {
	add_option( 'PS_career_xmlurl', '');
	register_setting( 'PS_career_options_group', 'PS_career_xmlurl' );
	add_option( 'PS_career_applink', '');
	register_setting( 'PS_career_options_group', 'PS_career_applink' );
	add_option( 'PS_career_mainjoburl', '');
	register_setting( 'PS_career_options_group', 'PS_career_jobimport_interval' );
	add_option( 'PS_career_jobimport_interval', '');
	register_setting( 'PS_career_options_group', 'PS_career_googleindexing_interval' );
	add_option( 'PS_career_googleindexing_interval', '');
	register_setting( 'PS_career_options_group', 'PS_career_mainjoburl' );
	add_option( 'PS_career_google4jobs', '');
	register_setting( 'PS_career_options_group', 'PS_career_google4jobs' );
	add_option( 'PS_career_googleindexing', '');
	register_setting( 'PS_career_options_group', 'PS_career_googleindexing' );
	add_option( 'PS_career_googleindexingkey', '');
	register_setting( 'PS_career_options_group', 'PS_career_googleindexingkey' );
	add_option( 'PS_career_googleapikey', '');
	register_setting( 'PS_career_options_group', 'PS_career_googleapikey' );
	add_option( 'PS_career_kununu_score', '');
	register_setting( 'PS_career_options_group', 'PS_career_kununu_score' );
	add_option( 'PS_career_kununu_live_score', '');
	register_setting( 'PS_career_options_group', 'PS_career_kununu_live_score' );
	add_option( 'PS_career_showposts', '');
	register_setting( 'PS_career_options_group', 'PS_career_showposts' );
	
	add_option( 'PS_career_selectdefault', '');
	register_setting( 'PS_career_options_group', 'PS_career_selectdefault' );
	
    add_option( 'PS_yoast_remove', '');
	register_setting( 'PS_career_options_group', 'PS_yoast_remove' );
	add_option( 'PS_wp_remove', '');
	register_setting( 'PS_career_options_group', 'PS_wp_remove' );
	add_option( 'PS_wp_rest_api', '');
	register_setting( 'PS_career_options_group', 'PS_wp_rest_api' );
	add_option( 'PS_header', '');
	register_setting( 'PS_career_options_group', 'PS_header' );
	add_option( 'PS_footer', '');
	register_setting( 'PS_career_options_group', 'PS_footer' );
	add_option( 'PS_header_mobile', '');
	register_setting( 'PS_career_options_group', 'PS_header_mobile' );
	add_option( 'PS_footer_mobile', '');
	register_setting( 'PS_career_options_group', 'PS_footer_mobile' );
	
	add_option( 'PS_api_key', '');
	register_setting( 'PS_career_options_group', 'PS_api_key' );
	add_option( 'PS_job_applications_form_custom_field_value', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_form_custom_field_value' );
	add_option( 'PS_job_applications_form_default_id', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_form_default_id' );
    add_option( 'PS_job_applications_form_always_double_opt_in', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_form_always_double_opt_in' );
	add_option( 'PS_job_applications_mail_sender', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_mail_sender' );
	add_option( 'PS_job_applications_mail_subject', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_mail_subject' );
    add_option( 'PS_job_applications_form_mail', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_form_mail' );
	add_option( 'PS_job_applications_debug', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_debug' );
	
    add_option( 'PS_job_applications_success_message', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_success_message' );
    add_option( 'PS_job_applications_success_message_already_registered', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_success_message_already_registered' );
    add_option( 'PS_job_applications_success_message_new_user_doubleoptin', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_success_message_new_user_doubleoptin' );
	
    add_option( 'PS_job_applications_linkedin_client_id', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_linkedin_client_id' );
    add_option( 'PS_job_applications_linkedin_client_secret', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_linkedin_client_secret' );
    add_option( 'PS_job_applications_linkedin_callback_url', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_linkedin_callback_url' );
	
    add_option( 'PS_job_applications_xing_consumer_key', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_xing_consumer_key' );
    add_option( 'PS_job_applications_xing_consumer_secret', '');
	register_setting( 'PS_career_options_group', 'PS_job_applications_xing_consumer_secret' );		
}

/*
function prescreen_career_register_options_page() {
	add_submenu_page( "options-general.php", "Prescreen Career", "Prescreen Career", "manage_options", "Prescreen Career", "prescreen_career_options_page");
}
*/

add_action( 'admin_footer', 'my_action_javascript' ); // Write our JS below here

function my_action_javascript() { ?>
	<script type="text/javascript" >

	</script> <?php
}

add_action( 'wp_ajax_my_action', 'my_action' );

function my_action() {
	global $wpdb; // this is how you get access to the database

	wp_die(); // this is required to terminate immediately and return a proper response
}



function prescreen_career_options_page()
{
global $wpdb;
$PS_career_google4jobs = get_option( "PS_career_google4jobs" );
$PS_career_googleindexing = get_option( "PS_career_googleindexing" );
$PS_job_applications_debug = get_option( "PS_job_applications_debug" );

$PS_job_applications_form_always_double_opt_in = get_option( "PS_job_applications_form_always_double_opt_in" );

$PS_send_msg = get_option( "PS_send_msg" );
$PS_log_msg = get_option( "PS_log_msg" );
$PS_admin_email = get_option( "PS_admin_email" );
If( !filter_var($PS_admin_email, FILTER_VALIDATE_EMAIL) ) $PS_admin_email = "";
$PS_yoast_remove = get_option( "PS_yoast_remove" );
$PS_wp_rest_api = get_option( "PS_wp_rest_api" );
$PS_wp_remove = get_option( "PS_wp_remove" );

?>
        <h1><?php _e( "Prescreen Career", "prescreen" ) ?></h1>
		
		<div style="width: 90%; display: flex;">
		
		<div style="width: 50%; padding-left: 30px;">
			 
			 
			<form method="post" action="options.php">
	            <?php settings_fields( 'PS_career_options_group' ); ?>
				
				<?php 
				if(get_option('PS_career_jobimport_interval') != 'never'){
					wp_clear_scheduled_hook( 'schedule_job_import' );
					schedule_job_import(); 
				} else {
					wp_clear_scheduled_hook( 'schedule_job_import' );
				}
				
				if(googleIndexingIsActive() && get_option('PS_career_googleindexing_interval') != 'never'){
					wp_clear_scheduled_hook( 'schedule_google_indexing' );
					schedule_google_indexing();
				} else {
					wp_clear_scheduled_hook( 'schedule_google_indexing' );
				}
				?>

	            
				<h2><?php _e( "Allgemeine Einstellungen", "prescreen" ) ?></h2>
				
	            <table style="text-align: left; width: 100%;">
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px;">Last Update:</th>
						<td>
						<?php
						$lastUpdate = $wpdb->get_results("SELECT lastupdate FROM ".$wpdb->prefix."prescreen_jobs_last_update WHERE id = 1");
						if (count($lastUpdate)> 0){ echo date('d.m.Y - H:i:s', strtotime($lastUpdate[0]->lastupdate)); }
						?>
						</td>
					</tr>
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_career_xmlurl">XML Url</label></th>
						<td><input style="width: 100%;" type="text" id="PS_career_xmlurl" name="PS_career_xmlurl" value="<?php echo get_option('PS_career_xmlurl'); ?>" /></td>
					</tr>
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px;"><label for="PS_career_applink">Application Link</label></th>
						<td><input style="width: 100%;" type="text" id="PS_career_applink" name="PS_career_applink" value="<?php echo get_option('PS_career_applink'); ?>" /></td>
					</tr>
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px;"><label for="PS_career_mainjoburl">Main Job Url</label></th>
						<td><input style="width: 100%;" type="text" id="PS_career_mainjoburl" name="PS_career_mainjoburl" value="<?php echo get_option('PS_career_mainjoburl'); ?>" /></td>
					</tr>
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px;"><label for="PS_career_showposts">Number of Jobs per Page</label></th>
						<td><input style="width: 100%;" type="text" id="PS_career_showposts" name="PS_career_showposts" value="<?php echo get_option('PS_career_showposts'); ?>" /></td>
					</tr>
		  		  	<tr align="top" style="display: none;">
					    <th scope="row"><label for="PS_wp_rest_api">Disable REST API:</label></th>
					    <td>
							<input type="checkbox" id="PS_wp_rest_api" name="PS_wp_rest_api"<?php echo ( isset( $PS_wp_rest_api ) and $PS_wp_rest_api != ""  ) ? " checked" : ""; ?> />
					  	</td>
					</tr>
					<tr align="top">
						<th scope="row"><label for="PS_wp_remove">Clean up Wordpress header:</label></th>
						<td>
							<input type="checkbox" id="PS_wp_remove" name="PS_wp_remove"<?php echo ( isset( $PS_wp_remove ) and $PS_wp_remove != ""  ) ? " checked" : ""; ?> />
						</td>
					</tr>
					<tr align="top">
						<th scope="row"><label for="PS_yoast_remove">Remove Yoast comments from HTML:</label></th>
						<td>
							<input type="checkbox" id="PS_yoast_remove" name="PS_yoast_remove"<?php echo ( isset( $PS_yoast_remove ) and $PS_yoast_remove != ""  ) ? " checked" : ""; ?> />
						</td>
					</tr>
				</table>
				
				
				<hr style="margin: 1rem 0 1.5rem 0;" />
				<h2><?php _e( "Job Application Form", "prescreen" ) ?></h2>
					
				<table style="text-align: left; width: 100%;">
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_api_key">Prescreen API Key:</label></th>
					  <td><input style="width: 100%;" id="PS_api_key" name="PS_api_key" value="<?php echo get_option('PS_api_key'); ?>" /></td>
					</tr>
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_form_custom_field_value">Formular Custom Field ID:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_form_custom_field_value" name="PS_job_applications_form_custom_field_value" value="<?php echo get_option('PS_job_applications_form_custom_field_value'); ?>" /></td>
					</tr>
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_form_default_id">Default Formular Template:</label></th>
					  <td>
						    <select id="PS_job_applications_form_default_id" name="PS_job_applications_form_default_id" value="<?php echo get_option('PS_job_applications_form_default_id'); ?>">
								
								<?php
								$records = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_custom_field_form_template");
								foreach($records as $customfield){
									?>
									<option <?php if(get_option('PS_job_applications_form_default_id') === $customfield->cfid){ echo 'selected'; } ?> value="<?php echo $customfield->cfid; ?>"><?php echo $customfield->value; ?></option>
									<?php
								}
								?>

							</select>
					  </td>
					</tr>
					<tr align="top">
						<th scope="row"><label for="PS_job_applications_form_always_double_opt_in">Send always Double Opt-In Mail:</label></th>
						<td>
							<input type="checkbox" id="PS_job_applications_form_always_double_opt_in" name="PS_job_applications_form_always_double_opt_in" <?php echo ( isset( $PS_job_applications_form_always_double_opt_in ) and $PS_job_applications_form_always_double_opt_in != ""  ) ? " checked" : ""; ?> />
						</td>
					</tr>
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_mail_sender">E-Mail Absenderadresse:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_mail_sender" name="PS_job_applications_mail_sender" value="<?php echo get_option('PS_job_applications_mail_sender'); ?>" /></td>
					</tr>
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_mail_subject">E-Mail Betreff:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_mail_subject" name="PS_job_applications_mail_subject" value="<?php echo get_option('PS_job_applications_mail_subject'); ?>" /></td>
					</tr>

					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px;"><label for="PS_job_applications_form_mail">Double Opt-In Mailtext:</label></th>
						<td><textarea rows="8" style="width: 100%;" id="PS_job_applications_form_mail" name="PS_job_applications_form_mail"><?php echo get_option('PS_job_applications_form_mail'); ?></textarea></td>
					</tr>
					
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_success_message">Erfolgsmeldung:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_success_message" name="PS_job_applications_success_message" value="<?php echo get_option('PS_job_applications_success_message'); ?>" /></td>
					</tr>
	
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_success_message_already_registered">Erfolgsmeldung für bereits registrierte Benutzer:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_success_message_already_registered" name="PS_job_applications_success_message_already_registered" value="<?php echo get_option('PS_job_applications_success_message_already_registered'); ?>" /></td>
					</tr>
	
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_success_message_new_user_doubleoptin">Erfolgsmeldung für neuen Benutzer mit Double Opt-In:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_success_message_new_user_doubleoptin" name="PS_job_applications_success_message_new_user_doubleoptin" value="<?php echo get_option('PS_job_applications_success_message_new_user_doubleoptin'); ?>" /></td>
					</tr>		
					
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_linkedin_client_id">LinkedIn Client ID:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_linkedin_client_id" name="PS_job_applications_linkedin_client_id" value="<?php echo get_option('PS_job_applications_linkedin_client_id'); ?>" /></td>
					</tr>
					
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_linkedin_client_secret">LinkedIn Client Secret:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_linkedin_client_secret" name="PS_job_applications_linkedin_client_secret" value="<?php echo get_option('PS_job_applications_linkedin_client_secret'); ?>" /></td>
					</tr>	
					<!--
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_linkedin_callback_url">LinkedIn Callback URL:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_linkedin_callback_url" name="PS_job_applications_linkedin_callback_url" value="<?php echo get_option('PS_job_applications_linkedin_callback_url'); ?>" /></td>
					</tr>	
					-->
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_xing_consumer_key">Xing Consumer Key:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_xing_consumer_key" name="PS_job_applications_xing_consumer_key" value="<?php echo get_option('PS_job_applications_xing_consumer_key'); ?>" /></td>
					</tr>
					
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_job_applications_xing_consumer_secret">Xing Consumer Secret:</label></th>
					  <td><input style="width: 100%;" id="PS_job_applications_xing_consumer_secret" name="PS_job_applications_xing_consumer_secret" value="<?php echo get_option('PS_job_applications_xing_consumer_secret'); ?>" /></td>
					</tr>	
		  		  	<tr align="top">
					    <th scope="row"><label for="PS_job_applications_debug">Debug:</label></th>
					    <td>
							<input type="checkbox" id="PS_job_applications_debug" name="PS_job_applications_debug"<?php echo ( isset( $PS_job_applications_debug ) and $PS_job_applications_debug != ""  ) ? " checked" : ""; ?> />
					  	</td>
					</tr>			
				</table>
				
				
				<hr style="margin: 1rem 0 1.5rem 0;" />
				<h2><?php _e( "Google 4 Jobs", "prescreen" ) ?></h2>
				<table style="text-align: left; width: 100%;">
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_career_google4jobs">Google4Jobs aktivieren</label></th>
						<td><input type="checkbox" id="PS_career_google4jobs" name="PS_career_google4jobs" <?php echo ( isset( $PS_career_google4jobs ) and $PS_career_google4jobs != ""  ) ? " checked" : ""; ?> /></td>
					</tr>
				</table>
				<hr style="margin: 1rem 0 1.5rem 0;" />
				<h2><?php _e( "Google Indexing", "prescreen" ) ?></h2>
				<table style="text-align: left; width: 100%;">
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_career_googleindexing">Google Indexing aktivieren</label></th>
						<td><input type="checkbox" id="PS_career_googleindexing" name="PS_career_googleindexing"<?php echo ( isset( $PS_career_googleindexing ) and $PS_career_googleindexing != ""  ) ? " checked" : ""; ?> /></td>
					</tr>
		
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px;"><label for="PS_career_googleindexingkey">Google Indexing Key</label></th>
						<td><textarea rows="8" style="width: 100%;font-family:Courier New;" id="PS_career_googleindexingkey" name="PS_career_googleindexingkey"><?php echo get_option('PS_career_googleindexingkey'); ?></textarea></td>
					</tr>
		
					<?php if(googleIndexingIsActive()){ ?>
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px;"><label for="PS_career_googleindexing_test">Google Indexing Test</label></th>
						<td>					
							  <input type="submit" class="testgoogleindex button button-primary" value="Google Index testen">
							  <div class="ps_career_ajax_result" style="margin-top: 20px;"></div>
						</td>
					</tr>
					<?php } ?>
					

	            </table>
				<hr style="margin: 1rem 0 1.5rem 0;" />
				<h2><?php _e( "Google API", "prescreen" ) ?></h2>
				<table style="text-align: left; width: 100%;">
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_career_googleapikey">Google Maps API Key</label></th>
						<td><input style="width: 100%;" id="PS_career_googleapikey" name="PS_career_googleapikey" value="<?php echo get_option('PS_career_googleapikey'); ?>" /></td>
					</tr>
		
	            </table>
				<hr style="margin: 1rem 0 1.5rem 0;" />
				<h2><?php _e( "Kununu", "prescreen" ) ?></h2>
				<table style="text-align: left; width: 100%;">

					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_career_kununu_score">Kununu-Score</label></th>
						<td><textarea rows="6" style="width: 100%;font-family:Courier New;" id="PS_career_kununu_score" name="PS_career_kununu_score"><?php echo get_option('PS_career_kununu_score'); ?></textarea></td>
					</tr>
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px;"><label for="PS_career_kununu_live_score">Kununu-Live-Score</label></th>
						<td><input style="width: 100%;" id="PS_career_kununu_live_score" name="PS_career_kununu_live_score" value="<?php echo get_option('PS_career_kununu_live_score'); ?>" /></td>
					</tr>

				</table>
	            <hr style="margin: 1rem 0 1.5rem 0;" />
				<h2><?php _e( "Meldungen", "prescreen" ) ?></h2>
				
				<table style="text-align: left; width: 100%;">
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_career_selectdefault">Umkreissuche Fehlermeldung</label></th>
						<td><input style="width: 100%;" id="PS_career_selectdefault" name="PS_career_selectdefault" value="<?php echo get_option('PS_career_selectdefault'); ?>" /></td>
					</tr>
				</table>
	            <hr style="margin: 1rem 0 1.5rem 0;" />
				<h2><?php _e( "Cronjobs", "prescreen" ) ?></h2>
				
				<table style="text-align: left; width: 100%;">
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_career_jobimport_interval">Interval Job Import</label></th>
						<td>
						<select id="PS_career_jobimport_interval" name="PS_career_jobimport_interval" value="<?php echo get_option('PS_career_jobimport_interval'); ?>">
							<option <?php if(get_option('PS_career_jobimport_interval') === '30minutes'){ echo 'selected'; } ?> value="30minutes">30minutes</option>
							<option <?php if(get_option('PS_career_jobimport_interval') === '1hour'){ echo 'selected'; } ?> value="1hour">1hour</option>
							<option <?php if(get_option('PS_career_jobimport_interval') === '2hours'){ echo 'selected'; } ?> value="2hours">2hours</option>
							<option <?php if(get_option('PS_career_jobimport_interval') === '3hours'){ echo 'selected'; } ?> value="3hours">3hours</option>
							<option <?php if(get_option('PS_career_jobimport_interval') === 'twicedaily'){ echo 'selected'; } ?> value="twicedaily">twicedaily</option>
							<option <?php if(get_option('PS_career_jobimport_interval') === 'daily'){ echo 'selected'; } ?> value="daily">daily</option>
							<option <?php if(get_option('PS_career_jobimport_interval') === 'never' || get_option('PS_career_jobimport_interval') === ''){ echo 'selected'; } ?> value="never">never</option>
						</select>
						</td>
					</tr>
					<?php if(googleIndexingIsActive()){ ?>
					<tr align="top">
						<th scope="row" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_career_googleindexing_interval">Google Indexing Update</label></th>
						<td>
						<select id="PS_career_googleindexing_interval" name="PS_career_googleindexing_interval" value="<?php echo get_option('PS_career_googleindexing_interval'); ?>">
							<option <?php if(get_option('PS_career_googleindexing_interval') === '30minutes'){ echo 'selected'; } ?> value="30minutes">30minutes</option>
							<option <?php if(get_option('PS_career_googleindexing_interval') === '1hour'){ echo 'selected'; } ?> value="1hour">1hour</option>
							<option <?php if(get_option('PS_career_googleindexing_interval') === '2hours'){ echo 'selected'; } ?> value="2hours">2hours</option>
							<option <?php if(get_option('PS_career_googleindexing_interval') === '3hours'){ echo 'selected'; } ?> value="3hours">3hours</option>
							<option <?php if(get_option('PS_career_googleindexing_interval') === 'twicedaily'){ echo 'selected'; } ?> value="twicedaily">twicedaily</option>
							<option <?php if(get_option('PS_career_googleindexing_interval') === 'daily'){ echo 'selected'; } ?> value="daily">daily</option>
							<option <?php if(get_option('PS_career_googleindexing_interval') === 'never' || get_option('PS_career_googleindexing_interval') === ''){ echo 'selected'; } ?> value="never">never</option>
						</select>
						</td>
					</tr>
					<?php } ?>
					
				</table>
				<hr style="margin: 1rem 0 1.5rem 0;" />
				<h2><?php _e( "Custom Scripts", "prescreen" ) ?></h2>
					
				<table style="text-align: left; width: 100%;">
			  		<tr align="top">
					  <th scope="row" valign="top" style="vertical-align: top; padding-right: 20px; width: 200px;"><label for="PS_header">Header code desktop:</label></th>
					  <td>
						  <textarea name="PS_header" id="PS_header" rows="8" style="font-family:Courier New;width: 100%;"><?php echo get_option('PS_header'); ?></textarea>
					  </td>
					</tr>
					<tr align="top">
					  <th scope="row" valign="top"><label for="PS_header_mobile">Header code mobile:</label></th>
					  <td>
						  <textarea name="PS_header_mobile" id="PS_header_mobile" rows="8" style="font-family:Courier New;width: 100%;"><?php echo get_option('PS_header_mobile'); ?></textarea>
					  </td>
					</tr>
					<tr align="top">
					  <th scope="row" valign="top"><label for="PS_footer">Footer code desktop:</label></th>
					  <td>
						  <textarea name="PS_footer" id="PS_footer" rows="8" style="font-family:Courier New;width: 100%;"><?php echo get_option('PS_footer'); ?></textarea>
					  </td>
					</tr>
					<tr align="top">
					  <th scope="row" valign="top"><label for="PS_footer_mobile">Footer code mobile:</label></th>
					  <td>
						  <textarea name="PS_footer_mobile" id="PS_footer_mobile" rows="8" style="font-family:Courier New;width: 100%;"><?php echo get_option('PS_footer_mobile'); ?></textarea>
					  </td>
					</tr>
				</table>
				
				
				
	            <?php submit_button(); ?>
			</form>
		</div>
        
        </div>
<?php
}




//remove automatic redirection creation
add_filter('wpseo_premium_post_redirect_slug_change', '__return_true' ); 
add_filter('wpseo_premium_term_redirect_slug_change', '__return_true' ); 


//remove yoast comments in frontend html
class ps_yoast {
	private $debug_marker_removed = false;
	private $head_marker_removed = false;
	
	public function __construct() {
		add_action( 'init', array( $this, 'bundle' ), 1);
	}
	
	public function bundle() {
		if(defined( 'WPSEO_VERSION' )) {
			$debug_marker = ( version_compare( WPSEO_VERSION, '4.4', '>=' ) ) ? 'debug_mark' : 'debug_marker';

			// main function to unhook the debug msg
			if(class_exists( 'WPSEO_Frontend' ) && method_exists( 'WPSEO_Frontend', $debug_marker )) {
				remove_action( 'wpseo_head', array( WPSEO_Frontend::get_instance(), $debug_marker ) , 2);
				
				$this->debug_marker_removed = true;
				
				// also removes the end debug msg as of 5.9
				if(version_compare( WPSEO_VERSION, '5.9', '>=' )) $this->head_marker_removed = true;
			}
			
			// compatible solution for everything below 5.8
			if(class_exists( 'WPSEO_Frontend' ) && method_exists( 'WPSEO_Frontend', 'head' ) && version_compare( WPSEO_VERSION, '5.8', '<' )) {
				remove_action( 'wp_head', array( WPSEO_Frontend::get_instance(), 'head' ) , 1);
				add_action( 'wp_head', array($this, 'rewrite'), 1);
				$this->head_marker_removed = true;
			}
			
			// temp solution for all installations on 5.8
			if(version_compare( WPSEO_VERSION, '5.8', '==' )) {
				add_action('get_header', array( $this, 'buffer_header' ));
				add_action('wp_head', array( $this, 'buffer_head' ), 999);
				$this->head_marker_removed = true;
			}
			
			// backup solution
			if($this->operating_status() == 2) {
				add_action('get_header', array( $this, 'buffer_header' ));
				add_action('wp_head', array( $this, 'buffer_head' ), 999);
			}
			
		}
	}
	
	public function operating_status() {
		if($this->debug_marker_removed && $this->head_marker_removed) {
			return 1;
		} elseif(!$this->debug_marker_removed && $this->head_marker_removed || $this->debug_marker_removed && !$this->head_marker_removed) {
			return 2;
		} else {
			return 3;
		}
	}
	
	// compatible solution for everything below 5.8
	public function rewrite() {
		$rewrite = new ReflectionMethod( 'WPSEO_Frontend', 'head' );
		
		$filename = $rewrite->getFileName();
		$start_line = $rewrite->getStartLine();
		$end_line = $rewrite->getEndLine()-1;

		$length = $end_line - $start_line;
		$source = file( $filename );
		$body = implode( '', array_slice($source, $start_line, $length) );
		$body = preg_replace( '/echo \'\<\!(.*?)\n/', '', $body);

		eval($body);
	}
	
	// temp solution for all installations on 5.8, and also the backup solution in the future
	public function buffer_header() {
		ob_start(function ($o) {
			return preg_replace('/\n?<.*?yoast.*?>/mi','',$o);
		});
	}
	
	public function buffer_head() {
		ob_end_flush();
	}
}

$remove_yoast = get_option( "PS_yoast_remove" );

If( isset( $remove_yoast ) and $remove_yoast != "" ) {
	add_filter( 'wpseo_debug_markers', '__return_false' );
	new ps_yoast;
}

function prescreen_load_plugin_textdomain(){	
	If( load_plugin_textdomain( "prescreen", FALSE, basename( dirname( __FILE__ ) ) . '/lang/' ) );
}



add_action( "init", "prescreen_start" );


function prescreen_start(){
	// Add code to header and footer
	if ( wp_is_mobile() ) {
		add_action('wp_head', function(){
			  echo get_option( "PS_header_mobile" ) . PHP_EOL;
		});

		add_action('wp_footer', function(){
			  echo get_option( "PS_footer_mobile" ) . PHP_EOL;
		});
	} else {
		add_action('wp_head', function(){
			  echo get_option( "PS_header" ) . PHP_EOL;
		});

		add_action('wp_footer', function(){
			  echo get_option( "PS_footer" ) . PHP_EOL;
		});
	}


	//disable WP REST API
	$disable_rest_api = get_option( "PS_wp_rest_api");

	If( isset( $disable_rest_api ) and $disable_rest_api != "" ) {
		add_filter( 'rest_authentication_errors', function( $result ) {
			if ( ! empty( $result ) ) {
				return $result;
			}
			if ( ! is_user_logged_in() ) {
				return new WP_Error( 'restx_logged_out', 'Sorry, you must be logged in to make a request.', array( 'status' => 401 ) );
			}
			return $result;
		});
	}

	//clean up Wordpress header
	$remove_wp = get_option( "PS_wp_remove" );

	If( isset( $remove_wp ) and $remove_wp != "" ) {
		remove_action( 'wp_head', 'rsd_link' ); //RSD
		remove_action( 'wp_head', 'wlwmanifest_link' ); //wlwmanifest (Windows Live Writer) link
		remove_action( 'wp_head', 'wp_generator' ); //meta name generator
		remove_action( 'wp_head', 'wp_shortlink_wp_head' ); //shortlink
		remove_action( 'wp_head', 'feed_links', 2 ); //feed links
		remove_action( 'wp_head', 'feed_links_extra', 3 );  //comments feed 
		remove_action( 'wp_head', 'index_rel_link' ); //index page
		remove_action( 'wp_head', 'start_post_rel_link' ); //article in the distant past
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' ); //prev and next links
		remove_action( 'wp_head', 'rest_output_link_wp_head', 10 ); //api.w.org relation link
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
		remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );

		add_filter( 'w3tc_can_print_comment', '__return_false', 10, 1 ); // Disable W3TC footer comment for all users

		add_filter( 'emoji_svg_url', '__return_false' ); // remove dns-prefetch s.w.org and emoji support

		function ps_wp_remove_x_pingback( $headers ){
			unset( $headers['X-Pingback'] );
			return $headers;
		}
		add_filter( 'xmlrpc_enabled', '__return_false' ); //XMLRPC
		add_filter( 'wp_headers', 'ps_wp_remove_x_pingback' ); //XMLRPC

		function ps_wp_remove_version() {
			return '';
		}
		add_filter('the_generator', 'ps_remove_wp_version'); //version number

		function ps_wp_cleanup_query_string( $src ){ 
			$parts = explode( '?', $src ); 
			return $parts[0]; 
		} 
		add_filter( 'script_loader_src', 'ps_wp_cleanup_query_string', 15, 1 ); //query string from static ressource, e.g. xxx.css?ver=1.1.1 
		add_filter( 'style_loader_src', 'ps_wp_cleanup_query_string', 15, 1 );
	}
}

?>