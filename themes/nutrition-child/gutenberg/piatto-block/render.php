<?php
/**
 * Renders the block on the frontend.
 *
 * @param array $attributes The block attributes.
 * @param string $content The inner block content.
 */


if ( ! empty( $attributes['alimentoID'] ) ) {
  // Fetch post by ID.
  $alimento_post = get_post( intval( $attributes['alimentoID'] ) );
}

?>
<div class="wp-block-asim-piatto-block__wrapper">
    <span class="wp-block-asim-piatto-block__piatto-badge"><?php _e('Piatto', 'asim'); ?></span>
    <?php echo $content; // Output inner block content ?>
</div>
