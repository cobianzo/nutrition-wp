<?php 

 add_action( 'init', function() {
	register_post_type( 'client', array(
	'labels' => array(
		'name' => __( 'Clients', 'asim' ),
		'singular_name' => __( 'Client', 'asim' ),
		'menu_name' => __( 'Clients', 'asim' ),
		'all_items' => __( 'All Clients', 'asim' ),
		'edit_item' => __( 'Edit Client', 'asim' ),
		'view_item' => __( 'View Client', 'asim' ),
		'view_items' => __( 'View Clients', 'asim' ),
		'add_new_item' => __( 'Add New Client', 'asim' ),
		'add_new' => __( 'Add New Client', 'asim' ),
		'new_item' => __( 'New Client', 'asim' ),
		'parent_item_colon' => __( 'Parent Client:', 'asim' ),
		'search_items' => __( 'Search Clients', 'asim' ),
		'not_found' => __( 'No clients found', 'asim' ),
		'not_found_in_trash' => __( 'No clients found in Trash', 'asim' ),
		'archives' => __( 'Client Archives', 'asim' ),
		'attributes' => __( 'Client Attributes', 'asim' ),
		'insert_into_item' => __( 'Insert into client', 'asim' ),
		'uploaded_to_this_item' => __( 'Uploaded to this client', 'asim' ),
		'filter_items_list' => __( 'Filter clients list', 'asim' ),
		'filter_by_date' => __( 'Filter clients by date', 'asim' ),
		'items_list_navigation' => __( 'Clients list navigation', 'asim' ),
		'items_list' => __( 'Clients list', 'asim' ),
		'item_published' => __( 'Client published.', 'asim' ),
		'item_published_privately' => __( 'Client published privately.', 'asim' ),
		'item_reverted_to_draft' => __( 'Client reverted to draft.', 'asim' ),
		'item_scheduled' => __( 'Client scheduled.', 'asim' ),
		'item_updated' => __( 'Client updated.', 'asim' ),
		'item_link' => __( 'Client Link', 'asim' ),
		'item_link_description' => __( 'A link to a client.', 'asim' ),
	),
	'description' => __( 'Client model associated to a user.', 'asim' ),
	'public' => false,
	'show_ui' => true,
	'show_in_rest' => false,
	'menu_position' => 2,
	'menu_icon' => 'dashicons-admin-users',
	'capability_type' => array(
		0 => 'client',
		1 => 'clients',
	),
	'map_meta_cap' => true,
	'supports' => array(
		0 => 'title',
		1 => 'author',
	),
	'taxonomies' => array(
		0 => 'diet-category',
	),
	'rewrite' => false,
	'delete_with_user' => true,
) );
} );

