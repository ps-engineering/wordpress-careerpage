<?php

function list_logging_table_page(){
	global $wpdb;
    $loggingListTable = new Logging_List_Table();
    $loggingListTable->prepare_items();
    ?>
        <div class="wrap">
            <div id="icon-users" class="icon32"></div>
            <h2>Logging</h2>
            <?php $loggingListTable->display(); ?>
        </div>
    <?php
}

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Logging_List_Table extends WP_List_Table {
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
			'description' 	=> 'Description',
			'created' 		=> 'Created'
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
		$records = $wpdb->get_results("SELECT id,description,created FROM ".$wpdb->prefix."prescreen_logging");
		foreach($records as $customfield){
			// Show only Custom Fields that has not type File

				$count++;
				
	
		        $data[] = array(
					'description'	=> $customfield->description,
                    'created'		=> date("d.m.Y H:i", strtotime($customfield->created)),
                    'sortcreated'   => $customfield->created
				);

		}
		return $data;      
    }
	
    public function column_default( $item, $column_name ){
        switch( $column_name ) {
			case 'description':
			case 'created':
                return $item[ $column_name ];
            default:
                return print_r( $item, true ) ;
        }
    }

    private function sort_data( $a, $b ){
        // Set defaults
        $orderby = 'sortcreated';
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