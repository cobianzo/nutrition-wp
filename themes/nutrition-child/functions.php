<?php


require_once __DIR__ . '/class-cliente.php';
require_once __DIR__ . '/includes/init.php';


/**
 * Languages
 */
// add_action( 'after_setup_theme', 'load_nutrition_textdomain' );
// function load_nutrition_textdomain() {
//   load_child_theme_textdomain( 'asim', get_stylesheet_directory() . '/languages' );
// }


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