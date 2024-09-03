<?php

/**
 * Adding the current role to the parent to show/hide stuff depending of user.
 */
add_filter('admin_body_class', function($classes) {
    if (is_admin()) {
        $user = wp_get_current_user();
        $classes .= ' user-role-' . $user->roles[0];
    }
    return $classes;
});

/** For single CPT admin edit pages. eg. admin-client-styles.css , etc */
add_action( 'admin_enqueue_scripts', function( $hook ) {

    
    $url = get_stylesheet_directory_uri() . '/admin/admin-styles.css';
    wp_enqueue_style( 'asim-admin-styles',  $url );


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
        $url = get_stylesheet_directory_uri() . '/admin/admin-diet-styles.css';
        wp_enqueue_style( 'asim-diet-admin-styles',  $url );
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
      wp_enqueue_style('diet-editor-styles', $url, array(), filemtime(get_stylesheet_directory() . '/admin/editor-style-diet.css'));
    }
    if ( isset($_GET['post']) &&  'aliment' === get_post_type( $_GET['post'] ) ) {
      $url = get_stylesheet_directory_uri() . '/admin/editor-style-aliment.css';
      add_editor_style( $url );
      wp_enqueue_style('aliment-editor-styles', $url, array(), filemtime(get_stylesheet_directory() . '/admin/editor-style-aliment.css'));
    }
  
  }
);
