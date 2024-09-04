<?php

// TODO: create one for 
// actions, 
// and another file for meta boxes

require_once __DIR__ . '/cpt-taxo-dieta.php';
require_once __DIR__ . '/class-dieta-helpers.php';


class Dieta {

  // placeholders: '[TITLE]', '[ALIMENTO_ID]', '[IMG_SRC]', '[MEAL_TYPE]',
  const TEMPLATE_ALIMENTO_WITH_PLACEHOLDERS = '<!-- wp:asim/alimento-block {"title":"[TITLE]","alimentoID":"[ALIMENTO_ID]","imgSrc":"[IMG_SRC]","mealType":"[MEAL_TYPE]"} --><div class="wp-block-asim-alimento-block"><div class="alimento-left-column"></div></div><!-- /wp:asim/alimento-block -->';

  // placeholders: '[DAY_OF_THE_WEEK]', '[DAY_OF_THE_WEEK_SLUG]', '[CONTENT]'
  const TEMPLATE_DAY_OF_WEEK_WITH_PLACEHOLDERS = '<!-- wp:group {"className":"giorno-settimana giorno-[DAY_OF_THE_WEEK_SLUG]","layout":{"type":"default"}} --><div id="[DAY_OF_THE_WEEK_SLUG]" class="wp-block-group giorno-settimana giorno-[DAY_OF_THE_WEEK_SLUG]"><!-- wp:heading --><h2 class="wp-block-heading" id="title-monday">[DAY_OF_THE_WEEK]</h2><!-- /wp:heading -->[CONTENT]</div><!-- /wp:group -->';

  /**
   * Init hooks
   */
  public static function init() {

    // add_action('enqueue_block_editor_assets', [__CLASS__, 'script_dieta_rules']);
    // We don't use this anymore. It weas about creating a rule to force using a group block 
    // at the top level of the content.

    
    // Actions in cliente-dashboard
    add_action( 'admin_post_create_diet', [ __CLASS__, 'create_diet_for_client' ] );
    add_action( 'admin_post_delete_diet_action', [__CLASS__, 'delete_diet_of_client'] );

    add_action( 'admin_post_create_diet_from_aliments', [ __CLASS__, 'create_diet_from_aliments' ] );


    // info sidebar metabox. prescidible
    add_action('add_meta_boxes', function() {
      add_meta_box(
        'diet_metabox_id',                // Unique ID
        'Related to this Diet',            // Title
        [__CLASS__, 'display_diet_metabox'],           // Callback function
        'diet',                           // Post type
        'side',                           // Context
        'high'                            // Priority
      );
    });


    add_filter( 'default_content', function ( $content, $post ) {
      if ( 'diet' === $post->post_type && empty( $post->post_content ) ) {
          $content = '<!-- wp:group -->
          <div class="wp-block-group"><!-- wp:paragraph -->
          <p>Delete this paragraph and start using Aliment Blocks here.</p>
          <!-- /wp:paragraph --></div>
          <!-- /wp:group -->';
      }
      return $content;
    }, 10, 2 );

    // sync the ACF field with the CPT author. The post_author is the one that counts, but the ACF
    // makes it easier to identify for the editor.
    add_action( 'save_post', [__CLASS__, 'sync_cliente_owner_with_author'] );

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


  

  // CRUD relationship dieta-client

  /**
   * Retrieve the diet posts in an array. Normally there is only one diet per client.
   *
   * @param [type] $client_id
   * @return array
   */
  public static function get_diets_by_client( $client_id ) {
    // Retrieve the WP_User object associated with the client post ID
    $user = Relation_Cliente_User::get_client_user_by_post_id( $client_id );

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
    $query = new \WP_Query( $args );

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
    // Verificar la acción @TODO: add a nonce no?
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
    $user = Relation_Cliente_User::get_client_user_by_post_id( $client_id );
    if ( ! $user || ! is_a( $user, 'WP_User' ) ) {
        wp_die( 'Invalid user associated with the client.' );
    }

    // Crear el nuevo CPT 'diet'
    $diet_title = sprintf( __( 'Diet for client %s', 'asim'), $client_post->post_title ); // , $diet_category->name

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
    if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
      wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
      exit;
    } 
    exit;
  }


  /**
   * Informational metabox on sidebar. Not important.
   *
   * @param [type] $post
   * @return void
   */
  public static function display_diet_metabox( $post ) {
    $the_user_owner_id = $post->post_author;
    $client_post       = Relation_Cliente_User::get_client_post_by_user_id( $the_user_owner_id );
    if ( $client_post ) {
      $edit_link = get_edit_post_link( $client_post->ID );
      echo '<p>Client: <a href="' . esc_url( $edit_link ) . '">' . $client_post->post_title . '</a></p>';
    }

    $programme = get_post_meta( $post->ID, '_created_from_programme', true );
    if ( $programme && get_post_status( $programme )) {
      $edit_link = get_edit_post_link( $programme );
      echo '<p>Created from programme: <a href="' . esc_url( $edit_link ) . '">' . get_the_title( $programme ) . '</a></p>';
    }
  }

  /**
   * Diet CPT, sync ACF with post_author
   *
   * @param [type] $post_id
   * @return void
   */
  public static function sync_cliente_owner_with_author( $post_id ) {
    // Avoid infinite loops
    remove_action( 'save_post', [__CLASS__, 'sync_cliente_owner_with_author'] );

    // Get the post object
    $post = get_post( $post_id );

    // Check if the post is of type 'diet'
    if ( 'diet' !== $post->post_type ) {
        return;
    }

    // Get the current ACF field value
    $cliente_owner = get_field( 'cliente_owner', $post_id );

    // Get the post author ID
    $post_author_id = $post->post_author;

    // Check if the ACF field is empty
    if ( empty( $cliente_owner ) ) {
        // Check if the post author has the 'client' role
        $post_author = get_userdata( $post_author_id );
        if ( in_array( 'client', (array) $post_author->roles ) ) {
            // Update the ACF field with the post author ID
            update_field( 'cliente_owner', $post_author_id, $post_id );
        }
    } else {
        // If the ACF field is not empty, sync the post author with the ACF field
        $new_author_id = intval( $cliente_owner );
        $new_author    = get_userdata( $new_author_id );

        // Check if the new author has the 'client' role
        if ( in_array( 'client', (array) $new_author->roles ) ) {
            // Update the post author
            wp_update_post( array(
                'ID'          => $post_id,
                'post_author' => $new_author_id,
            ) );
        }
    }

    // Re-add the hook to avoid disrupting other save_post actions
    add_action( 'save_post', [__CLASS__, 'sync_cliente_owner_with_author'] );
  }


  static public function create_diet_from_aliments() {
    // Very basic nonce verification
    if ( ! isset( $_POST['create_diet_from_aliments_nonce'] ) || ! wp_verify_nonce( $_POST['create_diet_from_aliments_nonce'], 'create_diet_from_aliments_action' ) ) {
      wp_die( 'Invalid nonce.' );
    }

    $client_id = isset( $_POST['client_id'] ) ? intval( $_POST['client_id'] ) : 0;
    $programme_id = isset( $_POST['programme_id'] ) ? intval( $_POST['programme_id'] ) : 0;
    $client_post = get_post( $client_id );
    if ( ! $client_post ) {
        wp_die( 'Invalid client ID.' );
    }

    $user = Relation_Cliente_User::get_client_user_by_post_id( $client_id );
    if ( ! isset( $user->ID ) ) {
      wp_die( 'Invalid user ID.' );
    }

    $diets                        = Dieta::get_diets_by_client( $client_id );
    $existing_diet_from_programme = null;
    foreach ( $diets as $diet ) {
      $existing_diet_from_programme = get_post_meta( $diet->ID, '_created_from_programme', true );
      if ( $existing_diet_from_programme ) {
        $existing_diet_from_programme = $diet;
        break;
      }
    }

    $content = '';
  
    $day_of_the_week = isset( $_POST['day_of_the_week'] ) ? $_POST['day_of_the_week'] : __( 'Monday', 'asim' );
    $day_of_the_week_slug = sanitize_title( $day_of_the_week );

    $days_of_the_week = array(
        __( 'Monday', 'asim' ),
        __( 'Tuesday', 'asim' ),
        __( 'Wednesday', 'asim' ),
        __( 'Thursday', 'asim' ),
        __( 'Friday', 'asim' ),
        __( 'Saturday', 'asim' ),
        __( 'Sunday', 'asim' )
    );

    

    $terms = get_terms(array(
      'taxonomy' => 'meal',
      'orderby'  => 'id',
      'order'    => 'desc',
    ));
          
    if (!empty($terms) && !is_wp_error($terms)) {
      $aliment_blocks = '';
      foreach ($terms as $term) {
        // echo '<br>TODELETE: evaluating ' . $term->slug .' <br>';
        $key                = $term->slug . '_aliments';
        $aliment_ids_string = isset( $_POST[$key] ) ? $_POST[$key] : '';
        $aliment_ids        = explode(',', $aliment_ids_string);
        
        $count = 0;
        foreach ($aliment_ids as $aliment_id) {
          $count++;
          if ( ! get_post_status( $aliment_id ) ) {
            continue;
          }

          $mark_as_alternative = (0 === ($count % 2));

          $block_for_aliment = str_replace( 
            [ 
              '[TITLE]',
              '[ALIMENTO_ID]',
              '[IMG_SRC]',
              '[MEAL_TYPE]',
            ], 
            [
              ! $mark_as_alternative? $term->name : __('Alternative', 'asim'),
              $aliment_id,
              get_the_post_thumbnail_url( $aliment_id, 'thumbnail' ),
              $term->slug,
            ], 
            self::TEMPLATE_ALIMENTO_WITH_PLACEHOLDERS );

            if ($mark_as_alternative) {
              $block_for_aliment = str_replace( '"mealType"', '"isAlternative":true,"mealType"', $block_for_aliment );
            }

            $aliment_blocks .= $block_for_aliment;
        }

        // If the aliment is the second aliment, mark it as alternative
      }
      
        // echo ! $is_day_of_the_week_replaced ? ' TODELET NOT REPLACED. Adding afterwards' : 'not existing diet, creating one.';
      $content .= str_replace(
        [ '[DAY_OF_THE_WEEK]', '[DAY_OF_THE_WEEK_SLUG]', '[CONTENT]' ],
        [ $day_of_the_week, $day_of_the_week_slug, $aliment_blocks ],
        self::TEMPLATE_DAY_OF_WEEK_WITH_PLACEHOLDERS
      );
      

      $is_day_of_the_week_replaced = false;
      if ( $existing_diet_from_programme ) {
        
        // if the content existed, we need to parse it, find the day of week, and replace the content
        $current_content_as_array = parse_blocks( $existing_diet_from_programme->post_content ); 
        $aliment_blocks_as_array  = parse_blocks( $content );
        
        
        
        // find the block that contains the id=day_of_the_week-slug, and replace it with $aliment_blocks_as_array
        $new_content = '';
        foreach ($current_content_as_array as $index => $block) {
          
          if ( isset( $block['blockName'] ) && $block['blockName'] === 'core/group' &&
            isset( $block['attrs'] ) && isset( $block['attrs']['className'] ) 
              && (false !== strpos( $block['attrs']['className'], 'giorno-' . $day_of_the_week_slug )) ) {
            // Found the group with the right id of the day of the week. We update its content

            // dd( htmlentities(serialize_block($block)));
            $new_content .= $content; 

            $is_day_of_the_week_replaced = $index;
            
          } else {
            $new_content .= serialize_block( $block );
          }
        }
        if ( false !== $is_day_of_the_week_replaced ) {
          $content = $new_content;
        } else {
          $content = $existing_diet_from_programme->post_content . $content;
        }
        
      }

      // Now the content is ready to replace the day of the week in template
    }
    // echo 'TODELETE dcsfd';
    // ddie( htmlentities($content));

    // find out if there is existing diet.
    

    // Create a new diet post. 
    $diet_title = sprintf( __( 'Diet for client %s', 'asim'), $client_post->post_title );
    $diet_post = array(
      'post_title'   => $existing_diet_from_programme ? $existing_diet_from_programme->post_title : $diet_title,
      'post_type'    => 'diet',
      'post_status'  => $existing_diet_from_programme ? 'draft' : $existing_diet_from_programme->post_status, // Cambiar si es necesario
      'post_author'  => $user->ID,
      'post_content' => $content,
      'meta_input'   => array(
          '_related_client_id'      => $client_id, // Guardar la relación con el cliente si es necesario
          '_created_from_programme' => $programme_id, // Guardar la relación con el cliente si es necesario
      ),
    );

    // $blocks = parse_blocks( $content );  verification not needed

    if ( $existing_diet_from_programme ) {
      $diet_id = $existing_diet_from_programme->ID; 
      $diet_post = [
        'ID' => $diet_id,
        'post_content' => $content
      ];
      wp_update_post( $diet_post );
    } else {
      $diet_id = wp_insert_post( $diet_post );
    }


    if ($diet_id) {
      update_post_meta( $client_id, '_diet_from_aliments', $diet_id );
      // once finished, redirect to the just created post edit page
      if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
        wp_safe_redirect( $_SERVER['HTTP_REFERER'] );
        exit;
      } 
      wp_safe_redirect( get_edit_post_link( $diet_id ) );
      exit;
    } else {
      wp_die('Error creating diet.'); 
    }
    
    exit;
  } 
}

Dieta::init();

