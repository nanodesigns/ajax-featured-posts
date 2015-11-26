<?php
/**
 * Plugin Name: AJAX Featured Posts
 * Plugin URI: 	http://.tutsplus.com/tutorials/make-featured-posts-using-ajax--cms-25375
 * Description:	A Plugin to make posts featured using AJAX
 * Version:		1.0.0
 * Author: 		Mayeenul Islam
 * Author URI: 	http://nanodesignsbd.com/
 * Text Domain: ajax-featured-posts
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Loading Necessary Scripts
 *
 * Loading styles and javascripts in Admin Panel.
 * --------------------------------------------------------------------------
 */
function afp_admin_scripts() {
	$screen = get_current_screen();
	if ( 'post' === $screen->post_type ) {

		wp_enqueue_style( 'afp-styles', plugins_url('/assets/css/ajax-featured-posts.css', __FILE__) );

		wp_enqueue_script( 'afp-scripts', plugins_url( '/assets/js/ajax-featured-posts.js', __FILE__ ), array('jquery'), '', true );

	}
}
add_action( 'admin_enqueue_scripts', 'afp_admin_scripts' );


/**
 * Meta Field specifics
 * --------------------------------------------------------------------------
 */
function afp_featured_specifics() {
	global $post;
	$post_id = $post->ID;

	if( 'post' === $post->post_type ) :

        // Use nonce for verification
        wp_nonce_field( basename( __FILE__ ), 'afp_featured_nonce' );
	
		// Fetch database content
		$featured = get_post_meta( $post_id , 'featured' , true );
	    
	    if( $featured && 'yes' === $featured ) {
	        $featured_class = ' featured';
	        $featured_icon 	= ' dashicons-star-filled';
	    } else {
	        $featured_class = '';
	        $featured_icon 	= ' dashicons-star-empty';
	    }
	    
	    echo '<div class="misc-pub-section featuring-post misc-pub-featured">';
	    	
	    	echo '<label><input type="checkbox" name="afp_featured_post" value="yes" '. checked( $featured, 'yes', false ) .'>';
	    	echo ' <strong class="featured-text">'. __( 'FEATURE THIS POST', 'ajax-featured-posts' ) .'</strong></label>';

	    echo '</div>';

	endif;
}
add_action( 'post_submitbox_misc_actions', 'afp_featured_specifics' );


function afp_save_featured_meta_data( $post_id ) {

	// verify nonce
    if (!isset($_POST['afp_featured_nonce']) || !wp_verify_nonce($_POST['afp_featured_nonce'], basename(__FILE__)))
        return $post_id;

    // check autosave
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return $post_id;

    // check permissions
    if ( 'post' === $_POST['post_type'] && !current_user_can('edit_posts', $post_id) )
        return $post_id;

    // form posting
    $featured 		= $_POST['afp_featured_post'];

    // data in database
    $fetched_data 	= get_post_meta( $post_id, 'featured', true );

    // doing the update or delete the key
    if( $featured && $featured != $fetched_data )
    	update_post_meta( $post_id, 'featured', esc_html($featured) );
    elseif( empty($featured) )
    	delete_post_meta( $post_id, 'featured' );
    elseif( empty($featured) && empty($fetched_data) )
        delete_post_meta( $post_id, 'featured' );

}
add_action( 'save_post',        'afp_save_featured_meta_data' );
add_action( 'new_to_publish',   'afp_save_featured_meta_data' );


/**
 * Add more columns to the Post table.
 * 
 * @param  array $columns Default columns.
 * @return array          Modified columns.
 * --------------------------------------------------------------------------
 */
function afp_post_columns( $columns ) {
    
    $new_columns = array(
        'featured'          => __( 'Featured', 'ajax-featured-posts' )
    );
    return array_merge( $columns, $new_columns );
}
add_filter( 'manage_post_posts_columns', 'afp_post_columns' );


/**
 * Populate the columns with the respective data.
 * 
 * @param  array $column    Default columns.
 * @param  integer $post_id That particular post_ID.
 * --------------------------------------------------------------------------
 */
function afp_post_table_columns_data( $column, $post_id ) {

	switch ( $column ) {
        case 'featured':
            $featured = get_post_meta( $post_id , 'featured' , true );

            if( $featured && 'yes' === $featured ) {
                $featured_class = ' featured';
                $featured_icon = ' dashicons-star-filled';
            } else {
                $featured_class = '';
                $featured_icon = ' dashicons-star-empty';
            }
            
            echo '<span id="'. $post_id .'" class="make-featured'. $featured_class .'"><i class="dashicons'. $featured_icon .'"></i></span>';

            break;
    }
}
add_action( 'manage_post_posts_custom_column', 'afp_post_table_columns_data', 10, 2 );



/**
 * Managing featured post AJAX call
 *
 * Get the AJAX call and process it via WordPress Functions.
 * --------------------------------------------------------------------------
 */
function afp_make_featured_ajax_callback() {

    if( isset( $_POST['id'] ) ) {

        global $project_prefix;

        $post_id = (int) $_POST['id'];

        //grabbing existing data to delete
        $db_data = get_post_meta( $post_id, 'featured', true );
        if( $db_data ) {
            delete_post_meta( $post_id, 'featured' );
            echo 'deleted';
            die;            
        }
        else {
            update_post_meta( $post_id, 'featured', 'yes' );
            echo 'added';
            die;            
        }

    } else {

        echo false;
        die;

    }

}
add_action( 'wp_ajax_afp_make_featured', 'afp_make_featured_ajax_callback' ); //for logged in users only