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
		
		add_action(
			'wp_insert_post',
			array( __CLASS__, 'create_user_from_client' ),
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
    add_action( 'profile_update', function( $user_id, $old_user_data ) {
      $new_email = get_user_by( 'id', $user_id )->user_email;
      if ( $old_user_data->user_email !== $new_email ) {
        self::update_client_post_from_user( $user_id, $new_email, $old_user_data->user_email );
      }
    }, 10, 2 );

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

	}

	/**
	 * Create user from client post type.
	 *
	 * @since 1.0
	 *
	 */
	public static function create_user_from_client( $post_id, $post, $update ) {
		
		if ( $post->post_status !== 'publish' || $post->post_type !== 'client' ) {
			return;
    }

		// Before we go, we check if there is a user with that email already, using our static function
		$user_exists = self::get_client_user_by_post_id( $post->ID );
		
		if ( ! $user_exists ) {
			// Only in new posts.

			$email    		 = get_field( 'email', $post->ID );
			$user_by_email = get_user_by( 'email', $email ); // this should be false

			// If there is already a user with that email. Trigger error log in this is a new post?
			if ( $user_by_email && $user_by_email->ID !== $user_exists ) {
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

			$user_id = wp_insert_user( $user_data );

			//userID		
			wp_update_post( array( 'ID' => $post->ID, 'post_author' => $user_id ), false, false );
			update_post_meta( $post->ID, self::META, (int) $user_id );

		} else {

			// If updating an existing client post:
			// If the email has changed, we update the email of the WP user too.
			// NOTE: if the new email corresponds to another existing user, there is another hook `acf/validate_value` to prevent it.
			$email = get_field( 'email', $post->ID );
			if ( $email !== $user_exists->user_email ) {
				$user_exists->user_email = $email;
				wp_update_user( $user_exists );
			}
			
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
		$old_email = get_field( 'email', $post_id, true );
		$email     = get_field( 'email', $post_id );
		if ( $old_email !== $email ) {
			$user_id = self::get_client_post_by_user_id( $post_id );
			if ( $user_id ) {
				wp_update_user( array(
					'ID'         => $user_id,
					'user_email' => $email,
				) );
			}
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
	public static function update_client_post_from_user( $user_id, $new_email, $old_email ) {
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
}

Cliente::init();
