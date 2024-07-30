<?php

add_theme_support( 'block-patterns' );


require_once __DIR__ . '/class-cliente.php';
require_once __DIR__ . '/class-dieta.php';
require_once __DIR__ . '/includes/init.php';


add_filter('admin_body_class', function($classes) {
    if (is_admin()) {
        $user = wp_get_current_user();
        $classes .= ' user-role-' . $user->roles[0];
    }
    return $classes;
});

/**
 * DEBUGGING HELPERS
 */

add_action('init', function(){
	if (isset($_GET['w-test'])) {
		ddie( get_option( 'blogdescription' ) );
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