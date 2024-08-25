<?php


class Programma {

  /**
   * Init hooks
   */
  public static function init() {

    add_action('init', [__CLASS__, 'piatto_block_register'] );

    // when clicking on Create Programma from template in client Dashboard.
    add_action('admin_post_create_programme', [__CLASS__, 'handle_create_programme'] );

  }

  /**
   * Define block
   *
   * @return void
   */
  public static function piatto_block_register() {

    // Start of the block development
    $block_file = __DIR__ . '/gutenberg/piatto-block/';
    register_block_type( $block_file );

    $a = wp_set_script_translations( 'asim-piatto-block-editor-script', 'asim', get_stylesheet_directory_uri() . '/languages' );
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
}

Programma::init();