<?php
require_once __DIR__ . "/google-api-php-client/vendor/autoload.php"; // Google API

function googleIndexing($shorthandle,$action){
	$mainJobURL = get_option('PS_career_mainjoburl');

	$client = new Google_Client();
	$accessToken = json_decode(get_option('PS_career_googleindexingkey'), true);

    // service_account_file.json is the private key that you created for your service account.
    $client->setAuthConfig($accessToken);
    $client->addScope('https://www.googleapis.com/auth/indexing');
 
    // Get a Guzzle HTTP Client
    $httpClient = $client->authorize();
    $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
    //$endpoint = 'indexing.googleapis.com/batch';
	
	if($action === 'delete'){
		$type = 'URL_DELETED';
	} else {
		$type = 'URL_UPDATED';
	}
 
    // Define contents here. The structure of the content is described in the next step.
    $content = '{
      "url": "'.$mainJobURL.'?sh='.$shorthandle.'",
      "type": "'.$type.'"
    }';
 
    $response = $httpClient->post($endpoint, [ 'body' => $content ]);
    $status_code = $response->getStatusCode();
	
	return $status_code;

}

function testGoogleIndexing(){
    global $wpdb;
    
	
    $mainJobURL = get_option('PS_career_mainjoburl');
    
    $client = new Google_Client();
    $accessToken = json_decode(get_option('PS_career_googleindexingkey'), true);

    // service_account_file.json is the private key that you created for your service account.
    $client->setAuthConfig($accessToken);
    $client->addScope('https://www.googleapis.com/auth/indexing');
 
    // Get a Guzzle HTTP Client
    $httpClient = $client->authorize();
    $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';
    //$endpoint = 'indexing.googleapis.com/batch';
    
    
    $getFirstJobs = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs WHERE status = 1");
    $shorthandle = $getFirstJobs[0]->shorthandle;

 
    // Define contents here. The structure of the content is described in the next step.
    $content = '{
      "url": "'.$mainJobURL.'?sh='.$shorthandle.'",
      "type": "URL_UPDATED"
    }';
 
    $response = $httpClient->post($endpoint, [ 'body' => $content ]);
	
	//return serialize($response);
    $status_code = $response->getStatusCode();
	
    if($status_code === 200){
        return 'Status-Code: '.$status_code.' - alles ok';
    } else {
        return 'Status-Code: '.$status_code;
    }
}
?>