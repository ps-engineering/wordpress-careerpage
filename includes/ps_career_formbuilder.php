<?php
global $wpdb;
require_once __DIR__ . "/ps_career_countries.php"; // Country Arrays

$apikey = get_option('PS_api_key');
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$full_url = $protocol."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$currentLanguage = 'de';


if( isset( $_GET['email'] ) && isset( $_GET['candidatekey'] ) && $_GET['email'] != '' &&  $_GET['candidatekey'] != ''){ 

?>

	<form id="job_application_form">
		<div class="job_application_form">
				<?php
					
					$selectCandidateQuery = "SELECT * FROM ".$wpdb->prefix."prescreen_candidates WHERE email = '".$_GET['email']."' AND candidatekey = '".$_GET['candidatekey']."'";
					$selectCandidate = $wpdb->get_results($selectCandidateQuery);
					$candidatestate = $selectCandidate[0]->userstate;
					$jsonData = json_decode($selectCandidate[0]->data);
					$candidateid = intval($selectCandidate[0]->userid);
					
					$wpdb->query( 
						$wpdb->prepare( "UPDATE ".$wpdb->prefix."prescreen_candidates SET doubleoptin = %s WHERE email = '".$_GET['email']."' AND candidatekey = '".$_GET['candidatekey']."'","finished" )
					); 
						
					// New Candidate; Create New Entry
					if($candidatestate === 'new'){
						
						$requestURLCandidate = 'https://api.prescreenapp.io/v1/candidate';
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
						echo '<span style="display: none;">';
						$response = curl_exec($curl_sendjob);
						echo '</span>';
						
						

						$jsonDataClean = json_decode( json_encode($jsonData), true);
						
						$callback = get_object_vars(json_decode($response));
							

						// Uploads not empty -> Check User Id and upload Files
						
						if (!empty($jsonDataClean['job_applications']['recruiterFiles'])) {
							
							$jsonDataUploads = array();
							$callback = get_object_vars(json_decode($response));
							
							$appId = $callback['job_applications'][0]->id;
	

							if(isset($appId) && $appId != ''){
	
								$i = 0;
								foreach ($jsonDataClean['job_applications']['recruiterFiles'] as &$file) {
									$jsonDataUploads['recruiter_files'][$i]['filename'] =  $file['filename'];
									$jsonDataUploads['recruiter_files'][$i]['source'] = 'candidate';
									$jsonDataUploads['recruiter_files'][$i]['file_type'] =  $file['file_type'];
									$jsonDataUploads['recruiter_files'][$i]['base64_content'] =  $file['base64_content'];
									$i++;
								}
								
								//print_r($jsonDataUploads);
								
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
						
	
						
					// "Existing" Candidate; Update Entry
					} else {
						
						//print_r($candidateid);
	
						// Update Candidate
						$requestURLCandidate = 'https://api.prescreenapp.io/v1/candidate/'.$candidateid.'';
						$curl_sendjob = curl_init($requestURLCandidate);
						unset($jsonData->{"email"});
	
						$jsonDataEncoded = json_encode($jsonData);
						curl_setopt($curl_sendjob, CURLOPT_POST, 1);
						curl_setopt($curl_sendjob, CURLOPT_POSTFIELDS, $jsonDataEncoded);
						curl_setopt($curl_sendjob, CURLOPT_CUSTOMREQUEST, 'PATCH');
						curl_setopt($curl_sendjob, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl_sendjob, CURLOPT_HTTPHEADER,
						    array(
								'Content-Type: application/json',
						        'apikey: '.$apikey.'',
						    )
						);
						//$result['status'] = 'success';
						//$result['message'] = 'Vielen Dank für Ihre Bewerbung.';
						echo '<span style="display: none;">';
						$response = curl_exec($curl_sendjob);
						echo '</span>';
						
						//print_r($response);

						$jsonDataClean = json_decode( json_encode($jsonData), true);
						
						$callback = get_object_vars(json_decode($response));
							

						// Uploads not empty -> Check User Id and upload Files
						
						if (!empty($jsonDataClean['job_applications']['recruiterFiles'])) {
							
							$jsonDataUploads = array();
							$callback = get_object_vars(json_decode($response));
							
							$appId = $callback['job_applications'][0]->id;
	

							if(isset($appId) && $appId != ''){
	
								$i = 0;
								foreach ($jsonDataClean['job_applications']['recruiterFiles'] as &$file) {
									$jsonDataUploads['recruiter_files'][$i]['filename'] =  $file['filename'];
									$jsonDataUploads['recruiter_files'][$i]['source'] = 'candidate';
									$jsonDataUploads['recruiter_files'][$i]['file_type'] =  $file['file_type'];
									$jsonDataUploads['recruiter_files'][$i]['base64_content'] =  $file['base64_content'];
									$i++;
								}
								
								//print_r($jsonDataUploads);
								
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
						
						
						
						// Insert Application
	
						$jobId = $jsonData->job_id;
	
						
						$sendApplication = 'https://api.prescreenapp.io/v1/application';
						$curl_sendapplication = curl_init($sendApplication);
						$jsonDataApplication = array(
						    'job_id' => intval($jobId),
							'candidate_id' => intval($candidateid),
							'is_finished' => true
						);
						$jsonDataEncodedApplication = json_encode($jsonDataApplication);
						curl_setopt($curl_sendapplication, CURLOPT_POST, 1);
						curl_setopt($curl_sendapplication, CURLOPT_POSTFIELDS, $jsonDataEncodedApplication);
						curl_setopt($curl_sendjob, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($curl_sendapplication, CURLOPT_HTTPHEADER,
						    array(
								'Content-Type: application/json',
						        'apikey: '.$apikey.'',
						    )
						);
						echo '<span style="display: none;">';
						$response = curl_exec($curl_sendapplication);
						echo '</span>';
						
					}
					
	
					//print_r($selectCandidate[0]->data);
				?>
			<div class="wpcf7-response-output wpcf7-mail-sent-ok">
				<?php echo get_option('PS_job_applications_success_message'); ?>
				<?php if(get_field('redirect_url_after_form_submit') && get_field('redirect_after_seconds')){ ?>
				<br /><br />Du wirst in <?php echo get_field('redirect_after_seconds'); ?> weitergeleitet.
				<script type="text/javascript">
					setTimeout(function(){
						window.location.href = '<?php echo get_field('redirect_url_after_form_submit'); ?>';
					}, <?php echo get_field('redirect_after_seconds'); ?>000);				
				</script>
				<?php } ?>
			</div>
		</div>	
	</form>
	
<?php } else { ?>

	<?php 
	//Login with Linked In
	if( isset( $_GET['loginwithlinkedin'] ) || isset( $_GET['code'] )){
		echo do_shortcode('[login_with_linkedin]');
	} 
	if( isset( $_GET['loginwithxing'] ) || isset( $_GET['oauth_token'] )){
		echo do_shortcode('[login_with_xing]');
	} 
	?>

	<?php if(get_field('job_application_content_above_form')){ ?>
	<div class="job_application_form__column halign--left col__9--12 column">
		<div class="job_application_content_above_form">
		<?php the_field('job_application_content_above_form'); ?>
		</div>
	</div>
	<?php } ?>
	
	<?php if(get_field('show_xing_login_button') || get_field('show_linkedin_login_button')){ ?>
	<div class="job_application_form__import-buttons job_application_form__column halign--left col__9--12 column">
		<?php if(get_field('show_xing_login_button')){ ?>
		<a href="<?php echo currentUrl($_SERVER); ?>&loginwithxing=true" class="job__application__import-button" id="jobAppImportXing"><span>Importieren aus XING</span><svg xmlns="http://www.w3.org/2000/svg" width="59.111" height="23.24" viewBox="0 0 59.111 23.24"><g data-name="Gruppe 7348"><path data-name="Pfad 9259" d="M5.976 13.043l2.4-4.474c.114-.228.217-.389.509-.389h2.55a.255.255 0 01.276.26.35.35 0 01-.039.156L7.93 15.411a.014.014 0 000 .008.015.015 0 000 .007l3.951 7.15a.351.351 0 01.038.156.255.255 0 01-.275.26H9.093c-.292 0-.395-.161-.508-.388l-2.614-4.809a.016.016 0 00-.014-.008.016.016 0 00-.014.008L3.33 22.604c-.114.228-.217.389-.509.389H.271a.265.265 0 01-.237-.119.3.3 0 010-.3l3.951-7.15a.015.015 0 000-.015L.243 8.594a.3.3 0 010-.3.265.265 0 01.237-.119h2.551c.314 0 .413.2.508.388l2.4 4.475a.016.016 0 00.028 0z" fill="#fff"/><path data-name="Pfad 9260" d="M25.826 17.044a.015.015 0 00.012-.015V8.562a.369.369 0 01.382-.381h2.028a.369.369 0 01.381.381v14.049a.369.369 0 01-.381.381h-2.112a.577.577 0 01-.509-.388l-4.39-8.467a.015.015 0 00-.018-.008.016.016 0 00-.012.015v8.467a.369.369 0 01-.382.381h-2.024a.369.369 0 01-.382-.381V8.561a.369.369 0 01.382-.381h2.111c.314 0 .413.2.509.388l4.39 8.467a.015.015 0 00.015.009z" fill="#fff"/><path data-name="Rechteck 4874" d="M14.749 8.18a1.4 1.4 0 011.4 1.4v12.017a1.4 1.4 0 01-1.4 1.4 1.394 1.394 0 01-1.4-1.399V9.58a1.4 1.4 0 011.4-1.4z" fill="#fff"/><g data-name="Gruppe 7346"><path data-name="Pfad 9261" d="M37.019 10.574a2.97 2.97 0 00-2.693 1.2 5.993 5.993 0 00-1.029 3.735 7.381 7.381 0 00.614 3.752 2.847 2.847 0 002.5 1.389 6.919 6.919 0 002.136-.327.016.016 0 00.01-.015V17.18a.015.015 0 00-.015-.016h-2.115a.37.37 0 01-.382-.382v-1.82a.37.37 0 01.382-.382h4.543a.37.37 0 01.382.382v7.13a12.452 12.452 0 01-5.094 1.145 5.12 5.12 0 01-4.459-2.045c-1.045-1.37-1.412-3.6-1.412-5.682a10.128 10.128 0 01.706-4.007 5.763 5.763 0 012.321-2.71 6.854 6.854 0 013.446-.808h.338a7.762 7.762 0 013.347.626c.294.148.389.259.389.456v1.748a.339.339 0 01-.113.275.3.3 0 01-.286.045 10.2 10.2 0 00-3.283-.565z" fill="#fff"/></g><g data-name="Gruppe 7347"><path data-name="Pfad 9262" d="M56.437 0a.674.674 0 00-.616.438L50.705 9.51l3.267 5.993a.718.718 0 00.642.438h2.3a.341.341 0 00.305-.147.361.361 0 000-.355l-3.242-5.922a.015.015 0 010-.015l5.091-9a.361.361 0 000-.355.341.341 0 00-.306-.147z" fill="#b0d400"/><path data-name="Pfad 9263" d="M46.777 3.146a.341.341 0 00-.305.147.361.361 0 000 .355l1.563 2.683a.018.018 0 010 .016l-2.449 4.314a.361.361 0 000 .355.341.341 0 00.305.147h2.306a.682.682 0 00.616-.438l2.491-4.386-1.587-2.755a.719.719 0 00-.643-.439z" fill="#fff"/></g></g></svg></a>
		<?php } ?>
		<?php if(get_field('show_linkedin_login_button')){ ?>
	
		<a href="<?php echo currentUrl($_SERVER); ?>&loginwithlinkedin=true" class="job__application__import-button" id="jobAppImportLinkedIn"><span>Importieren aus LinkedIn</span><svg xmlns="http://www.w3.org/2000/svg" width="75.386" height="19.968" viewBox="0 0 75.386 19.968"><g data-name="Gruppe 7350" fill="#fff"><path d="M0 16.726h8.281v-2.719H2.997V3.635h-3z" fill-rule="evenodd"/><path d="M12.473 16.726V7.709h-3v9.016zm-1.5-10.247a1.67 1.67 0 10-.019 0z" fill-rule="evenodd"/><path d="M13.864 16.726h3v-5.035a2.048 2.048 0 01.1-.731 1.641 1.641 0 011.537-1.1c1.084 0 1.518.827 1.518 2.038v4.824h3v-5.17c0-2.77-1.479-4.058-3.451-4.058a2.985 2.985 0 00-2.721 1.519h.02V7.71h-3c.039.846 0 9.016 0 9.016z" fill-rule="evenodd"/><path d="M27.24 3.635h-3v13.091h3v-2.924l.749-.943 2.346 3.866h3.687l-3.943-5.6 3.45-3.808h-3.608s-2.465 3.407-2.681 3.811v-7.5z" fill-rule="evenodd"/><path d="M42.037 12.965a7.343 7.343 0 00.1-1.178c0-2.331-1.183-4.7-4.3-4.7a4.773 4.773 0 00-4.87 5.025c0 2.956 1.873 4.8 5.146 4.8a9.316 9.316 0 003.49-.6l-.394-1.979a8.154 8.154 0 01-2.661.4c-1.4 0-2.622-.575-2.721-1.8l6.211.019zm-6.23-2.027a1.956 1.956 0 011.873-1.9 1.716 1.716 0 011.676 1.9z" fill-rule="evenodd"/><path d="M49.533 3.635V8.17h-.039a3 3 0 00-2.543-1.057c-2.307 0-4.337 1.846-4.318 5 0 2.924 1.834 4.828 4.121 4.828a3.388 3.388 0 003.016-1.578h.059l.118 1.366h2.662a49.5 49.5 0 01-.079-2.808V3.635zm0 8.917a3.352 3.352 0 01-.059.654 1.765 1.765 0 01-1.755 1.4c-1.242 0-2.051-1-2.051-2.577 0-1.481.69-2.673 2.07-2.673a1.772 1.772 0 011.755 1.423 2.493 2.493 0 01.039.539v1.231z" fill-rule="evenodd"/><g data-name="Gruppe 7349"><path data-name="Pfad 9264" d="M66.214 9.007v-.031l-.02.031z"/><path data-name="Pfad 9265" d="M73.92 0H57.01a1.449 1.449 0 00-1.466 1.43v17.107a1.449 1.449 0 001.466 1.43h16.909a1.449 1.449 0 001.466-1.43V1.43A1.449 1.449 0 0073.92 0zM61.561 16.717h-3V7.699h3zm-1.5-10.249h-.02a1.712 1.712 0 11.02 0zm12.3 10.249h-3v-4.825c0-1.212-.434-2.039-1.518-2.039a1.641 1.641 0 00-1.538 1.1 2.057 2.057 0 00-.1.731v5.033h-3s.039-8.171 0-9.016h3v1.277a2.975 2.975 0 012.7-1.489c1.972 0 3.45 1.289 3.45 4.058z"/></g></g></svg></a>
		<?php } ?>
	</div>
	<?php } ?>
	<div class="job_application_form__column halign--left col__9--12 column">
	<form id="job_application_form">
		<div class="job_application_form">
		
		<?php 
		if(get_field('job_application_form_send_email')){
			$send_email = true;
		} else {
			$send_email = false;
		}
		if( isset( $_COOKIE['job_sh'] )) $shortHandle = $_COOKIE['job_sh'];
		if( isset( $_GET['sh'] )) $shortHandle = $_GET['sh'];
		
		
		$jaTagsArray = array(); 
		$jaTags = get_field('job_application_tags');
		if( $jaTags ):
		foreach( $jaTags as $jaTag ):
			array_push($jaTagsArray, $jaTag['value']);
		endforeach;
		endif;
		?>

		<input type="hidden" name="shorthandle" id="shorthandle" value="<?php echo $shortHandle; ?>" />
		<input type="hidden" name="language" id="language" value="de" />
		<input type="hidden" name="send_email" id="send_email" value="<?php echo $send_email; ?>" />
		<input type="hidden" name="application_url" id="application_url" value="<?php echo $full_url; ?>" />
		<input type="hidden" name="source_id_default" id="source_id_default" value="<?php echo get_field('job_default_application_source')['value']; ?>" />
		<input type="hidden" name="candidate_tag_ids" id="candidate_tag_ids" value='<?php echo json_encode($jaTagsArray,JSON_FORCE_OBJECT); ?>' />
		<input type="hidden" name="redirect_url_after_form_submit" id="redirect_url_after_form_submit" value="<?php if(get_field('redirect_url_after_form_submit')){ echo get_field('redirect_url_after_form_submit'); } ?>" />
		<input type="hidden" name="redirect_after_seconds" id="redirect_after_seconds" value="<?php if(get_field('redirect_url_after_form_submit')){ echo get_field('redirect_after_seconds'); } ?>" />
		
		<?php 
		if(get_field('job_application_form_builder_repeater')):
		while(has_sub_field('job_application_form_builder_repeater')):
		?>
			<div class="<?php the_sub_field('column_width'); ?> jaf__single-column <?php echo get_sub_field('type')['value']; ?>">
				<?php 
				// Setup Label/Placeholder
				if(get_sub_field('label')){
					$label = get_sub_field('label');
				} else {
					$label = get_sub_field('type')['label'];
				}
				// Setup Required
				$required = '';
				if( get_sub_field('type')['value'] === 'firstname' || get_sub_field('type')['value'] === 'lastname' ){
					$required = 'required';
				} else {
					if(get_sub_field('required')){
						$required = 'required';
					}
				}
				?>
				

				<?php if(get_sub_field('type')['value'] === 'spacer'){ ?>
					<div class="jaf__separator">
						<?php if(get_sub_field('label')){ ?>
						<h3><?php the_sub_field('label'); ?></h3>
						<?php } ?>
					</div>
				<?php } else if(get_sub_field('type')['value'] === 'description'){ ?>
					<div class="jaf__separator">
						<?php if(get_sub_field('label')){ ?>
						<p><?php the_sub_field('label'); ?></p>
						<?php } ?>
					</div>
				<?php } else if(get_sub_field('type')['value'] === 'source_id'){ ?>
					<select name="<?php echo get_sub_field('type')['value']; ?>" <?php echo $required; ?>>
						<option value="" disabled selected hidden><?php echo $label; ?></option>
						<?php
						$applicationSourceQuery = "SELECT * FROM ".$wpdb->prefix."prescreen_application_source WHERE language = '".$currentLanguage."'";
						$applicationSource = $wpdb->get_results($applicationSourceQuery);
						foreach ($applicationSource as $singleApplicationSource) { 
							?>
							<option value="<?php echo $singleApplicationSource->sourceid; ?>"><?php echo $singleApplicationSource->title; ?></option>
							<?php
						}
						?>
					</select>
				<?php } else if(get_sub_field('type')['value'] === 'email'){ ?>
					<input name="<?php echo get_sub_field('type')['value']; ?>" type="email" placeholder="<?php echo $label; ?>" value="" required />
				<?php } else if(get_sub_field('type')['value'] === 'birthday'){ ?>
					<input name="<?php echo get_sub_field('type')['value']; ?>" type="date" placeholder="<?php echo $label; ?>" value="" <?php echo $required; ?> />
				<?php } else if(get_sub_field('type')['value'] === 'gender'){ ?>
					<select name="<?php echo get_sub_field('type')['value']; ?>" <?php echo $required; ?>>
						<option value="m">Herr</option>
						<option value="f">Frau</option>
						<option value="x">Divers</option>
					</select>
				<?php } else if(get_sub_field('type')['value'] === 'zip_code'){ ?>
					<input name="<?php echo get_sub_field('type')['value']; ?>" type="number" placeholder="<?php echo $label; ?>" value="" <?php echo $required; ?> />
				<?php } else if(get_sub_field('type')['value'] === 'country_of_residence'){ ?>
					<select name="<?php echo get_sub_field('type')['value']; ?>" <?php echo $required; ?>>
						<option value="" disabled selected hidden><?php echo $label; ?></option>
						<?php
						foreach ($countries_de as &$country) {
						    echo '<option value="'.$country['alpha2'].'">'.$country['name'].'</option>';
						}
						?>
					</select>
				<?php } else if(get_sub_field('type')['value'] === 'nationality'){ ?>
					<select name="<?php echo get_sub_field('type')['value']; ?>" <?php echo $required; ?>>
						<option value="" disabled selected hidden><?php echo $label; ?></option>
						<?php
						foreach ($countries_de as &$country) {
						    echo '<option value="'.$country['alpha2'].'">'.$country['name'].'</option>';
						}
						?>
					</select>
				<?php } else if(get_sub_field('type')['value'] === 'phone'){ ?>
					<input name="<?php echo get_sub_field('type')['value']; ?>" type="tel" placeholder="<?php the_sub_field('label'); ?>" value="" <?php echo $required; ?> />
				<?php } else if(get_sub_field('type')['value'] === 'has_extended_data_persistence'){ ?>
					<div style="width: 100%; min-height: 3em; text-align: left;">
					<span class="wpcf7-list-item">
						<label>
						<input type="checkbox" name="<?php echo get_sub_field('type')['value']; ?>" value="" <?php echo $required; ?> aria-invalid="false">
						<span class="wpcf7-list-item-label"><?php echo do_shortcode(get_sub_field('label')); ?></span>
						</label>
					</span>
					</div>	
				<?php } else if(get_sub_field('type')['value'] === 'data_privacy'){ ?>
					<div style="width: 100%; min-height: 3em; text-align: left;">
					<span class="wpcf7-list-item">
						<label>
						<input type="checkbox" name="<?php echo get_sub_field('type')['value']; ?>" value="" <?php echo $required; ?> aria-invalid="false">
						<span class="wpcf7-list-item-label"><?php the_sub_field('label'); ?></span>
						</label>
					</span>
					</div>	
				<?php } else if(get_sub_field('type')['value'] === 'cv_file'){ ?>
					<div class="file__upload__wrapper">
						<?php the_sub_field('label'); ?>
						<div class="file__upload__inner">
							<label for="<?php echo get_sub_field('type')['value']; ?>" class="file_<?php echo get_sub_field('type')['value']; ?>">
								<div class="file_names" data-originaltitle="Datei per Drag & Drop hochladen">
									Datei per Drag & Drop hochladen
								</div>
								<span class="upload_button">Datei auswählen</span>
							</label>
							<input id="<?php echo get_sub_field('type')['value']; ?>" name="<?php echo get_sub_field('type')['value']; ?>" type="file" placeholder="<?php the_sub_field('label'); ?>" value="" <?php echo $required; ?> />
						</div>
					</div>
				<?php } else if(get_sub_field('type')['value'] === 'avatar'){ ?>
					<div class="file__upload__wrapper">
						<?php the_sub_field('label'); ?>
						<div class="file__upload__inner">
							<label for="<?php echo get_sub_field('type')['value']; ?>" class="file_<?php echo get_sub_field('type')['value']; ?>">
								<div class="file_names" data-originaltitle="Datei per Drag & Drop hochladen">
									Datei per Drag & Drop hochladen
								</div>
								<span class="upload_button">Datei auswählen</span>
							</label>
							<input id="<?php echo get_sub_field('type')['value']; ?>" name="<?php echo get_sub_field('type')['value']; ?>" type="file" placeholder="<?php the_sub_field('label'); ?>" value="" <?php echo $required; ?> />
						</div>
					</div>
				<?php } else if(get_sub_field('type')['value'] === 'job_applications'){ ?>
					<div class="file__upload__wrapper">
						<?php the_sub_field('label'); ?>
						<div class="file__upload__inner">
							<label for="<?php echo get_sub_field('type')['value']; ?>" class="file_<?php echo get_sub_field('type')['value']; ?>">
								<div class="file_names" data-originaltitle="Dateien per Drag & Drop hochladen">
									Dateien per Drag & Drop hochladen
								</div>
								<span class="upload_button">Dateien auswählen</span>
							</label>
							<input id="<?php echo get_sub_field('type')['value']; ?>" name="<?php echo get_sub_field('type')['value']; ?>" type="file" multiple="multiple" placeholder="<?php the_sub_field('label'); ?>" value="" <?php echo $required; ?> />
						</div>
					</div>
				<?php } else if(get_sub_field('type')['value'] === 'motivational_letter'){ ?>
					<textarea name="<?php echo get_sub_field('type')['value']; ?>" placeholder="<?php the_sub_field('label'); ?>"></textarea>
				<?php } else if(get_sub_field('type')['value'] === 'custom_field'){ ?>
					<?php 
					$customFieldId = get_sub_field('job_application_form_custom_field')['value'];
					$customFieldId = substr($customFieldId, 0, strpos($customFieldId, "_"));
					$customFieldData = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_custom_fields WHERE cfid = '".$customFieldId."' AND is_active = 'true'");
					
					?>
					
					<?php // Check if Custom Field is active
					if(!empty($customFieldData)){ ?>
					
						<?php
						$customFieldType = $customFieldData[0]->type;
						$customFieldId = $customFieldData[0]->cfid;
						
						$customFieldValues = json_decode($customFieldData[0]->custom_field_values);
	
						?>
						<?php if($customFieldType === 'number'){ ?>
							<input data-field="custom_field" name="<?php echo $customFieldId; ?>" type="number" value="" placeholder="<?php the_sub_field('label'); ?>" <?php echo $required; ?> />
							
						<?php } else if($customFieldType === 'date'){ ?>
							<input data-field="custom_field" name="<?php echo $customFieldId; ?>" type="date" value="" placeholder="<?php the_sub_field('label'); ?>" <?php echo $required; ?> />
						<?php } else if($customFieldType === 'single_choice'){ ?>
							

							<?php
							if($currentLanguage === 'de'){
								$translationIdentifier = 0;
							}
							$sub_field_type = get_sub_field('custom_field_type');
							if( $sub_field_type['single_choice'] === 'dropdown' ){ 
							?>
							
								<select data-type="select" data-field="custom_field" name="<?php echo $customFieldId; ?>" <?php echo $required; ?>>
									<option value="" disabled selected hidden><?php echo $label; ?></option>
									<?php
									foreach ($customFieldValues as &$value) {
										$valueID = $value->id;
										$valueValue = $value->value;
										$valueAnswer = $value->translations[$translationIdentifier]->value_label;
										echo '<option value="'.$valueID.'">'.do_shortcode($valueAnswer).'</option>';
									}
									?>
								</select>

							<?php } elseif($sub_field_type['single_choice'] === 'radio'){ ?>
									<p><?php echo $label; ?></p>
									<div class="radio__checkbox__wrapper">
									<?php
									
									foreach ($customFieldValues as &$value) {
										$valueID = $value->id;
										$valueValue = $value->value;
										$valueAnswer = $value->translations[$translationIdentifier]->value_label;
										echo '<div class="single__radio-check"><label><input data-field="custom_field" type="radio" name="'.$customFieldId.'" value="'.$valueID.'"><span class="radio-check__label">'.do_shortcode($valueAnswer).'</span></label></div>';
									}
									?>
									</div>
							<?php } ?>
						<?php } else if($customFieldType === 'multiple_choice'){ ?>
						
							<?php
							if($currentLanguage === 'de'){
								$translationIdentifier = 0;
							}
							$sub_field_type = get_sub_field('custom_field_type');
							//print_r($sub_field_type);
							if( $sub_field_type['multiple_choice'] === 'checklist' ){ 
							?>
							
									<p><?php echo $label; ?></p>
									<div class="radio__checkbox__wrapper">
									<?php
									//print_r($customFieldValues);
									foreach ($customFieldValues as &$value) {
										$valueID = $value->id;
										$valueValue = $value->value;
										$valueAnswer = $value->translations[$translationIdentifier]->value_label;
										echo '<div class="single__radio-check"><label><input data-field="custom_field" type="checkbox" name="'.$customFieldId.'" value="'.$valueID.'"><span class="radio-check__label">'.do_shortcode($valueAnswer).'</span></label></div>';
									}
									?>
									</div>
							
							<?php } elseif($sub_field_type['multiple_choice'] === 'tag'){ ?>
							
									<p><?php echo $label; ?></p>
									
									<?php
									//print_r($customFieldValues);
									foreach ($customFieldValues as &$value) {
										$valueID = $value->id;
										$valueValue = $value->value;
										$valueAnswer = $value->translations[$translationIdentifier]->value_label;
										echo '<div class="single__radio-tag"><label><input data-field="custom_field" type="checkbox" name="'.$customFieldId.'" value="'.$valueID.'"><span class="single__tag"><span>'.do_shortcode($valueAnswer).'</span></span></label></div>';
									}
									?>
							
							<?php
							}
							?>	
						
						<?php } else { ?>
							<input data-field="custom_field" name="<?php echo $customFieldId; ?>" type="text" value="" placeholder="<?php the_sub_field('label'); ?>" <?php echo $required; ?> />
						<?php } ?>
					
					<?php } ?>
					
				<?php } else { ?>
					<input name="<?php echo get_sub_field('type')['value']; ?>" type="text" placeholder="<?php the_sub_field('label'); ?>" value="" <?php echo $required; ?> />
				<?php
					}
				
				?>
			</div>
		<?php
		endwhile;
		endif; 
		?>
		
		
		<button type="submit" style="margin-top: 2rem;" class="button button--contained">Absenden</button>
		</div>
	</form>
	</div>
	<div class="halign--left col__3--12 column">
		<?php if(get_field('job_application_content_side_box')){ ?>
		<div class="af__side-box">
			<?php the_field('job_application_content_side_box'); ?>
		</div>
		<?php } ?>
	</div>
	
	

<?php } ?>