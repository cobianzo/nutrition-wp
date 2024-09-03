<?php 

/**
 * 
 */

 extract( $args ); // $post_id (of client).

 $diets = Dieta::get_client_diets( $post_id );

 if ( empty( $diets ) ) :
?>
  <h4><?php _e('Currently this client has not any diet assigned.','asim'); ?></h4>
<?php
  else :
?>
<h4><?php _e('Current diet(s) for the client.','asim'); ?></h4>
<div class="client-dashboard client-dashboard-diet">
  <?php
    $user = Relation_Cliente_User::get_client_user_by_post_id( $post_id );
    if ( ! $user || ! is_a( $user, 'WP_User' ) ) {
        wp_die( 'Invalid user associated with the client.' );
    }

    // Get diets by client
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
                <small>(<?php echo get_post_status(); ?> - <?php the_ID(); ?>) <?php echo get_the_date(); ?></small>
            </a>
            <a href="<?php echo esc_url(admin_url('admin-post.php?action=delete_diet_action&post_id=' . get_the_ID() . '&nonce=' . $nonce )); ?>" class="button button-primary">
                Delete Diet Post
            </a>
            <?php 
            $programme_id = get_post_meta( get_the_ID(), '_created_from_programme', true ); 
            if ( $programme_id ) {
              printf( __( '<div class="tile__footer">Created from Programme <b><em>%s</em></b></div>' ), get_the_title( $programme_id ) );
            }
            ?>
        </div>

        <?php
        $html_output = ob_get_clean();
        echo $html_output;
      }
    }

    wp_reset_postdata();
?>
</div>
<?php
endif;
?>



<div class="client-dashboard client-dashboard-diet">
  <?php 
  $terms = get_terms(array(
      'taxonomy'   => 'diet-category',
      'hide_empty' => false,
      'orderby'    => 'id',
      'order'      => 'desc',
  ));
  if (!empty($terms) && !is_wp_error($terms)) {
    foreach ($terms as $term) :
      // Crear el enlace con el query param
      
      $url = admin_url( "admin-post.php?action=create_diet&client_id={$post_id}&diet-category=$term->term_id" );

      ob_start(); ?>

      <a class="tile tile--button" href="<?php echo esc_url($url); ?>">
          <?php echo sprintf( __( 'Create a new diet from type <b>%s</b>', 'asim' ), esc_html($term->name) ); ?>
      </a>

      <?php
      echo ob_get_clean();
    endforeach;
  }

  ?>
</div>


  <?php
  // Creation of Diets from Alients belonging to the programme of this client
  $programmas = Programma::get_programma_by_client( $post_id, false );
  if ( ! empty( $programmas ) && count($programmas) > 1 ) {
    echo '<p>' . __('Error: There are more than one programme for the client. There should be only one.', 'asim') . '</p>';
  }

  if ( empty( $programmas ) ) {
    echo '<p>' . __('No Programme found for this client.', 'asim') . '</p>';
  } else {
    $programma = $programmas[0];
    $alimenti = get_post_meta( $programma->ID, Programma::META_ALIMENTI, true );

    echo '<h4>' . sprintf(__('Found a programme for this client: <a href="%s">%s</a>', 'asim'), get_edit_post_link($programma->ID), get_the_title($programma->ID)) . '</h4>';

    if ( empty( $alimenti ) ) : ?>
      
      <p><?php _e('No Aliment associated to the programme.', 'asim'); ?></p>

    <?php
    else :
    ?>

    <div class="client-dashboard-wrapper">
    <?php

        // Sort the aliments grouped by meal
        foreach ( $alimenti as $alimento_id ) {
          $alimenti_meals = array();
          foreach ( $alimenti as $alimento_id ) {
            $terms = wp_get_post_terms( $alimento_id, 'meal' );
            if ( is_array( $terms ) ) {
              foreach ( $terms as $term ) {
                if ( !isset($alimenti_meals[ $term->slug ]) ) {
                  $alimenti_meals[ $term->slug ] = [$alimento_id];
                } else {
                  $alimenti_meals[ $term->slug ][] = $alimento_id;
                }
              }
            }
          }
        }
        // retrieve all terms for the taxonomy 'meal'
        $terms = get_terms(array(
            'taxonomy' => 'meal',
            'orderby'  => 'id',
            'order'    => 'desc',
        ));
        if (!empty($terms) && !is_wp_error($terms)) {
          
          echo '<ul id="container-aliments">';
          foreach ($terms as $term) {
            echo '<li id="term-' . esc_attr($term->slug) . '-aliments">';
            echo '<h3>' . esc_html($term->name) . '</h3>';
            
            $aliments = isset($alimenti_meals[ $term->slug ])? $alimenti_meals[ $term->slug ] : [];
            $aliments = array_unique($aliments);
            if (!empty($aliments)) {  
              echo '<ul class="client-dashboard client-dashboard-diet">';
              foreach ($aliments as $alimento_id) {
                $alimento_post = get_post($alimento_id);
                if ($alimento_post) {
                  $thumbnail_url = get_the_post_thumbnail_url($alimento_id, 'thumbnail');
                  $title = get_the_title($alimento_id);
                  echo '<li class="ui-checkbox unchecked" data-alimentoid="' . esc_attr( $alimento_id ). '" ' .
                    ' data-termslug="' . esc_attr( $term->slug ) . '" onClick="checkAliment(this)">'
                  . '>';
                  Alimento::preview_alimento( $alimento_id, 1 );
                  echo '</li>';
                }
              }
              echo '</li>';
            } // end if aliments
            echo '</ul>';
            echo '</li>';
          }  
          echo '</ul>';
        }

        ?>

        <div class="client-dashboard" id="client-dashboard-create-diet-from-aliments">
          <select id="day-of-the-week" name="day-of-the-week">
            <option value="<?php echo esc_attr(__('Monday', 'asim')); ?>"><?php echo esc_html(__('Monday', 'asim')); ?></option>
            <option value="<?php echo esc_attr(__('Tuesday', 'asim')); ?>"><?php echo esc_html(__('Tuesday', 'asim')); ?></option>
            <option value="<?php echo esc_attr(__('Wednesday', 'asim')); ?>"><?php echo esc_html(__('Wednesday', 'asim')); ?></option>
            <option value="<?php echo esc_attr(__('Thursday', 'asim')); ?>"><?php echo esc_html(__('Thursday', 'asim')); ?></option>
            <option value="<?php echo esc_attr(__('Friday', 'asim')); ?>"><?php echo esc_html(__('Friday', 'asim')); ?></option>
            <option value="<?php echo esc_attr(__('Saturday', 'asim')); ?>"><?php echo esc_html(__('Saturday', 'asim')); ?></option>
            <option value="<?php echo esc_attr(__('Sunday', 'asim')); ?>"><?php echo esc_html(__('Sunday', 'asim')); ?></option>
          </select>

          <?php 
          $diet_from_programme = null;
          foreach ( $diets as $diet ) {
            $meta = (bool) get_post_meta( $diet->ID, '_created_from_programme', true );
            if ( $meta ) {
              $diet_from_programme = $diet;
              break;
            }
          }
          ?>
          <div class="tile tile--button" onclick="createDietFromAliments()""><?php 
          if ( $diet_from_programme ) {
            printf( __('Update diet <em>%s</em> (%s) for the selected day of the week', 'asim'), $diet_from_programme->post_title, $diet_from_programme->ID );
          } else {
            _e('Create diet out of the selected aliments', 'asim'); 
          }
          ?>
          </div>
        </div>


        <?php
        echo '</ul>';
        // NOTE: There is a JS associated to this markup, attached t wp_footer
        ?>
      </div>

      <?php
      endif;

    } // else if !programa
  ?>


<?php