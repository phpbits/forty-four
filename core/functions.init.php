<?php
/*
 * Perform Action when page is 404
 */

if( !function_exists( 'fortyfourwp_init' ) ){
	add_action('wp','fortyfourwp_init');
	function fortyfourwp_init(){
		if( is_404() && !current_user_can( 'manage_options' ) ){
			global $wpdb;
			global $fortyfourwp;

			$data = array();

			$data['referer'] = wp_get_referer();
			$data['path']    = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
			$data['ip']      = $_SERVER["REMOTE_ADDR"];

			//get redirect if available
			$a = fortyfourwp_get_data(
					array(
						'fields' 		=> array(
											'redirect_url',
											'redirect_type',
										), 
						'where' 		=> 'url', 
						'keyword' 		=> $data['path'], 
						'orderby' 		=> 'access_date',
						'order'			=> 'desc' , 
						'items'			=> 1, 
						'select_type' 	=> 'search'
					)
				);

			//save data
			$data['id'] = fortyfourwp_insert_data(
								array(
									'id'       		=> '',
									'url'      		=> $data['path'],
									'referrer' 		=> $data['referer'],
									'ip'       		=> $data['ip'],
									'user_agent'	=> (isset( $_SERVER['HTTP_USER_AGENT'] )) ? $_SERVER['HTTP_USER_AGENT'] : ''
								)
							);
			$fortyfourwp = json_encode($data);

			//redirect
			if(isset($a[0]->redirect_url) && !empty($a[0]->redirect_url)){
			    fortyfourwp_update_data($data['id'], $data = array( 'redirect_url' => $a[0]->redirect_url, 'redirect_type' => $a[0]->redirect_type ) );
			    wp_redirect( $a[0]->redirect_url, $a[0]->redirect_type );
				exit();
	        	// header('Location:'. $a[0]->redirect_url);
			}
		}
	}
}

if( !function_exists( 'fortyfourwp_title' ) ){
	add_filter( 'the_title', 'fortyfourwp_title', 10, 2 );
	add_filter( 'the_content', 'fortyfourwp_title', 10, 2 );
	function fortyfourwp_title( $title ) {

		//Return new title if called inside loop
	    if ( in_the_loop() && is_404() ){
	      $general_settings = (array) get_option( 'fortyfourwp_general' );
	      if( isset( $general_settings['title'] ) ){
	        $title = $general_settings['title'];
	      }
	    }

	    //Else return regular
	    return $title;
	}
}

if( !function_exists( 'fortyfourwp_content' ) ){
	add_filter( 'the_content', 'fortyfourwp_content', 10, 2 );
	function fortyfourwp_content( $content ) {

		//Return new content if called inside loop
	    if ( in_the_loop() && is_404() ){
	      $general_settings = (array) get_option( 'fortyfourwp_general' );
	      if( isset( $general_settings['content'] ) ){
	        $content = $general_settings['content'];
	      }
	    }

	    return $content;
	}
}

?>
