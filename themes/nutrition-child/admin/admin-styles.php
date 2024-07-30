<?php

add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }

    global $current_screen;

    if ( $current_screen->post_type != 'client' ) {
        return;
    }
    // get current stylesheet url
    $url = get_stylesheet_directory_uri() . '/admin/admin-client-styles.css';
    wp_enqueue_style( 'asim-client-admin-styles',  $url );
} );
