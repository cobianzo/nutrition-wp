<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 * @package block-developer-examples
 */

$extra = [ 
	'class' => ($attributes['imgSrc'] ? ' has-image ' : ' no-image ' ) . ' is-' . $attributes['mealType']
];

?>
<div <?php echo get_block_wrapper_attributes($extra) ?>>
  <h3 class="alimento-title"><?php echo $attributes['title']; ?></h3>
  <div class="alimento-left-column">
    <?php echo $content; ?>
  </div>
  <div class="alimento-right-column">
    <?php if ($attributes['imgSrc']) { ?>
      <img src="<?php echo $attributes['imgSrc']; ?>" alt="<?php echo $attributes['title']; ?>" />
    <?php } ?>
  </div>
</div>


