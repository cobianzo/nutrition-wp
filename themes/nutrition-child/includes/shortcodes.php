<?php

/**
 * [user_display_name wrapper="p" class="my-class"]
 */
add_shortcode( 'user_display_name', 'mostrar_nombre_de_usuario' );
function mostrar_nombre_de_usuario( $atts = [] ) {

  $atts = shortcode_atts(
    array(
        'wrapper' => 'p', // Tipo de etiqueta HTML
        'class' => ''
    ),
    $atts,
    'user_display_name'
  );

  $wrapper = tag_escape( $atts['wrapper'] ); // Escapar la etiqueta HTML
  $class = esc_attr( $atts['class'] );

  if ( is_user_logged_in() ) {
      $current_user = wp_get_current_user();
      return '<' . $wrapper . ' class="' . $class . '">' . esc_html($current_user->display_name) . '</' . $wrapper . '>';

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
      echo '<div class="wp-block-button"><a class="wp-block-button__link" href="' . get_permalink() . '">' 
        . get_the_title()
        . '<br>' . get_the_date()
        . '</a></div>';
    }
  }

  return ob_get_clean();
}


// [registro_misurazioni]
add_shortcode( 'registro_misurazioni', 'show_registro_misurazioni' );
function show_registro_misurazioni( $atts = [] ) {
  
  if ( !is_user_logged_in() || !current_user_can( 'client' ) ) {
    return 'Access denied';
  }

  // get the `client` CPT for current user. 
  $client_post = Cliente::get_client_post_by_user_id( get_current_user_id() );

  if ( !$client_post ) {
    return 'Client not found';
  }

  $visits = get_field('visits', $client_post->ID);
  $output = '';
  if ( !empty($visits) ) {
    $output .= '<ul>';
    foreach ( $visits as $visit ) {
      $output .= '<li>';
      $output .= $visit['date'] . ' - ' . $visit['weight'] . 'Kg - ' . $visit['height'] . 'cm - ' . $visit['other'];
      $output .= '</li>';
    }
    $output .= '</ul>';
  }

  return $output;
}