<?php
/*
 * GilidPanel Admin Settings
 */

class FORTYFOURWP_API_Settings {

	/*
	 * For easier overriding we declared the keys
	 * here as well as our tabs array which is populated
	 * when registering settings
	 */
	private $general_settings_key = 'fortyfourwp_general';
	private $stat_settings_key = 'fortyfourwp_logs';
	private $upgrade_settings_key = 'fortyfourwp_upgrade';
	private $plugin_options_key = 'fortyfourwp_opts';
	private $plugin_settings_tabs = array();


	/*
	 * Fired during plugins_loaded (very very early),
	 * so don't miss-use this, only actions and filters,
	 * current ones speak for themselves.
	 */
	function __construct() {
		add_action( 'init', array( &$this, 'load_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_stat_settings' ) );
		add_action( 'admin_init', array( &$this, 'register_upgrade_settings' ) );
		add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );
		add_action( 'admin_init' , array( &$this,'on_load_page' ) );
		//add filter for WordPress 2.8 changed backend box system !
		add_filter( 'screen_layout_columns', array( &$this, 'on_screen_layout_columns' ), 10, 2 );
	}

	function on_load_page(){

		if( isset( $_GET['page'] ) && $_GET['page'] == 'fortyfourwp_opts' ){
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
			wp_enqueue_media();
			add_thickbox();

		
			wp_enqueue_script(
				'fortyfourwp-admin',
				plugins_url('../lib/js/jquery-admin-settings.js', __FILE__ ),
				array( 'jquery','wp-color-picker' ),
				'',
				true
			);
		}

		wp_localize_script( 'fortyfourwp-admin', 'fortyfourwp_vars', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		wp_enqueue_style( 'wp-color-picker' );

		wp_register_style(
			'fortyfourwp-settings',
	    	plugins_url('../lib/css/settings.css', __FILE__ ),
	    	array(),
	    	'20130524',
	    	'all'
	    );
    	wp_enqueue_style( 'fortyfourwp-settings' );

		$this->current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;

		//add metaboxes
		if( isset( $this->pagehook ) ){
			add_meta_box( 'fortyfourwp-appearance-metabox', __( 'Appearance','forty-four' ), array(&$this, 'appearance_metabox' ),$this->pagehook .'_'. $this->general_settings_key, 'normal', 'core' );
			add_meta_box( 'fortyfourwp-publish-side-metabox', __( 'Publish','forty-four' ), array( &$this, 'publish_side_metabox' ), $this->pagehook .'_'. $this->general_settings_key, 'side', 'core' );
			add_meta_box( 'fortyfourwp-background-image-metabox', __( 'Background Image','forty-four' ), array( &$this, 'background_image_metabox' ), $this->pagehook .'_'. $this->general_settings_key, 'side', 'default' );
		}
	}

	//for WordPress 2.8 we have to tell, that we support 2 columns !
	function on_screen_layout_columns( $columns, $screen ) {
		if ( $screen == $this->pagehook ) {
			$columns[$this->pagehook] = $this->screen_layout_columns = 2;
		}

		return $columns;
	}

	/*
	 * Loads both the general and advanced settings from
	 * the database into their respective arrays. Uses
	 * array_merge to merge with default values if they're
	 * missing.
	 */
	function load_settings() {
		$this->general_settings = (array) get_option( $this->general_settings_key );
		$this->stat_settings 	= (array) get_option( $this->stat_settings_key );
		$this->upgrade_settings = (array) get_option( $this->upgrade_settings_key );
	}

	/*
	 * Registers the general settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function register_general_settings() {
		$this->plugin_settings_tabs[ $this->general_settings_key ] = __( 'General', 'forty-four' );

		register_setting( $this->general_settings_key, $this->general_settings_key );
		add_settings_section( 'general_section', __( 'General Options', 'forty-four' ), array( &$this, 'general_options_section' ), $this->general_settings_key );
	}

	function general_options_section(){
		$content = ( isset( $this->general_settings['content'] ) ) ? $this->general_settings['content'] : '';
		?>
		<div id="poststuff" class="fortyfourwp-metabox-holder metabox-holder<?php echo 2 == $this->screen_layout_columns ? ' has-right-sidebar' : ''; ?>">
			<div id="side-info-column" class="inner-sidebar">
				<?php do_meta_boxes( $this->pagehook . '_' . $this->current_tab, 'side', $this->general_settings ); ?>
				<div class="fortyfourwp-banners">
					<p><a href="https://phpbits.net/plugin/forty-four/?utm_source=sidebar-v1&utm_medium=banner&utm_campaign=forty-four" target="_blank"><img src="<?php echo plugins_url('/lib/images/banner-pro.jpg', dirname(__FILE__) )?>" /></a></p>
					<p><a href="https://wordpress.org/plugins/widget-options/" target="_blank"><img src="<?php echo plugins_url('/lib/images/banner-widget-options.jpg', dirname(__FILE__) )?>" /></a></p>
					<p><a href="https://phpbits.net/plugin/mobi/?utm_source=sidebar-v1&utm_medium=banner&utm_campaign=forty-four" target="_blank"><img src="<?php echo plugins_url('/lib/images/banner-mobi.jpg', dirname(__FILE__) )?>" /></a></p>
				</div>
			</div>
			<div id="post-body" class="has-sidebar">
				<div id="post-body-content" class="has-sidebar-content">
					<div id="titlediv">
						<div id="titlewrap">
							<input type="text" name="<?php echo $this->general_settings_key; ?>[title]" size="30" value="<?php echo ( isset( $this->general_settings['title'] ) ) ? $this->general_settings['title'] : '';?>" id="title" autocomplete="off" placeholder="Enter title here">
						</div>
					</div>
					<div id="postdivrich" class="postarea">
						<?php
							$settings = array(
							    'textarea_name' =>	$this->general_settings_key . '[content]',
							    'media_buttons' =>	false,
							    'teeny'			=>	true
							);
							wp_editor( html_entity_decode(stripcslashes($content)) , 'settings_content', $settings );
						?>
					</div><br />
					<?php do_meta_boxes( $this->pagehook . '_' . $this->current_tab, 'normal', $this->general_settings ); ?>

				</div>
			</div>
			<br class="clear"/>
		</div>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
			});
			//]]>
		</script>
	<?php }

	function publish_side_metabox( $settings_option ){
		$url = substr( sha1( rand() ), 0, 10 );
		$url = esc_url( home_url( '/' ) ) . $url;
		?>
		<div id="major-publishing-actions">
			<div id="delete-action">
			<a class="submitdelete deletion" href="<?php echo $url;?>" target="_blank"><?php _e( 'View 404 Page','forty-four' );?></a>
			</div>

			<div id="publishing-action">
				<input type="submit" name="publish" id="publish" class="button button-primary button-large" value="<?php _e( 'Publish','forty-four' );?>">
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	function background_image_metabox($settings_option){
		?>
		<div class="uploader">
		  <input type="hidden" name="<?php echo $this->general_settings_key; ?>[background_image]" id="background_image" value="<?php echo isset( $settings_option['background_image'] ) ? $settings_option['background_image'] : '';?>" />
		  <input type="hidden" name="<?php echo $this->general_settings_key; ?>[background_image_id]" id="background_image_id" value="<?php echo (isset( $settings_option['background_image_id'] )) ? $settings_option['background_image_id'] : '';?>" />
		  <div id="fortyfourwp_bg_img"><?php echo (!empty($settings_option['background_image'])) ? '<img src="'. $settings_option['background_image'] .'" style="width:100%;">' : '';?></div>
		  <a href="#" name="background_image_button" id="background_image_button" <?php echo (!empty($settings_option['background_image'])) ? 'style="display:none;"': ''?>/><?php _e( 'Set Background Image','forty-four');?></a>
		  <a href="#" id="background_image_remove" <?php echo (!empty($settings_option['background_image'])) ? 'style="display:block;"': ''?>/><?php _e( 'Remove Background Image','forty-four');?></a>
		</div>
		<?php
	}

	function appearance_metabox($settings_option){
		?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-hidebg"><?php _e( 'Hide Background Image', 'forty-four' )?></label>
					</th>
					<td>
						<input type="checkbox" value="1" id="fortyfourwp-hidebg" name="<?php echo $this->general_settings_key; ?>[remove_bg]" <?php echo ( isset( $settings_option['remove_bg'] ) ) ? 'checked="checked"' : '';?> /> <small><?php _e( 'Check this option if you want to hide image background and show background color instead.', 'forty-four' );?></small>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-shadow"><?php _e( 'Add Text Shadow', 'forty-four' )?></label>
					</th>
					<td>
						<input type="checkbox" value="1" id="fortyfourwp-shadow" name="<?php echo $this->general_settings_key; ?>[shadow_on]" <?php echo ( isset( $settings_option['shadow_on'] ) ) ? 'checked="checked"' : '';?> />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-bg"><?php _e( 'Text Shadow Color', 'forty-four' )?></label>
					</th>
					<td>
						<input type="text" value="<?php echo ( isset( $settings_option['shadow_color'] ) ) ? $settings_option['shadow_color'] : '';?>" id="fortyfourwp-bg" class="fortyfourwp-color-field" name="<?php echo $this->general_settings_key; ?>[shadow_color]" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-bg"><?php _e( 'Background Color', 'forty-four' )?></label>
					</th>
					<td>
						<input type="text" value="<?php echo ( isset( $settings_option['background_color'] ) ) ? $settings_option['background_color'] : '';?>" id="fortyfourwp-bg" class="fortyfourwp-color-field" data-default-color="#ffffff" name="<?php echo $this->general_settings_key; ?>[background_color]" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-text-color"><?php _e( 'Text Color', 'forty-four' )?></label>
					</th>
					<td>
						<input type="text" value="<?php echo ( isset( $settings_option['text_color'] ) ) ? $settings_option['text_color'] : '';?>" id="fortyfourwp-text-color" class="fortyfourwp-color-field" name="<?php echo $this->general_settings_key; ?>[text_color]" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-link-color"><?php _e( 'Link Color', 'forty-four' )?></label>
					</th>
					<td>
						<input type="text" value="<?php echo ( isset( $settings_option['link_color'] ) ) ? $settings_option['link_color'] : '';?>" id="fortyfourwp-link-color" class="fortyfourwp-color-field" name="<?php echo $this->general_settings_key; ?>[link_color]" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-link-color"><?php _e( 'Search Border Color', 'forty-four' )?></label>
					</th>
					<td>
						<input type="text" value="<?php echo ( isset( $settings_option['border_color'] ) ) ? $settings_option['border_color'] : '';?>" id="fortyfourwp-border-color" class="fortyfourwp-color-field" name="<?php echo $this->general_settings_key; ?>[border_color]" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-search-text"><?php _e( 'Search Box Text', 'forty-four' )?></label>
					</th>
					<td>
						<input type="text" value="<?php echo ( isset( $settings_option['search_text'] ) ) ? $settings_option['search_text'] : '';?>" id="fortyfourwp-search-text" class="widefat" name="<?php echo $this->general_settings_key; ?>[search_text]" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-home-text"><?php _e( 'Back to Home Text', 'forty-four' )?></label>
					</th>
					<td>
						<input type="text" value="<?php echo ( isset( $settings_option['home_text'] ) ) ? $settings_option['home_text'] : '';?>" id="fortyfourwp-home-text" class="widefat" name="<?php echo $this->general_settings_key; ?>[home_text]" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="fortyfourwp-hidetitle"><?php _e( 'Hide 404 Title', 'forty-four' )?></label>
					</th>
					<td>
						<input type="checkbox" value="1" id="fortyfourwp-hidetitle" name="<?php echo $this->general_settings_key; ?>[remove_title]" <?php echo ( isset( $settings_option['remove_title'] ) ) ? 'checked="checked"' : '';?> /> <small><?php _e( 'Check this option if you do not want to show the 404 Title.', 'forty-four' );?></small>
					</td>
				</tr>

			</tbody>
		</table>
		<?php
	}

	/*
	 * Registers the stat settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function register_stat_settings() {
		$this->plugin_settings_tabs[ $this->stat_settings_key ] = __( 'Logs', 'forty-four' );

		register_setting( $this->stat_settings_key, $this->stat_settings_key );
		add_settings_section( 'stat_section', __( '404 Logs', 'forty-four' ), array( &$this, 'stat_options_section' ), $this->stat_settings_key );
	}

	function stat_options_section(){
		$screen = get_current_screen();
		$list_table = new FORTYFOURWP_List_Table();
		$bulk = $list_table->process_bulk_action();
		$list_table->prepare_items();
		?>
		<div class="wrap">
			<?php echo (!empty($bulk)) ? $bulk : '';?>
			<form method="get" action="<?php echo admin_url( 'themes.php?page=fortyfourwp_opts&tab=fortyfourwp_logs' );?>">
				<input type="hidden" name="page" value="fortyfourwp_opts" />
				<input type="hidden" name="tab" value="fortyfourwp_logs" />
				<?php $list_table->search_box( __( 'Search', 'forty-four' ), 'forty-four' ); ?>
				<?php $list_table->display();?>
			</form>

		</div>
		<?php
	}

	/*
	 * Registers the upgrade settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function register_upgrade_settings() {
		$this->plugin_settings_tabs[ $this->upgrade_settings_key ] = __( 'Pro Features', 'forty-four' );

		register_setting( $this->upgrade_settings_key, $this->upgrade_settings_key );
		add_settings_section( 'upgrade_section', __( 'Upgrade to Forty Four Pro', 'forty-four' ), array( &$this, 'upgrade_options_section' ), $this->upgrade_settings_key );
	}

	function upgrade_options_section(){
		?>
		<div class="wrap fortyfourwp-upgrade-section">
			<p><strong><?php _e( 'Get a Fully Feature-Packed 404 Pages and Redirection Plugin for WordPress!', 'forty-four' );?></strong></p>
				<p><?php _e( 'Aside from the free features already available, you will get the following features. ', 'forty-four' );?></p>
	            <ul>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Branding and Custom Menu', 'forty-four' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Additional Layout', 'forty-four' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Google Fonts Typography Option', 'forty-four' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Detailed Logs', 'forty-four' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Referrer Logs', 'forty-four' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Search Keywords', 'forty-four' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Custom Scripts', 'forty-four' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'and more improvements ...', 'forty-four' );?></li>
	            </ul>
	            
	            <p><strong><a href="https://phpbits.net/plugin/forty-four/" class="widget-opts-learnmore" target="_blank"><?php _e( 'Learn More', 'forty-four' );?> <span class="dashicons dashicons-arrow-right-alt"></span></a></strong></p>
		</div>
		<?php
	}

	/*
	 * Called during admin_menu, adds an options
	 * page under Settings called My Settings, rendered
	 * using the wplftr_plugin_options_page method.
	 */
	function add_admin_menus() {
		$this->pagehook = add_theme_page( '404 Page', '404 Page', 'manage_options', $this->plugin_options_key, array( &$this, 'plugin_options_page' ) );
	}

	/*
	 * Plugin Options page rendering goes here, checks
	 * for active tab and replaces key with the related
	 * settings key. Uses the wplftr_plugin_options_tabs method
	 * to render the tabs.
	 */
	function plugin_options_page() {
		$tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : $this->general_settings_key;
		?>
		<div class="wrap">
			<?php $this->plugin_options_tabs(); ?>
			<?php if( 'fortyfourwp_logs' != $tab ): ?>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'update-options' ); ?>
				<?php settings_fields( $tab ); ?>
			<?php endif;?>
				<?php do_settings_sections( $tab ); ?>
			<?php if( 'fortyfourwp_logs' != $tab ): ?>
			</form>
			<?php endif;?>
		</div>
		<?php
	}

	/*
	 * Renders our tabs in the plugin options page,
	 * walks through the object's tabs array and prints
	 * them one by one. Provides the heading for the
	 * wplftr_plugin_options_page method.
	 */
	function plugin_options_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : $this->general_settings_key;

		screen_icon();
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		echo '</h2>';
	}
};

// Initialize the plugin
add_action( 'plugins_loaded', create_function( '', '$settings_fortyfourwp = new FORTYFOURWP_API_Settings;' ) );

?>
