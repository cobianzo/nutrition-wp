<?php


class Dieta {

  // const ALLOWED_BLOCKS = [
  //   'core/paragraph',
  //   'core/group',
  //   'core/heading',
  //   'core/list',
  //   'asim/alimento-block', // Replace with your custom block's name
  // ];

  /**
   * Init hooks
   */
  public static function init() {

    add_action('enqueue_block_editor_assets', [__CLASS__, 'script_dieta_rules']);

    // MOVE this to generic, it's not only for Dieta.
    add_filter('allowed_block_types_all', [__CLASS__, 'restrict_gutenberg_blocks_for_diet_cpt'], 10, 2);

    // Actions in cliente-dashboard
    add_action( 'admin_post_create_diet', [ __CLASS__, 'create_diet_for_client' ] );
    add_action( 'admin_post_delete_diet_action', [__CLASS__, 'delete_diet_of_client'] );

  }

  // @TODO: maybe we need to delete all this script, after the generic rules.
  public static function script_dieta_rules() {
    $current_post_type = get_post_type();
    
    // Enqueue only for 'diet' post type
    if ( $current_post_type === 'diet' ) {
      $script_path = 'build/dieta-rules.js';
      $asset_file = get_stylesheet_directory() . '/build/dieta-rules.asset.php';
  
      if ( file_exists( $asset_file ) ) {
        $assets = include( $asset_file );
      } else {
        wp_die( sprintf('File %s not generated. Fix this first', $asset_file ) );
      }
  
      // script only for diets
      wp_enqueue_script(
        'dieta-rules-script',
        get_stylesheet_directory_uri() . '/' . $script_path,
        $assets['dependencies'],
        $assets['version'],
        true
      );
    }
  }


  /**
   * with PHP restrict the allowed blocks
   *
   * @param [type] $allowed_blocks
   * @param [type] $post
   * @return void
   */
  public static function restrict_gutenberg_blocks_for_diet_cpt($allowed_blocks, $editor) {

    $json_path      = get_stylesheet_directory_uri() . '/includes/allowed-blocks.json';
    $json_data      = file_get_contents($json_path);
    $allowed_blocks = json_decode($json_data, true);

    // Check if the current post type is 'diet'
    // if ($editor->post && $editor ->post->post_type === 'diet') {
      // if (!current_user_can('administrator')) {
        // Specify the allowed blocks
    if ( is_array($allowed_blocks) ) {
      return $allowed_blocks;
    }
    // }

    // For other post types, return the default allowed blocks
    return $allowed_blocks;
  }

  // CRUD relationship dieta-client

  /**
   * Retrieve the diet posts in an array. Normally there is only one diet per client.
   *
   * @param [type] $client_id
   * @return void
   */
  public static function get_client_diets( $client_id ) {
    // Retrieve the WP_User object associated with the client post ID
    $user = Cliente::get_client_user_by_post_id( $client_id );

    // Check if a valid WP_User object is returned
    if ( !$user || !is_a( $user, 'WP_User' ) ) {
        return array(); // Return an empty array if the user is not found
    }

    // Get the user's ID
    $user_id = $user->ID;

    // Set up the query arguments to get all diet posts authored by this user
    $args = array(
        'post_type'      => 'diet',  // CPT slug for diet
        'author'         => $user_id,
        'posts_per_page' => -1,      // Retrieve all diets for this user
        'post_status'    => 'any', // You can adjust this to include drafts, etc.
    );

    // Execute the query
    $query = new WP_Query( $args );

    // If posts are found, return them as an array
    if ( $query->have_posts() ) {
        return $query->posts;
    }

    // Return an empty array if no diets are found
    return array();

  }


  /**
   * When clicking in the button on the Dashboard.
   *
   * @return void
   */
  public static function create_diet_for_client() {
    // Verificar la acción
    if ( ! isset( $_GET['action'] ) || 'create_diet' !== $_GET['action'] ) {
        return;
    }

    // @TODO: add capability check to see if current user can create diets.

    // Verificar los parámetros necesarios
    if ( ! isset( $_GET['client_id'] ) || ! isset( $_GET['diet-category'] ) ) {
        wp_die( 'Missing required parameters.' );
    }

    $client_id = intval( $_GET['client_id'] );
    $diet_category_id = intval( $_GET['diet-category'] );

    // get the whole term
    $diet_category = get_term_by( 'id', $diet_category_id, 'diet-category' );

    // Obtener la información del cliente
    $client_post = get_post( $client_id );
    if ( ! $client_post ) {
        wp_die( 'Invalid client ID.' );
    }

    // Obtener el usuario asociado al cliente
    $user = Cliente::get_client_user_by_post_id( $client_id );
    if ( ! $user || ! is_a( $user, 'WP_User' ) ) {
        wp_die( 'Invalid user associated with the client.' );
    }

    // Crear el nuevo CPT 'diet'
    $diet_title = sprintf( __( 'Diet for client %s - %s', 'asim'), $client_post->post_title, $diet_category->name );

    // Initialize the content, if there is linked patter associated to this term.
    $content = '';
    $pattern_slug = get_field('linked_pattern_template', "term_$diet_category_id");
    $pattern_post = get_page_by_path($pattern_slug, OBJECT, 'wp_block');
    if(!empty($pattern_post)){
      $content = $pattern_post->post_content;
    }

    $diet_post = array(
        'post_title'   => $diet_title,
        'post_type'    => 'diet',
        'post_status'  => 'draft', // Cambiar si es necesario
        'post_author'  => $user->ID,
        'post_content' => $content,
        'meta_input'   => array(
            '_related_client_id' => $client_id, // Guardar la relación con el cliente si es necesario
        ),
    );

    $diet_id = wp_insert_post( $diet_post );

    if ( is_wp_error( $diet_id ) ) {
        wp_die( 'Error creating the diet post.' );
    }

    // Asociar la nueva 'diet' con el término 'diet-category'
    wp_set_post_terms( $diet_id, array( $diet_category_id ), 'diet-category' );

    // Redirigir a la página de edición del nuevo CPT 'diet'
    wp_redirect( admin_url( "post.php?post={$diet_id}&action=edit&create_new_from_template=" 
      . (empty($pattern_post)? '0' : $pattern_post->ID) ) );
    exit;
  }


  public static function delete_diet_of_client() {
    // Check if the nonce is set and verify it
    // Check if the nonce is set
    if (!isset($_GET['nonce'])) {
      wp_die('Nonce is missing');
    }

    // Verify the nonce
    $nonce = $_GET['nonce'];
    $post_id = intval($_GET['post_id']);
    $redirect = urldecode( $_GET['redirect'] );
    
    if ( ! wp_verify_nonce($nonce, 'delete_diet_nonce_action' ) ) {
        wp_die( 'Nonce verification failed for ' . $post_id . ': ' . $nonce);
    }

    // Check if user has permission to delete the post
    if (!current_user_can('delete_posts')) {
        wp_die('You do not have permission to delete this post');
    }

    // Get the post ID from the query parameter
    $post_id = intval($_GET['post_id']);

    // Check if the post exists and is of type 'diet'
    if (get_post_type($post_id) === 'diet') {
        // Delete the post
        wp_delete_post($post_id, true); // true for force delete
    }

    // Redirect back to the edit page or another location
    if (! $redirect ) {
      $redirect = admin_url( 'edit.php?post_type=diet' );
    }
    wp_redirect( $redirect );
    exit;
  }

}

Dieta::init();





add_action('add_meta_boxes', 'add_diet_metabox');
function add_diet_metabox() {
  add_meta_box(
      'diet_metabox_id',                // Unique ID
      'Related Client Post',            // Title
      'display_diet_metabox',           // Callback function
      'diet',                           // Post type
      'side',                           // Context
      'high'                            // Priority
  );
}

function display_diet_metabox( $post ) {
  $the_user_owner_id = $post->post_author;
  $client_post = Cliente::get_client_post_by_user_id( $the_user_owner_id );
  if ( $client_post ) {
    $edit_link = get_edit_post_link( $client_post->ID );
    echo '<p><a href="' . esc_url( $edit_link ) . '">' . $client_post->post_title . '</a></p>';
  }
}
