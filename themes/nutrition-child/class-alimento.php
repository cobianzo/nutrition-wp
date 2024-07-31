<?php


class Alimento {

  /**
   * Init hooks
   */
  public static function init() {

    add_action('init', [__CLASS__, 'alimento_stuff']);

  }
  public static function alimento_stuff() {
    $block_file = __DIR__ . '/gutenberg/alimento-block/';
    register_block_type($block_file);
  }
}

Alimento::init();