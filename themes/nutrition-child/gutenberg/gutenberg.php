<?php


class Gutenberg {


  public static function init() {

    add_action( 'init', [ __CLASS__, 'register_alimento_block' ], 10 );
    add_action( 'init', function() { register_block_type(__DIR__ . '/test2-block/'); }, 10 );
    add_action( 'init', [ __CLASS__, 'register_piatto_block' ], 11 );
    add_action( 'init', [ __CLASS__, 'register_testblock_block' ], 12 );

    add_filter( 'block_categories_all', [__CLASS__, 'register_block_category_diet'], 10, 2 );
  }

  static public function register_alimento_block() {
    
    // Start of the block development
    $block_file = __DIR__ . '/alimento-block/';
    register_block_type($block_file);

    $a = wp_set_script_translations( 'asim-alimento-block-editor-script', 'asim', get_stylesheet_directory_uri() . '/languages' );
    
  }

  static public function register_piatto_block() {
    // Start of the block development
    register_block_type( __DIR__ . '/piatto-block/' );
  }


  static public function register_testblock_block() {
    // Start of the block development
    register_block_type( __DIR__ . '/testblock-block/', [
      'render_callback' => [__CLASS__, 'render_testblock_callback']
    ] );
  }
  function render_testblock_callback( $attributes, $content ) {
    ob_start();
    ?>
    <div <?php echo get_block_wrapper_attributes(); ?>>
        <p>RENDER SIDE</p>
        <?php echo $attributes['test_attribute']; ?>
        <?php echo $content; ?>
    </div>
    <?php
    return ob_get_clean();
  }

  public static function register_block_category_diet( $categories ) {
    return array_merge( $categories, array( [ 'slug'  => 'diet', 'title' => __( 'Diet', 'asim' ) ] ) );
  }


}

Gutenberg::init();