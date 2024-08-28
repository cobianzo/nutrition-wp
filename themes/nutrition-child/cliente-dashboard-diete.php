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

        ob_start(); ?>

        <a class="tile tile--button" href="<?php echo esc_url($url); ?>">
            <?php echo sprintf( __( 'Create a new diet from type <b>%s</b>', 'asim' ), esc_html($term->name) ); ?>
        </a>

        <?php
        echo ob_get_clean();
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
        ob_start(); ?>

        <div class="tile tile--button tile--<?php echo esc_attr( get_post_status() ); ?>">
            <a href="<?php echo esc_url( $edit_link ); ?>">
                <?php echo get_the_title(); ?><br/>
                <small>(<?php echo get_post_status(); ?>) <?php echo get_the_date(); ?></small>
            </a>
            <a href="<?php echo esc_url(admin_url('admin-post.php?action=delete_diet_action&post_id=' . get_the_ID() . '&nonce=' . $nonce . '&redirect=' . $redirect)); ?>" class="button button-primary">
                Delete Diet Post
            </a>
        </div>

        <?php
        $html_output = ob_get_clean();
        echo $html_output;
      }
    }

    wp_reset_postdata();
  ?>

  <?php
  // Creation of Diets from Alients belonging to the programme of this client
  $programma = Programma::get_programma_by_client( $post_id );
  if ( ! $programma ) {
    echo '<p>' . __('No Programme found for this client.', 'asim') . '</p>';
  } else {
    $alimenti = get_post_meta( $programma->ID, Programma::META_ALIMENTI, true );

    echo '<p>' . sprintf(__('Found a programme for this client: <a href="%s">%s</a>', 'asim'), get_edit_post_link($programma->ID), get_the_title($programma->ID)) . '</p>';

    if ( empty( $alimenti ) ) {
      echo '<p>' . __('No Aliment associated to the programme.', 'asim') . '</p>';
    } else {


      // For every aliment, show a checkbox with its thumbnail and title
      foreach ( $alimenti as $alimento_id ) {
        $alimento_post = get_post($alimento_id);
        if ($alimento_post) {
          $thumbnail_url = get_the_post_thumbnail_url($alimento_id, 'thumbnail');
          $title = get_the_title($alimento_id);
          echo '<div class="alimento-item">';
          echo '<input type="checkbox" name="alimenti[]" value="' . esc_attr($alimento_id) . '" />';
          if ($thumbnail_url) {
            echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($title) . '" style="width: 50px; height: 50px; margin-right: 10px;" />';
          }
          echo '<span>' . esc_html($title) . '</span>';
          echo '</div>';          
        }
      }
      
    }

  }
  ?>

</div>
<?php
endif;