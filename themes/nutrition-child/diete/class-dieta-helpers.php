<?php


class Dieta_Helpers {

  public static function get_diets_by_client( $client_id ) {

    $user = Relation_Cliente_User::get_client_user_by_post_id( $client_id );

    if ( ! isset( $user->ID ) ) {
      return [];
    }

    return self::get_diets_by_user( $user->ID );

  }

  public static function get_diets_by_user( $user_id ) {

    $args = array(
      'post_type'      => 'diet',  // CPT slug for diet
      'author'         => $user_id,
      'posts_per_page' => -1,      // Retrieve all diets for this user
      'post_status'    => 'any', // You can adjust this to include drafts, etc.
    );

    $query = new WP_Query( $args );

    return $query->posts; 

  }

}

