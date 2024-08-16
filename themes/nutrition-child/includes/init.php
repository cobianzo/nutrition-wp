<?php 

// Kind of the functions.php equivalent
// We insert generic things here.

class Setup {

  
  public static function init() {
    
    // editor styles css: Add css to the gutenberg and the tiny mce editors, in case I want to use it.
    add_action( 'admin_init', [__CLASS__, 'mytheme_enqueue_block_editor_assets'] );

    // print styles css:
    add_action( 'wp_enqueue_scripts', [__CLASS__, 'enqueue_print_stylesheet'] );

    // retrict the blocks in the whole site. We don't need most of them
    add_filter( 'allowed_block_types_all', [__CLASS__, 'restrict_gutenberg_blocks_for_diet_cpt'], 10, 2 );

    

  }

  public static function mytheme_enqueue_block_editor_assets() {
    // Enqueue the editor style
    // get the url path to the child theme
    $url = get_stylesheet_directory_uri() . '/includes/editor-style.css';
    add_editor_style( $url );
    
    if ( isset($_GET['post']) &&  'diet' === get_post_type( $_GET['post'] ) ) {
      $url = get_stylesheet_directory_uri() . '/includes/editor-style-diet.css';
      add_editor_style( $url );
    }
  
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