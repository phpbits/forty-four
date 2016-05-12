<?php
/**
 * Add Rating Notice after 8 days
 */
if( !class_exists( 'FORTYFOURWP_notices' ) ):
class FORTYFOURWP_notices {
    public function  __construct() {
        if ( is_admin() ){
            add_action( 'admin_notices', array( &$this, 'admin_messages') );
            // if( isset( $_GET['page'] ) ){
            //     $page = sanitize_text_field( $_GET['page'] );
            //     if( 'fortyfourwp_opts' == $page ){
            //         add_action( 'admin_notices', array( &$this, 'beta_messages') );
            //     }
            // } 
        }
        add_action('wp_ajax_fortyfourwp_hiderating', array( &$this, 'hide_rating') );
    }

    /* Hide the rating div 
     * @return json string
     * 
     */
    function hide_rating(){
        update_option('fortyfourwp_RatingDiv','yes');
        echo json_encode(array("success")); exit;
    }

    /**
     * Admin Messages
     * @return void
     */
    function admin_messages() {
        if (!current_user_can('update_plugins'))
        return;
            

        $install_date   = get_option('fortyfourwp_installDate');
        $saved          = get_option('fortyfourwp_RatingDiv');
        $display_date   = date('Y-m-d h:i:s');
        $datetime1      = new DateTime($install_date);
        $datetime2      = new DateTime($display_date);
        $diff_intrval   = round(($datetime2->format('U') - $datetime1->format('U')) / (60*60*24));
        if( 'yes' != $saved && $diff_intrval >= 8 ){
        echo '<div class="fortyfourwp_notice updated" style="box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
            <p>Awesome, you\'ve been using <strong>Forty Four - 404 Plugin for WordPress</strong> for more than 1 week. <br> May i ask you to give it a <strong>5-star rating</strong> on Wordpress? </br>
            This will help to spread its popularity and to make this plugin a better one.
            <br><br>Your help is much appreciated. Thank you very much,<br> ~ Jeffrey Carandang <em>(phpbits)</em>
            <ul>
                <li><a href="https://wordpress.org/support/view/plugin-reviews/forty-four/" class="thankyou" target="_blank" title="Ok, you deserved it" style="font-weight:bold;">'. __( 'Ok, you deserved it', 'forty-four' ) .'</a></li>
                <li><a href="javascript:void(0);" class="fortyfourwp_bHideRating" title="I already did" style="font-weight:bold;">'. __( 'I already did', 'forty-four' ) .'</a></li>
                <li><a href="javascript:void(0);" class="fortyfourwp_bHideRating" title="No, not good enough" style="font-weight:bold;">'. __( 'No, not good enough, i do not like to rate it!', 'forty-four' ) .'</a></li>
            </ul>
        </div>
        <script>
        jQuery( document ).ready(function( $ ) {

        jQuery(\'.fortyfourwp_bHideRating\').click(function(){
            var data={\'action\':\'fortyfourwp_hiderating\'}
                 jQuery.ajax({
            
            url: "'. admin_url( 'admin-ajax.php' ) .'",
            type: "post",
            data: data,
            dataType: "json",
            async: !0,
            success: function(e) {
                if (e=="success") {
                   jQuery(\'.fortyfourwp_notice\').slideUp(\'slow\');
                   
                }
            }
             });
            })
        
        });
        </script>
        ';
        }
    }

    function beta_messages() {
        if (!current_user_can('update_plugins'))
        return;

        $start_ts   = strtotime( '4/12/2016' );
        $end_ts     = strtotime( '4/30/2016' );
        $user_ts    = strtotime( date('m/d/Y') );
        $show       = false;

        // Check that user date is between start & end
        if( (($user_ts >= $start_ts) && ($user_ts <= $end_ts)) ){
            $show = true;
        }
        if( !$show ) return;
        ?>
        <div class="fortyfourwp_beta notice notice-info is-dismissible" style="box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
            <p><a href="https://phpbits.net/forty-four-pro-wordpress-now-beta" target="_blank" ><strong><?php _e( 'Join Forty Four Pro Beta now!', 'forty-four' );?></strong></a> 
            <?php _e( 'Be the first to try the new upgraded features and get 75% OFF your regular license for your valuable feedback. Thank you!', 'forty-four' );?> - <a href="https://phpbits.net/forty-four-pro-wordpress-now-beta" target="_blank" ><?php _e( 'more info', 'forty-four' );?></a> </p>
        </div>
        <?php
    }
}
new FORTYFOURWP_notices();
endif;
?>