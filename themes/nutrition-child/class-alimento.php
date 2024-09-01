<?php

// the block alimento is registered in gutenberg folder

class Alimento {

  /**
   * Init hooks
   */
  public static function init() {


  }


  public static function preview_alimento( $alimento_id, $echo = 1 ) {
    $content = '';
    if ( get_post_status( $alimento_id ) ) {
      $thumbnail_url = get_the_post_thumbnail_url($alimento_id, 'thumbnail');
      $title = get_the_title($alimento_id);
      if ($thumbnail_url) {
        $content .=  '<img src="' . esc_url($thumbnail_url) . '" alt="' . esc_attr($title) . '" style="width: 50px; height: 50px; margin-right: 10px;" />';
      }
      $content .= '<span>' . esc_html($title) . '</span>';
    }
    if ( $echo ) echo $content;
    return $content;
  }
  // === 


}

Alimento::init();