<?php

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }

    global $current_screen;

    if ( $current_screen->post_type === 'client' ) {   
        // get current stylesheet url
        $url = get_stylesheet_directory_uri() . '/admin/admin-client-styles.css';
        wp_enqueue_style( 'asim-client-admin-styles',  $url );
    } 
    elseif ( $current_screen->post_type === 'diet' ) {   
        
        // ... 
    }
} );


// For the editor (timy mce and gutenberg editor.)
add_action( 'admin_init', function () {
    // Enqueue the editor style
    // get the url path to the child theme
    $url = get_stylesheet_directory_uri() . '/admin/editor-style.css';
    add_editor_style( $url );
    
    if ( isset($_GET['post']) &&  'diet' === get_post_type( $_GET['post'] ) ) {
      $url = get_stylesheet_directory_uri() . '/admin/editor-style-diet.css';
      add_editor_style( $url );
    }
  
  }
);
