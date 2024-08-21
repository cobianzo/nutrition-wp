<?php


class Programma {

  /**
   * Init hooks
   */
  public static function init() {

    add_action('init', [__CLASS__, 'piatto_block_register'] );

  }

  /**
   * Define block
   *
   * @return void
   */
  public static function piatto_block_register() {

    // Start of the block development
    $block_file = __DIR__ . '/gutenberg/piatto-block/';
    register_block_type( $block_file );

    $a = wp_set_script_translations( 'asim-piatto-block-editor-script', 'asim', get_stylesheet_directory_uri() . '/languages' );
  }
}

Programma::init();