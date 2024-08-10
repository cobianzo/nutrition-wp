import { registerBlockType } from "@wordpress/blocks";
import { __ } from "@wordpress/i18n";
// import { addFilter } from "@wordpress/hooks";
// import { getBlockType } from "@wordpress/blocks";
// import { createElement } from "@wordpress/element";

import Edit from "./edit";
registerBlockType("asim/alimento-block", {
  title: __("Alimento Block", "asim"),
  icon: "cart",
  category: "common",
  edit: Edit,
  save: () => <div>{__("Hello from Block 1", "asim")}</div>,
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
