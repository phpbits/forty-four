<?php

/*
  Plugin Name: Forty Four - 404 Plugin for WordPress
  Plugin URI: http://codecanyon.net/item/wordpress-ultimate-404-plugin/5130642?ref=phpbits
  Description: Very Lightweight Plugin for Better 404 Page Management! Add custom 301 redirect for SEO purposes, check logs & customize the design and content.
  Author: phpbits
  Version: 1.4
  Author URI: http://phpbits.net/

  Text Domain: forty-four
 */

define('FORTYFOURWP_VERSION', '1.0');

//avoid direct calls to this file

if (!function_exists('add_action')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

/*##################################
  REQUIRE
################################## */
require_once( dirname( __FILE__ ) . '/core/functions.init.php' );
require_once( dirname( __FILE__ ) . '/core/functions.table.php' );
require_once( dirname( __FILE__ ) . '/core/functions.function.php' );
require_once( dirname( __FILE__ ) . '/core/functions.display.php' );
require_once( dirname( __FILE__ ) . '/core/functions.notices.php' );
require_once( dirname( __FILE__ ) . '/admin/functions.settings.php' );
require_once( dirname( __FILE__ ) . '/admin/functions.list-table.php' );
require_once( dirname( __FILE__ ) . '/admin/functions.visits.php' );
require_once( dirname( __FILE__ ) . '/admin/functions.ajax.php' );


/*##################################
  OPTIONS
################################## */
if( !function_exists( 'FORTYFOURWP' ) ){
  function FORTYFOURWP_OPTIONS(){
    $options= get_option('fortyfourwp-settings');
    $options= maybe_unserialize($options);
    return (object) $options;
  }
}
?>
