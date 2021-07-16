<?php

function list_application_tags_table_page(){
	global $wpdb;
    $applicationTagsListTable = new Application_Tags_List_Table();
    $applicationTagsListTable->prepare_items();
    ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Tags</h2>
			<div style="display: flex; margin-top: 1em;">
				<input type="submit" class="loadtags button button-primary" value="Tags neu importieren" style="margin-right: 20px;">
			</div>
			<div class="ps_career_ajax_result" style="margin-top: 20px;"></div>
            <?php $applicationTagsListTable->display(); ?>
        </div>
    <?php
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Application_Tags_List_Table extends WP_List_Table {
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
			'tagid' 	=> 'ID',
			'name' 		=> 'Name'
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
		$records = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."prescreen_application_tags");
		foreach($records as $customfield){
			// Show only Custom Fields that has not type File

				$count++;
				
	
		        $data[] = array(
					'tagid'	=> $customfield->tagid,
					'name'	=> $customfield->name
				);

		}
		return $data;      
    }
	
    public function column_default( $item, $column_name ){
        switch( $column_name ) {
			case 'tagid':
			case 'name':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    private function sort_data( $a, $b ){
        // Set defaults
        $orderby = 'tagid';
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

        if($order === 'desc'){
            return $result;
        }

        return -$result;
    }
}