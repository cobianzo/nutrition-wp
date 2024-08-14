import allowedBlocksData from "../includes/allowed-blocks.json";

// Aplicar restricciones en Gutenberg
wp.hooks.addFilter(
  "allowedBlockTypes",
  "my-plugin/allowed-blocks",
  (allowedBlocks, blockEditorContext) => {
    alert();
    return allowedBlocksData.allowedBlocks;
  }
);
