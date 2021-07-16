<?php

function list_application_cf_table_page(){
	global $wpdb;
    $applicationCfListTable = new Application_CF_List_Table();
    $applicationCfListTable->prepare_items();
    ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Custom Fields</h2>
			<div style="display: flex; margin-top: 1em;">
				<input type="submit" class="loadcustomfields button button-primary" value="Custom Fields neu importieren" style="margin-right: 20px;">
			</div>
			<div class="ps_career_ajax_result" style="margin-top: 20px;"></div>
            <?php $applicationCfListTable->display(); ?>
        </div>
    <?php
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Application_CF_List_Table extends WP_List_Table {
    public function prepare_items(){
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

    public function get_columns(){
        $columns = array(
			'is_active' 	=> 'Is Active?',
			'cfid' 		=> 'ID',
			'type' 		=> 'Type',
			'name' 		=> 'Name',
			//'label' 	=> 'Label',
			'custom_field_values'	=> 'Custom Field Values',
            'translations' 	=> 'Translations'
        );
        return $columns;
    }

    public function get_hidden_columns(){
        return array();
    }
    public function get_sortable_columns(){

    }

    private function table_data(){
		global $wpdb;
        $data = array();
		$count = 0;
		$records = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_custom_fields");
		foreach($records as $customfield){
			// Show only Custom Fields that has not type File
			if($customfield->type != 'file'){
				$count++;
				
				if($customfield->is_active === 'true'){
					$isActive = '<div style="width: 20px; height: 20px;" title="Custom Field is active"><svg style="width: 100%; height: 100%; object-fit: contain;" height="96" width="96" viewbox="0 0 96 96" xmlns="http://www.w3.org/2000/svg"><path fill="#008000" d="M48 4C23.7 4 4 23.699 4 48s19.7 44 44 44 44-19.699 44-44S72.3 4 48 4zm0 80c-19.882 0-36-16.118-36-36s16.118-36 36-36 36 16.118 36 36-16.118 36-36 36z"/><path fill="#008000" d="M64.284 37.17a4.002 4.002 0 00-5.657 0L44.485 51.313l-5.657-5.657a4 4 0 10-5.657 5.658l8.484 8.483a4.002 4.002 0 005.658 0l16.97-16.97a3.998 3.998 0 00.001-5.657z"/></svg></div>';
				} else {
					$isActive = '<div style="width: 20px; height: 20px;" title="Custom Field is not active anymore"><svg style="width: 100%; height: 100%; object-fit: contain;" viewbox="0 0 96 96" height="96" width="96" xmlns="http://www.w3.org/2000/svg"><path fill="#ff0000" d="M48 4C23.7 4 4 23.699 4 48s19.7 44 44 44 44-19.699 44-44S72.3 4 48 4zm0 80c-19.882 0-36-16.118-36-36s16.118-36 36-36 36 16.118 36 36-16.118 36-36 36z"/><path fill="#ff0000" d="M53.657 48l8.485-8.485a4 4 0 10-5.658-5.656L48 42.343l-8.485-8.484a4 4 0 00-5.657 5.656L42.343 48l-8.485 8.485a4 4 0 105.657 5.656L48 53.657l8.484 8.484a4 4 0 105.658-5.656L53.657 48z"/></svg></div>';
				}
	
		        $data[] = array(
					'is_active' => $isActive,
					'cfid'	=> $customfield->cfid,
					'type'	=> $customfield->type,
					'name'	=> $customfield->name,
					//'label'	=> $customfield->label,
					'custom_field_values'=> $customfield->custom_field_values,
		            'translations' 	=> $customfield->translations
				);
			}
		}
		return $data;      
    }
	
    public function column_default( $item, $column_name ){
        switch( $column_name ) {
			case 'is_active':
			case 'cfid':
			case 'type':
			case 'name':
			//case 'label':
			case 'custom_field_values':
            case 'translations':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    private function sort_data( $a, $b ){
        // Set defaults
        $orderby = 'cfid';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby'])){
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order'])){
            $order = $_GET['order'];
        }


        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc'){
            return $result;
        }

        return -$result;
    }
}