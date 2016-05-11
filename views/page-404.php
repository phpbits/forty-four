<?php
global $fortyfourwp;
$general_settings = (array) get_option( 'fortyfourwp_general' );
$imagedata = array();
$fortyfourwp = !empty( $fortyfourwp ) ? json_decode( $fortyfourwp ) : array();
?>
<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<!-- Add Google Font -->
<link href='https://fonts.googleapis.com/css?family=Muli:400,400italic,300italic,300' rel='stylesheet' type='text/css'>
<!-- Reset CSS -->
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'forty-four/lib/css/normalize.css' );?>" />

<!-- Stylesheets -->
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url( 'forty-four/lib/css/style.css' );?>" />
<style type="text/css">
<?php if( isset( $general_settings['background_image'] ) && !empty($general_settings['background_image']) ){ ?>
	body.error404{ background-image: url("<?php echo $general_settings['background_image'];?>"); }
<?php }
if( isset( $general_settings['remove_bg'] ) && !empty($general_settings['remove_bg']) ){ ?>
	body.error404{ background-image: none !important;}
<?php }
if( isset( $general_settings['background_color'] ) && !empty($general_settings['background_color']) ){ ?>
	body.error404{ background-color: <?php echo $general_settings['background_color'];?>;}
<?php }
if( isset( $general_settings['text_color'] ) && !empty($general_settings['text_color']) ){ ?>
	body.error404, body.error404 p{ color: <?php echo $general_settings['text_color'];?>; }
<?php }
if( isset( $general_settings['link_color'] ) && !empty($general_settings['link_color']) ){ ?>
	body.error404 a, body.error404 p a, body.error404 p.gohome a{ color: <?php echo $general_settings['link_color'];?>;}
	body.error404 p.gohome a:hover{ border-color: <?php echo $general_settings['link_color'];?>;}
<?php }
if( isset( $general_settings['border_color'] ) && !empty($general_settings['border_color']) ){ ?>
	body.error404 .search404 input[type="text"]{ border-color: <?php echo $general_settings['border_color'];?>;}
<?php }
if( isset( $general_settings['shadow_color'] ) && !empty($general_settings['shadow_color']) ){ ?>
	body.error404 .text-shadow-on, body.error404 .text-shadow-on input{ text-shadow: 1px 1px 0px <?php echo $general_settings['shadow_color'];?>;}
<?php }?>

</style>
</head>
	<body class="error404">

		<!-- ========================= CONTENT >> START ========================= -->
		<div class="container404 <?php if( isset( $general_settings['shadow_on'] ) && !empty($general_settings['shadow_on']) ){ echo 'text-shadow-on'; }?>">
			<div class="content404">
				<?php if( !isset( $general_settings['remove_title'] ) || ( isset($general_settings['remove_title']) && empty($general_settings['remove_title']) ) ){ ?>
					<h1><?php _e( '404', 'forty-four' );?></h1>
					<hr />
				<?php } ?>
				
				<?php echo ( isset( $general_settings['title'] ) ) ? '<h2>'. $general_settings['title'] .'</h2>' : '';?>
				<?php 
				if( isset( $general_settings['content'] ) ){
					$html = apply_filters( 'the_content', stripslashes( $general_settings['content'] ) );
		          	echo html_entity_decode($html);
				}
		        ?>
				<!-- search form -->
				<div class="search404">
					<form role="search" method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input type="text" name="s" id="s" <?php echo isset( $fortyfourwp->id ) ? 'data-id="'. $fortyfourwp->id .'"' : '';?>  value="<?php echo ( isset( $general_settings['search_text'] ) ) ? $general_settings['search_text'] : '';?>" onblur="if(this.value=='') this.value='<?php echo ( isset( $general_settings['search_text'] ) ) ? $general_settings['search_text'] : '';?>';" onfocus="if(this.value=='<?php echo ( isset( $general_settings['search_text'] ) ) ? $general_settings['search_text'] : '';?>') this.value='';" />
						<button type="submit" id="searchsubmit"><i class="search-icon">&#xe800;</i></button>
					</form>
				</div>
				<!-- search form end -->

				<p class="gohome"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo ( isset( $general_settings['home_text'] ) ) ? $general_settings['home_text'] : '';?></a></p>
			</div>
		</div>
		<!-- ========================= CONTENT >> END ========================= -->

		<!-- ========================= ADD SCRIPTS ON THE FOOTER SECTION ========================= -->
		<script type='text/javascript' src='<?php echo includes_url( 'js/jquery/jquery.js' );?>'></script>
		<script type='text/javascript'>
			/* <![CDATA[ */
			var vars = {"ajaxurl":"<?php echo admin_url( 'admin-ajax.php' );?>"};
			/* ]]> */
		</script>
		<script type='text/javascript' src='<?php echo plugins_url( 'forty-four/lib/js/fortyfour.js' );?>'></script>
	</body>
</html>