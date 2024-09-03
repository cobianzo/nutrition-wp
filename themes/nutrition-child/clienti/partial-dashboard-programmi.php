<?php 

/**
 * 
 */

 extract( $args ); // $post_id. of the client


 $programs = Programma::get_programma_by_client( $post_id, false );

?>



<div class="client-dashboard client-dashboard-programme">
  <?php 
  if ( empty( $programs ) ) :

    $args = array(
      'post_type' => 'wp_block',
      'tax_query' => array(
          array(
              'taxonomy' => 'wp_pattern_category',
              'field'    => 'slug',
              'terms'    => 'programma-alimentare', // Slug de la categoría de pattern
          ),
      ),
    );

    $patterns = get_posts( $args );

    if ( empty( $patterns ) ) {
    ?>
      <h4><?php _e('There are no templates for Food Programmes. Create them in the Editor.','asim'); ?></h4>
    <?php 
    } else {
      foreach ($patterns as $pattern) {
        // Crear el enlace con el query param
        
        // Generate a nonce for the request
        $nonce = wp_create_nonce('create_programme_action');

        // Construct the URL with parameters
        $url = add_query_arg([
            'action' => 'create_programme',
            'client_id' => $post_id,
            'pattern_template' => $pattern->ID,
            'create_programme_nonce' => $nonce,
        ], admin_url('admin-post.php'));

        ob_start(); ?>

        <a class="tile tile--button" href="<?php echo esc_url($url); ?>">
            <?php echo sprintf( __( 'Create a new programme from template <b>%s</b>', 'asim' ), get_the_title( $pattern->ID ) ); ?>
        </a>

        <?php
        $html_output = ob_get_clean();
        echo $html_output;
      }
    }
  ?>

  <?php
  endif;
  ?>
</div>

<!-- End if the initial dashboard to create a new programme -->

<?php
if ( ! empty( $programs ) ) : 
?>
<h3><?php _e('Current food programme for the client.','asim'); ?></h3>
<div class="client-dashboard client-dashboard-programme">
  <?php
    
    
    foreach ( $programs as $programme ) {
      $edit_link = get_edit_post_link( $programme->ID );

      ob_start(); ?>

      <div class="tile tile--button tile--<?php echo esc_attr( get_post_status( $programme->ID ) ); ?>">
          <a href="<?php echo esc_url( $edit_link ); ?>">
              <?php echo get_the_title( $programme->ID ); ?><br/>
              <small>(<?php echo get_post_status(); ?>) <?php echo get_the_date(); ?></small>
          </a>
      </div>

      <?php
      $html_output = ob_get_clean(); 
      echo $html_output;
    }
    
  ?>
</div>
<?php
endif;