<?php

/*

add_action( 'admin_menu', array($this, 'add_menu_application_list_table_page' ));

function add_menu_application_list_table_page()
{
    add_menu_page( 'Applications', 'Applications', 'edit_pages', 'applications', array($this, 'list_application_table_page') );
}
*/

function list_application_table_page()
{
	global $wpdb;
    $applicationListTable = new Application_List_Table();
    $applicationListTable->prepare_items();
    ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Applications</h2>

            <?php $applicationListTable->display(); ?>
        </div>
    <?php
}


// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/**
 * Create a new table class that will extend the WP_List_Table
 */
class Application_List_Table extends WP_List_Table
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
			//'id'		=> 'Id',
			'doubleoptin' 		=> 'Double Opt-In',
			'shorthandle' 		=> 'Shorthandle',
			'jobtitle' 		=> 'Job Title',
			'jobcity' 		=> 'Job City',
            //'appdata'		=> 'Data',
			'email'			=> 'E-Mail',
			//'candidatekey'	=> 'Candidatekey',
            'userstate' 	=> 'State',
			'userid' 		=> 'Userid',
			
			'created' 		=> 'Created'
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
        //return array('title' => array('title', false), 'published_at' => array('published_at', false));
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
		//$records = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_candidates");
        $records = $wpdb->get_results("SELECT id,userid,doubleoptin,shorthandle,email,userstate,created FROM ".$wpdb->prefix."prescreen_candidates");
		foreach($records as $application){
			$count++;

			if($application->userid === '0'){
				$userID = '';
			} else {
				$userID = $application->userid;
			}
			
			if($application->userstate === 'exists'){
				$userState = 'Existing User';
			} else {
				$userState = 'New User';
			}
			
			if($application->doubleoptin === 'unnecessary'){
				$doubleoptin = '<div style="width: 20px; height: 20px;" title="Kein Double-Opt-In notwendig"><svg style="width: 100%; height: 100%; object-fit: contain;" height="96" width="96" viewbox="0 0 96 96" xmlns="http://www.w3.org/2000/svg"><path d="M48 4C23.7 4 4 23.699 4 48s19.7 44 44 44 44-19.699 44-44S72.3 4 48 4zm0 80c-19.882 0-36-16.118-36-36s16.118-36 36-36 36 16.118 36 36-16.118 36-36 36z"/><path d="M64.284 37.17a4.002 4.002 0 00-5.657 0L44.485 51.313l-5.657-5.657a4 4 0 10-5.657 5.658l8.484 8.483a4.002 4.002 0 005.658 0l16.97-16.97a3.998 3.998 0 00.001-5.657z"/></svg></div>';
			} else if($application->doubleoptin === 'finished'){
				$doubleoptin = '<div style="width: 20px; height: 20px;" title="Double-Opt-In via Mail abgeschlossen"><svg style="width: 100%; height: 100%; object-fit: contain;" id="Icons" version="1.1" viewBox="0 0 32 32" xml:space="preserve" xmlns="http://www.w3.org/2000/svg"><style>.st0{fill:none;stroke:#000;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10}</style><path class="st0" d="M3 8l13 8 13-8M27 22l-4 4-2-2"/><circle class="st0" cx="24" cy="24" r="7"/><path class="st0" d="M17.1 25H7c-2.2 0-4-1.8-4-4V7c0-2.2 1.8-4 4-4h18c2.2 0 4 1.8 4 4v12.1"/></svg></div>';
			} else {
				$doubleoptin = '';
			}
			
			if($application->shorthandle){
				$jobTitleCitySelect = "SELECT title,city FROM ".$wpdb->prefix."prescreen_jobs WHERE shorthandle = '".$application->shorthandle."'";
                
                if(!empty($jobTitleCitySelect) && !empty($jobTitleCitySelect[0])){
                    if(is_object($wpdb->get_results($jobTitleCitySelect)[0])){
        				$jobTitle = $wpdb->get_results($jobTitleCitySelect)[0]->title;
        
        				$jobCityID = $wpdb->get_results($jobTitleCitySelect)[0]->city;
        				$jobCitySelect = $wpdb->get_results("SELECT title FROM ".$wpdb->prefix."prescreen_jobs_cities WHERE id = ".$jobCityID."");
        				$jobCity = $jobCitySelect[0]->title;
                    }
                }
			} else {
				$jobTitle = '';
				$jobCity = '';
			}
			
			
	        $data[] = array(
				'doubleoptin'		=> $doubleoptin,
				'shorthandle'	=> $application->shorthandle,
				'jobtitle'	=> $jobTitle,
				'jobcity'	=> $jobCity,
				'email'			=> $application->email,
	            'userstate' 	=> $userState,
				'userid' 		=> $userID,
				'created'		=> date("d.m.Y", strtotime($application->created)),
                'sortcreated'   => $application->created
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
			//case 'id':
			case 'doubleoptin':
			case 'shorthandle':
			case 'jobtitle':
			case 'jobcity':
			//case 'appdata':
			case 'email':
            //case 'candidatekey':
            case 'userstate':
			case 'userid':
			
			case 'created':
			/*
			case 'status':
			case 'google_indexing':
            case 'title':
            case 'showurl':
			case 'bannerfooterurl':
			case 'department':
            case 'city':
			case 'positiontype':
			case 'instance':
			case 'team':
			case 'published_at':
			*/
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
        $orderby = 'sortcreated';
        $order = 'desc';

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


