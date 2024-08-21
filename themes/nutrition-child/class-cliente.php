<?php
/**
 * Package: asim
 * File: class-cliente.php
 * 
 * Class Cliente
 * We sync the CPT `client` with WP User.
 * Case studies:
 * - Creating a new `client` => creates a new WP User
 * - Creating a new `client` with an exisitng user email => Error
 * - Creating a new `client` with the same title of an existing one => Rename the WP user name with a suffix.
 * - Updating an existing `client` with a new email => updates the WP User
 * - Updating an existing `client` with an existing email of another user => error
 * - Updating an existing `WP User` => updates the associated `client`
 * - Deleting a `client` from the Trash => deletes the associated WP User
 * - @TODO: Deleting a WP User `client` => deletes the associated CPT `client`
 * 
 * @TODO: test sending password email after new user creation.
 * @TODO: Hardcode the creation of the role `client` here.
 * 
 * sync Featured images with user avatar.
 */

require_once __DIR__ . '/includes/cpt-tax/cpt-cliente.php';

class Cliente {

	/**
	 * Meta key for the WP user ID associated to the CPT.
	 */
	const META = '_wp_client_user_id';
	/**
	 * Init hooks
	 *
	 * @since 1.0
	 */
	public static function init() {
		
		// When creating or updating the post, we need to create or update the WP User
		add_action(
			'wp_insert_post',
			array( __CLASS__, 'create_update_user_from_client' ),
			10, 3
		);

		add_action(
			'before_delete_post', // we can use `before_delete_post` which executes when the Trash is emptied.
			array( __CLASS__, 'delete_user_from_client' )
		);

    // Updates (from CPT >> WP User and from WP User >> CPT)
    // add_action( 
    //   'save_post',
    //   array( __CLASS__, 'update_user_from_client' )
    // );

		// Update (from WP User >> CPT)
    add_action( 'profile_update', [__CLASS__, 'sync_client_from_wpuser'], 10, 2 );

		// We don't let change the email if there is another user with that email.
		// This applies on new users and updating existing users.
		add_filter( 'acf/validate_value', function( $valid, $value, $field, $input_name ) {
			// Check if the field is the one you want to add extra rules to
			if ( $field['name'] !== 'email' || false === strpos( $input_name, 'field_66a29ebbb49bd' ) ) {
				return $valid;
			}
			
			$post_id = isset($_REQUEST['post_id'])? $_REQUEST['post_id'] : false;
			if ( $post_id ) {
				$old_value = get_post_meta( $post_id, 'email', true );
				if ( $old_value !== $value ) {
					 	// there was an update, we need to make sure that the new email doesn't already exist
					 	$user_exists = get_user_by( 'email', $value );
						if ($user_exists) {
							return sprintf( __( 'Email already exists: <a target="_blank" href="%s"><b>%s</b></a>', 'asim' ),
								get_edit_user_link($user_exists->ID),
								$user_exists->user_email );
						}
				}
			}

			return $valid;
		}, 10, 4 );

		/**
		 * Simply extra info for the edit.php `client` CPT, nothing else
		 */
		add_filter('acf/load_field', function( $field ) {
			if ( 'field_66a35e82dcb60' === $field['key'] ) {
				$message = '';
				$post_id = isset($_REQUEST['post'])? $_REQUEST['post'] : false;
				if ( $post_id ) {

				} else {
					$message .= __( '<h3>Post ID not found</h3>', 'asim' );
				}

				$message .= __( '<h3>Associated WP User profile:</h3>', 'asim' );
				$user = self::get_client_user_by_post_id( $post_id );
				if ( $user ) {
					$message .= sprintf( __( '<br> - User ID: <b>%s</b>', 'asim' ), $user->ID );
					$message .= sprintf( __( '<br> - User login: <b>%s</b>', 'asim' ), $user->user_login );
					$message .= sprintf( __( '<br> - User email: <b>%s</b>', 'asim' ), $user->user_email );
					$message .= sprintf( __( '<br> - Link: <a href="%s" target="_blank">click here to edit %s\'s user profile</a>', 'asim' ), get_edit_user_link($user->ID), $user->display_name );
				} else {
					$message .= sprintf( __( 'Not user associated for post ID %s', 'asim' ), $post_id );
					$message .= get_post_meta( $post_id, self::META, true );
				}

				$field['message'] = $message;
			};
			return $field;
		});

		/**
		 * Simply extra info for the user-edit.php?user_id=xx page, nothing else
		 */
		add_filter('acf/load_field', function( $field ) {
			// Client Data field
			if ( 'field_66a37144b2159' === $field['key'] && 
				current_user_can( 'edit_clients' )
			) {
				$message = '';

				$user_id = isset($_REQUEST['user_id'])? $_REQUEST['user_id'] : false;

				if ( $user_id ) {
					$client_post = self::get_client_post_by_user_id( $user_id );
					if ( $client_post ) {
						$message .= sprintf( __( '<br> - Post ID: <b>%s</b>', 'asim' ), $client_post->ID );
						$message .= sprintf( __( '<br> - Post link: <a href="%s" target="_blank">click here to edit %s\'s post</a>', 'asim' ), get_edit_post_link($client_post->ID), $client_post->post_title );
					} else {
						$message .= sprintf( __( 'No post associated with user ID <b>%s</b>', 'asim' ), $user_id );
					}
				}

				$field['message'] = $message;
			}

			return $field;
		}, 10, 1 );

		// Custom HTML for diets dashboard:
    add_filter('acf/load_field', [__CLASS__, 'client_dashboard_for_diets'] );
		
		// Custom HTML for programmi dashboard:
    add_filter('acf/load_field', [__CLASS__, 'client_dashboard_for_programmes'] );
    
    // Add admin styles for Edit client in CMS:
    require_once( __DIR__ . '/admin/admin-styles.php' );
	}

	/**
	 * Create user from client post type.
	 *
	 * @since 1.0
	 *
	 */
	public static function create_update_user_from_client( $post_id, $post, $update ) {
		
		if ( $post->post_status !== 'publish' || $post->post_type !== 'client' ) {
			return;
    }


		// to avoid calling the function multiple times
		remove_action('profile_update', [__CLASS__, 'sync_client_from_wpuser' ] );


		// Before we go, we check if there is a user with that email already, using our static function
		$user_exists = self::get_client_user_by_post_id( $post->ID );
		
		// ACF fields to WP User: 
		$acf_to_wpuser_fields = [
			'first_name' => get_field( 'name', $post_id ),
			'last_name' => get_field( 'surname', $post_id ),
		];

		$acf_to_acf_fields = [
			'address'         => get_field( 'address', $post_id ),
			'telephone'       => get_field( 'telephone', $post_id ),
			'sex'             => get_field( 'sex', $post_id ),
			'born'            => get_field( 'born', $post_id ),
			'profession'      => get_field( 'profession', $post_id ),
			'pathology'       => get_field( 'pathology', $post_id )
		];

		if ( ! $user_exists ) {
			// Only in new posts.

			$email    		 = get_field( 'email', $post->ID );
			$user_by_email = get_user_by( 'email', $email ); // this should be false

			// If there is already a user with that email. Trigger error log in this is a new post?
			if ( $user_by_email ) {
				return;
			}

			$username  = sanitize_user( $post->post_title, true );
			// the username must be unique:
			$appendix = 2;
			while ( username_exists( $username ) ) {
				$username .= "-$appendix";
				$appendix++;
			}

			$user_data = array(
				'user_login' => $username,
				'user_email' => $email,
				'user_pass'  => wp_generate_password(12, true, true),
				'role'       => 'client',
			);

			$ff = array_merge($user_data, $acf_to_wpuser_fields );
			$user_id = wp_insert_user( $ff );

			// Change the owner of the post to the associated user and create a post meta for easy identification
			wp_update_post( array( 'ID' => $post->ID, 'post_author' => $user_id ), false, false );
			update_post_meta( $post->ID, self::META, (int) $user_id );

		} else {
			// If updating an existing client post:
			$user_data = ! ( $user_exists instanceof WP_User )? [] : get_object_vars( $user_exists->data );

			// If updating an existing client post:
			// If the email has changed, we update the email of the WP user too.
			// NOTE: if the new email corresponds to another existing user, there is another hook `acf/validate_value` to prevent it.
			$email = get_field( 'email', $post->ID );
			if ( $email !== $user_data['user_email'] ) {
				$acf_to_wpuser_fields['user_email'] = $email;
			}

			$user_id = $user_data['ID'];
			$ff = array_merge( $user_data, $acf_to_wpuser_fields );
			wp_update_user( $ff );
		}

		// for both creation or update, we update the acf fields of the WP User
		foreach ( $acf_to_acf_fields as $field_name => $new_value ) {
			if ($field_name === 'pathology') {

				$up = update_user_meta( 37, $field_name, [5] );

				// sync the ACF field with the terms of the CPT
				if ( is_array($new_value) ) {
					$term_ids = array_map(function($term_id) {
						return (int) $term_id;
					}, $new_value);
					wp_set_post_terms($post_id, $term_ids, 'diet-category');
				}
				
				
				// and sync the ACF field for the user too.
			} else {
				update_field( $field_name, $new_value, 'user_' . $user_id );
			}
				
		} //end foreach.

		// return to activate the hook.
		add_action( 'profile_update', [__CLASS__, 'sync_client_from_wpuser' ] );

	}


	public static function sync_client_from_wpuser( $user_id, $old_user_data ) {
		$user = get_user_by( 'id', $user_id );
		$user_data = ! ( $user instanceof WP_User )? [] : get_object_vars( $user->data );
		$new_email = $user_data['user_email'];
		if ( $old_user_data->user_email !== $new_email ) {
			self::update_client_email_post_from_user( $user_id, $new_email, $old_user_data->user_email );
		}

		$client_post = self::get_client_post_by_user_id( $user_id );
		update_field( 'name', $user_data['first_name'], $client_post->ID );
		update_field( 'surname', $user_data['last_name'], $client_post->ID );
	
		// rest of fields
		$acf_to_acf_fields = [
			'address'         => get_field( 'address', 'user_' . $user_id ),
			'telephone'       => get_field( 'telephone', 'user_' . $user_id ),
			'sex'             => get_field( 'sex', 'user_' . $user_id ),
			'born'            => get_field( 'born', 'user_' . $user_id ),
			'profession'      => get_field( 'profession', 'user_' . $user_id ),
		];
		foreach ( $acf_to_acf_fields as $field_name => $new_value ) {
			update_field( $field_name, $new_value, $client_post->ID );
		}

	}

	/**
	 * Delete WP User when the `client` post type is permanently deleted (from the Trash)
	 *
	 * @since 1.0
	 *
	 * @param int $post_id Post ID.
	 */
	public static function delete_user_from_client( $post_id ) {
		if ( 'client' !== get_post_type( $post_id ) ) {
			return;
		}
		// executes when deleted from the Trash!
		$user = self::get_client_user_by_post_id( $post_id );
		if ( $user ) {
			$current = get_current_user_id();
			wp_delete_user( $user->ID, $current );
		}
	}
  
  
	/**
	 * Update user from client post type on save_post
	 *
	 * @since 1.0
	 *
	 * @param int $post_id Post ID.
	 */
	public static function update_user_from_client( $post_id ) {
		if ( 'client' !== get_post_type( $post_id ) ) {
			return;
		}
		$old_email      = get_field( 'email', $post_id, true );
		$email          = get_field( 'email', $post_id );
		$wp_user_fields = [];
		if ( $old_email !== $email ) {
			$user_id = self::get_client_post_by_user_id( $post_id );
			if ( $user_id ) {
				$wp_user_fields = array_merge( $wp_user_fields, [
					'ID'         => $user_id,
					'user_email' => $email,
				] );
			}
		}

		// now the rest of user fields:
		$name    = get_field( 'name', $post_id );
		$surname = get_field( 'surname', $post_id );
		$wp_user_fields = array_merge( $wp_user_fields, [
			'first_name' => $name,
			'last_name'  => $surname,
		] );

		if ( ! empty( $wp_user_fields ) ) {
			wp_update_user( $wp_user_fields );
		}
	}


	/**
	 * Update ACF field `email` from the post `client` when a WP User changes his email
	 * on profile_update
	 * 
	 * @since 1.0
	 *
	 * @param int $user_id WP user ID.
	 * @param string $new_email New email.
	 * @param string $old_email Old email.
	 */
	public static function update_client_email_post_from_user( $user_id, $new_email, $old_email ) {
		$client_post = self::get_client_post_by_user_id( $user_id );
		if ( $client_post ) {

			update_field( 'email', $new_email, $client_post->ID );
		}
	}

	

	/**
	 * Get the unique post type `client` for the given WP user
	 *
	 * @since 1.0
	 *
	 * @param int $user_id WP user ID.
	 * @return WP_Post|null The `client` post or null if not found.
	 */
	public static function get_client_post_by_user_id( $user_id ) {
		$args = array(
			'post_type'      => 'client',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'     => self::META,
					'value'   => $user_id,
					'compare' => '=',
				)
			),
		);
		$query = new \WP_Query( $args );
		if ( $query->have_posts() ) {
			return $query->posts[0];
		}
		return null;
	}

  /**
   * Get the unique WP user `client` for the given `client` post type.
   *
   * @since 1.0
   *
   * @param int $post_id client post ID.
   * @return WP_User|null The WP User
   */
  public static function get_client_user_by_post_id( $post_id ) {
    // $client = get_post_field( 'post_author', $post_id );
		$client_double_check = get_post_meta( $post_id, self::META, true );
    if ( $client_double_check ) {
      return get_user_by( 'id', $client_double_check );
    }
    return null;
  }

  /** Dashboard in client CMS page
	 * See cliente-dashboard.php
	*/
  public static function client_dashboard_for_diets( $field ) {

    if ( 'field_66aa86c7f526b' === $field['key'] ) {

      $post_id = isset($_REQUEST['post'])? $_REQUEST['post'] : false;

      $field['label'] = 'Actions';
      ob_start();
      get_template_part( 'cliente-dashboard', '', ['post_id' => $post_id] );
      $field['message'] = ob_get_clean();
    }
    
    return $field;
  }


  /** Dashboard in client CMS page
	 * See cliente-dashboard.php
	*/
  public static function client_dashboard_for_programmes( $field ) {

    if ( 'field_66c5c6d769b16' === $field['key'] ) {

      $post_id = isset($_REQUEST['post'])? $_REQUEST['post'] : false;

      $field['label'] = 'Actions';
      ob_start();
      get_template_part( 'cliente-dashboard-programmi', '', ['post_id' => $post_id] );
      $field['message'] = ob_get_clean();
    }
    
    return $field;
  }

	/**
	 * Action when clicked on the button to create a Diet in the Client Dashboard
	 *
	 * @return void
	 */
	function create_diet_for_client() {
    // Verificar la acción
    if ( ! isset( $_GET['action'] ) || 'create_diet' !== $_GET['action'] ) {
        return;
    }

    // Verificar los parámetros necesarios
    if ( ! isset( $_GET['client_id'] ) || ! isset( $_GET['diet-category'] ) ) {
        wp_die( 'Missing required parameters.' );
    }

		// @TODO: verify that current user can create diets.
		

    $client_id = intval( $_GET['client_id'] );
    $diet_category_id = intval( $_GET['diet-category'] );

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
    $diet_title = 'Diet for client ' . $client_post->post_title;

    $diet_post = array(
        'post_title'   => $diet_title,
        'post_type'    => 'diet',
        'post_status'  => 'draft', // Cambiar si es necesario
        'post_author'  => $user->ID,
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
    wp_redirect( admin_url( "post.php?post={$diet_id}&action=edit" ) );
    exit;
}

/**
 * Action when clicked on the button to create a Programme in the Client Dashboard
 *
 * @return void
 */
function create_programme_for_client() {
	// Verificar la acción
	if ( ! isset( $_GET['action'] ) || 'create_programme' !== $_GET['action'] ) {
			return;
	}

	// Verificar los parámetros necesarios
	if ( ! isset( $_GET['client_id'] ) || ! isset( $_GET['diet-category'] ) ) {
			wp_die( 'Missing required parameters.' );
	}

	// @TODO: verify that current user can create programmes.
	

	$client_id        = intval( $_GET['client_id'] );
	$diet_category_id = intval( $_GET['diet-category'] );

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

	// Crear el nuevo CPT 'programme'
	$programme_title = 'Food programme for client ' . $client_post->post_title;

	$programme_post = array(
			'post_title'   => $programme_title,
			'post_type'    => 'programme',
			'post_status'  => 'draft', // Cambiar si es necesario
			'post_author'  => $user->ID,
			'meta_input'   => array(
					'_related_client_id' => $client_id, // Guardar la relación con el cliente si es necesario
			),
	);

	$programme_id = wp_insert_post( $programme_post );

	if ( is_wp_error( $programme_id ) ) {
			wp_die( 'Error creating the programme post.' );
	}

	// Asociar la nueva 'programme' con el término 'diet-category'
	wp_set_post_terms( $programme_id, array( $diet_category_id ), 'diet-category' );

	// Redirigir a la página de edición del nuevo CPT 'programme'
	wp_redirect( admin_url( "post.php?post={$programme_id}&action=edit" ) );
	exit;
}


}

Cliente::init();
