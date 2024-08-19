<?php


class Alimento {

  /**
   * Init hooks
   */
  public static function init() {

    add_action('init', [__CLASS__, 'alimento_stuff']);

  }
  public static function alimento_stuff() {

    // Start of the block development
    $block_file = __DIR__ . '/gutenberg/alimento-block/';
    register_block_type($block_file);

    $a = wp_set_script_translations( 'asim-alimento-block-editor-script', 'asim', get_stylesheet_directory_uri() . '/languages' );
    // ddie(__('Breakfast', 'asim'));
  }
}

Alimento::init();