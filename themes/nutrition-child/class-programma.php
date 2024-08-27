<?php


class Programma {

  /**
   * Init hooks
   */
  public static function init() {

    // when clicking on Create Programma from template in client Dashboard.
    add_action('admin_post_create_programme', [__CLASS__, 'handle_create_programme'] );

    // Save the alimenti in the programma. If we create a diet from this programme, they might contain these aliments
    add_action( 'save_post', [__CLASS__, 'save_alimento_ids_as_post_meta'], 10, 1 );

    add_action('init', [__CLASS__, 'register_alimento_meta']);
    // Meta box to show the alimenti in the sidebar
    add_action('add_meta_boxes', [__CLASS__, 'add_alimento_meta_box']);

  }


  public static function handle_create_programme() {
    
    // Verify the nonce
    if ( ! isset($_GET['create_programme_nonce']) || ! wp_verify_nonce($_GET['create_programme_nonce'], 'create_programme_action') ) {
      wp_die('Nonce verification failed');
    }

    // Check user permissions (optional) @TODO: do it with a custmo capability for programmes
    if ( ! current_user_can('edit_posts') ) {
      wp_die('You are not allowed to perform this action');
    }

    if ( empty( $_GET['client_id'] ) ) {
      wp_die('No client ID');
    }
    
    $client = get_post( intval( $_GET['client_id'] ) );
    if ( empty( $client ) ) {
      wp_die('No client found');
    }
    $user = Cliente::get_client_user_by_post_id( $client->ID );
    if ( ! is_a( $user, 'WP_User' ) ) {
      wp_die('No user for client ' . $client->ID  );
    }

    // Process the data from GET parameters
    if ( isset($_GET['pattern_template']) && ! empty($_GET['pattern_template']) ) {
      $pattern_post = get_post( intval( sanitize_text_field($_GET['pattern_template']) ) );
      
      $content = ''; // @TODO: add a fallback for the content of the new programme if we didnt find a template
      if( !empty($pattern_post) ){
        $content = $pattern_post->post_content;
      }

      // Create the programme (insert a custom post type, etc.)
      $new_programme_id = wp_insert_post([
          'post_title' => sprintf( __( 'Food Programme for %s', 'asim' ), get_the_title( $client ) ),
          'post_type'  => 'programme',
          'post_status' => 'publish',
          'post_author'  => $user->ID,
          'post_content' => $content,
          'meta_input'   => array(
              '_related_client_id' => $client_id, // Guardar la relación con el cliente si es necesario
          ),
      ]);

      // Redirect after successful creation
      if ( ! is_wp_error($new_programme_id) ) {
          wp_redirect( add_query_arg('status', 'success', wp_get_referer()) );
          exit;
      } else {
          wp_die('Failed to create programme.');
      }
    }

    wp_die('No programme name provided.');
  }


  public static function save_alimento_ids_as_post_meta( $post_id ) {
    // Check that this is not an autosave or a revision

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
      return;
    }
    if ( 'programme' !== get_post_type( $post_id ) ) {
      return;
    }

    // Get the post content
    $post_content = get_post_field( 'post_content', $post_id );

    // Parse the blocks
    $blocks = parse_blocks( $post_content );

    $alimento_ids = [];

    // Loop through the blocks and collect alimentoID
    foreach ( $blocks as $block ) {
      if ( 'asim/piatto-block' === $block['blockName'] && isset( $block['attrs']['alimentoID'] ) ) {
          $alimento_ids[] = $block['attrs']['alimentoID'];
      }
    }

    // Save the alimentoIDs as post meta (could be serialized if multiple)
    if ( ! empty( $alimento_ids ) ) {
        update_post_meta( $post_id, '_alimento_ids', $alimento_ids );
    } else {
        delete_post_meta( $post_id, '_alimento_ids' );
    }
  }


  
  public static function register_alimento_meta() {
    register_post_meta('post', '_alimento_ids', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'array',
    ));
  }

  public static function add_alimento_meta_box() {
    add_meta_box(
        'alimento_meta_box',               
        __('Alimento IDs', 'asim'), 
        function ($post) {
          // Get stored alimento IDs from post meta
          $alimento_ids = get_post_meta($post->ID, '_alimento_ids', true);
          if (!empty($alimento_ids)) {
              echo '<div class="alimento-meta-box">';
              foreach ($alimento_ids as $alimento_id) {
                  $alimento_post = get_post($alimento_id);
                  if ($alimento_post) {
                      $thumbnail_url = get_the_post_thumbnail_url($alimento_id, 'thumbnail');
                      $title = get_the_title($alimento_id);
                      if ($thumbnail_url) {
                          echo '<div class="alimento-item">';
                          echo '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($title) . '" style="width: 50px; height: 50px; margin-right: 10px;" />';
                          echo '<span>' . esc_html($title) . '</span>';
                          echo '</div>';
                      }
                  }
              }
              echo '</div>';
          } else {
              echo '<p>' . __('No Alimento IDs found.', 'asim') . '</p>';
          }
        },
        'programme',                             // Post type where the box appears
        'side',                             // Location (side, normal, advanced)
        'high'                              // Priority (default, high, low)
    );
  }
}

Programma::init();