<?php
/*
 * All Ajax Functions
 */
if( !function_exists( 'fortyfourwp_ajax' ) ){
	add_action( 'wp_ajax_fortyfourwp_ajax', 'fortyfourwp_ajax' );
	add_action( 'wp_ajax_nopriv_fortyfourwp_ajax', 'fortyfourwp_ajax' );
	function fortyfourwp_ajax(){
		$id    			= intval( sanitize_text_field( $_REQUEST['id'] ) );
		$type  			= sanitize_text_field( $_REQUEST['type'] );
		$visits 		= array();
		$referrers 		= array();
		$keywords 		= array();
		$views 			= 0;
		$total_visits	= 1;

		if(!empty($id)){
	    ob_start(); ?>
	    <div class="fortyfourwp_tb">
	    <?php
	    switch ( $type ) {
	      case 'edit':
	        //get data from table by ID selected
	        $data = fortyfourwp_get_data(
	        			array(
	        					'fields' 	=> array( 
	        									'url', 
	        									'referrer', 
	        									'alternative_keyword', 
	        									'redirect_url', 
	        									'access_date' 
	        								), 
	        					'where' 	=> 'id', 
	        					'keyword' 	=> $id , 
	        					'items'		=> 1, 
	        				)
	        		);
			if( isset( $data[0]->url ) && !empty( $data[0]->url ) ){
				//get total visits
				$total_visits = fortyfourwp_get_data(
									array(
										'fields' 	=> 'count', 
										'where' 	=> 'url', 
										'keyword' 	=> $data[0]->url
									)
								);
				//get latest redirect url
				$redirect_latest = fortyfourwp_get_data(
										array(
											'fields' 		=> array(
																'redirect_url',
																'redirect_type'
															), 
											'where' 		=> 'url', 
											'keyword' 		=> $data[0]->url, 
											'orderby' 		=> 'access_date',
											'order'			=> 'desc' , 
											'items'			=> 1, 
											'select_type' 	=> 'search'
										)
									);
			}
	        ?>
			<br />
			<span class="fortyfourwp-total-visits"><?php _e( 'Total Visits: ', 'forty-four' ); echo '<span>'. $total_visits .'</span>';?></span>
	        <table class="form-table fortyfourwp-stats-head">
    			<tbody>
	    			<tr valign="top">
	    				<td colspan="2">
	              			<input type="text" class="widefat fortyfourwp-v-url" data-url="<?php echo $data[0]->url;?>" readonly="readonly" value="<?php echo isset( $data[0]->url ) ? esc_url( home_url( $data[0]->url ) ) : ''; ?>" />
	    				</td>
						<td class="gotolink"><a href="<?php echo isset( $data[0]->url ) ? esc_url( home_url( $data[0]->url ) ) : ''; ?>" target="_blank"><?php _e( 'Go to Link', 'forty-four' ); ?></a></td>
	    			</tr>
					<tr valign="top" class="fortyfourwp_add-redirect">
	    				<td>
	              			<input type="text" id="fortyfourwp-redirect-url" class="widefat" readonly="readonly" value="<?php echo isset( $redirect_latest[0]->redirect_url ) ? $redirect_latest[0]->redirect_url : ''; ?>" />
	              			<input type="hidden" id="fortyfourwp-redirect-id" value="<?php echo isset( $id ) ? $id : ''; ?>" />
	    				</td>
						<td class="fortyfourwp-redirect">
							<span>
								<?php if( isset( $redirect_latest[0]->redirect_type ) && !empty( $redirect_latest[0]->redirect_type ) ){
									echo '<i>'. $redirect_latest[0]->redirect_type . '</i> ' . __( 'Redirect', 'forty-four' );
								}else{
									_e( '<i>no</i> Redirect', 'forty-four' );
								}?>
							</span>
	              			<select class="fortyfourwp-redirect-type" id="fortyfourwp-redirect-type">
								<option value="301" <?php if( isset( $redirect_latest[0] ) && isset( $redirect_latest[0]->redirect_type ) && $redirect_latest[0]->redirect_type == '301' ){ echo 'selected="selected"'; }?> ><?php _e( '301 Redirect(SEO)', 'forty-four' );?></option>
								<option value="302" <?php if( isset( $redirect_latest[0] ) && isset( $redirect_latest[0]->redirect_type ) && $redirect_latest[0]->redirect_type == '302' ){ echo 'selected="selected"'; }?>><?php _e( '302 Redirect', 'forty-four' );?></option>
								<option value="307" <?php if( isset( $redirect_latest[0] ) && isset( $redirect_latest[0]->redirect_type ) && $redirect_latest[0]->redirect_type == '307' ){ echo 'selected="selected"'; }?>><?php _e( '307 Redirect', 'forty-four' );?></option>
							</select>
	    				</td>
						<td class="gotolink"><a href="#"><span class="fortyfourwp-add"><?php _e( 'Add/Edit Redirect', 'forty-four' ); ?></span><span class="fortyfourwp-save"><?php _e( 'Save Redirect', 'forty-four' ); ?></span></a></td>
	    			</tr>
    			</tbody>
    		</table>

	        <?php
	        break;

	      default:
	        # code...
	        break;
	    } ?>
	    </div>
	    <?php
	    echo ob_get_clean();
		}
	  die();
	}
}

add_action('wp_ajax_fortyfourwp_addkeyword', 'fortyfourwp_addkeyword');
add_action('wp_ajax_nopriv_fortyfourwp_addkeyword', 'fortyfourwp_addkeyword');

function fortyfourwp_addkeyword(){
	if($_REQUEST['action'] == "fortyfourwp_addkeyword" && !current_user_can( 'manage_options' ) ){
		global $wpdb;
		$keyword 	= (isset( $_REQUEST['keyword'] )) ? sanitize_text_field( $_REQUEST['keyword'] ) : '';
		$id 		= (isset( $_REQUEST['id'] )) ? sanitize_text_field( $_REQUEST['id'] ) : '';
		if( !empty( $keyword ) && !empty( $id ) ){
			echo fortyfourwp_update_data($id,
				$edited = array(
						'alternative_keyword' => $keyword
					)
			);
		}
		die();
		
	}
	
	die();
}

add_action('wp_ajax_fortyfourwp_saveredirect', 'fortyfourwp_saveredirect');

function fortyfourwp_saveredirect(){
	if($_REQUEST['action'] == "fortyfourwp_saveredirect" && isset($_REQUEST['id']) && isset($_REQUEST['redirect']) ){
		$redirect = ( !empty( $_REQUEST['redirect'] ) ) ? fortyfourwp_addhttp( sanitize_text_field( $_REQUEST['redirect'] ) ) : '';
		echo fortyfourwp_update_data( sanitize_text_field( $_REQUEST['id'] ) , $data = array("redirect_url" => $redirect , "redirect_type" => sanitize_text_field( $_REQUEST['type'] ) ), array( 'type' => 'redirect_url', 'url' => sanitize_text_field( $_REQUEST['url'] ) ) );
	}
	
	die();
}

function fortyfourwp_addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}
?>
