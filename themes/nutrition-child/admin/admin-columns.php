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
    $columns['programmi'] = __( 'Programmes', 'asim' );
    $columns['wp_user'] = __( 'Profile user', 'asim' );
    $columns['last_visit'] = __( 'Last Visit', 'asim' );

    return $columns;
  }

  public static function show_dietas_column_content($column, $post_id) {
    if ($column == 'dietas') {
      
      $diets = Dieta_Helpers::get_diets_by_client( $post_id );
      if ( ! $diets ) {
        echo 'No data';
        return;
      }
      echo sprintf(
        '<a href="%s" class="asim-btn-small" target="_blank">%s</a>',
        esc_url( add_query_arg( array( 'diet_id' => $diets[0]->ID ), get_edit_post_link( $diets[0]->ID ) ) ),
        esc_html( $diets[0]->post_title )
      );
      
    } elseif ($column == 'programmi') {
      
      $programmas = Programma::get_programma_by_client( $post_id, false );
      if ( ! empty( $programmas ) && count($programmas) > 1 ) {
        echo '<p>' . __('Error: There are more than one programme for the client. There should be only one.', 'asim') . '</p>';
      }

      if ( empty( $programmas ) ) {
        echo '<p>' . __('No Programme found for this client.', 'asim') . '</p>';
      } else {
        echo sprintf(
          '<a href="%s" class="asim-btn-small" target="_blank">%s</a>',
          esc_url( add_query_arg( array( 'diet_id' => $programmas[0]->ID ), get_edit_post_link( $programmas[0]->ID ) ) ),
          esc_html( $programmas[0]->post_title )
        );
      }
      
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
    } elseif ($column == 'last_visit') {
      // recorrer hasta la ultima con data: 
      $visits = get_field('visits', $post_id);
      $last_date = 'No d';
      if ( ! empty( $visits ) ) {
        foreach ( $visits as $visit) {
          if ( !empty( $visit['date'] ) ) {
            $last_date = date('d/m/Y', strtotime($visit['date']));
          }
        }
      }
      echo $last_date;
      
    }

  }
}

Admin_Columns::init();