<?php

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class FORTYFOURWP_List_Table extends WP_List_Table {
	public $notice = '';
	function __construct(){
	    global $status, $page;

	        parent::__construct( array(
	            'singular'  => __( 'visit', 'forty-four' ),     //singular name of the listed records
	            'plural'    => __( 'visits', 'forty-four' ),   //plural name of the listed records
	            'ajax'      => false        //does this table support ajax?

	    ) );
	}


	function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'path':
			case 'referrer':
			case 'redirect':
			case 'total':
			case 'opts':
			default:
			    return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}

	function get_sortable_columns() {
		$sortable_columns = array(
		'total'  => array( 'total',false)
		);
		return $sortable_columns;
	}

	function get_columns(){
		$columns = array(
		    'cb'              => '<input type="checkbox" />',
		    'path'            => __( 'URL', 'forty-four' ),
		    // 'referrer'        => __( 'Referrer', 'forty-four' ),
		    'redirect'        => __( 'Redirect', 'forty-four' ),
		    'total'           => __( 'Total Views', 'forty-four' ),
		    'opts'            => __( 'Actions', 'forty-four' )
		);
		return $columns;
	}


	function column_path( $item ){
		$edit = add_query_arg(
	                array(
						'action'	=> 'fortyfourwp_ajax',
						'id'		=> $item->id,
						'type'		=> 'edit',
						'width' 	=>	820,
						'height'	=>	170
	                ),
	                admin_url( 'admin-ajax.php' )
	            );

	    $a = sprintf(
	      		'<a href="%1$s" class="fortyfourwp-edit thickbox"  title="'. __( '404 Options', 'forty-four' ) .'" >%2$s</a>',
				esc_url( $edit ),
				esc_url( $item->url )
	    );

		return '<strong>' . $a . '</strong>' ;
	}

	function column_referrer( $item ) {
    	return !isset( $item->referrer ) || empty( $item->referrer ) ? __( 'n/a', 'forty-four' ) : $item->referrer;
	}

	function column_redirect( $item ) {
    $a = sprintf(
			'<a href="%1$s" target="_blank">%1$s</a>',
			esc_url( $item->redirect_url )
    );

    if( !isset( $item->redirect_url ) || empty( $item->redirect_url ) ){
      return __( 'n/a', 'forty-four' );
    }

		return '<strong>' . $a . '</strong>' ;
	}

	function column_total( $item ) {
		return $item->total;
	}

	function column_opts( $item ) {
		ob_start();
		$edit = add_query_arg(
		            array(
		                'action'  => 'fortyfourwp_ajax',
		                'id'      => $item->id,
		                'type'    => 'edit',
						'width'		=>	820,
						'height'	=>	170
		            ),
		            admin_url( 'admin-ajax.php' )
		        );
		?>
		<div class="row-actions" style="left: 0px;">
			<span class="edit">
				<a href="<?php echo $edit;?>" class="fortyfourwp-edit thickbox" title="<?php _e( '404 Options', 'forty-four' );?>" data-id="<?php echo $item->id;?>"><?php _e( 'Add/Edit Redirect', 'forty-four' );?></a>
			</span>
		</div>
		<?php

		return ob_get_clean();
	}

	function get_bulk_actions() {
	  $actions = array(
	    'delete'    => __('Trash', 'forty-four')
	  );
	  return $actions;
	}

	function single_row( $tag, $level = 0 ) {
	    global $taxonomy;
	    // $tag = sanitize_term( $tag, $taxonomy );

	    static $row_class = '';

	    if(isset($_GET['fortyfourwp_status']) && 'trash' == $_GET['fortyfourwp_status']){
	    	$tag->status = '';
	    }
	    // $row_class = ( $row_class == '' ? ' class="wpul404 '. $tag->status .'"' : ' class="wpul404 '. $tag->status .'"' );

	    $this->level = $level;

	    echo '<tr' . $row_class . '>';
	    $this->single_row_columns( $tag );
	    echo '</tr>';
	}

	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display() {
		$singular   = $this->_args['singular'];
		$url        = admin_url( 'themes.php?page=fortyfourwp_opts&tab=fortyfourwp_logs' );
		$redirected = add_query_arg( array( 'fortyfourwp_status' => 'redirected' ), $url );
		$noredirect = add_query_arg( array( 'fortyfourwp_status' => 'noredirect' ), $url );
		// $trash      = add_query_arg( array( 'fortyfourwp_status' => 'trash' ), $url );
		if(isset($_GET['trashed'])){
			echo $this->notice( 'success', __( '<strong>Review Successfully moved to Trash.</strong>','forty-four' ));
		}

		$this->display_tablenav( 'top' );
	?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tbody id="the-list"<?php
				if ( $singular ) {
					echo " data-wp-lists='list:$singular'";
				} ?>>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

			<tfoot>
				<tr>
					<?php $this->print_column_headers( false ); ?>
				</tr>
			</tfoot>
		</table>
	<?php
			$this->display_tablenav( 'bottom' );
	}

	/**
	 * Process our bulk actions
	 *
	 * @since 1.2
	 */
	function process_bulk_action() {
		if( isset($_GET['page']) &&  $_GET['page'] == 'fortyfourwp_opts' && isset($_GET['tab']) &&  $_GET['tab'] == 'fortyfourwp_logs' ) {
			if(isset($_GET['visit']) && !empty($_GET['visit'])) {
				$entry_id = ( is_array( $_GET['visit'] ) ) ? $this->sanitize( $_GET['visit'] ) : array( sanitize_text_field( $_GET['visit'] ) );
			}
			if ( 'delete' === $this->current_action() && isset($_GET['visit']) && !empty($_GET['visit']) ) {
		        
		        foreach ( $entry_id as $id ) {
		            $id = absint( $id );
		            fortyfourwp_delete_data( $id, 'url' );
		        }
		        return $notices = '<div id="message" class="updated below-h2"><p>'. count($entry_id) . __( ' Logs Successfully Deleted.','wp-reviewr') .'</p></div>';
		    }
		}  
	}

	function column_cb($item) {
	    return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item->id
		);
	}

	function prepare_items() {
		$per_page = 10;

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$args = array(
			'posts_per_page' => $per_page,
			'orderby'        => 'id',
			'order'          => 'DESC',
			'offset'         => ( $this->get_pagenum() - 1 ) * $per_page );

		if ( ! empty( $_REQUEST['s'] ) )
			$args['s'] = sanitize_text_field( $_REQUEST['s'] );

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$args['orderby'] = sanitize_text_field( $_REQUEST['orderby'] );
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			if ( 'asc' == strtolower( $_REQUEST['order'] ) )
				$args['order'] = 'ASC';
			elseif ( 'desc' == strtolower( $_REQUEST['order'] ) )
				$args['order'] = 'DESC';
		}

		$this->items = FORTYFOURWP_VISITS::find( $args );

		$total_items = FORTYFOURWP_VISITS::$found_items;
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
				'total_items' 	=> $total_items,
				'total_pages' 	=> $total_pages,
				'per_page' 		=> $per_page 
				) 
			);

	}
	function notice($type, $message = '' ){
		if(!empty($message))
			return '<div id="setting-error-settings_updated" class="updated settings-error"> <p>'. $message .'</p></div>';
	}

	/**
     * A custom sanitization function that will take the incoming input, and sanitize
     * the input before handing it back to WordPress to save to the database.
     *
     * @since    1.0.0
     *
     * @param    array    $input        The address input.
     * @return   array    $new_input    The sanitized input.
     */
    function sanitize( $input ) {
        if (!is_array($input) || !count($input)) {
            return array();
        }
        // Initialize the new array that will hold the sanitize values
        $new_input = array();
        // Loop through the input and sanitize each of the values
        foreach ( $input as $key => $val ) {
            if( !is_array( $val ) ){
                $new_input[ $key ] = sanitize_text_field( $val );
            }else if( is_array( $val ) ){
                $new_input[ $key ] = $this->sanitize( $val );
            }
        }
        return $new_input;
    }
}

?>
