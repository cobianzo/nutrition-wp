<?php 

/**
 * 
 */

 extract( $args ); // $post_id.

 $diets = Dieta::get_client_diets( $post_id );

 if ( empty( $diets ) ) :
?>
  <h4><?php _e('Currently this client has not any diet assigned.','asim'); ?></h4>
<?php
 endif;
?>




<div class="client-dashboard client-dashboard-diet">
  <?php 
  if ( empty( $diets ) || 1 ) :

    $terms = get_terms(array(
        'taxonomy' => 'diet-category',
        'hide_empty' => false,
    ));
    if (!empty($terms) && !is_wp_error($terms)) {
      foreach ($terms as $term) {
        // Crear el enlace con el query param
        
        $url = admin_url( "admin-post.php?action=create_diet&client_id={$post_id}&diet-category=$term->term_id" );

        echo '<a class="tile tile--button" href="' . $url . '">' . 
          sprintf( __( 'Create a new diet from type <b>%s</b>', 'asim' ), esc_html($term->name) )
        . '</a>';
      }
    }
  endif;  
    ?>
</div>
<?php
if ( ! empty( $diets ) ) : 
?>
<h4><?php _e('Current diet(s) for the client.','asim'); ?></h4>
<div class="client-dashboard client-dashboard-diet">
  <?php
    $user = Cliente::get_client_user_by_post_id( $post_id );
    if ( ! $user || ! is_a( $user, 'WP_User' ) ) {
        wp_die( 'Invalid user associated with the client.' );
    }

    $args = array(
        'post_type'      => 'diet',  // CPT slug for diet
        'author'         => $user->ID,
        'posts_per_page' => -1,      // Retrieve all diets for this user
        'post_status'    => 'any', // You can adjust this to include drafts, etc.
    );

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
      $nonce = wp_create_nonce('delete_diet_nonce_action' );
      $redirect = get_edit_post_link( $post_id );
      while ( $query->have_posts() ) {
        $query->the_post();
        $edit_link = get_edit_post_link( get_the_ID() );
        echo '<div class="tile tile--button tile--' . esc_attr( get_post_status() ) .'"><a href="' . esc_url( $edit_link ) . '">' 
          . get_the_title() 
        . '<br/><small>' 
        . '(' . get_post_status() . ') ' 
        . get_the_date() 
        . '</small></a>'
        . '<a href="' 
          . esc_url(admin_url('admin-post.php?action=delete_diet_action&post_id=' . get_the_ID() . '&nonce=' . $nonce . '&redirect=' . $redirect )) 
          . '" class="button button-primary">'
          . '.Delete Diet Post'
        . '</a>'
        . '</div>';
        }
    }

    wp_reset_postdata();
  ?>
</div>
<?php
endif;