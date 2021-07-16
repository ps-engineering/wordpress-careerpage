<?php

/*
add_action( 'admin_menu','add_menu_jobs_list_table_page');

function add_menu_jobs_list_table_page()
{
    add_menu_page( 'Jobs', 'Jobs', 'edit_pages', 'jobs', 'list_jobs_table_page' );
}
*/

function list_jobs_table_page(){
	global $wpdb;
    $exampleListTable = new Example_List_Table();
    $exampleListTable->prepare_items();
    ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Jobs</h2>
			<?php
			$lastUpdate = $wpdb->get_results("SELECT lastupdate FROM ".$wpdb->prefix."prescreen_jobs_last_update WHERE id = 1");
			?>
			<p><strong>Last Update:</strong> <?php if (count($lastUpdate)> 0){ echo date('d.m.Y - H:i:s', strtotime($lastUpdate[0]->lastupdate)); } ?></p>

			<div style="display: flex;">
				<!--<form action="<?php echo admin_url('admin-post.php'); ?>" method="post" style="margin-top: 30px;">-->
				  <!--<input type="hidden" name="action" value="my_job_update">-->
				  <input type="submit" class="loadjobs button button-primary" value="Jobs neu importieren" style="margin-right: 20px;">
				<!--</form>-->
				
				<?php if(googleIndexingIsActive()){ ?>
					  <input type="submit" class="loadgoogleindex button button-primary" value="Google Index aktualisieren">
				<?php } ?>
			</div>
			
			<div class="ps_career_ajax_result" style="margin-top: 20px;"></div>
			
            <?php $exampleListTable->display(); ?>
        </div>
    <?php
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/*
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
*/
/**
 * Create a new table class that will extend the WP_List_Table
 */
class Example_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = 25;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'status'					=> '',
			'google_indexing'			=> '',
			'title'					=> 'Title',
            //'handle'				=> 'Handle',
            //'shorthandle' 			=> 'Shorthandle',
            'showurl' 				=> 'Show URL',
			//'applyurl' 				=> 'Apply URL',
			//'applyaddress' 			=> 'Apply Address',
			//'template' 				=> 'Template',
			//'bannerurl' 			=> 'Banner URL',
			'bannerfooterurl' 		=> 'Banner Footer URL',
			'department' 			=> 'Department',
			'city' 					=> 'City',
			'positiontype' 			=> 'Position Type',
			//'seniority' 			=> 'Seniority',
			//'headcount' 			=> 'Headcount',
			'instance' 				=> 'Instance',
			'team'					=> 'Team',
			//'custom_data_fields'	=> 'Custom Data Fields',
			'published_at' 			=> 'Published at'
			//'startofwork' 			=> 'Start of Work',
			//'industry'	 			=> 'Industry'
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        return array('title' => array('title', false), 'published_at' => array('published_at', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
		global $wpdb;
        $data = array();
		$count = 0;
		$records = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs");
		foreach($records as $job){
			$count++;
			
			// Get Custom Data Fields
			//$custom_data_fields = $job->custom_data_fields;
			//echo '<pre>';
			//print_r(json_decode($custom_data_fields));
			//echo '</pre>';
			/*
			if($custom_data_fields != ''){
			$array = explode(',', $custom_data_fields); 
			$custom_data_field_array = array();
			$custom_data_field_array_clean = array();
			foreach($array as $custom_data_field) //loop over values
			{
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_custom_data_fields WHERE id = ".$custom_data_field."");
				//echo $results[0]->name;
				$custom_data_field_array[] = $results[0]->name;
				$custom_data_field_array_clean[] = '<li>'.$results[0]->name.' (ID: '.$results[0]->id.', Form Label: '.$results[0]->form_label.', Value ID: '.$results[0]->value_id.', Value: '.$results[0]->value.', Value Label: '.$results[0]->value_label.')</li>';
			}
			$custom_data_field_array = implode(",", $custom_data_field_array);
			$custom_data_field_array_clean = implode("", $custom_data_field_array_clean);
			} else {
				$custom_data_field_array = '';
				$custom_data_field_array_clean = '';
			}
			*/
			
			
			// Get Cities
			$city = $job->city;
			if($city != ''){
			$array = explode(',', $city); 
			$cities = array();
			$cities_clean = array();
			foreach($array as $city) //loop over values
			{
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_cities WHERE id = ".$city."");
				//$city = $results[0]->title;
				//print_r($results[]);
				if($results != ''){
					$cities[] = '<a href="javascript:;" class="open-city-dialog" data-title="'.$results[0]->title.'" data-id="'.$results[0]->id.'" data-country="'.$results[0]->country.'" data-countrycode="'.$results[0]->countrycode.'" data-lat="'.$results[0]->lat.'" data-lng="'.$results[0]->lng.'">'.$results[0]->title.'</a>';
					$cities_clean[] = $results[0]->title.' (ID: '.$results[0]->id.', Country: '.$results[0]->country.', CountryCode: '.$results[0]->countrycode.', Lat: '.$results[0]->lat.', Long: '.$results[0]->lng.')';
				} else {
					$cities[] = '';
					$cities_clean[] = '';
				}
			}
			$cities = implode(",", $cities);
			$cities_clean = implode(",", $cities_clean);
			} else {
				$cities = '';
				$cities_clean = '';
			}
			

			// Get Departments
			$department = $job->department;
			if($department != ''){
			$array = explode(',', $department); 
			$departments = array();
			$departments_clean = array();
			foreach($array as $department) //loop over values
			{
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_departments WHERE id = ".$department."");
				//$city = $results[0]->title;
				$departments[] = '<a href="javascript:;" class="open-department-dialog" data-title="'.$results[0]->title.'" data-id="'.$results[0]->id.'">'.$results[0]->title.'</a>';
				$departments_clean[] = $results[0]->title.' (ID: '.$results[0]->id.')';
			}
			$departments = implode(",", $departments);
			$departments_clean = implode(",", $departments_clean);
			} else {
				$departments = '';
				$departments_clean = '';
			}
			
			// Get Industries
			$industry = $job->industry;
			if($industry != ''){
			$array = explode(',', $industry); 
			$industries = array();
			$industries_clean = array();
			foreach($array as $industry) //loop over values
			{
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_industries WHERE id = ".$industry."");
				$industries[] = $results[0]->name;
				$industries_clean[] = $results[0]->name.' (ID: '.$results[0]->id.')';
			}
			$industries = implode(",", $industries);
			$industries_clean = implode(",", $industries_clean);
			} else {
				$industries = '';
				$industries_clean = '';
			}
			
			// Get Team
			$team = $job->team;
			if($team != ''){
			$array = explode(',', $team); 
			$teams = array();
			$teams_clean = array();
			foreach($array as $team) //loop over values
			{
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_teams WHERE id = ".$team."");
				$teams[] = '<a href="javascript:;" class="open-team-dialog" data-title="'.$results[0]->name.'" data-id="'.$results[0]->id.'">'.$results[0]->name.'</a>';
				$teams_clean[] = $results[0]->name.' (ID: '.$results[0]->id.')';
				//print_r($results);
			}
			$teams = implode(",", $teams);
			$teams_clean = implode(",", $teams_clean);
			} else {
				$teams = '';
				$teams_clean = '';
			}

			// Get Instances
			$instance = $job->instance;
			if($instance != ''){
			$array = explode(',', $instance); 
			$instances = array();
			$instances_clean = array();
			foreach($array as $instance) //loop over values
			{
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_instances WHERE id = ".$instance."");
				$instances[] = '<a href="javascript:;" class="open-instance-dialog" data-title="'.$results[0]->name.'" data-id="'.$results[0]->id.'" data-islegal="'.$results[0]->is_legal_entity.'" data-handle="'.$results[0]->handle.'">'.$results[0]->name.'</a>';
				$instances_clean[] = $results[0]->name.' (ID: '.$results[0]->id.', Is Legal Entity: '.$results[0]->is_legal_entity.', Handle: '.$results[0]->handle.')';
				//print_r($results);
			}
			$instances = implode(",", $instances);
			$instances_clean = implode(",", $instances_clean);
			} else {
				$instances = '';
				$instances_clean = '';
			}
			
			// Get PositionTypes
			$positiontype = $job->positiontype;
			if($positiontype != ''){
			$array = explode(',', $positiontype); 
			$positiontypes = array();
			$positiontypes_clean = array();
			foreach($array as $positiontype) //loop over values
			{
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_positiontypes WHERE id = ".$positiontype."");
				$positiontypes[] = '<a href="javascript:;" class="open-positiontype-dialog" data-title="'.$results[0]->title.'" data-id="'.$results[0]->id.'">'.$results[0]->title.'</a>';
				$positiontypes_clean[] = $results[0]->title.' (ID: '.$results[0]->id.')';
				//print_r($results);
			}
			$positiontypes = implode(",", $positiontypes);
			$positiontypes_clean = implode(",", $positiontypes_clean);
			} else {
				$positiontypes = '';
				$positiontypes_clean = '';
			}
			
			// Get Seniorities
			$seniority = $job->seniority;
			if($seniority != ''){
			$array = explode(',', $seniority); 
			$seniorities = array();
			$seniorities_clean = array();
			foreach($array as $seniority) //loop over values
			{
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_seniorities WHERE id = ".$seniority."");
				$seniorities[] = $results[0]->title;
				$seniorities_clean[] = $results[0]->title.' (ID: '.$results[0]->id.')';
				//print_r($results);
			}
			$seniorities = implode(",", $seniorities);
			$seniorities_clean = implode(",", $seniorities_clean);
			} else {
				$seniorities = '';
				$seniorities_clean = '';
			}
			
			// Get Templates
			$template = $job->template;
			if($template != ''){
			$array = explode(',', $template); 
			$templates = array();
			foreach($array as $template) //loop over values
			{
				$results = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_jobs_templates WHERE id = ".$template."");
				$templates[] = $results[0]->name;
				//print_r($results);
			}
			$templates = implode(",", $templates);
			} else {
				$templates = '';
			}
			
			//$jobstatus = $job->status;
			
			if($job->status == 1 || $job->status == 0){
				$jobstatus = '<span style="font-size: 20px;">&#10003;</span>';
			} else {
				$jobstatus = '<span style="font-size: 20px;">&#8855;</span>';
			}
			
			if(googleIndexingIsActive()){
				if($job->google_indexing === 'inactive'){
					$job_google_indexing = '<svg width="535" height="545" viewBox="0 0 535 545" xmlns="http://www.w3.org/2000/svg"><g fill="#D5D5D5" fill-rule="nonzero"><path d="M534.5 278.4c0-18.5-1.5-37.1-4.7-55.3H273.1v104.8h147c-6.1 33.8-25.7 63.7-54.4 82.7v68h87.7c51.5-47.4 81.1-117.4 81.1-200.2z"/><path d="M273.1 544.3c73.4 0 135.3-24.1 180.4-65.7l-87.7-68c-24.4 16.6-55.9 26-92.6 26-71 0-131.2-47.9-152.8-112.3H29.9v70.1c46.2 91.9 140.3 149.9 243.2 149.9z"/><path d="M120.3 324.3c-11.4-33.8-11.4-70.4 0-104.2V150H29.9c-38.6 76.9-38.6 167.5 0 244.4l90.4-70.1z"/><path d="M273.1 107.7c38.8-.6 76.3 14 104.4 40.8l77.7-77.7C406 24.6 340.7-.8 273.1 0 170.2 0 76.1 58 29.9 150l90.4 70.1c21.5-64.5 81.8-112.4 152.8-112.4z"/></g></svg>';
				} elseif($job->google_indexing === 'active'){
					$job_google_indexing = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 533.5 544.3"><path d="M533.5 278.4c0-18.5-1.5-37.1-4.7-55.3H272.1v104.8h147c-6.1 33.8-25.7 63.7-54.4 82.7v68h87.7c51.5-47.4 81.1-117.4 81.1-200.2z" fill="#4285f4"/><path d="M272.1 544.3c73.4 0 135.3-24.1 180.4-65.7l-87.7-68c-24.4 16.6-55.9 26-92.6 26-71 0-131.2-47.9-152.8-112.3H28.9v70.1c46.2 91.9 140.3 149.9 243.2 149.9z" fill="#34a853"/><path d="M119.3 324.3c-11.4-33.8-11.4-70.4 0-104.2V150H28.9c-38.6 76.9-38.6 167.5 0 244.4l90.4-70.1z" fill="#fbbc04"/><path d="M272.1 107.7c38.8-.6 76.3 14 104.4 40.8l77.7-77.7C405 24.6 339.7-.8 272.1 0 169.2 0 75.1 58 28.9 150l90.4 70.1c21.5-64.5 81.8-112.4 152.8-112.4z" fill="#ea4335"/></svg>';
				} else {
					$job_google_indexing = '';
				}
			} else {
				$job_google_indexing = '';
			}

	        $data[] = array(
				'status' => $jobstatus,
				'google_indexing' => $job_google_indexing,
            	'title'				=> '<a href="javascript:;" class="open-job-dialog" data-title="'.$job->title.'" data-handle="'.$job->handle.'" data-shorthandle="'.$job->shorthandle.'" data-showurl="'.$job->showurl.'" data-applyurl="'.$job->applyurl.'" data-applyaddress="'.$job->applyaddress.'" data-bannerurl="'.$job->bannerurl.'" data-templates="'.$templates.'" data-departments="'.$departments_clean.'" data-cities="'.$cities_clean.'" data-positiontypes="'.$positiontypes_clean.'" data-seniorities="'.$seniorities_clean.'" data-headcount="'.$job->headcount.'" data-instances="'.$instances_clean.'" data-team="'.$teams_clean.'" data-custom-data-fields="'.htmlspecialchars(json_encode($job->custom_data_fields), ENT_QUOTES).'" data-published-at="'.$job->published_at.'" data-startofwork="'.$job->startofwork.'" data-industries="'.$industries_clean.'" >'.$job->title.'</a>',
				//'handle'			=> $job->handle,
				//'shorthandle'		=> $job->shorthandle,
				'showurl'			=> '<a href="'.$job->showurl.'" target="_blank">Show Job</a>',
				//'applyurl'			=> '<a href="'.$job->applyurl.'" target="_blank">Apply</a>',
				//'applyaddress'		=> '<a href="mailto:'.$job->applyaddress.'">Apply Mail</a>',
				//'template'			=> $templates,
				//'bannerurl'			=> '<img src="'.$job->bannerurl.'" style="width: 50px; height: auto;" />',
				'bannerfooterurl'	=> '<img src="'.$job->bannerfooterurl.'" style="width: 50px; height: auto;" />',
				'department' 		=> $departments,
				'city' 				=> $cities,
				'positiontype' 		=> $positiontypes,
				//'seniority' 		=> $seniorities,
				//'headcount' 		=> $job->headcount,
				'instance' 			=> $instances,
				'team' 				=> $teams,
				//'custom_data_fields'=> $custom_data_field_array,
				'published_at' 		=> $job->published_at
				//'startofwork' 		=> $job->startofwork,
				//'industry'	 		=> $industries
			);
			
		}

        return $data;
    }
	

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
			case 'status':
			case 'google_indexing':
            case 'title':
            //case 'handle':
            //case 'shorthandle':
            case 'showurl':
            //case 'applyurl':
            //case 'applyaddress':
			//case 'template':
			//case 'bannerurl':
			case 'bannerfooterurl':
			case 'department':
            case 'city':
			case 'positiontype':
			//case 'seniority':
			//case 'headcount':
			case 'instance':
			case 'team':
			//case 'custom_data_fields':
			case 'published_at':
			//case 'startofwork':
			//case 'industry':
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'title';
        $order = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}


