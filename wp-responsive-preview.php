<?php
/**
 * @package WP-Reponsive-Wordpress
 * @version 1.2
 */
/*
	/*
	Plugin Name: WP Responsive Preview
	Plugin URI: http://www.jordesign.com/wp-responsive-preview
	Description: Preview your site at random page widths to test your Responsive design.
	Author: Jordan Gillman
	Version: 1.2
	Author URI: http://www.jordesign.com
	*/
	
/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'responsive_preview_meta_boxes_setup' );
add_action( 'load-post-new.php', 'responsive_preview_meta_boxes_setup' );

/* Meta box setup function. */
function responsive_preview_meta_boxes_setup() {

	/* Add meta boxes on the 'add_meta_boxes' hook. */
	add_action( 'add_meta_boxes', 'responsive_preview_add_post_meta_boxes' );
}

/* Create one or more meta boxes to be displayed on the post editor screen. */
function responsive_preview_add_post_meta_boxes($postType) {
    $types = array('post', 'page', 'podcast','event');
	if(in_array($postType, $types)){
		add_meta_box(
				'responsive-preview',
				esc_html__( 'Responsive Preview', 'Responsive Preview' ),
				'responsive_preview_class_meta_box',
				$postType,
				'side',
				'high'
		);
	}
};

/* Display the post meta box. */
function responsive_preview_class_meta_box( $post ) { 

	$post_type = $post->post_type;
	$post_type_object = get_post_type_object($post_type);
	$can_publish = current_user_can($post_type_object->cap->publish_posts);
	?>
	<div id="responsive-preview-action">
	<?php
	$adminurl = admin_url( 'options-general.php?page=wp-responsive-preview' );
	if ( 'publish' == $post->post_status ) {
		$preview_link = esc_url( get_permalink( $post->ID ) );
		$preview_link = $adminurl."&url=".$preview_link  ;
		$preview_button = __( 'Check Responsive Preview' );
	} else {
		$preview_link = get_permalink( $post->ID );
		if ( is_ssl() )
			$preview_link = str_replace( 'http://', 'https://', $preview_link );
		$preview_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( 'preview', 'true', $preview_link ) ) );
		$preview_link = $adminurl."&url=".$preview_link  ;
		$preview_button = __( 'Check Responsive Preview' );
	}
	?>
	<a class="button responsive-preview" href="<?php echo $preview_link; ?>" target="wp-preview" id="responsive-post-preview"><?php echo $preview_button; ?></a>
	</div>
<?php }

/* Add link to Admin Bar */
function jg_responsive_admin_bar_render() {
	global $wp_admin_bar;
	
	// get the current page url
	$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
	  $url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
	  $url .= $_SERVER["REQUEST_URI"];
	  $adminurl = admin_url( 'options-general.php?page=wp-responsive-preview' );
	
	// construct preview url
	$jg_responsive_link = $adminurl."&url=".$url;
	
	//add menu item
	$wp_admin_bar->add_menu( array(
		'id' => 'responsive_preview', // link ID
		'title' => __('Check Responsive Preview'), // link title
		'href' => $jg_responsive_link, // link to the preview url
		'meta' => array( 'class' => 'responsiveLink', 'target' => '_blank', 'title' => 'Check this page at a random width')  
	));
}
add_action( 'wp_before_admin_bar_render', 'jg_responsive_admin_bar_render' );

class wpResponsivePlugin {   
    function super_plugin_menu(){
    	$handle = add_options_page('WP Responsive Preview', 'WP Responsive Preview', 'edit_published_posts', 'wp-responsive-preview', 'super_plugin_options');
    		add_action("admin_head-$handle",array('wpResponsivePlugin','loadResponsiveAssets'));
		}
		

		function loadResponsiveAssets() {
	       wp_enqueue_script('wpResponsiveJs', plugins_url( 'init.js' , __FILE__ ), array( 'jquery' ) );
	       wp_register_style( 'wpResponsiveCss', plugins_url( 'styles.css' , __FILE__ ), false, '1.0.0' );
        wp_enqueue_style( 'wpResponsiveCss' );
	   }
}

add_action('admin_menu', array('wpResponsivePlugin','super_plugin_menu'));


function super_plugin_options(){ ?>

<?php //Get URL Parameter
    $src = (empty($_GET['url'])) ? 'http://wordpress.org' : addslashes(filter_input(INPUT_GET, 'url', FILTER_SANITIZE_URL));
    ?>
<!--iFrame-->
<div id="tools">
    <h1>Responsive Preview</h1>
    <p>Select a size range to preview your page at.</p>
    <ul class="nav size" id="nav">
        <li><a href="#" id="size-toggle" class="active">Size</a></li>
        <li><a href="#" id="size-s">S</a></li>
        <li><a href="#" id="size-m">M</a></li>
        <li><a href="#" id="size-l">L</a></li>
        <li><a href="#" id="size-xl">XL</a></li>
        <li><a href="#" id="size-random">Random</a></li>
    </ul>

</div>

<div id="vp-wrap"><iframe id="viewport" src="<?php echo $src; ?>"></iframe></div>
<!--end iFrame-->
</body>
</html>
<?php }

