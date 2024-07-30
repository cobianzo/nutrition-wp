<?php 

/**
 * 
 */

 extract( $args ); // $post_id.
?>


<div id="client-dashboard">
  <div class="tile">Dashboard for client: <?php echo $post_id; ?></div>
  <button class="tile"><?php _e( 'Create Diet', 'asim' ); ?></button>
  <button class="tile">Tile 3</button>
  <div class="tile">Tile 4</div>

</div>