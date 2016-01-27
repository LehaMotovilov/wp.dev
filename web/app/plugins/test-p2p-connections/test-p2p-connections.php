<?php
/**
 * Plugin Name: Test "Posts 2 Posts" functional
 * Description: Test connections for "Posts 2 Posts" plugin by scribu.
 * Version: 0.1.0
 * Author: LehaMotovilov
 * Author URI: http://lehamotovilov.com/
 */

/**
 * Create test connection from Post to Page.
 *
 * @link https://github.com/scribu/wp-posts-to-posts/wiki/p2p_register_connection_type
 */
add_action( 'p2p_init', function() {
	// Custom Post Types.
	p2p_register_connection_type( [
		'name' 	=> 'first_to_second',
		'from' 	=> 'cpt_first',
		'to' 	=> 'cpt_second',
	] );

	// Default Post Types.
	p2p_register_connection_type( [
		// Required.
		'name' 					=> 'posts_to_pages',
		'from' 					=> 'post',
		'to' 					=> 'page',
		// Optional.
		'admin_box' 			=> true,
		'admin_column' 			=> true,
		'admin_dropdown' 		=> false,
		'can_create_post' 		=> true,
		'from_query_vars' 		=> [],
		'to_query_vars'			=> [],
		'fields' 				=> [],
		'cardinality' 			=> 'many-to-many',
		'duplicate_connections' => false,
		'self_connections' 		=> false,
		'sortable' 				=> false,
		'title' 				=> '',
		'from_labels' 			=> [],
		'to_labels' 			=> [],
		'reciprocal' 			=> false,
	] );
} );

/**
 * Register some test CPT for test connections.
 */
add_action( 'init', function() {
	register_post_type( 'cpt_first', [
		'labels'             => [
			'name'               => 'First',
			'singular_name'      => 'First',
			'menu_name'          => 'First',
			'name_admin_bar'     => 'First',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New First',
			'new_item'           => 'New First',
			'edit_item'          => 'Edit First',
			'view_item'          => 'View First',
			'all_items'          => 'All First',
			'search_items'       => 'Search First',
			'parent_item_colon'  => 'Parent First:',
			'not_found'          => 'No books found.',
			'not_found_in_trash' => 'No books found in Trash.'
		],
		'description'        => 'Description',
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => [ 'slug' => 'cpt_first' ],
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => [
			'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'
		]
	] );
	register_post_type( 'cpt_second', [
		'labels'             => [
			'name'               => 'Second',
			'singular_name'      => 'Second',
			'menu_name'          => 'Second',
			'name_admin_bar'     => 'Second',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Second',
			'new_item'           => 'New Second',
			'edit_item'          => 'Edit Second',
			'view_item'          => 'View Second',
			'all_items'          => 'All Second',
			'search_items'       => 'Search Second',
			'parent_item_colon'  => 'Parent Second:',
			'not_found'          => 'No books found.',
			'not_found_in_trash' => 'No books found in Trash.'
		],
		'description'        => 'Description',
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => [ 'slug' => 'cpt_second' ],
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => [
			'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'
		]
	] );
} );
