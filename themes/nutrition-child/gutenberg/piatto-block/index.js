import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
import { useBlockProps } from "@wordpress/block-editor";
import { InnerBlocks } from "@wordpress/block-editor";

import Edit from "./edit";
registerBlockType("asim/piatto-block", {
  title: __("Piatto Block", "asim"),
  icon: "food",
  edit: Edit,
  save: function ({ attributes }) {
    const blockProps = useBlockProps.save();

    // Use InnerBlocks.Content to render the inner blocks' content
    return (
      <div {...blockProps}>
        <InnerBlocks.Content />
      </div>
    );
  },
});

// Function to disable the Styles tab
// @TODELETE: this doesnt work!
// const disableStylesTab = (BlockEdit) => (props) => {
//   // Check if the block is the one you want to modify
//   if (props.name === "asim/alimento-block") {
//     // Remove the Styles tab by deleting the styles from block settings
//     console.log("%c props", "background: #222; color: #bada55", props);
//     delete getBlockType(props.name).styles;
//   }
//   return <BlockEdit {...props} />;
// };

// // Add the filter to modify the BlockEdit component
// addFilter("editor.BlockEdit", "asim/disable-styles-tab", disableStylesTab);
