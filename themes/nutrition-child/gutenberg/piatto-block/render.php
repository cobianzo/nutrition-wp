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
<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php echo $content; // Output inner block content ?>
</div>
