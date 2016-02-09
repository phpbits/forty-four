<?php
/*
 * Create Custom Table on the Database
 */
if( !function_exists( 'fortyfourwp_register_table' ) ){
	add_action( 'init', 'fortyfourwp_register_table', 1 );
	add_action( 'switch_blog', 'fortyfourwp_register_table' );

	function fortyfourwp_register_table(){
		global $wpdb;
	    $wpdb->fortyfour_logs = "{$wpdb->prefix}fortyfour_logs";
	}
}

if( !function_exists( 'fortyfourwp_create_tables' ) ){
	function fortyfourwp_create_tables(){
	    //Create Table Data
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		global $charset_collate;
		$version = get_option('FORTYFOURWP_VERSION');
		fortyfourwp_register_table(); // Call this manually as we may have missed the init hook

		$sql_create_table = "CREATE TABLE {$wpdb->fortyfour_logs} (
				id bigint(20) unsigned NOT NULL auto_increment,
				url varchar(999),
				referrer varchar(999),
				alternative_keyword varchar(999),
				redirect_url varchar(999),
				redirect_type varchar(5),
				ip varchar(999),
				user_agent varchar(999),
				access_date varchar(999),
				PRIMARY KEY  (id)
			) $charset_collate; ";
	 	if($version != FORTYFOURWP_VERSION){
	 		dbDelta( $sql_create_table );
	 		update_option('FORTYFOURWP_VERSION', FORTYFOURWP_VERSION);
	 	}
	 	//die();
	}
	// Create tables on plugin activation
	register_activation_hook( __FILE__, 'fortyfourwp_create_tables' );
}

if( !function_exists( 'fortyfourwp_update_db_check' ) ){
	function fortyfourwp_update_db_check() {
	    $version = get_option('FORTYFOURWP_VERSION');
	    if ($version  != FORTYFOURWP_VERSION) {
	        fortyfourwp_create_tables();
	    }
	    $settings = get_option('fortyfourwp_general');
	    if(empty($settings)){
	    	$defaults= array(
	    		'background_image'	=>	'',
	    		'title'				=>	'Sorry! Page not found!',
	    		'content'			=>	"Sorry, the page you asked for couldn't be found. If you're in denial and believe this is a conspiracy that can't possibly be true, please try using our search form.",
				'background_color'	=>	'',
				'text_color'		=>	'',
				'link_color'		=>	'',
				'search_text'		=>	'Where would you like to go?',
				'home_text' 		=>	'// Go to Homepage'
	    		);
	    	add_option('fortyfourwp_general',$defaults);
	    }

	    if( !get_option( 'fortyfourwp_installDate' ) ){
			add_option( 'fortyfourwp_installDate', date( 'Y-m-d h:i:s' ) );
		}
	}
	add_action( 'plugins_loaded', 'fortyfourwp_update_db_check' );
}
?>
