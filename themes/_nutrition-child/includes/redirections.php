<?php

class Redirections {

  /**
   * @TODO: redirections to:
   * 
   * If not logged in - allow only homepage.
   * If logged in - Don't allow homepage :
   *    If `client` 
   *      Show latest Diet page.
   *        if not Diet show profile.php page
   *    If `admin` -> Show Dashboard WP.
   *
   * @return void
   */
  public static function init() {

    // allow to see the diet only to the client associated to it:
    add_action( 'pre_get_posts', [__CLASS__, 'restringir_acceso_a_diet'] );

    // redirections for the homeage, if you are logged in as non `client`
    add_action( 'template_redirect', [__CLASS__, 'redirect_non_clients_to_dashboard'] );


  }


  /**
   * for page single-diet. Redirect if we are not the owner of the diet.
   *
   * @param [type] $query
   * @return void
   */
  public static function restringir_acceso_a_diet( $query ) {
    if ( !is_admin() && $query->is_main_query() && is_singular( 'diet' ) ) {
        if ( !is_user_logged_in() ) {
            // Si el usuario no está logueado, redirigir a la página de inicio
            wp_redirect( home_url() );
            exit;
        }

        $current_user = wp_get_current_user();
        $post = get_post();

        // Verifica si el usuario tiene permisos de editor, administrador o es el autor del post
        if ( !in_array( 'editor', $current_user->roles ) && 
             !in_array( 'administrator', $current_user->roles ) && 
             $current_user->ID !== $post->post_author ) {
            // Si no tiene permiso, redirigir a la página de inicio
            wp_redirect( home_url() );
            exit;
        }
    }
  }

  /**
   * For homepage
   *
   * @return void
   */
  public static function redirect_non_clients_to_dashboard() {
    // Verifica si el usuario está logueado
    if (is_user_logged_in()) {
        // Obtiene la información del usuario actual
        $current_user = wp_get_current_user();
        
        // Verifica si el usuario no tiene el rol 'client'
        if (!in_array('client', $current_user->roles)) {
            // Redirige al dashboard del admin
            wp_redirect(admin_url());
            exit;
        }
    }
  }
}

Redirections::init();