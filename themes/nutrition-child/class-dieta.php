<?php


class Dieta {

  /**
   * Init hooks
   */
  public static function init() {

    add_action('enqueue_block_editor_assets', [__CLASS__, 'script_dieta_rules']);

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
}

Dieta::init();