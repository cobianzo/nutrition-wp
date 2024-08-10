<?php 


require_once __DIR__ . '/cpt-cliente.php';


add_action('admin_init', 'mytheme_enqueue_block_editor_assets');
function mytheme_enqueue_block_editor_assets() {
  // Enqueue the editor style
  // get the url path to the child theme
  $url = get_stylesheet_directory_uri() . '/includes/editor-style.css';
  add_editor_style( $url );
  
  if ( 'diet' === get_post_type( $_GET['post']) ) {
    $url = get_stylesheet_directory_uri() . '/includes/editor-style-diet.css';
    add_editor_style( $url );
  }

}
