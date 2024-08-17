<?php 

// Kind of the functions.php equivalent
// We insert generic things here.

class Setup {

  
  public static function init() {
    
    // editor styles css: in /admin/admin-styles.php
    
    // print styles css:
    add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_print_stylesheet'] );

    // enqueue fonts
    add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_fonts'] );
    add_action( 'admin_enqueue_scripts', [__CLASS__, 'enqueue_fonts'] );

    // retrict the blocks in the whole site. We don't need most of them
    add_filter( 'allowed_block_types_all', [__CLASS__, 'restrict_gutenberg_blocks_for_diet_cpt'], 10, 2 );


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


  public static function enqueue_fonts() {
    // Enqueue parent theme styles
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Enqueue child theme styles
    wp_enqueue_style('child-style', get_stylesheet_uri());

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
  public static function restrict_gutenberg_blocks_for_diet_cpt($allowed_blocks, $editor) {

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

}

Setup::init();