<?php
/*
Plugin Name: Divi Overlays
Plugin URL: https://divilife.com/
Description: Create unlimited popup overlays using the Divi Builder.
Version: 2.8.0
Author: Divi Life — Tim Strifler
Author URI: https://divilife.com

// This file includes code from Main WordPress Formatting API, licensed GPLv2 - https://wordpress.org/about/gpl/
*/

define( 'DOV_VERSION', '2.8.0');
define( 'DOV_SERVER_TIMEZONE', 'UTC');
define( 'DOV_SCHEDULING_DATETIME_FORMAT', 'm\/d\/Y g:i A');
define( 'DOV_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'DOV_PLUGIN_URL', plugin_dir_url( __FILE__ ));

// Load the API Key library if it is not already loaded
if ( ! class_exists( 'edd_divioverlays' ) ) {
	
	require_once( plugin_dir_path( __FILE__ ) . 'updater.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'updater-admin.php' );
}

// Register the Custom Dive Overlays Post Type
function register_cpt_divi_overlay() {
 
    $labels = array(
        'name' => _x( 'Divi Overlays', 'divi_overlay' ),
        'singular_name' => _x( 'Divi Overlay', 'divi_overlay' ),
        'add_new' => _x( 'Add New', 'divi_overlay' ),
        'add_new_item' => _x( 'Add New Divi Overlay', 'divi_overlay' ),
        'edit_item' => _x( 'Edit Divi Overlay', 'divi_overlay' ),
        'new_item' => _x( 'New Divi Overlay', 'divi_overlay' ),
        'view_item' => _x( 'View Divi Overlay', 'divi_overlay' ),
        'search_items' => _x( 'Search Divi Overlay', 'divi_overlay' ),
        'not_found' => _x( 'No Divi Overlays found', 'divi_overlay' ),
        'not_found_in_trash' => _x( 'No overlays found in Trash', 'divi_overlay' ),
        'parent_item_colon' => _x( 'Parent Divi Overlay:', 'divi_overlay' ),
        'menu_name' => _x( 'Divi Overlays', 'divi_overlay' ),
    );
 
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        //'description' => 'Divi Overlay Description',
        'supports' => array( 'title', 'editor', 'author' ),
        //'taxonomies' => array( 'genres' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        //'menu_icon' => 'dashicons-format-audio',
        'show_in_nav_menus' => true,
        'exclude_from_search' => true,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
 
    register_post_type( 'divi_overlay', $args );
}
 
add_action( 'init', 'register_cpt_divi_overlay' );

/* Add custom column in post type */
add_filter( 'manage_edit-divi_overlay_columns', 'my_edit_divi_overlay_columns' ) ;

function my_edit_divi_overlay_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Title' ),
		'unique_indentifier' => __( 'CSS ID' ),
		'unique_menu_id' => __( 'Menu ID' ),
		'author' => __( 'Author' ),
		'date' => __( 'Date' )
	);

	return $columns;
}
add_action( 'manage_divi_overlay_posts_custom_column', 'my_manage_divi_overlay_columns', 10, 2 );


function my_manage_divi_overlay_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		/* If displaying the 'unique-indentifier' column. */
		case 'unique_indentifier' :

			/* Get the post meta. */
			$post_slug = "overlay_unique_id_$post->ID";

			echo $post_slug;

			break;

		case 'unique_menu_id' :

			/* Get the post meta. */
			$post_slug = "unique_overlay_menu_id_$post->ID";

			echo $post_slug;

			break;
		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}
/* Custom column End here */


// Add Divi Theme Builder
add_filter('et_builder_post_types','divi_overlays_enable_builder');

function divi_overlays_enable_builder($post_types){
	$post_types[] = 'divi_overlay';
	return $post_types;
}


// Meta boxes for Divi Overlay //
function et_add_divi_overlay_meta_box() {
	
	$screen = get_current_screen();
	
	if ( $screen->post_type == 'divi_overlay' ) {
		
		if ( 'add' != $screen->action ) {
			add_meta_box( 'do_manualtriggers', esc_html__( 'Manual Triggers', 'DiviOverlays' ), 'do_manualtriggers_callback', 'divi_overlay', 'side', 'high' );
		}
		
		$status = get_option( 'divilife_edd_divioverlays_license_status' );
		$check_license = divilife_edd_divioverlays_check_license( TRUE );
		if ( ( isset( $check_license->license ) && $check_license->license != 'valid' && 'add' == $screen->action ) 
			|| ( isset( $check_license->license ) && $check_license->license != 'valid' && 'edit' == $_GET['action'] ) 
			|| ( $status === false && 'add' == $screen->action ) 
			|| ( $status === false && 'edit' == $_GET['action'] ) 
			) {
			
			$message = '';
			$base_url = admin_url( 'edit.php?post_type=divi_overlay&page=dovs-settings' );
			$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
			
			wp_redirect( $redirect );
			exit();
		}
		
		add_meta_box( 'do_displaylocations_meta_box1', esc_html__( 'Display Locations', 'DiviOverlays' ), 'do_displaylocations_callback', 'divi_overlay', 'side' );
		add_meta_box( 'do_overlay_color_picker2', 'Overlay Background', 'overlay_color_box_callback', 'divi_overlay');
		add_meta_box( 'do_animation_meta_box3', esc_html__( 'Divi Overlay Animation', 'DiviOverlays' ), 'do_single_animation_meta_box', 'divi_overlay', 'side', 'high' );
		add_meta_box( 'do_moresettings_meta_box4', esc_html__( 'Additional Overlay Settings', 'DiviOverlays' ), 'do_moresettings_callback', 'divi_overlay', 'side' );
		add_meta_box( 'do_closecustoms_meta_box5', esc_html__( 'Close Button Customizations', 'DiviOverlays' ), 'do_closecustoms_callback', 'divi_overlay', 'side' );
		add_meta_box( 'do_automatictriggers6', esc_html__( 'Automatic Triggers', 'DiviOverlays' ), 'do_automatictriggers_callback', 'divi_overlay', 'side' );
	}
}
add_action( 'add_meta_boxes', 'et_add_divi_overlay_meta_box' );

add_filter('is_protected_meta', 'removefields_from_customfieldsmetabox', 10, 2);
function removefields_from_customfieldsmetabox( $protected, $meta_key ) {
	
	if ( function_exists( 'get_current_screen' ) ) {
		
		$screen = get_current_screen();
		
		$remove = $protected;
		
		if ( $screen !== null && $screen->post_type != 'divi_overlay' ) {
		
			if ( $meta_key == 'overlay_automatictrigger'
				|| $meta_key == 'overlay_automatictrigger_disablemobile'
				|| $meta_key == 'overlay_automatictrigger_disabletablet'
				|| $meta_key == 'overlay_automatictrigger_disabledesktop'
				|| $meta_key == 'overlay_automatictrigger_onceperload' 
				|| $meta_key == 'overlay_automatictrigger_scroll_from_value'
				|| $meta_key == 'overlay_automatictrigger_scroll_to_value'
				|| $meta_key == 'do_enable_scheduling' 
				|| $meta_key == 'do_at_pages' 
				|| $meta_key == 'do_at_pages_selected' 
				|| $meta_key == 'do_at_pagesexception_selected' 
				|| $meta_key == 'post_do_customizeclosebtn' 
				|| $meta_key == 'post_do_hideclosebtn' 
				|| $meta_key == 'post_do_preventscroll' 
				|| $meta_key == 'post_enableurltrigger' 
				|| $meta_key == 'css_selector_at_pages'
				|| $meta_key == 'css_selector_at_pages_selected'
				|| $meta_key == 'do_date_start'
				|| $meta_key == 'do_date_end'
				|| $meta_key == 'dov_closebtn_cookie'
				|| $meta_key == 'do_enableajax'
				) {
					
				$remove = true;
			}
		}
		
		return $remove;
	}
}


function do_get_wp_posts() {
			
	if ( isset( $_POST['q'] ) ) {
	
		$q = stripslashes( $_POST['q'] );
	
	} else {
		
		return;
	}
	
	
	if ( isset( $_POST['page'] ) ) {
		
		$page = (int) $_POST['page'];
		
	} else {
		
		$page = 1;
	}
	
	
	if ( isset( $_POST['json'] ) ) {
		
		$json = (int) $_POST['json'];
		
	} else {
		
		$json = 0;
	}
	
	$total_count = 0;
	$data = null;
	
	if ( ! function_exists( 'et_get_registered_post_type_options' ) ) {
		
		$data = json_encode(
		
			array(
				'total_count' => 1,
				'items' => array( 0 => (object) array( 'id' => 0, 'post_title' => 'This functionality requires Divi.' ) )
			)
		);
			
		die( $data );
	}
	
	$post_types = et_get_registered_post_type_options();
	
	$excluded_post_types = array( 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'et_pb_layout', 'divi_bars', 'divi_overlay', 'divi_mega_pro', 'customize_changeset' );
	
	$post_types = array_diff( array_keys( $post_types ), $excluded_post_types );
	
	$args = array(
		'do_by_title_like' => $q,
		'post_type' => $post_types,
		'cache_results'  => false,
		'posts_per_page' => 7,
		'paged' => $page,
		'orderby' => 'id',
		'order' => 'DESC'
	);
	
	add_filter( 'posts_where', 'do276_title_filter', 10, 2 );
	$query = new WP_Query( $args );
	remove_filter( 'posts_where', 'do276_title_filter', 10, 2 );
	
	$total_count = (int) $query->found_posts;
	
	$posts = array();
	
	if ( $query->have_posts() ) {
		
		while ( $query->have_posts() ) {
			
			$query->the_post();
			
			$post_filtered = array();
			
			$post_filtered[ 'id' ] = get_the_ID();
			$post_filtered[ 'post_title' ] = get_the_title();
			
			$posts[] = $post_filtered;
			
		}
	}
	
	wp_reset_postdata();
	
	if ( $json ) {
		
		header( 'Content-type: application/json' );
		$data = json_encode(
		
			array(
				'total_count' => $total_count,
				'items' => $posts
			)
		);
		
		die( $data );
	}
	
	return $posts;
}
add_action( 'wp_ajax_nopriv_ajax_do_listposts', 'do_get_wp_posts' );
add_action( 'wp_ajax_ajax_do_listposts', 'do_get_wp_posts' );


function do276_title_filter( $where, &$wp_query )
{
    global $wpdb;
	
    if ( $search_term = $wp_query->get( 'do_by_title_like' ) ) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $wpdb->esc_like( $search_term ) . '%\'';
    }
	
    return $where;
}


function do_get_wp_categories() {
	
	if ( isset( $_POST['q'] ) ) {
	
		$q = stripslashes( $_POST['q'] );
	
	} else {
		
		return;
	}
	
	
	if ( isset( $_POST['page'] ) ) {
		
		$page = (int) $_POST['page'];
		
	} else {
		
		$page = 1;
	}
	
	
	if ( isset( $_POST['json'] ) ) {
		
		$json = (int) $_POST['json'];
		
	} else {
		
		$json = 0;
	}
	
	$data = null;
	
	$limit = 7;
	$offset = ( $page - 1 ) * $limit;
	
	$args = array(
		'taxonomy'               => array( 'category', 'project_category', 'product_cat' ),
		'name__like'             => $q,
		'number'                 => $limit,
		'offset'                 => $offset,
		'fields'                 => 'id=>name',
		'hide_empty'             => false,
		'get'                    => 'all',
		'orderby'				 => 'name',
		'order'					 => 'ASC'
	);
	
	$term_query = new WP_Term_Query( $args );
	
	// Get all categories found
	$args['number'] = '';
	$totalterm_query = new WP_Term_Query( $args );
	
	$json_posts = array();
	if ( ! empty( $term_query ) && ! is_wp_error( $term_query ) ) {
		
		$categories = $term_query->get_terms();
		
		$total_count = (int) sizeof( $totalterm_query->get_terms() );
		
		$pop_categories = array();
		foreach( $categories as $term_id => $term_name ) {
			$pop_categories[ 'id' ] = $term_id;
			$pop_categories[ 'name' ] = $term_name;
			$json_posts[] = $pop_categories;
		}
		
	} else {
		
		
	}
	
	if ( $json ) {
		
		header( 'Content-type: application/json' );
		$data = json_encode(
		
			array(
				'total_count' => $total_count,
				'items' => $json_posts
			)
		);
		
		die( $data );
	}
}
add_action( 'wp_ajax_nopriv_ajax_do_listcategories', 'do_get_wp_categories' );
add_action( 'wp_ajax_ajax_do_listcategories', 'do_get_wp_categories' );


function do_get_wp_tags() {
	
	if ( isset( $_POST['q'] ) ) {
	
		$q = stripslashes( $_POST['q'] );
	
	} else {
		
		return;
	}
	
	
	if ( isset( $_POST['page'] ) ) {
		
		$page = (int) $_POST['page'];
		
	} else {
		
		$page = 1;
	}
	
	
	if ( isset( $_POST['json'] ) ) {
		
		$json = (int) $_POST['json'];
		
	} else {
		
		$json = 0;
	}
	
	$data = null;
	
	$limit = 7;
	$offset = ( $page - 1 ) * $limit;
	
	$args = array(
		'taxonomy'               => array( 'post_tag' ),
		'name__like'             => $q,
		'number'                 => $limit,
		'offset'                 => $offset,
		'fields'                 => 'id=>name',
		'hide_empty'             => false,
		'get'                    => 'all',
		'orderby'				 => 'name',
		'order'					 => 'ASC'
	);
	
	$term_query = new WP_Term_Query( $args );
	
	// Get all tags found
	$args['number'] = '';
	$totalterm_query = new WP_Term_Query( $args );
	
	$json_posts = array();
	if ( ! empty( $term_query ) && ! is_wp_error( $term_query ) ) {
		
		$tags = $term_query->get_terms();
		
		$total_count = (int) sizeof( $totalterm_query->get_terms() );
		
		$pop_tags = array();
		foreach( $tags as $term_id => $term_name ) {
			$pop_tags[ 'id' ] = $term_id;
			$pop_tags[ 'name' ] = $term_name;
			$json_posts[] = $pop_tags;
		}
		
	} else {
		
		
	}
	
	if ( $json ) {
		
		header( 'Content-type: application/json' );
		$data = json_encode(
		
			array(
				'total_count' => $total_count,
				'items' => $json_posts
			)
		);
		
		die( $data );
	}
}
add_action( 'wp_ajax_nopriv_ajax_do_listtags', 'do_get_wp_tags' );
add_action( 'wp_ajax_ajax_do_listtags', 'do_get_wp_tags' );


if ( ! function_exists( 'do_displaylocations_callback' ) ) :

	function do_displaylocations_callback( $post ) {
			
		wp_nonce_field( 'do_displaylocations', 'do_displaylocations_nonce' );
		
		$at_pages = get_post_meta( $post->ID, 'do_at_pages', true );
		$selectedpages = get_post_meta( $post->ID, 'do_at_pages_selected' );
		$selectedexceptpages = get_post_meta( $post->ID, 'do_at_pagesexception_selected' );
	
		$at_categories = get_post_meta( $post->ID, 'category_at_categories', true );
		$selectedcategories = get_post_meta( $post->ID, 'category_at_categories_selected' );
		$selectedexceptcategories = get_post_meta( $post->ID, 'category_at_exceptioncategories_selected' );
		
		$at_tags = get_post_meta( $post->ID, 'tag_at_tags', true );
		$selectedtags = get_post_meta( $post->ID, 'tag_at_tags_selected' );
		$selectedexcepttags = get_post_meta( $post->ID, 'tag_at_exceptiontags_selected' );
		
		if( $at_pages == '' ) {
			
			$at_pages = 'all';
		}
		
		if( $at_categories == '' ) {
			
			$at_categories = 'all';
		}
		
		if( $at_tags == '' ) {
			
			$at_tags = 'all';
		}
		
		$do_displaylocations_archive = get_post_meta( $post->ID, 'do_displaylocations_archive' );
		
		if( !isset( $do_displaylocations_archive[0] ) ) {
			
			$do_displaylocations_archive[0] = '0';
		}
		
		?>
		<div class="custom_meta_box">
			<div class="at_pages">
				<select name="post_at_pages" class="at_pages chosen do-filter-by-pages" data-dropdownshowhideblock="1">
					<option value="all"<?php if ( $at_pages == 'all' ) { ?> selected="selected"<?php } ?> data-showhideblock=".do-list-exceptionpages-container">All pages</option>
					<option value="specific"<?php if ( $at_pages == 'specific' ) { ?> selected="selected"<?php } ?> data-showhideblock=".do-list-pages-container">Only specific pages</option>
				</select>
				<div class="do-list-pages-container<?php if ( $at_pages == 'specific' ) { ?> do-show<?php } ?>">
					<select name="post_at_pages_selected[]" class="do-list-pages" data-placeholder="Choose posts or pages..." multiple tabindex="3">
					<?php
						if ( isset( $selectedpages[0] ) ) {
							
							foreach( $selectedpages[0] as $selectedidx => $selectedvalue ) {
								
								$post_title = get_the_title( $selectedvalue );
								
								print '<option value="' . $selectedvalue . '" selected="selected">' . $post_title . '</option>';
							}
						}
					?>
					</select>
				</div>
				<div class="do-list-exceptionpages-container<?php if ( $at_pages == 'all' ) { ?> do-show<?php } ?>">
					<h4 class="do-exceptedpages">Add Exceptions:</h4>
					<select name="post_at_exceptionpages_selected[]" class="do-list-pages" data-placeholder="Choose posts or pages..." multiple tabindex="3">
					<?php
						if ( isset( $selectedexceptpages[0] ) ) {
							
							foreach( $selectedexceptpages[0] as $selectedidx => $selectedvalue ) {
								
								$post_title = get_the_title( $selectedvalue );
								
								print '<option value="' . $selectedvalue . '" selected="selected">' . $post_title . '</option>';
							}
						}
					?>
					</select>
				</div>
			</div>
			<div class="at_categories">
				<select name="category_at_categories" class="at_categories chosen do-filter-by-categories" data-dropdownshowhideblock="1">
					<option value="all"<?php if ( $at_categories == 'all' ) { ?> selected="selected"<?php } ?> data-showhideblock=".do-list-exceptioncategories-container">All categories</option>
					<option value="specific"<?php if ( $at_categories == 'specific' ) { ?> selected="selected"<?php } ?> data-showhideblock=".do-list-categories-container">Only specific categories</option>
				</select>
				<div class="do-list-categories-container<?php if ( $at_categories == 'specific' ) { ?> do-show<?php } ?>">
					<select name="category_at_categories_selected[]" class="do-list-categories" data-placeholder="Choose categories..." multiple tabindex="3">
					<?php
						if ( isset( $selectedcategories[0] ) ) {
							
							foreach( $selectedcategories[0] as $selectedidx => $selectedvalue ) {
								
								$cat_name = get_cat_name( $selectedvalue );
								
								print '<option value="' . $selectedvalue . '" selected="selected">' . $cat_name . '</option>';
							}
						}
					?>
					</select>
				</div>
				<div class="do-list-exceptioncategories-container<?php if ( $at_categories == 'all' ) { ?> do-show<?php } ?>">
					<h4 class="do-exceptedcategories">Add Exceptions:</h4>
					<select name="category_at_exceptioncategories_selected[]" class="do-list-categories" data-placeholder="Choose categories..." multiple tabindex="3">
					<?php
						if ( isset( $selectedexceptcategories[0] ) ) {
							
							foreach( $selectedexceptcategories[0] as $selectedidx => $selectedvalue ) {
								
								$cat_name = get_cat_name( $selectedvalue );
								
								print '<option value="' . $selectedvalue . '" selected="selected">' . $cat_name . '</option>';
							}
						}
					?>
					</select>
				</div>
			</div>
			<div class="at_tags">
				<select name="tag_at_tags" class="at_tags chosen do-filter-by-tags" data-dropdownshowhideblock="1">
					<option value="all"<?php if ( $at_tags == 'all' ) { ?> selected="selected"<?php } ?> data-showhideblock=".do-list-exceptiontags-container">All tags</option>
					<option value="specific"<?php if ( $at_tags == 'specific' ) { ?> selected="selected"<?php } ?> data-showhideblock=".do-list-tags-container">Only specific tags</option>
				</select>
				<div class="do-list-tags-container<?php if ( $at_tags == 'specific' ) { ?> do-show<?php } ?>">
					<select name="tag_at_tags_selected[]" class="do-list-tags" data-placeholder="Choose tags..." multiple tabindex="3">
					<?php
						if ( isset( $selectedtags[0] ) ) {
							
							foreach( $selectedtags[0] as $selectedidx => $selectedvalue ) {
								
								$term_name = get_term( $selectedvalue );
								
								print '<option value="' . $selectedvalue . '" selected="selected">' . $term_name->name . '</option>';
							}
						}
					?>
					</select>
				</div>
				<div class="do-list-exceptiontags-container<?php if ( $at_tags == 'all' ) { ?> do-show<?php } ?>">
					<h4 class="do-exceptedtags">Add Exceptions:</h4>
					<select name="tag_at_exceptiontags_selected[]" class="do-list-tags" data-placeholder="Choose tags..." multiple tabindex="3">
					<?php
						if ( isset( $selectedexcepttags[0] ) ) {
							
							foreach( $selectedexcepttags[0] as $selectedidx => $selectedvalue ) {
								
								$term_name = get_term( $selectedvalue );
								
								print '<option value="' . $selectedvalue . '" selected="selected">' . $term_name->name . '</option>';
							}
						}
					?>
					</select>
				</div>
			</div>
			<div class="custom_meta_box">
				<p>
					<input name="do_displaylocations_archive" type="checkbox" id="do_displaylocations_archive" value="1" <?php checked( $do_displaylocations_archive[0], 1 ); ?> /> Archive
				</p>
			</div>
			<div class="clear"></div> 
		</div>
		<?php
	}
	
endif;


if ( ! function_exists( 'do_manualtriggers_callback' ) ) :

	function do_manualtriggers_callback( $post ) {
		?>
		<div class="custom_meta_box">
			<p>
				<label class="label-color-field"><p>CSS ID:</label>
				overlay_unique_id_<?php print $post->ID ?></p>
			</p>
			<div class="clear"></div> 
		</div> 
		<div class="custom_meta_box">
			<p>
				<label class="label-color-field"><p>Menu ID:</label>
				unique_overlay_menu_id_<?php print $post->ID ?></p>
			</p>
			<div class="clear"></div> 
		</div>
		<?php
	}
	
endif;


if ( ! function_exists( 'do_closecustoms_callback' ) ) :

	function do_closecustoms_callback( $post ) {
		
		wp_nonce_field( 'do_closecustoms', 'do_closecustoms_nonce' );
		
		$textcolor = get_post_meta( $post->ID, 'post_doclosebtn_text_color', true );
		$bgcolor = get_post_meta( $post->ID, 'post_doclosebtn_bg_color', true );
		$fontsize = get_post_meta( $post->ID, 'post_doclosebtn_fontsize', true );
		$borderradius = get_post_meta( $post->ID, 'post_doclosebtn_borderradius', true );
		$padding = get_post_meta( $post->ID, 'post_doclosebtn_padding', true );
		$close_cookie = get_post_meta( $post->ID, 'dov_closebtn_cookie', true );
		
		if( !isset( $fontsize ) ) {
			
			$fontsize = 25;
		}
		
		$hideclosebtn = get_post_meta( $post->ID, 'post_do_hideclosebtn' );
		if( !isset( $hideclosebtn[0] ) ) {
			
			$hideclosebtn[0] = '0';
		}
		
		$customizeclosebtn = get_post_meta( $post->ID, 'post_do_customizeclosebtn' );
		if( !isset( $customizeclosebtn[0] ) ) {
			
			$customizeclosebtn[0] = '0';
		}
		
		if( $close_cookie == '' ) {
			
			$close_cookie = 0;
		}
		
		?>
		<div class="custom_meta_box">
			<p>
				<label>Close Button Cookie:</label>
				<input class="dov_closebtn_cookie" type="text" name="dov_closebtn_cookie" value="<?php echo $close_cookie; ?>" readonly="readonly"> days
			</p>
			<div id="slider-doclosebtn-cookie" class="slider-bar"></div>
		</div>
		
		<div class="custom_meta_box">
			<p>
				<input name="post_do_hideclosebtn" type="checkbox" id="post_do_hideclosebtn" value="1" <?php checked( $hideclosebtn[0], 1 ); ?> /> Hide Main Close Button
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<p>
				<input name="post_do_customizeclosebtn" type="checkbox" id="post_do_customizeclosebtn" value="1" class="enable_custombtn" <?php checked( $customizeclosebtn[0], 1 ); ?> /> Customize Close Button
			</p>
			<div class="enable_customizations<?php if ( $customizeclosebtn[0] == 1 ) { ?> do-show<?php } ?>">
				<div class="custom_meta_box">
					<p>
						<label class="label-color-field">Text color:</label>
						<input class="doclosebtn-text-color" type="text" name="post_doclosebtn_text_color" value="<?php echo $textcolor; ?>"/>
					</p>
					<div class="clear"></div> 
				</div> 
				<div class="custom_meta_box">
					<p>
						<label class="label-color-field">Background color:</label>
						<input class="doclosebtn-bg-color" type="text" name="post_doclosebtn_bg_color" value="<?php echo $bgcolor; ?>"/>
					</p>
					<div class="clear"></div> 
				</div>
				<div class="custom_meta_box">
					<p>
						<label>Font size:</label>
						<input class="post_doclosebtn_fontsize" type="text" name="post_doclosebtn_fontsize" value="<?php echo $fontsize; ?>" readonly="readonly" > px
					</p>
					<div id="slider-doclosebtn-fontsize" class="slider-bar"></div>
				</div>
				<div class="custom_meta_box">
					<p>
						<label>Border radius:</label>
						<input class="post_doclosebtn_borderradius" type="text" name="post_doclosebtn_borderradius" value="<?php echo $borderradius; ?>" readonly="readonly" > %
					</p>
					<div id="slider-doclosebtn-borderradius" class="slider-bar"></div>
				</div>
				<div class="custom_meta_box">
					<p>
						<label>Padding:</label>
						<input class="post_doclosebtn_padding" type="text" name="post_doclosebtn_padding" value="<?php echo $padding; ?>" readonly="readonly" > px
					</p>
					<div id="slider-doclosebtn-padding" class="slider-bar"></div>
				</div>
				<div class="custom_meta_box">
					<p>
						<label>Preview:</label>
					</p>
					<button type="button" class="overlay-customclose-btn"><span>&times;</span></button>
				</div>
			</div>
			<div class="clear"></div> 
		</div>
		<?php
	}
	
endif;		


if ( ! function_exists( 'do_moresettings_callback' ) ) :

	function do_moresettings_callback( $post ) {
	
		wp_nonce_field( 'do_mainpage_preventscroll', 'do_mainpage_preventscroll_nonce' );
		
		$preventscroll = get_post_meta( $post->ID, 'post_do_preventscroll' );
		
		$css_selector = get_post_meta( $post->ID, 'post_css_selector', true );
		
		$enableurltrigger = get_post_meta( $post->ID, 'post_enableurltrigger' );
		
		$do_enableajax = get_post_meta( $post->ID, 'do_enableajax' );
		
		if( !isset( $preventscroll[0] ) ) {
			
			$preventscroll[0] = '0';
		}
		
		if( !isset( $enableurltrigger[0] ) ) {
			
			$enableurltrigger[0] = '0';
		}
		
		if( !isset( $do_enableajax[0] ) ) {
			
			$do_enableajax[0] = '0';
		}
		?>
		<div class="custom_meta_box">
			<p>
				<label>CSS Selector Trigger:</label>
				<input class="css_selector" type="text" name="post_css_selector" value="<?php echo $css_selector; ?>"/>
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<p>
				<input name="post_do_preventscroll" type="checkbox" id="post_do_preventscroll" value="1" <?php checked( $preventscroll[0], 1 ); ?> /> Prevent main page scrolling
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<p>
				<label for="post_enableurltrigger"></label>
				<input name="post_enableurltrigger" type="checkbox" class="enableurltrigger" value="1" <?php checked( $enableurltrigger[0], 1 ); ?> /> Enable URL Trigger
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<p>
				<label for="cache_results"></label>
				<input name="do_enableajax" type="checkbox" class="do_enableajax" value="1" <?php checked( $do_enableajax[0], 1 ); ?> /> Enable AJAX (Load content on call)
			</p>
			<div class="clear"></div> 
		</div>
		<?php
	}
	
endif;


if ( ! function_exists( 'do_automatictriggers_callback' ) ) :

	function do_automatictriggers_callback( $post ) {
		
		$post_id = get_the_ID();
		$disablemobile = get_post_meta( $post_id, 'overlay_automatictrigger_disablemobile' );
		$disabletablet = get_post_meta( $post_id, 'overlay_automatictrigger_disabletablet' );
		$disabledesktop = get_post_meta( $post_id, 'overlay_automatictrigger_disabledesktop' );
		
		$onceperload = get_post_meta( $post_id, 'overlay_automatictrigger_onceperload' );
		
		$enable_scheduling = get_post_meta( $post_id, 'do_enable_scheduling' );
		$date_start = get_post_meta( $post->ID, 'do_date_start', true );
		$date_end = get_post_meta( $post->ID, 'do_date_end', true );
		$date_start = doConvertDateToUserTimezone( $date_start );
		$date_end = doConvertDateToUserTimezone( $date_end );
		
		$time_start = get_post_meta( $post->ID, 'do_time_start', true );
		$time_end = get_post_meta( $post->ID, 'do_time_end', true );
		
		$overlay_at_selected = get_post_meta( $post_id, 'overlay_automatictrigger', true );
		$overlay_ats = array(
			''   => esc_html__( 'None', 'Divi' ),
			'overlay-timed'   => esc_html__( 'Timed Delay', 'Divi' ),
			'overlay-scroll'    => esc_html__( 'Scroll Percentage', 'Divi' ),
			'overlay-exit' => esc_html__( 'Exit Intent', 'Divi' ),
		);
		
		for( $a = 1; $a <= 7; $a++ ) {
			
			$daysofweek[$a] = get_post_meta( $post_id, 'divioverlays_scheduling_daysofweek_' . $a );
			
			if ( !isset( $daysofweek[$a][0] ) ) {
				
				$daysofweek[$a][0] = '0';
			}
			else {
				
				$daysofweek[$a] = $daysofweek[$a][0];
			}
		}
		
		if( !isset( $disablemobile[0] ) ) {
			
			$disablemobile[0] = '1';
		}
		
		if( !isset( $disabletablet[0] ) ) {
			
			$disabletablet[0] = '0';
		}
		
		if( !isset( $disabledesktop[0] ) ) {
			
			$disabledesktop[0] = '0';
		}
		
		if( !isset( $onceperload[0] ) ) {
			
			$onceperload[0] = '1';
		}
		
		if( !isset( $enable_scheduling[0] ) ) {
			
			$enable_scheduling[0] = 0;
		}
		?>
		<p class="divi_automatictrigger_settings et_pb_single_title">
			<label for="post_overlay_automatictrigger"></label>
			<select id="post_overlay_automatictrigger" name="post_overlay_automatictrigger" class="post_overlay_automatictrigger chosen">
			<?php
			foreach ( $overlay_ats as $at_value => $at_name ) {
				printf( '<option value="%2$s"%3$s>%1$s</option>',
					esc_html( $at_name ),
					esc_attr( $at_value ),
					selected( $at_value, $overlay_at_selected, false )
				);
			} ?>
			</select>
		</p>
		
		<?php
		
			$at_timed = get_post_meta( $post->ID, 'overlay_automatictrigger_timed_value', true );
			$at_scroll_from = get_post_meta( $post->ID, 'overlay_automatictrigger_scroll_from_value', true );
			$at_scroll_to = get_post_meta( $post->ID, 'overlay_automatictrigger_scroll_to_value', true );
		?>
		<div class="divi_automatictrigger_timed<?php if ( $overlay_at_selected == 'overlay-timed' ) { ?> do-show<?php } ?>">
			<p>
				<label>Specify timed delay (in seconds):</label>
				<input class="post_at_timed" type="text" name="post_at_timed" value="<?php echo $at_timed; ?>"/>
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="divi_automatictrigger_scroll<?php if ( $overlay_at_selected == 'overlay-scroll' ) { ?> do-show<?php } ?>">
			<p>Specify in pixels or percentage:</p>
			<div class="at-scroll-settings">
				<label for="post_at_scroll_from">From:</label>
				<input class="post_at_scroll" type="text" name="post_at_scroll_from" value="<?php echo $at_scroll_from; ?>"/>
				<label for="post_at_scroll_to">to:</label>
				<input class="post_at_scroll" type="text" name="post_at_scroll_to" value="<?php echo $at_scroll_to; ?>"/>
			</div> 
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box do-at-devices<?php if ( strlen( $overlay_at_selected ) > 1 ) { ?> do-show<?php } ?>">
			<p>
				<input name="post_at_disablemobile" type="checkbox" id="post_at_disablemobile" value="1" <?php checked( $disablemobile[0], 1 ); ?> /> Disable On Mobile
			</p>
			<div class="clear"></div> 
			<p>
				<input name="post_at_disabletablet" type="checkbox" id="post_at_disabletablet" value="1" <?php checked( $disabletablet[0], 1 ); ?> /> Disable On Tablet
			</p>
			<div class="clear"></div> 
			<p>
				<input name="post_at_disabledesktop" type="checkbox" id="post_at_disabledesktop" value="1" <?php checked( $disabledesktop[0], 1 ); ?> /> Disable On Desktop
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box do-at-onceperload<?php if ( strlen( $overlay_at_selected ) > 1 ) { ?> do-show<?php } ?>">
			<p>
				<input name="post_at_onceperload" type="checkbox" id="post_at_onceperload" value="1" <?php checked( $onceperload[0], 1 ); ?> />
				 Display once per page load
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box do-at-scheduling<?php if ( strlen( $overlay_at_selected ) > 1 ) { ?> do-show<?php } ?>">
			<div class="custom_meta_box">
				<p class="divioverlay_placement et_pb_single_title">
					<label for="do_enable_scheduling">Set Scheduling:</label>
					<select name="do_enable_scheduling" class="chosen divioverlay-enable-scheduling" data-dropdownshowhideblock="1">
						<option value="0"<?php if ( $enable_scheduling[0] == 0 ) { ?> selected="selected"<?php } ?>>Disabled</option>
						<option value="1"<?php if ( $enable_scheduling[0] == 1 ) { ?> selected="selected"<?php } ?> data-showhideblock=".do-onetime">
						<?php print esc_html__( 'Start &amp; End Time', 'DiviOverlays' ) ?>
						</option>
						<option value="2"<?php if ( $enable_scheduling[0] == 2 ) { ?> selected="selected"<?php } ?> data-showhideblock=".do-recurring">
						<?php print esc_html__( 'Recurring Scheduling', 'DiviOverlays' ) ?>
						</option>
					</select>
				</p>
			</div>
			
			<div class="row do-onetime<?php if ( $enable_scheduling[0] == 1 ) { ?> do-show<?php } ?>">
				<div class="col-xs-12">
					<label>
						Start date <br/>
						<input type="text" name="do_date_start" value="<?php print $date_start; ?>" class="form-control">
					</label>
				</div>
				<div class="col-xs-12">
					<label>
						End date <br/>
						<input type="text" name="do_date_end" value="<?php print $date_end; ?>" class="form-control">
					</label>
				</div>
			</div>
			
			<div class="row do-recurring<?php if ( $enable_scheduling[0] == 2 ) { ?> do-show<?php } ?>">
				<div class="col-xs-12">
					<p><input name="divioverlays_scheduling_daysofweek[]" type="checkbox" id="divioverlays_scheduling_daysofweek" value="1" <?php checked( $daysofweek[1][0], 1 ); ?> /> Monday</p>
					<p><input name="divioverlays_scheduling_daysofweek[]" type="checkbox" id="divioverlays_scheduling_daysofweek" value="2" <?php checked( $daysofweek[2][0], 1 ); ?> /> Tuesday</p>
					<p><input name="divioverlays_scheduling_daysofweek[]" type="checkbox" id="divioverlays_scheduling_daysofweek" value="3" <?php checked( $daysofweek[3][0], 1 ); ?> /> Wednesday</p>
					<p><input name="divioverlays_scheduling_daysofweek[]" type="checkbox" id="divioverlays_scheduling_daysofweek" value="4" <?php checked( $daysofweek[4][0], 1 ); ?> /> Thursday</p>
					<p><input name="divioverlays_scheduling_daysofweek[]" type="checkbox" id="divioverlays_scheduling_daysofweek" value="5" <?php checked( $daysofweek[5][0], 1 ); ?> /> Friday</p>
					<p><input name="divioverlays_scheduling_daysofweek[]" type="checkbox" id="divioverlays_scheduling_daysofweek" value="6" <?php checked( $daysofweek[6][0], 1 ); ?> /> Saturday</p>
					<p><input name="divioverlays_scheduling_daysofweek[]" type="checkbox" id="divioverlays_scheduling_daysofweek" value="7" <?php checked( $daysofweek[7][0], 1 ); ?> /> Sunday</p>
				</div>
				<div class="col-sm-6">
					<label>
						Start Time <br/>
						<input type="text" name="do_time_start" value="<?php print $time_start; ?>" class="form-control">
					</label>
					<div id="datetimepicker11"></div>
				</div>
				<div class="col-sm-6">
					<label>
						End Time <br/>
						<input type="text" name="do_time_end" value="<?php print $time_end; ?>" class="form-control">
					</label>
				</div>
			</div>
			
			<div class="row">
				<div class="col-xs-12">
					<div class="do-recurring-user-msg alert alert-danger">
						
					</div>
				</div>
			</div>
			
			<div class="clear"></div> 
		</div>
		
		<?php
	}
	
endif;


if ( ! function_exists( 'do_single_animation_meta_box' ) ) :

	function do_single_animation_meta_box($post) {
		
		$post_id = get_the_ID();
		$overlay_effect = get_post_meta( $post_id, '_et_pb_overlay_effect', true );
		$overlay_effects = array(
			'overlay-hugeinc'   => esc_html__( 'Fade & Slide', 'Divi' ),
			'overlay-corner'    => esc_html__( 'Corner', 'Divi' ),
			'overlay-slidedown' => esc_html__( 'Slide down', 'Divi' ),
			'overlay-scale' => esc_html__( 'Scale', 'Divi' ),
			'overlay-door' => esc_html__( 'Door', 'Divi' ),
			'overlay-contentpush' => esc_html__( 'Content Push', 'Divi' ),
			'overlay-contentscale' => esc_html__( 'Content Scale', 'Divi' ),
			'overlay-cornershape' => esc_html__( 'Corner Shape', 'Divi' ),
			'overlay-boxes' => esc_html__( 'Little Boxes', 'Divi' ),
			'overlay-simplegenie' => esc_html__( 'Simple Genie', 'Divi' ),
			'overlay-genie' => esc_html__( 'Genie', 'Divi' ),
		);
		?>
		<p class="et_pb_page_settings et_pb_single_title">
			<label for="et_pb_page_layout" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Select Overlay Animation', 'Divi' ); ?>: </label>
			<select id="et_pb_overlay_effect" name="et_pb_overlay_effect" class="chosen">
			<?php
			foreach ( $overlay_effects as $overlay_value => $overlay_name ) {
				printf( '<option value="%2$s"%3$s>%1$s</option>',
					esc_html( $overlay_name ),
					esc_attr( $overlay_value ),
					selected( $overlay_value, $overlay_effect, false )
				);
			} ?>
			</select>
		</p>
		
	<?php }
	
endif;


// Save Meta Box Value //
/*========================= Color Picker ============================*/
function overlay_color_box_callback( $post ) {	
	wp_nonce_field( 'overlay_color_box', 'overlay_color_box_nonce' );
	$color = get_post_meta( $post->ID, 'post_overlay_bg_color', true );
	$fontcolor = get_post_meta( $post->ID, 'post_overlay_font_color', true );
	?>
	<div class="custom_meta_box">
		<p>
			<label class="label-color-field">Select Overlay Background Color: </label>
			<input class="cs-wp-color-picker" type="text" name="post_bg" value="<?php echo $color; ?>"/>
		</p>
		<div class="clear"></div> 
	</div> 
	<div class="custom_meta_box">
		<p>
			<label class="label-color-field">Select Overlay Font Color: </label>
			<input class="color-field" type="text" name="post_font_color" value="<?php echo $fontcolor; ?>"/>
		</p>
		<div class="clear"></div> 
	</div> 
	<script>
		(function( $ ) {
			// Add Color Picker to all inputs that have 'color-field' class
			$(function() {
			$('.color-field').wpColorPicker();
			});
		})( jQuery );
	</script>
	<?php
}

function divi_overlay_config( $hook ) {
	
	if ( is_divi_builder_enabled() ) { return; }
	
	// enqueue style
	wp_register_style( 'divi-overlays-wp-color-picker', DOV_PLUGIN_URL . 'overlay-effects/css/admin/cs-wp-color-picker.min.css', array( 'wp-color-picker' ), '1.0.0', 'all' );
	wp_register_script( 'divi-overlays-wp-color-picker', DOV_PLUGIN_URL . 'overlay-effects/js/admin/cs-wp-color-picker.min.js', array( 'wp-color-picker' ), '1.0.0', true );
	
	wp_register_style( 'divi-overlays-select2', DOV_PLUGIN_URL . 'overlay-effects/css/admin/select2.4.0.9.min.css', array(), '4.0.9', 'all' );
	wp_register_script( 'divi-overlays-select2', DOV_PLUGIN_URL . 'overlay-effects/js/admin/select2.4.0.9.min.js', array('jquery'), '4.0.9', true );
	wp_register_style( 'divi-overlays-select2-bootstrap', DOV_PLUGIN_URL . 'overlay-effects/css/admin/select2-bootstrap.min.css', array('divi-overlays-admin-bootstrap'), '1.0.0', 'all' );
	
	
	/* Scheduling requirements */
	wp_register_script( 'divi-overlays-datetime-moment', '//cdn.jsdelivr.net/momentjs/latest/moment.min.js', array('jquery'), '1.0.0', true );
	wp_register_script( 'divi-overlays-datetime-moment-timezone', '//cdn.jsdelivr.net/npm/moment-timezone@0.5.13/builds/moment-timezone-with-data.min.js', array('jquery'), '1.0.0', true );
	wp_register_style( 'divi-overlays-admin-bootstrap', DOV_PLUGIN_URL . 'overlay-effects/css/admin/bootstrap.css', array(), '1.0.0', 'all' );
	wp_register_script( 'divi-overlays-datetime-bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), '1.0.0', true );
	wp_register_script( 'divi-overlays-datetime-bootstrap-select', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/js/bootstrap-select.min.js', array('jquery'), '1.0.0', true );
	wp_register_style( 'divi-overlays-admin-bootstrap-select', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.1/css/bootstrap-select.min.css', array(), '1.0.0', 'all' );
	
	
	/* Include Date Range Picker */
	wp_register_style( 'divi-overlays-datetime-corecss', DOV_PLUGIN_URL . 'overlay-effects/css/admin/bootstrap-datetimepicker.min.css', array( 'divi-overlays-admin-bootstrap' ), '1.0.0', 'all' );
	wp_register_script( 'divi-overlays-datetime-corejs', DOV_PLUGIN_URL . 'overlay-effects/js/admin/bootstrap-datetimepicker.min.js', array( 'jquery', 'divi-overlays-datetime-bootstrap' ), '1.0.0', true );
	
	wp_register_style( 'divi-overlays-admin', DOV_PLUGIN_URL . 'overlay-effects/css/admin/admin.css', array(), '1.0.0', 'all' );
	wp_register_script( 'divi-overlays-admin-functions', DOV_PLUGIN_URL . 'overlay-effects/js/admin/admin-functions.js', array( 'jquery', 'divi-overlays-select2' ), '1.0.0', true );
}
add_action('admin_enqueue_scripts', 'divi_overlay_config');


function divi_overlay_high_priority_includes( $hook ) {
	
	if ( is_divi_builder_enabled() ) { return; }
	
	$screen = get_current_screen();
	
	if ( $screen->post_type != 'divi_overlay' ) {
		return;
	}
	
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'divi-overlays-wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_script( 'divi-overlays-wp-color-picker' );
	
	wp_enqueue_script( 'divi-overlays-datetime-moment' );
	wp_enqueue_script( 'divi-overlays-datetime-moment-timezone' );
	wp_enqueue_style( 'divi-overlays-admin-bootstrap' );
	wp_enqueue_script( 'divi-overlays-datetime-bootstrap' );
	wp_enqueue_script( 'divi-overlays-datetime-bootstrap-select' );
	wp_enqueue_style( 'divi-overlays-admin-bootstrap-select' );
	
	wp_enqueue_style( 'divi-overlays-select2' );
	wp_enqueue_style( 'divi-overlays-select2-bootstrap' );
	wp_enqueue_script( 'divi-overlays-select2' );
	
	wp_enqueue_style( 'divi-overlays-datetime-corecss' );
	wp_enqueue_script( 'divi-overlays-datetime-corejs' );
	
	wp_enqueue_style( 'divi-overlays-admin' );
	wp_enqueue_script( 'divi-overlays-admin-functions' );
}
add_action('admin_enqueue_scripts', 'divi_overlay_high_priority_includes', '999');


/*===================================================================*/

// Save Meta Box Value //
function et_divi_overlay_settings_save_details( $post_id, $post ){
	global $pagenow;

	if ( 'post.php' != $pagenow ) return $post_id;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;
	
	$post_value = '';

	if ( isset( $_POST['et_pb_overlay_effect'] ) ) {
		update_post_meta( $post_id, '_et_pb_overlay_effect', sanitize_text_field( $_POST['et_pb_overlay_effect'] ) );
	} else {
		delete_post_meta( $post_id, '_et_pb_overlay_effect' );
	}
	if ( isset( $_POST['post_bg'] ) ) {
		update_post_meta( $post_id, 'post_overlay_bg_color', sanitize_text_field( $_POST['post_bg'] ) );
	}
	if ( isset( $_POST['post_font_color'] ) ) {
		update_post_meta( $post_id, 'post_overlay_font_color', sanitize_text_field( $_POST['post_font_color'] ) );
	}
	
	if ( isset( $_POST['post_do_preventscroll'] ) ) {
		
		$post_do_preventscroll = 1;
		
	} else {
		
		$post_do_preventscroll = 0;
	}
	update_post_meta( $post_id, 'post_do_preventscroll', $post_do_preventscroll );
	
	
	/* Display Locations */
	
	/* By Posts */
	if ( isset( $_POST['post_at_pages'] ) ) {
		
		$post_value = sanitize_text_field( $_POST['post_at_pages'] );
		update_post_meta( $post_id, 'do_at_pages', $post_value );
	}
	
	if ( $post_value == 'specific' ) {
		
		if ( isset( $_POST['post_at_pages_selected'] ) ) {
			update_post_meta( $post_id, 'do_at_pages_selected', $_POST['post_at_pages_selected'] );
		}
	}
	else {
		
		update_post_meta( $post_id, 'do_at_pages_selected', '' );
	}
		
	if ( isset( $_POST['post_at_exceptionpages_selected'] ) ) {
	
		update_post_meta( $post_id, 'do_at_pagesexception_selected', $_POST['post_at_exceptionpages_selected'] );
	}
	else {
		
		update_post_meta( $post_id, 'do_at_pagesexception_selected', '' );
	}
		
	/* By Categories */
	if ( isset( $_POST['category_at_categories'] ) ) {
		
		$category_at_categories = sanitize_text_field( $_POST['category_at_categories'] );
		update_post_meta( $post_id, 'category_at_categories', $category_at_categories );
	}
	
	if ( $category_at_categories == 'specific' ) {
		
		if ( isset( $_POST['category_at_categories_selected'] ) ) {
			update_post_meta( $post_id, 'category_at_categories_selected', $_POST['category_at_categories_selected'] );
		}
	}
	else {
		
		update_post_meta( $post_id, 'category_at_categories_selected', '' );
	}
	
	if ( isset( $_POST['category_at_exceptioncategories_selected'] ) ) {
	
		update_post_meta( $post_id, 'category_at_exceptioncategories_selected', $_POST['category_at_exceptioncategories_selected'] );
	}
	else {
		
		update_post_meta( $post_id, 'category_at_exceptioncategories_selected', '' );
	}
	
	/* By Tags */
	if ( isset( $_POST['tag_at_tags'] ) ) {
		
		$tag_at_tags = sanitize_text_field( $_POST['tag_at_tags'] );
		update_post_meta( $post_id, 'tag_at_tags', $tag_at_tags );
	}
	
	if ( $tag_at_tags == 'specific' ) {
		
		if ( isset( $_POST['tag_at_tags_selected'] ) ) {
			update_post_meta( $post_id, 'tag_at_tags_selected', $_POST['tag_at_tags_selected'] );
		}
	}
	else {
		
		update_post_meta( $post_id, 'tag_at_tags_selected', '' );
	}
	
	if ( isset( $_POST['tag_at_exceptiontags_selected'] ) ) {
	
		update_post_meta( $post_id, 'tag_at_exceptiontags_selected', $_POST['tag_at_exceptiontags_selected'] );
	}
	else {
		
		update_post_meta( $post_id, 'tag_at_exceptiontags_selected', '' );
	}
	
	/* By Archive */
	if ( isset( $_POST['do_displaylocations_archive'] ) ) {
		
		$do_displaylocations_archive = 1;
		
	} else {
		
		$do_displaylocations_archive = 0;
	}
	update_post_meta( $post_id, 'do_displaylocations_archive', $do_displaylocations_archive );
	
	
	/* Additional Settings */
	if ( isset( $_POST['post_css_selector'] ) ) {
		update_post_meta( $post_id, 'post_css_selector', sanitize_text_field( $_POST['post_css_selector'] ) );
	}
	
	if ( isset( $_POST['css_selector_at_pages'] ) ) {
		
		$post_value = sanitize_text_field( $_POST['css_selector_at_pages'] );
		update_post_meta( $post_id, 'css_selector_at_pages', $post_value );
	}
	
	if ( $post_value == 'specific' ) {
		
		if ( isset( $_POST['css_selector_at_pages_selected'] ) ) {
			update_post_meta( $post_id, 'css_selector_at_pages_selected', $_POST['css_selector_at_pages_selected'] );
		}
	}
	else {
		
		update_post_meta( $post_id, 'css_selector_at_pages_selected', '' );
	}
	
	if ( isset( $_POST['post_enableurltrigger'] ) ) {
		
		$post_enableurltrigger = 1;
		
		if ( isset( $_POST['post_enableurltrigger_pages'] ) ) {
			$post_value = sanitize_text_field( $_POST['post_enableurltrigger_pages'] );
			update_post_meta( $post_id, 'post_enableurltrigger_pages', $post_value );
		}
		
		if ( $post_value == 'specific' ) {
			
			if ( isset( $_POST['post_dolistpages'] ) ) {
				update_post_meta( $post_id, 'post_dolistpages', $_POST['post_dolistpages'] );
			}
		}
		else {
			
			update_post_meta( $post_id, 'post_dolistpages', '' );
		}
		
	} else {
		
		$post_enableurltrigger = 0;
	}
	update_post_meta( $post_id, 'post_enableurltrigger', $post_enableurltrigger );
	
	
	if ( isset( $_POST['do_enableajax'] ) ) {
		
		$do_enableajax = 1;
		
	} else {
		
		$do_enableajax = 0;
	}
	update_post_meta( $post_id, 'do_enableajax', $do_enableajax );
	
	
	if ( isset( $_POST['post_overlay_automatictrigger'] ) && $_POST['post_overlay_automatictrigger'] != '' ) {
		
		update_post_meta( $post_id, 'overlay_automatictrigger', sanitize_text_field( $_POST['post_overlay_automatictrigger'] ) );
	
		if ( isset( $_POST['post_at_timed'] ) ) {
			update_post_meta( $post_id, 'overlay_automatictrigger_timed_value', sanitize_text_field( $_POST['post_at_timed'] ) );
		}
		
		if ( isset( $_POST['post_at_scroll_from'] ) || isset( $_POST['post_at_scroll_to'] ) ) {
			update_post_meta( $post_id, 'overlay_automatictrigger_scroll_from_value', sanitize_text_field( $_POST['post_at_scroll_from'] ) );
			update_post_meta( $post_id, 'overlay_automatictrigger_scroll_to_value', sanitize_text_field( $_POST['post_at_scroll_to'] ) );
		}
		
		if ( isset( $_POST['post_at_disablemobile'] ) ) {
			
			$post_at_disablemobile = 1;
			
		} else {
			
			$post_at_disablemobile = 0;
		}
		
		if ( isset( $_POST['post_at_disabletablet'] ) ) {
			
			$post_at_disabletablet = 1;
			
		} else {
			
			$post_at_disabletablet = 0;
		}
		
		if ( isset( $_POST['post_at_disabledesktop'] ) ) {
			
			$post_at_disabledesktop = 1;
			
		} else {
			
			$post_at_disabledesktop = 0;
		}
		
		
		if ( isset( $_POST['post_at_onceperload'] ) ) {
			
			$post_at_onceperload = 1;
			
		} else {
			
			$post_at_onceperload = 0;
		}
		
		update_post_meta( $post_id, 'overlay_automatictrigger_onceperload', $post_at_onceperload );
		
		
	} else {
		
		update_post_meta( $post_id, 'overlay_automatictrigger', 0 );
		update_post_meta( $post_id, 'overlay_automatictrigger_onceperload', 0 );
		$post_at_disablemobile = 0;
		$post_at_disabletablet = 0;
		$post_at_disabledesktop = 0;
	}
	update_post_meta( $post_id, 'overlay_automatictrigger_disablemobile', $post_at_disablemobile );
	update_post_meta( $post_id, 'overlay_automatictrigger_disabletablet', $post_at_disabletablet );
	update_post_meta( $post_id, 'overlay_automatictrigger_disabledesktop', $post_at_disabledesktop );
	
	
	/* Close Button Customizations */
	if ( isset( $_POST['dov_closebtn_cookie'] ) ) {
		update_post_meta( $post_id, 'dov_closebtn_cookie', sanitize_text_field( $_POST['dov_closebtn_cookie'] ) );
	}
	
	/* Close Button Customizations */
	if ( isset( $_POST['post_do_hideclosebtn'] ) ) {
		
		$post_do_hideclosebtn = 1;
		
	} else {
		
		$post_do_hideclosebtn = 0;
	}
	update_post_meta( $post_id, 'post_do_hideclosebtn', $post_do_hideclosebtn );
	
	if ( isset( $_POST['post_do_customizeclosebtn'] ) ) {
		
		$post_do_customizeclosebtn = 1;
		
	} else {
		
		$post_do_customizeclosebtn = 0;
	}
	update_post_meta( $post_id, 'post_do_customizeclosebtn', $post_do_customizeclosebtn );
	
	if ( isset( $_POST['post_doclosebtn_text_color'] ) ) {
		update_post_meta( $post_id, 'post_doclosebtn_text_color', sanitize_text_field( $_POST['post_doclosebtn_text_color'] ) );
	}
	
	if ( isset( $_POST['post_doclosebtn_bg_color'] ) ) {
		update_post_meta( $post_id, 'post_doclosebtn_bg_color', sanitize_text_field( $_POST['post_doclosebtn_bg_color'] ) );
	}
	
	if ( isset( $_POST['post_doclosebtn_fontsize'] ) ) {
		update_post_meta( $post_id, 'post_doclosebtn_fontsize', sanitize_text_field( $_POST['post_doclosebtn_fontsize'] ) );
	}
	
	if ( isset( $_POST['post_doclosebtn_borderradius'] ) ) {
		update_post_meta( $post_id, 'post_doclosebtn_borderradius', sanitize_text_field( $_POST['post_doclosebtn_borderradius'] ) );
	}
	
	if ( isset( $_POST['post_doclosebtn_padding'] ) ) {
		update_post_meta( $post_id, 'post_doclosebtn_padding', sanitize_text_field( $_POST['post_doclosebtn_padding'] ) );
	}
	
	
	/* Save Scheduling */
	if ( isset( $_POST['do_enable_scheduling'] ) ) {
		
		$enable_scheduling = (int) $_POST['do_enable_scheduling'];
		
	} else {
		
		$enable_scheduling = 0;
	}
	update_post_meta( $post_id, 'do_enable_scheduling', $enable_scheduling );
	
	/* Save Scheduling */
	if ( $enable_scheduling ) {
		
		$dov_settings = get_option( 'dov_settings' );
		
		$timezone = DOV_SERVER_TIMEZONE;
		
		if ( isset( $dov_settings['dov_timezone'] ) ) {
			
			$timezone = $dov_settings['dov_timezone'];
		}
		
		if ( $enable_scheduling == 1 ) {
			
			if ( isset( $_POST['do_date_start'] ) ) {
				
				$date_string = doConvertDateToUTC( $_POST['do_date_start'], $timezone );
				
				update_post_meta( $post_id, 'do_date_start', sanitize_text_field( $date_string ) );
			}
			
			if ( isset( $_POST['do_date_end'] ) ) {
				
				$date_string = doConvertDateToUTC( $_POST['do_date_end'], $timezone );
				
				update_post_meta( $post_id, 'do_date_end', sanitize_text_field( $date_string ) );
			}
		}
		
		if ( $enable_scheduling == 2 ) {
			
			if ( isset( $_POST['do_time_start'] ) ) {
				
				$date_string = $_POST['do_time_start'];
				
				update_post_meta( $post_id, 'do_time_start', sanitize_text_field( $date_string ) );
			}
			
			if ( isset( $_POST['do_time_end'] ) ) {
				
				$date_string = $_POST['do_time_end'];
				
				update_post_meta( $post_id, 'do_time_end', sanitize_text_field( $date_string ) );
			}
			
			if ( isset( $_POST['divioverlays_scheduling_daysofweek'] ) ) {
			
				$daysofweek = array_map( 'sanitize_text_field', wp_unslash( $_POST['divioverlays_scheduling_daysofweek'] ) );
				
				// Reset all daysofweek values
				for( $a = 1; $a <= 7; $a++ ) {
					update_post_meta( $post_id, 'divioverlays_scheduling_daysofweek_' . $a, 0 );
				}
				
				foreach( $daysofweek as $day ) {
					update_post_meta( $post_id, 'divioverlays_scheduling_daysofweek_' . $day, 1);
				}
			}
		}
	}
	
}
add_action( 'save_post', 'et_divi_overlay_settings_save_details', 10, 2 );

function doConvertDateToUTC( $date = null, $timezone = DOV_SERVER_TIMEZONE, $format = DOV_SCHEDULING_DATETIME_FORMAT ) {
			
	if ( $date === null ) {
		
		return;
	}
	
	if ( !doValidateDate( $date, $format ) ) {
		
		return;
	}
	
	$date = new DateTime( $date, new DateTimeZone( $timezone ) );
	$date->setTimezone( new DateTimeZone( DOV_SERVER_TIMEZONE ) );
	$str_server_now = $date->format( $format );
	
	return $str_server_now;
}


function doConvertDateToUserTimezone( $date = null, $format = DOV_SCHEDULING_DATETIME_FORMAT ) {
			
	if ( $date === null ) {
		
		return;
	}
	
	if ( !doValidateDate( $date, $format ) ) {
		
		return;
	}
	
	$dov_settings = get_option( 'dov_settings' );
	
	$timezone = DOV_SERVER_TIMEZONE;
	
	if ( isset( $dov_settings['dov_timezone'] ) ) {
		
		$timezone = $dov_settings['dov_timezone'];
	}
	
	$date = new DateTime( $date, new DateTimeZone( DOV_SERVER_TIMEZONE ) );
	$date->setTimezone( new DateTimeZone( $timezone ) );
	$str_server_now = $date->format( $format );
	
	return $str_server_now;
}

function doValidateDate( $dateStr, $format ) {
			
	$date = DateTime::createFromFormat($format, $dateStr);
	return $date && ($date->format($format) === $dateStr);
}


add_action( 'widgets_init', 'theme_slug_widgets_init' );
function theme_slug_widgets_init() {
    register_sidebar( array(
        'name' => __( 'Divi Overlays: Global Overlays', 'theme-slug' ),
        'id' => 'custom_footer_sidebar',
        'description' => __( 'Use a Text Widget and paste the content shortcodes for any overlays you want to trigger from the main menu, the sidebar, or footer.', 'theme-slug' )
    ) );
}

add_action('wp_enqueue_scripts', 'fwds_scripts');
function fwds_scripts() {
	
	if ( is_divi_builder_enabled() ) { return; }
	
	wp_enqueue_script('jquery');
	wp_register_style('custom_style_css', plugins_url('overlay-effects/css/style.css', __FILE__));
    wp_enqueue_style('custom_style_css');
	wp_register_script('snap_svg_js', plugins_url('overlay-effects/js/snap.svg-min.js', __FILE__),array("jquery"));
	wp_enqueue_script('snap_svg_js');
	wp_register_script('modernizr_js', plugins_url('overlay-effects/js/modernizr.custom.js', __FILE__),array("jquery"));
    wp_enqueue_script('modernizr_js');
}

// Deprecated function - leave for backward compatibility
add_shortcode("overlay_content", "overlay_content_function");
function overlay_content_function($atts) {
	return '';
}

function is_divi_builder_enabled() {
	
	if ( isset( $_GET['et_fb'] ) ) {
		
		$divi_builder_enabled = htmlspecialchars( $_GET['et_fb'] );
		
		// is divi theme builder ?
		if ( $divi_builder_enabled === '1' ) {
			
			return TRUE;
		}
	}
	
	return FALSE;
}

function showOverlay( $overlay_id = NULL ) {
	
	if ( is_divi_builder_enabled() ) { return; }
	
	ob_start();
	
    if ( !is_numeric( $overlay_id ) )
        return NULL;
	
	$overlay_id = (int) $overlay_id;
    
	$post_data = get_post( $overlay_id );
	
	
	/* Scheduling */
	$enable_scheduling = get_post_meta( $post_data->ID, 'do_enable_scheduling' );
	
	if( !isset( $enable_scheduling[0] ) ) {
		
		$enable_scheduling[0] = 0;
	}
	
	$enable_scheduling = (int) $enable_scheduling[0];
	
	if ( $enable_scheduling ) {
		
		$timezone = DOV_SERVER_TIMEZONE;
		
		$divioverlays_settings = get_option( 'dov_settings' );
		
		if ( isset( $divioverlays_settings['dov_timezone'] ) ) {
			
			$timezone = $divioverlays_settings['dov_timezone'];
		}
		
		$timezone = new DateTimeZone( $timezone );
		
		$date_now = new DateTime( 'now', $timezone );
		
		// Start & End Time
		if ( $enable_scheduling == 1 ) {
			
			$date_start = get_post_meta( $post_data->ID, 'do_date_start', true );
			$date_end = get_post_meta( $post_data->ID, 'do_date_end', true );
			
			$date_start = doConvertDateToUserTimezone( $date_start );
			$date_start = new DateTime( $date_start, $timezone );
			
			if ( $date_start >= $date_now ) {
				
				return;
			}
			
			if ( $date_end != '' ) {
			
				$date_end = doConvertDateToUserTimezone( $date_end );
				$date_end = new DateTime( $date_end, $timezone );
				
				if ( $date_end <= $date_now ) {
					
					return;
				}
			}
		}
		
		
		// Recurring Scheduling
		if ( $enable_scheduling == 2 ) {
			
			$wNum = $date_now->format( 'N' );
			
			$is_today = get_post_meta( $post_data->ID, 'divioverlays_scheduling_daysofweek_' . $wNum );
			
			if ( isset( $is_today[0] ) && $is_today[0] == 1 ) {
				
				$is_today = $is_today[0];
				
				$time_start = get_post_meta( $post_data->ID, 'do_time_start', true );
				$time_end = get_post_meta( $post_data->ID, 'do_time_end', true );
				$schedule_start = null;
				$schedule_end = null;
				
				if ( $time_start != '' ) {
					
					$time_start_24 = date( 'H:i', strtotime( $time_start ) );
					$time_start_24 = explode( ':', $time_start_24 );
					$time_start_now = new DateTime( 'now', $timezone );
					$schedule_start = $time_start_now->setTime( $time_start_24[0], $time_start_24[1], 0 );
				}
				
				if ( $time_end != '' ) {
					
					$time_end_24 = date( 'H:i', strtotime( $time_end ) );
					$time_end_24 = explode( ':', $time_end_24 );
					$time_end_now = new DateTime( 'now', $timezone );
					$schedule_end = $time_end_now->setTime( $time_end_24[0], $time_end_24[1], 0 );
				}
				
				if ( ( $time_start != '' && $time_end != '' && $schedule_start >= $date_now && $schedule_end > $date_now )
					|| ( $time_start != '' && $time_end != '' && $schedule_start <= $date_now && $schedule_end < $date_now )
					|| ( $time_start != '' && $time_end == '' && $schedule_start <= $date_now )
					|| ( $time_start == '' && $time_end != '' && $schedule_end < $date_now )
					) {
					
					return;
				}
			} else {
				
				return;
			}
		}
	}
	/* End Scheduling */
	
	
	$overlay_effect = get_post_meta($post_data->ID,'_et_pb_overlay_effect',true);
	
	if ( $overlay_effect == '' ) {
		
		$overlay_effect = 'overlay-hugeinc';
	}
	
	$bgcolor = get_post_meta( $post_data->ID, 'post_overlay_bg_color', true );
	$fontcolor = get_post_meta( $post_data->ID, 'post_overlay_font_color', true );
	
	$preventscroll = get_post_meta( $post_data->ID, 'post_do_preventscroll' );
	if ( isset( $preventscroll[0] ) ) {
		
		$preventscroll = $preventscroll[0];
		
	} else {
		
		$preventscroll = 0;
	}
	
	$hideclosebtn = get_post_meta( $post_data->ID, 'post_do_hideclosebtn' );
	if ( isset( $hideclosebtn[0] ) ) {
		
		$hideclosebtn = $hideclosebtn[0];
		
	} else {
		
		$hideclosebtn = 0;
	}
	
	$data_path_to = null;
	$svg = null;
	
	if ( $overlay_effect == 'overlay-cornershape' ) {
		
		$data_path_to = 'data-path-to = "m 0,0 1439.999975,0 0,805.99999 -1439.999975,0 z"';
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 1440 806" preserveAspectRatio="none">
				<path class="overlay-path" d="m 0,0 1439.999975,0 0,805.99999 0,-805.99999 z"/>
			</svg>';
	}
	if ( $overlay_effect == 'overlay-boxes' ) {
		
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="101%" viewBox="0 0 1440 806" preserveAspectRatio="none">
				<path d="m0.005959,200.364029l207.551124,0l0,204.342453l-207.551124,0l0,-204.342453z"/>
				<path d="m0.005959,400.45401l207.551124,0l0,204.342499l-207.551124,0l0,-204.342499z"/>
				<path d="m0.005959,600.544067l207.551124,0l0,204.342468l-207.551124,0l0,-204.342468z"/>
				<path d="m205.752151,-0.36l207.551163,0l0,204.342437l-207.551163,0l0,-204.342437z"/>
				<path d="m204.744629,200.364029l207.551147,0l0,204.342453l-207.551147,0l0,-204.342453z"/>
				<path d="m204.744629,400.45401l207.551147,0l0,204.342499l-207.551147,0l0,-204.342499z"/>
				<path d="m204.744629,600.544067l207.551147,0l0,204.342468l-207.551147,0l0,-204.342468z"/>
				<path d="m410.416046,-0.36l207.551117,0l0,204.342437l-207.551117,0l0,-204.342437z"/>
				<path d="m410.416046,200.364029l207.551117,0l0,204.342453l-207.551117,0l0,-204.342453z"/>
				<path d="m410.416046,400.45401l207.551117,0l0,204.342499l-207.551117,0l0,-204.342499z"/>
				<path d="m410.416046,600.544067l207.551117,0l0,204.342468l-207.551117,0l0,-204.342468z"/>
				<path d="m616.087402,-0.36l207.551086,0l0,204.342437l-207.551086,0l0,-204.342437z"/>
				<path d="m616.087402,200.364029l207.551086,0l0,204.342453l-207.551086,0l0,-204.342453z"/>
				<path d="m616.087402,400.45401l207.551086,0l0,204.342499l-207.551086,0l0,-204.342499z"/>
				<path d="m616.087402,600.544067l207.551086,0l0,204.342468l-207.551086,0l0,-204.342468z"/>
				<path d="m821.748718,-0.36l207.550964,0l0,204.342437l-207.550964,0l0,-204.342437z"/>
				<path d="m821.748718,200.364029l207.550964,0l0,204.342453l-207.550964,0l0,-204.342453z"/>
				<path d="m821.748718,400.45401l207.550964,0l0,204.342499l-207.550964,0l0,-204.342499z"/>
				<path d="m821.748718,600.544067l207.550964,0l0,204.342468l-207.550964,0l0,-204.342468z"/>
				<path d="m1027.203979,-0.36l207.550903,0l0,204.342437l-207.550903,0l0,-204.342437z"/>
				<path d="m1027.203979,200.364029l207.550903,0l0,204.342453l-207.550903,0l0,-204.342453z"/>
				<path d="m1027.203979,400.45401l207.550903,0l0,204.342499l-207.550903,0l0,-204.342499z"/>
				<path d="m1027.203979,600.544067l207.550903,0l0,204.342468l-207.550903,0l0,-204.342468z"/>
				<path d="m1232.659302,-0.36l207.551147,0l0,204.342437l-207.551147,0l0,-204.342437z"/>
				<path d="m1232.659302,200.364029l207.551147,0l0,204.342453l-207.551147,0l0,-204.342453z"/>
				<path d="m1232.659302,400.45401l207.551147,0l0,204.342499l-207.551147,0l0,-204.342499z"/>
				<path d="m1232.659302,600.544067l207.551147,0l0,204.342468l-207.551147,0l0,-204.342468z"/>
				<path d="m-0.791443,-0.360001l207.551163,0l0,204.342438l-207.551163,0l0,-204.342438z"/>
			</svg>';
	}
	
	if ( $overlay_effect == 'overlay-genie' ) {
		
		$data_path_to = 'data-steps = "m 701.56545,809.01175 35.16718,0 0,19.68384 -35.16718,0 z;m 698.9986,728.03569 41.23353,0 -3.41953,77.8735 -34.98557,0 z;m 687.08153,513.78234 53.1506,0 C 738.0505,683.9161 737.86917,503.34193 737.27015,806 l -35.90067,0 c -7.82727,-276.34892 -2.06916,-72.79261 -14.28795,-292.21766 z;m 403.87105,257.94772 566.31246,2.93091 C 923.38284,513.78233 738.73561,372.23931 737.27015,806 l -35.90067,0 C 701.32034,404.49318 455.17312,480.07689 403.87105,257.94772 z;M 51.871052,165.94772 1362.1835,168.87863 C 1171.3828,653.78233 738.73561,372.23931 737.27015,806 l -35.90067,0 C 701.32034,404.49318 31.173122,513.78234 51.871052,165.94772 z;m 52,26 1364,4 c -12.8007,666.9037 -273.2644,483.78234 -322.7299,776 l -633.90062,0 C 359.32034,432.49318 -6.6979288,733.83462 52,26 z;m 0,0 1439.999975,0 0,805.99999 -1439.999975,0 z"';
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 1440 806" preserveAspectRatio="none">
				<path class="overlay-path" d="m 701.56545,809.01175 35.16718,0 0,19.68384 -35.16718,0 z"/>
			</svg>';
	}
	
		$customizeclosebtn = get_post_meta( $post_data->ID, 'post_do_customizeclosebtn' );
		if( !isset( $customizeclosebtn[0] ) ) {
			
			$customizeclosebtn[0] = '0';
		}
		
		$close_cookie = get_post_meta( $post_data->ID, 'dov_closebtn_cookie', true );
		if( !isset( $close_cookie ) ) {
			
			$close_cookie = 1;
		}
		
		$enableajax = get_post_meta( $post_data->ID, 'do_enableajax', true );
		if( !isset( $enableajax ) ) {
			
			$enableajax = 1;
		}
		
		if ( $enableajax && !$ajax ) {
			
			$output = '';
			
		} else {
			
			$output = apply_filters( 'the_content', $post_data->post_content );
			
			// Builder automatically adds `#et-boc` on selector on non official post type
			// Avoid having 2 elements with the same id in the same page
			$output = str_replace( 'id="et-boc"', '', $output );
			
			// Monarch fix: Remove Divi Builder main section class and add it later with JS
			$output = str_replace( 'et_pb_section ', 'dov_dv_section ', $output );
		}
		
		if ( !$ajax ) {
	?>
	<div id="divi-overlay-container-<?php echo $overlay_id;?>" class="overlay-container">
		<div id="overlay-<?php echo $post_data->ID;?>" class="overlay <?php echo $overlay_effect;?>" <?php echo $data_path_to;?> 
		data-bgcolor="<?php echo $bgcolor;?>" data-fontcolor="<?php echo $fontcolor;?>" data-preventscroll="<?php print $preventscroll ?>" 
		data-scrolltop="" data-cookie="<?php print $close_cookie ?>" data-enableajax="<?php print $enableajax ?>" data-contentloaded="0">
			
			<?php echo $svg; ?>
			
			<?php if ( $hideclosebtn == 0 ) { ?>
			<button type="button" class="overlay-close overlay-customclose-btn-<?php echo $overlay_id ?>"><span class="<?php if ( $customizeclosebtn[0] == 1 ) { ?>custom_btn<?php } ?>">&times;</span></button>
			<?php } ?>
			
			<div class="entry-content">
			<?php 
		}
				
				// is divi theme builder ?
				if ( is_singular() && 'on' === get_post_meta( $post_data->ID, '_et_pb_use_builder', true ) ) {
					
					print $output;
					
				} else {
					?>
					<div class="et_section_regular">
						<div class="et_pb_row et_pb_row_0">
							<div class="et_pb_column et_pb_column_4_4  et_pb_column_0">
								<?php print $output; ?>
							</div>
						</div>
					</div>
					<?php
				}
				
		if ( !$ajax ) {
			
			?>
			</div>
			
		</div>
	</div>
	<?php 
		}
		
	return ob_get_clean();
}


function setHeightWidthSrc($s, $width, $height)
{
  return preg_replace(
    '@^<iframe\s*title="(.*)"\s*width="(.*)"\s*height="(.*)"\s*src="(.*?)"\s*(.*?)</iframe>$@s',
    '<iframe title="\1" width="' . $width . '" height="' . $height . '" src="\4?wmode=transparent" \5</iframe>',
    $s
  );
}

function formatContent( $pee, $br = true ) {
	
	$pre_tags = array();

	if ( trim($pee) === '' )
		return '';

	/*
	 * Pre tags shouldn't be touched by autop.
	 * Replace pre tags with placeholders and bring them back after autop.
	 */
	if ( strpos($pee, '<pre') !== false ) {
		$pee_parts = explode( '</pre>', $pee );
		$last_pee = array_pop($pee_parts);
		$pee = '';
		$i = 0;

		foreach ( $pee_parts as $pee_part ) {
			$start = strpos($pee_part, '<pre');

			// Malformed html?
			if ( $start === false ) {
				$pee .= $pee_part;
				continue;
			}

			$name = "<pre wp-pre-tag-$i></pre>";
			$pre_tags[$name] = substr( $pee_part, $start ) . '</pre>';

			$pee .= substr( $pee_part, 0, $start ) . $name;
			$i++;
		}

		$pee .= $last_pee;
	}
	// Change multiple <br>s into two line breaks, which will turn into paragraphs.
	$pee = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee);
	
	$pee = str_replace(array("\r\n\r\n", "\r\r"), '<br /><br />', $pee);

	$allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

	// Add a double line break above block-level opening tags.
	$pee = preg_replace('!(<' . $allblocks . '[\s/>])!', "\n\n$1", $pee);

	// Add a double line break below block-level closing tags.
	$pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);

	// Standardize newline characters to "\n".
	$pee = str_replace(array("\r\n", "\r"), "\n", $pee);

	// Find newlines in all elements and add placeholders.
	$pee = wp_replace_in_html_tags( $pee, array( "\n" => " <!-- wpnl --> " ) );

	// Collapse line breaks before and after <option> elements so they don't get autop'd.
	if ( strpos( $pee, '<option' ) !== false ) {
		$pee = preg_replace( '|\s*<option|', '<option', $pee );
		$pee = preg_replace( '|</option>\s*|', '</option>', $pee );
	}

	/*
	 * Collapse line breaks inside <object> elements, before <param> and <embed> elements
	 * so they don't get autop'd.
	 */
	if ( strpos( $pee, '</object>' ) !== false ) {
		$pee = preg_replace( '|(<object[^>]*>)\s*|', '$1', $pee );
		$pee = preg_replace( '|\s*</object>|', '</object>', $pee );
		$pee = preg_replace( '%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee );
	}

	/*
	 * Collapse line breaks inside <audio> and <video> elements,
	 * before and after <source> and <track> elements.
	 */
	if ( strpos( $pee, '<source' ) !== false || strpos( $pee, '<track' ) !== false ) {
		$pee = preg_replace( '%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee );
		$pee = preg_replace( '%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee );
		$pee = preg_replace( '%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee );
	}

	// Collapse line breaks before and after <figcaption> elements.
	if ( strpos( $pee, '<figcaption' ) !== false ) {
		$pee = preg_replace( '|\s*(<figcaption[^>]*>)|', '$1', $pee );
		$pee = preg_replace( '|</figcaption>\s*|', '</figcaption>', $pee );
	}

	// Remove more than two contiguous line breaks.
	$pee = preg_replace("/\n\n+/", "\n\n", $pee);

	// Split up the contents into an array of strings, separated by double line breaks.
	$pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);

	// Reset $pee prior to rebuilding.
	$pee = '';

	// Rebuild the content as a string, wrapping every bit with a <p>.
	foreach ( $pees as $tinkle ) {
		$pee .= trim($tinkle, "\n") . "\n";
	}

	// Under certain strange conditions it could create a P of entirely whitespace.
	$pee = preg_replace('|<p>\s*</p>|', '', $pee);

	// Add a closing <p> inside <div>, <address>, or <form> tag if missing.
	$pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);

	// If an opening or closing block element tag is wrapped in a <p>, unwrap it.
	$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);

	// In some cases <li> may get wrapped in <p>, fix them.
	$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee);

	// If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
	$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
	$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);

	// If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
	$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);

	// If an opening or closing block element tag is followed by a closing <p> tag, remove it.
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);

	// Optionally insert line breaks.
	if ( $br ) {
		// Replace newlines that shouldn't be touched with a placeholder.
		$pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee);

		// Normalize <br>
		$pee = str_replace( array( '<br>', '<br/>' ), '<br />', $pee );
		
		// Replace any new line characters that aren't preceded by a <br /> with a <br />.
		$pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);

		// Replace newline placeholders with newlines.
		$pee = str_replace('<WPPreserveNewline />', "\n", $pee);
	}
	
	// If a <br /> tag is after an opening or closing block tag, remove it.
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
	
	// If 2 <br /><br /> tags are after an opening or closing block tag, remove them.
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br /><br />!', "$1", $pee);

	// If a <br /> tag is before a subset of opening or closing block tags, remove it.
	$pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol|span|input|label|fieldset|legend|optgroup|option|select|form|textarea|button|datalist|keygen|output)[^>]*>)!', '$1', $pee);
	
	// If 2 <br /><br /> tags are before a subset of opening or closing block tags, remove them.
	$pee = preg_replace('!<br /><br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol|span|input|label|fieldset|legend|optgroup|option|select|form|textarea|button|datalist|keygen|output)[^>]*>)!', '$1', $pee);
	
	$pee = preg_replace( "|\n</p>$|", '</p>', $pee );

	// Replace placeholder <pre> tags with their original content.
	if ( !empty($pre_tags) )
		$pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);

	// Restore newlines in all elements.
	if ( false !== strpos( $pee, '<!-- wpnl -->' ) ) {
		$pee = str_replace( array( ' <!-- wpnl --> ', '<!-- wpnl -->' ), "\n", $pee );
	}

	return $pee;
}

add_action( 'init', 'divioverlays_refreshdivi', 1 );
function divioverlays_refreshdivi() {
	
	if ( isset( $_GET['divioverlays_id'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'divioverlays_getcontent' ) {
		
		check_ajax_referer( 'divilife_divioverlays', 'security' );
		
		// Disable "Static CSS File Generation" temporarily in case it's activated
		et_update_option( 'et_pb_static_css_file', 'off' );
	}
}


add_action('wp_footer','custom_js_function');
function custom_js_function() {
	
	if ( is_divi_builder_enabled() ) { return; }
	
	print '<style id="divioverlay-styles" type="text/css"></style>';
	print '<div id="divioverlay-links"></div>';
	print '<div id="sidebar-overlay">';
	
	/* Search Divi Overlay in current post */
	global $post;
	$post_content = $post->post_content;
	$matches = array();
	$pattern = '/id="(.*?overlay_[0-9]+)"/';
	preg_match_all($pattern, $post_content, $matches);
	
	$overlays_overlay_ = $matches[1];
	
	$matches = array();
	$pattern = '/id="(.*?overlay_unique_id_[0-9]+)"/';
	preg_match_all($pattern, $post_content, $matches);
	
	$overlays_overlay_unique_id_ = $matches[1];
	
	$matches = array();
	$pattern = '/class="(.*?overlay\-[0-9]+)"/';
	preg_match_all($pattern, $post_content, $matches);
	
	$overlays_class_overlay = $matches[1];
	
	$overlays_in_post = $overlays_overlay_ + $overlays_overlay_unique_id_ + $overlays_class_overlay;
	
	$overlays_in_post = array_filter( array_map( "prepareOverlays", $overlays_in_post ) );
	
	if ( is_array( $overlays_in_post ) && count( $overlays_in_post ) > 0 ) {
		
		$overlays_in_post = array_flip( $overlays_in_post );
		
	}
	
	
	/* Search Divi Overlay in active menus */
	$theme_locations = get_nav_menu_locations();
	
	$overlays_in_menus = array();
	
	if ( is_array( $theme_locations ) && count( $theme_locations ) > 0 ) {
		
		$overlays_in_menus = array();
		
		foreach( $theme_locations as $theme_location => $theme_location_value ) {
			
			$menu = get_term( $theme_locations[$theme_location], 'nav_menu' );
			
			// menu exists?
			if( !is_wp_error($menu) ) {
				
				$menu_items = wp_get_nav_menu_items($menu->term_id);
				
				foreach ( (array) $menu_items as $key => $menu_item ) {
					
					$url = $menu_item->url;
					
					$extract_id = prepareOverlays( $url );
					
					if ( $extract_id ) {
						
						$overlays_in_menus[ $extract_id ] = 1;
					}
					
					/* Search Divi Overlay in menu classes */
					if ( count( $menu_item->classes ) > 0 && $menu_item->classes[0] != '' ) {
						
						foreach ( $menu_item->classes as $key => $class ) {
							
							if ( $class != '' ) {
								
								$extract_id = prepareOverlays( $class );
								
								if ( $extract_id ) {
								
									$overlays_in_menus[ $extract_id ] = 1;
								}
							}
						}
					}
					
					/* Search Divi Overlay in Link Relationship (XFN) */
					if ( !empty( $menu_item->xfn ) ) {
						
						$extract_id = prepareOverlays( $menu_item->xfn );
						
						if ( $extract_id ) {
						
							$overlays_in_menus[ $extract_id ] = 1;
						}
					}
				}
			}
		}
	}
	
	$overlays_in_menus = array_filter( $overlays_in_menus );
	
	
	/* Search CSS Triggers in all Divi Overlays */
	global $wp_query;
	
	$args = array(
		'meta_key'   => 'post_css_selector',
		'meta_value' => '',
		'meta_compare' => '!=',
		'post_type' => 'divi_overlay',
		'cache_results'  => false,
		'posts_per_page' => 20
	);
	$query = new WP_Query( $args );
	
	$posts = $query->get_posts();
	
	$overlays_with_css_trigger = array();
	
	if ( isset( $posts[0] ) ) {
		
		print '<script type="text/javascript">var overlays_with_css_trigger = {';
		
		foreach( $posts as $dv_post ) {
			
			$post_id = $dv_post->ID;
			
			$get_css_selector = get_post_meta( $post_id, 'post_css_selector' );
				
			$css_selector = $get_css_selector[0];
			
			if ( $css_selector != '' ) {
				
				print '\'' . $post_id . '\': \'' . $css_selector . '\',';
				
				$overlays_with_css_trigger[ $post_id ] = $css_selector;
			}
		}
		
		print '};</script>';
	}
	
	
	/* Search URL Triggers in all Divi Overlays */
	$args = array(
		'meta_key'   => 'post_enableurltrigger',
		'meta_value' => '1',
		'meta_compare' => '=',
		'post_type' => 'divi_overlay',
		'cache_results'  => false
	);
	$query = new WP_Query( $args );
	
	$posts = $query->get_posts();
	
	$overlays_with_url_trigger = array();
	
	if ( isset( $posts[0] ) ) {
		
		$display_in_current = false;
		
		foreach( $posts as $dv_post ) {
			
			$post_id = $dv_post->ID;
			
			$overlays_with_url_trigger[ $post_id ] = 1;
		}
	}
	$overlays_with_url_trigger = array_filter( $overlays_with_url_trigger );
	
	
	/* Search Automatic Triggers in all Divi Overlays */
	
	// Server-Side Device Detection with Browscap
	require_once( plugin_dir_path( __FILE__ ) . 'php-libraries/Browscap/Browscap.php' );
	$browscap = new Browscap( plugin_dir_path( __FILE__ ) . '/php-libraries/Browscap/Cache/' );
	$browscap->doAutoUpdate = false;
	$current_browser = $browscap->getBrowser();
	
	$isMobileDevice = $current_browser->isMobileDevice;
	$isTabletDevice = $current_browser->isTablet;
	
	$overlays_with_automatic_trigger = array();
	
	$args = array(
		'meta_key'   => 'overlay_automatictrigger',
		'meta_value' => '',
		'meta_compare' => '!=',
		'post_type' => 'divi_overlay',
		'cache_results'  => false
	);
	$query = new WP_Query( $args );
	
	$posts = $query->get_posts();
	
	if ( isset( $posts[0] ) ) {
		
		print '<script type="text/javascript">var overlays_with_automatic_trigger = {';
		
		foreach( $posts as $dv_post ) {
			
			$post_id = $dv_post->ID;
			
			$at_disablemobile = get_post_meta( $post_id, 'overlay_automatictrigger_disablemobile' );
			$at_disabletablet = get_post_meta( $post_id, 'overlay_automatictrigger_disabletablet' );
			$at_disabledesktop = get_post_meta( $post_id, 'overlay_automatictrigger_disabledesktop' );
			$onceperload = get_post_meta( $post_id, 'overlay_automatictrigger_onceperload', true );
			
			if ( isset( $onceperload[0] ) ) {
				
				$onceperload = $onceperload[0];
				
			} else {
				
				$onceperload = 1;
			}
			
			if ( isset( $at_disablemobile[0] ) ) {
				
				$at_disablemobile = $at_disablemobile[0];
				
			} else {
				
				$at_disablemobile = 1;
			}
			
			if ( isset( $at_disabletablet[0] ) ) {
				
				$at_disabletablet = $at_disabletablet[0];
				
			} else {
				
				$at_disabletablet = 0;
			}
			
			if ( isset( $at_disabledesktop[0] ) ) {
				
				$at_disabledesktop = $at_disabledesktop[0];
				
			} else {
				
				$at_disabledesktop = 0;
			}
			
			$printSettings = 1;
			if ( $at_disablemobile && $isMobileDevice ) {
				
				$printSettings = 0;
			}
			
			if ( $at_disablemobile && $isMobileDevice && $isTabletDevice ) {
				
				$printSettings = 1;
			}
			
			if ( $at_disabletablet && $isTabletDevice ) {
				
				$printSettings = 0;
			}
			
			if ( $at_disabledesktop && !$isMobileDevice && !$isTabletDevice ) {
				
				$printSettings = 0;
			}
			
			if ( $printSettings ) {
				
				$at_type = get_post_meta( $post_id, 'overlay_automatictrigger', true );
				$at_timed = get_post_meta( $post_id, 'overlay_automatictrigger_timed_value', true );
				$at_scroll_from = get_post_meta( $post_id, 'overlay_automatictrigger_scroll_from_value', true );
				$at_scroll_to = get_post_meta( $post_id, 'overlay_automatictrigger_scroll_to_value', true );
				
				if ( $at_type != '' ) {
					
					switch ( $at_type ) {
						
						case 'overlay-timed':
							$at_value = $at_timed;
						break;
						
						case 'overlay-scroll':
							$at_value = $at_scroll_from . ':' . $at_scroll_to;
						break;
						
						default:
							$at_value = $at_type;
					}
					
					$at_settings = json_encode( array( 'at_type' => $at_type, 'at_value' => $at_value, 'at_onceperload' => $onceperload ) );
					
					print '\'' . $post_id . '\': \'' . $at_settings . '\',';
					
					$overlays_with_automatic_trigger[ $post_id ] = $at_type;
				}
			}
		}
		
		print '};</script>';
	}
	$overlays_with_automatic_trigger = array_filter( $overlays_with_automatic_trigger );
	
	
	/* Search Divi Overlays with Custom Close Buttons */
	$args = array(
		'meta_key'   => 'post_do_customizeclosebtn',
		'meta_value' => '',
		'meta_compare' => '!=',
		'post_type' => 'divi_overlay',
		'cache_results'  => false
	);
	$query = new WP_Query( $args );
	
	$posts = $query->get_posts();
	
	if ( isset( $posts[0] ) ) {
		
		print '<style type="text/css">';
		
		foreach( $posts as $dv_post ) {
			
			$post_id = $dv_post->ID;
			
			$cbc_textcolor = get_post_meta( $post_id, 'post_doclosebtn_text_color', true );
			$cbc_bgcolor = get_post_meta( $post_id, 'post_doclosebtn_bg_color', true );
			$cbc_fontsize = get_post_meta( $post_id, 'post_doclosebtn_fontsize', true );
			$cbc_borderradius = get_post_meta( $post_id, 'post_doclosebtn_borderradius', true );
			$cbc_padding = get_post_meta( $post_id, 'post_doclosebtn_padding', true );
			
			$customizeclosebtn = get_post_meta( $post_id, 'post_do_customizeclosebtn' );
			if ( isset( $customizeclosebtn[0] ) ) {
				
				$customizeclosebtn = $customizeclosebtn[0];
				
			} else {
				
				continue;
			}
			
			if ( $customizeclosebtn ) {
				
				print '
				.overlay-customclose-btn-' . $post_id . ' {
					color:' . $cbc_textcolor . ' !important;
					background-color:' . $cbc_bgcolor . ' !important;
					font-size:' . $cbc_fontsize . 'px !important;
					padding:' . $cbc_padding . 'px !important;
					-moz-border-radius:' . $cbc_borderradius . '% !important;
					-webkit-border-radius:' . $cbc_borderradius . '% !important;
					-khtml-border-radius:' . $cbc_borderradius . '% !important;
					border-radius:' . $cbc_borderradius . '% !important;
				}
				';
			}
		}
		
		print '</style>';
	}
	
	
	/* Ignore repeated ids and print overlays */
	$overlays = $overlays_in_post + $overlays_in_menus + $overlays_with_css_trigger + $overlays_with_url_trigger + $overlays_with_automatic_trigger;
	
	if ( is_array( $overlays ) && count( $overlays ) > 0 ) {
		
		global $post;
		
		$display_in_current = false;
		
		$current_post_id = (int) get_option( 'page_on_front' );
		
		$is_home = is_home();
		
		if ( !$is_home ) {
			
			if ( !is_archive() ) {
				
				$current_post_id = get_the_ID();
			}
			else {
				
				$current_post_id = 0;
			}
		}
		
		if ( is_category() ) {
			
			$current_category_id = (int) get_queried_object_id();
		}
		else {
			
			$current_category_id = 0;
		}
		
		if ( is_tag() ) {
			
			$current_tag_id = (int) get_queried_object_id();
		}
		else {
			
			$current_tag_id = 0;
		}
		
		
		foreach( $overlays as $overlay_id => $idx ) {
			
			if ( get_post_status ( $overlay_id ) == 'publish' ) {
				
				$display_on_archive = get_post_meta( $overlay_id, 'do_displaylocations_archive', true );
				
				if ( isset( $display_on_archive[0] ) ) {
					
					$display_on_archive = (int) $display_on_archive[0];
					
				} else {
					
					$display_on_archive = 0;
				}
			
				// Display By Post
				if ( ( is_page() || is_single() ) ) {
				
					$at_pages = get_post_meta( $overlay_id, 'do_at_pages' );
					
					$display_in_posts = ( !isset( $at_pages[0] ) ) ? 'all' : $at_pages[0];
					
					if ( $display_in_posts == 'specific' ) {
						
						$display_in_current = false;
						
						$in_posts = array_filter( get_post_meta( $overlay_id, 'do_at_pages_selected' ) );
						
						if ( isset ( $in_posts[0] ) ) {
							
							foreach( $in_posts[0] as $in_post => $the_id ) {
								
								if ( $the_id == $current_post_id ) {
									
									$display_in_current = true;
									
									break;
								}
							}
						}
					}
					
					if ( $display_in_posts == 'all' ) {
						
						$display_in_current = true;
						
						$except_in_posts = array_filter( get_post_meta( $overlay_id, 'do_at_pagesexception_selected' ) );
						
						if ( isset ( $except_in_posts[0] ) ) {
							
							foreach( $except_in_posts[0] as $in_post => $the_id ) {
								
								if ( $the_id == $current_post_id ) {
									
									$display_in_current = false;
									
									break;
								}
							}
						}
					}
				}
			
				// Display By Category
				if ( is_category() ) {
					
					$category_at_categories = get_post_meta( $overlay_id, 'category_at_categories' );
					
					$display_in_categories = ( !isset( $category_at_categories[0] ) ) ? 'all' : $category_at_categories[0];
					
					if ( $display_in_categories == 'specific' ) {
						
						$display_in_current = false;
						
						$in_categories = get_post_meta( $overlay_id, 'category_at_categories_selected' );
						
						if ( isset ( $in_categories[0] ) ) {
							
							foreach( $in_categories[0] as $in_category => $the_id ) {
								
								if ( $the_id == $current_category_id ) {
									
									$display_in_current = true;
									
									break;
								}
							}
						}
					}
					
					if ( $display_in_categories == 'all' ) {
						
						$display_in_current = true;
						
						$except_in_categories = get_post_meta( $overlay_id, 'category_at_exceptioncategories_selected' );
						
						if ( isset ( $except_in_categories[0] ) ) {
							
							foreach( $except_in_categories[0] as $in_category => $the_id ) {
								
								if ( $the_id == $current_category_id ) {
									
									$display_in_current = false;
									
									break;
								}
							}
						}
					}
				}
				
				// Display By Tag
				if ( is_tag() ) {
					
					$tag_at_tags = get_post_meta( $overlay_id, 'tag_at_tags' );
					
					$display_in_tags = ( !isset( $tag_at_tags[0] ) ) ? 'all' : $tag_at_tags[0];
					
					if ( $display_in_tags == 'specific' ) {
						
						$display_in_current = false;
						
						$in_tags = get_post_meta( $overlay_id, 'tag_at_tags_selected' );
						
						if ( isset ( $in_tags[0] ) ) {
							
							foreach( $in_tags[0] as $in_tag => $the_id ) {
								
								if ( $the_id == $current_tag_id ) {
									
									$display_in_current = true;
									
									break;
								}
							}
						}
					}
					
					if ( $display_in_tags == 'all' ) {
						
						$display_in_current = true;
						
						$except_in_tags = get_post_meta( $overlay_id, 'tag_at_exceptiontags_selected' );
						
						if ( isset ( $except_in_tags[0] ) ) {
							
							foreach( $except_in_tags[0] as $in_tag => $the_id ) {
								
								if ( $the_id == $current_tag_id ) {
									
									$display_in_current = false;
									
									break;
								}
							}
						}
					}
				}
				
				
				if ( is_archive() && $display_on_archive ) {
					
					$display_in_current = true;
				}
				
				
				if ( $display_in_current ) {
				
					print showOverlay( $overlay_id );
				}
			}
		}
	}
	
	print '</div>';
	
	?>
	<script type="text/javascript">
	var divioverlays_ajaxurl = "<?php print esc_url( home_url( '/' ) ); ?>"
	, divioverlays_us = "<?php print wp_create_nonce( 'divilife_divioverlays' ) ?>"
	, divioverlays_loadingimg = "<?php print plugins_url( '/', __FILE__ ) . 'overlay-effects/img/divilife-loader.svg' ?>";
    </script>
	<?php
 
	wp_register_script('exit-intent', plugins_url( 'overlay-effects/js/jquery.exitintent.js', __FILE__),array("jquery"));
	wp_enqueue_script('exit-intent');
	wp_register_script('divi-custom-js', plugins_url('overlay-effects/js/custom.js', __FILE__), array("jquery"), '', true);
    wp_enqueue_script('divi-custom-js');
}


add_action( 'wp_footer', 'divioverlays_getcontent' );
function divioverlays_getcontent() {
	
	if ( isset( $_GET['divioverlays_id'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'divioverlays_getcontent' ) {
		
		check_ajax_referer( 'divilife_divioverlays', 'security' );
		
		$overlay_id = intval( sanitize_text_field( $_GET['divioverlays_id'] ) );
		
		$post_data = get_post( $overlay_id );
		$output = apply_filters( 'the_content', $post_data->post_content );
		
		// Builder automatically adds `#et-boc` on selector on non official post type
		// Avoid having 2 elements with the same id in the same page
		$output = str_replace( 'id="et-boc"', '', $output );
		
		// Monarch fix: Remove Divi Builder main section class and add it later with JS
		$output = str_replace( 'et_pb_section ', 'dov_dv_section ', $output );
		
		print '<div id="divioverlay-content-ajax">' . $output . '</div>';
		
		// Restore "Static CSS File Generation"
		et_update_option( 'et_pb_static_css_file', 'on' );
	}
}


function get_all_wordpress_menus(){
    return get_terms( 'nav_menu', array( 'hide_empty' => true ) ); 
}


function prepareOverlays( $key = NULL )
{
    if ( !$key ) {
        return NULL;
	}
	
	// it is an url with hash overlay?
	if ( strpos( $key, "#" ) !== false ) {
		
		$exploded_url = explode( "#", $key );
		
		if ( isset( $exploded_url[1] ) ) {
			
			$key = str_replace( 'overlay-', '', $exploded_url[1] );
		}
	}
	
	$key = str_replace( 'unique_overlay_menu_id_', '', $key );
	$key = str_replace( 'overlay_', '', $key );
	$key = str_replace( 'unique_id_', '', $key );
	$key = str_replace( 'divioverlay-', '', $key );
	
    if ( $key == '' ) {
        return NULL;
	}
	
	if ( !overlayIsPublished( $key ) ) {
		
		return NULL;
	}
	
	return $key;
}

function overlayIsPublished( $key ) {
	
	$post = get_post_status( $key );
	
	if ( $post != 'publish' ) {
		
		return FALSE;
	}
	
	return TRUE;
}

function OnceMigrateCbcValues() {
	
    if ( get_option( 'OnceMigrateCbcValues', '0' ) == '1' ) {
        return;
    }
	
	/* Search Divi Overlays with Custom Close Buttons */
	$args = array(
		'meta_key'   => 'post_do_customizeclosebtn',
		'meta_value' => '',
		'meta_compare' => '!=',
		'post_type' => 'divi_overlay',
		'cache_results'  => false
	);
	$query = new WP_Query( $args );
	
	$posts = $query->get_posts();
	
	if ( isset( $posts[0] ) ) {
		
		migrateCbcValues( $posts );
	}

    // Add or update the wp_option
    update_option( 'OnceMigrateCbcValues', '1' );
}
add_action( 'init', 'OnceMigrateCbcValues' );

function migrateCbcValues( $posts = null ){
	
	if ( is_array( $posts ) ) {
	
		foreach( $posts as $dv_post ) {
			
			$post_id = $dv_post->ID;
			
			updateCbcValues( $post_id );
		}
	}
}

function updateCbcValues( $post_id = null ) {
	
	if ( $post_id ) {
	
		$old_cbc_textcolor = get_post_meta( $post_id, 'post_closebtn_text_color', true );
		$old_cbc_bgcolor = get_post_meta( $post_id, 'post_closebtn_bg_color', true );
		$old_cbc_fontsize = get_post_meta( $post_id, 'post_closebtn_fontsize', true );
		$old_cbc_borderradius = get_post_meta( $post_id, 'post_closebtn_borderradius', true );
		$old_cbc_padding = get_post_meta( $post_id, 'post_closebtn_padding', true );
		
		if ( $old_cbc_textcolor != '' ) {
			update_post_meta( $post_id, 'post_doclosebtn_text_color', sanitize_text_field( $old_cbc_textcolor ) );
		}
		
		if ( $old_cbc_bgcolor != '' ) {
			update_post_meta( $post_id, 'post_doclosebtn_bg_color', sanitize_text_field( $old_cbc_bgcolor ) );
		}
		
		if ( $old_cbc_fontsize != '' ) {
			update_post_meta( $post_id, 'post_doclosebtn_fontsize', sanitize_text_field( $old_cbc_fontsize ) );
		}
		
		if ( $old_cbc_borderradius != '' ) {
			update_post_meta( $post_id, 'post_doclosebtn_borderradius', sanitize_text_field( $old_cbc_borderradius ) );
		}
		
		if ( $old_cbc_padding != '' ) {
			update_post_meta( $post_id, 'post_doclosebtn_padding', sanitize_text_field( $old_cbc_padding ) );
		}
		
		// Reset old values
		update_post_meta( $post_id, 'post_closebtn_text_color', '' );
		update_post_meta( $post_id, 'post_closebtn_bg_color', '' );
		update_post_meta( $post_id, 'post_closebtn_fontsize', '' );
		update_post_meta( $post_id, 'post_closebtn_borderradius', '' );
		update_post_meta( $post_id, 'post_closebtn_padding', '' );
	}
}



 
 
  
  
 


function prevent_playable_tags() {
	
	if ( is_divi_builder_enabled() ) { return; }
	
	?>
	<script type="text/javascript">
		function togglePlayableTags( overlay_id, wait ) {
		
			var $ = jQuery;
			
			if ( !overlay_id  ) {
				
				overlay_id = "";
			}
			
			if ( !wait  ) {
				
				wait = 1;
			}
			
			/* Prevent playable tags load content before overlay call */
			setTimeout(function() {
				
				$( overlay_id + ".overlay").find("iframe").not( '[id^="gform"], .frm-g-recaptcha' ).each(function() { 
				
					var iframeParent = $(this).parent();
					var iframe = $(this).prop("outerHTML");
					var src = iframe.match(/src=[\'"]?((?:(?!\/>|>|"|\'|\s).)+)"/)[0];
					
					src = src.replace("src", "data-src");
					iframe = iframe.replace(/src=".*?"/i, "src=\"about:blank\" data-src=\"\"" );
					
					if ( src != "data-src=\"about:blank\"" ) {
						iframe = iframe.replace("data-src=\"\"", src );
					}
					
					$( iframe ).insertAfter( $(this) );
					
					$(this).remove();
				});
				
			}, wait);
			
			$( overlay_id + ".overlay").find("video").each(function() {
				$(this).get(0).pause();
			});
			
			$( overlay_id + ".overlay").find("audio").each(function() {
				
				this.pause();
				this.currentTime = 0;
			});
		}
		
		togglePlayableTags( '', 1000 );
	</script>
	<?php
}
add_action('wp_head', 'prevent_playable_tags');

