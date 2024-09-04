<?php
/**
 * Package: asim
 * File: class-cliente.php
 * 
 * Class Cliente
 */

require_once __DIR__ . '/cpt-cliente.php';
require_once __DIR__ . '/relation-cliente_user.php';
require_once __DIR__ . '/class-client-actions.php';

class Cliente {


	/**
	 * Init hooks
	 *
	 * @since 1.0
	 */
	public static function init() {
		
		// Custom HTML for diets dashboard:
    add_action( 'acf/render_field', [__CLASS__, 'client_dashboard_for_diets'] );

		// Custom HTML for programmi dashboard:
		add_filter( 'acf/load_field', [__CLASS__, 'client_dashboard_for_programmes'] );

		add_action( 'admin_enqueue_scripts', function( $hook ) {
			global $post;
			// Check if the current page is 'post.php' or 'post-new.php' and the post type is 'client'
			if ( ($hook === 'post.php' || $hook === 'post-new.php') 
				&& isset($post->post_type) && $post->post_type === 'client' ) {
				$programma = Programma::get_programma_by_client( $post->ID );
				add_action( 'admin_footer', fn() => self::client_dashboard_for_diets_scripts($post->ID, $programma->ID) );
			}
		}, 10, 1 );

	}
	// ====== END INIT ===== 

  /** Dashboard in client CMS page
	 * See cliente-dashboard-diete.php
	*/
  public static function client_dashboard_for_diets( $field ) {

		// field type 'message' defined in ACF.
    if ( 'field_66aa86c7f526b' === $field['key'] ) {

      $post_id = isset($_REQUEST['post'])? $_REQUEST['post'] : false;
      get_template_part( 'clienti/partial' ,'dashboard-diete', ['post_id' => $post_id] );

    }

  }

	public static function client_dashboard_for_diets_scripts($client_id, $programme_id) {
		?>	
			<form id="creation-diet-from-aliments"
				action="<?php echo admin_url('admin-post.php'); ?>"
				method="post"
				style="display:none;"
				>
    		<?php wp_nonce_field('create_diet_from_aliments_action', 'create_diet_from_aliments_nonce'); ?>
    
				<div style="position: fixed; right: 0; bottom: 100px;" >
					<input type="hidden" name="action" value="create_diet_from_aliments">
					<!-- These fields should be hidden, but for easier debugging ar text -->
					<input type="text" name="client_id" value="<?php echo esc_attr($client_id); ?>">
					<input type="text" name="programme_id" value="<?php echo esc_attr($programme_id); ?>">
					<input type="text" name="day_of_the_week" value="<?php echo esc_attr('Monday'); ?>">
				</div>

				<div style="position:fixed; right:0; bottom: 0">
					<?php 
					$terms = get_terms(array(
						'taxonomy' => 'meal',
						'orderby'  => 'id',
					));
					if (!empty($terms) && !is_wp_error($terms)) {
						foreach ($terms as $term) : ?>
							<input id="<?php echo esc_attr($term->slug); ?>_aliment_ids" type="text" name="<?php echo esc_attr($term->slug); ?>_aliments" value="">
						<?php
						endforeach;
					}
					?>
				</div>
			</form>
			<script type="text/javascript">
				function checkAliment( liEl ) {
					liEl.classList.toggle('checked');
					liEl.classList.toggle('unchecked', !liEl.classList.includes('checked'));
					
				}
				function createDietFromAliments() {
					const formToCreateDiet = document.getElementById('creation-diet-from-aliments');
					const containerUIButtons = document.getElementById('container-aliments');
					console.log( 'containers', formToCreateDiet, containerUIButtons);
					
					<?php 
					$term_slugs = wp_list_pluck($terms, 'slug');
					?>
					var termSlugs = <?php echo json_encode($term_slugs); ?>;
					termSlugs.forEach( termSlug => {
						const alimentsContainer = document.getElementById('term-'+termSlug+'-aliments');
						const checkedAliments = alimentsContainer.querySelectorAll('li.checked');
						if (checkedAliments) {
							// we update the field for that term
							let checkedAlimentsIds = [];
							const alimentIds = [...checkedAliments].map( (alEl) => alEl.getAttribute('data-alimentoid') );
					
							formToCreateDiet.querySelector('#'+termSlug+'_aliment_ids').value = alimentIds.join(',');

						}
					});

					// Add day of the week to the form: 
					formToCreateDiet.querySelector('input[name="day_of_the_week"]').value = document.getElementById('day-of-the-week').value;

					// @TODO: add validation. Don't submit if no aliments are selected
							
					// now we can submit the form, which will be handled in the backend as a WP action (create_diet_from_aliments).
					// comment this for testing the inputs
					formToCreateDiet.submit();
				}
			</script>
		<?php
	}


  /** Dashboard in client CMS page
	 * See cliente-dashboard-programmi.php
	*/
  public static function client_dashboard_for_programmes( $field ) {

    if ( 'field_66c5c6d769b16' === $field['key'] ) {

      $post_id = isset($_REQUEST['post'])? $_REQUEST['post'] : false;

      // $field['label'] = 'Actions';
      ob_start();
			
      get_template_part( 'clienti/partial', 'dashboard-programmi', ['post_id' => $post_id] );
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
    $user = Relation_Cliente_User::get_client_user_by_post_id( $client_id );
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
	$user = Relation_Cliente_User::get_client_user_by_post_id( $client_id );
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
