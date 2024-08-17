<?php

/**
 * [user_display_name]
 */
add_shortcode( 'user_display_name', 'mostrar_nombre_de_usuario' );
function mostrar_nombre_de_usuario( $atts = [] ) {

  $atts = shortcode_atts(
    array(
        'wrapper' => 'p', // Tipo de etiqueta HTML
    ),
    $atts,
    'user_display_name'
  );

  $wrapper = tag_escape($atts['wrapper']); // Escapar la etiqueta HTML

  if ( is_user_logged_in() ) {
      $current_user = wp_get_current_user();
      return '<' . $wrapper . '>' . esc_html($current_user->display_name) . '</' . $wrapper . '>';

  }
  return '';
}


// [client_diet_links]
add_shortcode( 'client_diet_links', 'show_client_diet_links' );
function show_client_diet_links( $atts = [] ) {
  
  if ( !is_user_logged_in() || !current_user_can( 'client' ) ) {
    return 'Access denied';
  }

  $user = wp_get_current_user();
  $args = array(
    'post_type'      => 'diet',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'author'         => $user->ID,
  );

  $query = new WP_Query( $args );

  ob_start();;
  if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
      $query->the_post();
      echo '<div class="wp-block-button"><a class="wp-block-button__link" href="' . get_permalink() . '">' . get_the_title() . '</a></div>';
    }
  }

  return ob_get_clean();
}


// [registro-misurazioni]