<?php

add_theme_support( 'block-patterns' );

require_once __DIR__ . '/class-cliente.php';
require_once __DIR__ . '/class-dieta.php';
require_once __DIR__ . '/class-dieta-category.php';
require_once __DIR__ . '/class-alimento.php';
require_once __DIR__ . '/includes/redirections.php';
require_once __DIR__ . '/includes/shortcodes.php';

// Kind of the functions.php equivalent
// We insert generic things here.

class Setup {

  
  public static function init() {
    
    // editor styles css: in /admin/admin-styles.php
    
    // print styles css:
    add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_print_stylesheet'] );

    // enqueue fonts
    add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_style_css'] );
    add_action( 'admin_enqueue_scripts', [__CLASS__, 'enqueue_style_css'] );

    // retrict the blocks in the whole site. We don't need most of them
    add_filter( 'allowed_block_types_all', [__CLASS__, 'restrict_gutenberg_blocks_for_cpt'], 10, 2 );

    // Gutenberg enqueues
    add_action( 'enqueue_block_editor_assets', [__CLASS__, 'enqueue_gutenberg_generic_rules'] );

  }

  

  static public function enqueue_print_stylesheet() {
    wp_enqueue_style(
      'print-styles', // Identificador único de la hoja de estilos
      get_stylesheet_directory_uri() . '/print.css', // Ruta a la hoja de estilos
      array(), // Dependencias
      '1.3', // Versión de la hoja de estilos
      'print' // Tipo de medio: sólo para impresión
    );
  }


  public static function enqueue_style_css() {
    // Enqueue parent theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Enqueue child theme styles
    wp_enqueue_style('child-style', get_stylesheet_uri(), array('parent-style'));

    // Enqueue custom fonts
    // wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css');    
  }

  /**
   * with PHP restrict the allowed blocks
   * @TODO: I think I need to whitelist the patterns. At least those with the category 'Diet'
   *
   * @param [type] $allowed_blocks
   * @param [type] $post
   * @return void
   */
  public static function restrict_gutenberg_blocks_for_cpt($allowed_blocks, $editor) {

    // Check if the current post type is 'diet' or 'aliment'
    if ( ( $editor->post && in_array( $editor->post->post_type, array('diet', 'aliment') ) ) ) {

      $json_path          = get_stylesheet_directory_uri() . '/includes/allowed-blocks.json';
      $json_data          = file_get_contents($json_path);
      $our_allowed_blocks = json_decode($json_data, true);

      if ( is_array($our_allowed_blocks) ) {
        return $our_allowed_blocks;
      }

    }

    // For other post types, return the default allowed blocks
    return $allowed_blocks;
  }


  /**
   * Currently deactivated, but we can reactivate it. Just need to know what it does.
   *
   * @return void
   */
  public static function enqueue_gutenberg_generic_rules() {
    $script_path = 'build/generic-rules.js';
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
  }
}

Setup::init();


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

