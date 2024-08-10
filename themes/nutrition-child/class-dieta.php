<?php


class Dieta {

  const ALLOWED_BLOCKS = [
    'core/paragraph',
    'core/group',
    'core/heading',
    'asim/alimento-block', // Replace with your custom block's name
  ];

  /**
   * Init hooks
   */
  public static function init() {

    add_action('enqueue_block_editor_assets', [__CLASS__, 'script_dieta_rules']);
    add_filter('allowed_block_types_all', [__CLASS__, 'restrict_gutenberg_blocks_for_diet_cpt'], 10, 2);


  }
  public static function script_dieta_rules() {
    $current_post_type = get_post_type();
    
    // Enqueue only for 'diet' post type
    if ( $current_post_type === 'diet' ) {
      $script_path = 'build/dieta-rules.js';
      $asset_file = get_stylesheet_directory() . '/build/dieta-rules.asset.php';
  
      if ( file_exists( $asset_file ) ) {
          $assets = include( $asset_file );
      } else {
          wp_die( sprintf('File %s not generated. Fix this first', $asset_file ) );
      }
  
      wp_enqueue_script(
          'dieta-rules-script',
          get_stylesheet_directory_uri() . '/' . $script_path,
          $assets['dependencies'],
          $assets['version'],
          true
      );
    }
  }


  /**
   * with PHP restrict the allowed blocks
   *
   * @param [type] $allowed_blocks
   * @param [type] $post
   * @return void
   */
  public static function restrict_gutenberg_blocks_for_diet_cpt($allowed_blocks, $editor) {
    // Check if the current post type is 'diet'
    if ($editor->post->post_type === 'diet') {
        // Specify the allowed blocks
        return self::ALLOWED_BLOCKS;
    }

    // For other post types, return the default allowed blocks
    return $allowed_blocks;
  }
}

Dieta::init();