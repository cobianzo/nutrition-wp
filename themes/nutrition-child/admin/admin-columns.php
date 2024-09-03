<?php


/**
 * At the moment we also use the plugin Admin Columns 
 * to show certain columns.
 * 
 * There where that plugin is not enought, we do it
 * programmatically.
 * 
 */
class Admin_Columns {

  public static function init() {

    add_filter('manage_client_posts_columns', [__CLASS__,'remove_author_column_from_client_cpt'], 1, 1);
    add_action('manage_client_posts_custom_column', [__CLASS__, 'show_dietas_column_content'], 10, 2);

  }


  public static function remove_author_column_from_client_cpt($columns) {
    // Verificamos si la columna 'author' existe y la eliminamos
    unset($columns['author']);
    unset($columns['date']);
    $columns['dietas'] = __( 'Dietas', 'asim' );
    $columns['wp_user'] = __( 'Profile user', 'asim' );

    return $columns;
  }

  public static function show_dietas_column_content($column, $post_id) {
    if ($column == 'dietas') {
      
      $custom_value = get_post_meta($post_id, 'dietas_meta_key', true); // Reemplaza 'dietas_meta_key' por tu clave meta
      echo !empty($custom_value) ? esc_html($custom_value) : __('No hay dietas');
    } elseif ($column == 'wp_user') {
      $user = Relation_Cliente_User::get_client_user_by_post_id( $post_id );
      if ( ! isset( $user->ID ) ) {
        echo 'No data';
        return;
      }
      echo sprintf(
        '<a href="%s">%s</a>',
        esc_url( add_query_arg( array( 'user_id' => $user->ID ), admin_url( 'user-edit.php' ) ) ),
        esc_html( $user->display_name )
      );
    }

  }
}

Admin_Columns::init();