<?php

add_theme_support( 'block-patterns' );


require_once __DIR__ . '/class-cliente.php';
require_once __DIR__ . '/class-dieta.php';
require_once __DIR__ . '/class-dieta-category.php';
require_once __DIR__ . '/class-alimento.php';
require_once __DIR__ . '/includes/init.php';


add_filter('admin_body_class', function($classes) {
    if (is_admin()) {
        $user = wp_get_current_user();
        $classes .= ' user-role-' . $user->roles[0];
    }
    return $classes;
});

// Generic JS rules (Gutenberg):
add_action('enqueue_block_editor_assets', function() {
	$script_path = 'build/dieta-rules.js';
	$asset_file = get_stylesheet_directory() . '/build/generic-rules.asset.php';

	if ( file_exists( $asset_file ) ) {
		$assets = include( $asset_file );
	} else {
		wp_die( sprintf('File %s not generated. Fix this first', $asset_file ) );
	}

	// script only for diets
	wp_enqueue_script(
		'generic-rules-script',
		get_stylesheet_directory_uri() . '/' . $script_path,
		$assets['dependencies'],
		$assets['version'],
		true
	);
});


/**
 * DEBUGGING HELPERS
 */

add_action('init', function(){
	if (isset($_GET['w-test'])) {
		$user_id = 37;
		$new_value = [ 5 ];
		$up = update_user_meta( $user_id, 'pathology', $new_value );
		$f = get_field( 'pathology', 'user_' . $user_id );
		ddie($f);
	}
});
function dd($var) {
	echo '<pre>';
	print_r($var);
	echo '</pre>';
}
function ddie($var) {
	dd($var);
	wp_die();
}
// Updates the option blogdescription with logs.
function up( $val, $timestamp = false )  {
	// get value of blogdescription and update it concating with $val
	$blogdescription = get_option( 'blogdescription' );
	
	// if $timestamp we add the hour/min and second before the value
	if ( $timestamp ) {
		$blogdescription .= ' || ' . date( 'H:i:s' ) . ' : ';
	}
	$blogdescription .= $val;

	update_option( 'blogdescription', $blogdescription );
}



// todelete

