<?php


class Dieta {

  /**
   * Init hooks
   */
  public static function init() {

    add_action('init', [__CLASS__, 'my_custom_dieta_pattern']);

  }
  public static function my_custom_dieta_pattern() {
    if (function_exists('register_block_pattern')) {
        register_block_pattern(
            'nutrition-child/dieta-pattern', // Un identificador único para el pattern
            array(
                'title'       => __('Dieta Predeterminada', 'asim'),
                'description' => _x('Un patrón para una dieta predeterminada.', 'Block pattern description', 'textdomain'),
                'content'     => "<!-- wp:paragraph --><p>Contenido predeterminado para dieta...</p><!-- /wp:paragraph -->",
                'categories'  => ['dieta'],
            )
        );
    }
  }
}

Dieta::init();