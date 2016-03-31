<?php
if( !class_exists( 'FORTYFOURWP_VISITS' ) ){
	class FORTYFOURWP_VISITS {

		public static $found_items = 0;
		// protected $get_search_sql;

		var $initial = false;

		var $id;
		var $url;
		var $referrer;
		var $alternative_keyword;
		var $redirect_url;
		var $ip;
		var $access_date;


		public static function find( $args = '' ) {
			global $wpdb;
			$query    = "SELECT *, COUNT(url) as total FROM $wpdb->fortyfour_logs";

			$per_page = 10;
			$where 	  = '1=1';

			$orderby  = !empty($_GET["orderby"]) ? $args["orderby"] : 'id';
			$order    = !empty($_GET["order"]) ? $args["order"] : 'DESC';

			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
				$where .= fortyfourwp_search_sql(
					sanitize_text_field( $_GET['s'] ),
					array( 'url','redirect_url' )
				);
			}
			$query .= " WHERE $where";

	    	$query .= ' GROUP BY (url)';

			if(!empty($orderby) & !empty($order)){ $query.=' ORDER BY '.$orderby.' '.$order; }

			self::$found_items = $found_items = $wpdb->query($query);

			$paged = !empty($_GET["paged"]) ? sanitize_text_field( $_GET["paged"] ) : '';
	        //Page Number
	        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged=1; }
	        //How many pages do we have in total?
	        $totalpages = ceil($found_items / $per_page);
	        //adjust the query to take pagination into account
	       if(!empty($paged) && !empty($per_page)){
	         $offset=($paged-1)*$per_page;
	         $query.=' LIMIT '.(int)$offset.','.(int)$per_page;
	       }

	       // echo $query;
	       $posts = $wpdb->get_results($query);
	       // echo $query;


			$objs = array();

			foreach ( (array) $posts as $post )
				$objs[] = new self( $post );
			// print_r($objs);
			return $objs;
		}

		public function __construct( $post = null ) {
			$this->initial = true;
			if ( $post) {
				$this->initial               = false;
				$this->id                    = $post->id;
				$this->url                   = $post->url;
				$this->referrer              = $post->referrer;
				$this->alternative_keyword   = $post->alternative_keyword;

				$latest = fortyfourwp_get_data(
                    array(
                        'fields'        => array(
                                            'redirect_url',
                                        ), 
                        'where'         => 'url', 
                        'keyword'       => $post->url, 
                        'orderby'       => 'access_date',
                        'order'         => 'desc' , 
                        'items'         => 1
                    )
                );

                $this->redirect_url 	= isset( $latest[0]->redirect_url ) ? $latest[0]->redirect_url : '';

				$this->ip                    = $post->ip;
				$this->access_date           = $post->access_date;
				$this->total                 = $post->total;
				// $this->status = ('1' == $post->review_approved) ? 'approved' : 'unapproved';
			}
		}

	}
}

?>
