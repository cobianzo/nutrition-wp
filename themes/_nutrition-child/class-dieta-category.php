<?php
class Dieta_Category {

  /**
   * Init hooks
   */
  public static function init() {
    // @TODO: move this to term edit hook, for tax diet-category.
    // @TODELETE: we do it all from the CMS, with a convention on the name.
    // add_action('admin_init', [__CLASS__, 'register_diet_patterns']);
  }
  
  /**
   * We create a pattern for every term in the 'diet-category' taxonomy.
   * When we create a new `diet`, if we assign it to the term, we initialize the content to that pattern.
   *
   * @todelete/
   * @return void
   */
  public static function register_diet_patterns() {

    /**
     * only if we are in the 
     */


    // Get all terms of taxonomy 'diet-category'
    $terms = get_terms(array(
        'taxonomy' => 'diet-category',
        'hide_empty' => false,
    ));

    // Check and register block patterns for each term
    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $pattern_slug = 'diet-template-' . $term->slug;
        
            // Verificar si ya existe un bloque reutilizable con el mismo slug
            $existing_block = get_posts(array(
                'name'        => $pattern_slug,
                'post_type'   => 'wp_block',
                'post_status' => 'publish',
                'numberposts' => 1,
            ));

            if (empty($existing_block)) {
                // Si no existe, crear un nuevo bloque reutilizable
                $content = sprintf('<!-- wp:heading --><h2>%s</h2><!-- /wp:heading -->', esc_html($pattern_slug));

                // Insertar el bloque reutilizable en la base de datos
                $new_block_id = wp_insert_post(array(
                    'post_title'   => $pattern_slug,
                    'post_name'    => $pattern_slug,
                    'post_content' => $content,
                    'post_status'  => 'publish',
                    'post_type'    => 'wp_block',
                ));

                if ($new_block_id) {
                    echo 'Bloque reutilizable creado con éxito.';
                } else {
                    echo 'Hubo un error al crear el bloque reutilizable.';
                }
            } else {
                // echo 'El bloque reutilizable ya existe.';
            }
        }
    }
  }
}

Dieta_Category::init();
