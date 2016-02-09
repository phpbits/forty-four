<?php
/*
 * Set Display on the frontend
 */

/*
 * change template on 404.page
 */

if( !function_exists( 'fortyfourwp_setTemplate' ) ){
	add_filter( 'template_include', 'fortyfourwp_setTemplate');
	function fortyfourwp_setTemplate($page_template){
		if ( is_404() ) {
	       $page_template = dirname( __DIR__ ) . '/views/page-404.php';
	    }
	    return $page_template;
	}
}
?>
