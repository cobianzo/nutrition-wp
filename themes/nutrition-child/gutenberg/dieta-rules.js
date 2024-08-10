// Import necessary WordPress packages
import { createHigherOrderComponent } from "@wordpress/compose";
import { addFilter } from "@wordpress/hooks";
import { InspectorControls } from "@wordpress/block-editor";
import { PanelBody } from "@wordpress/components";
import { Fragment } from "@wordpress/element";
import { select, dispatch } from "@wordpress/data";

const allowedTopBlocks = [
  "core/group",
  "core/paragraph",
  "asim/alimento-block",
];

const allowedBlocks = [
  "core/group",
  "core/paragraph",
  "core/heading",
  "core/list",
  "core/heading",
  "asim/alimento-block",
];
/**
 * FILTER 1: For `diet`, at the top level accept only core/group blocks.
 * @param {*} allowedBlocks
 * @param {*} blockEditor
 * @returns
 */
const restrictBlocks = createHigherOrderComponent((BlockEdit) => {
  return (props) => {
    // Check if the block has a parent (i.e., it's nested)
    const parentBlock = select("core/block-editor").getBlockRootClientId(
      props.clientId
    );

    // Apply restrictions only if the post type is 'diet'
    const postType = select("core/editor").getEditedPostAttribute("type");
    console.log("%c postType ", "background: #222; color: #bada55", postType); // todelete
    if (postType === "diet") {
      // Only allow `core/group` block at the root level
      console.log("%c" + props.name, "background: #222; color: #bada55");
      if (!parentBlock && !allowedTopBlocks.includes(props.name)) {
        dispatch("core/notices").createErrorNotice(
          "Only Group blocks are allowed at the top level.",
          { id: "group-block-restriction" }
        );

        return null;
      }
    }

    return <BlockEdit {...props} />;
  };
}, "restrictBlocks");

// Add filter to apply the component
addFilter("editor.BlockEdit", "custom/restrict-blocks", restrictBlocks);

/**
 * FILTER 2: restring blocks for `diet` to paragraph, group and headings.
 * @param {*} allowedBlocks
 * @param {*} blockEditor
 * @returns
 */
// Function to unregister blocks that are not in the allowedBlocks array
console.log("TODELEEL");
const restrictBlocksForDietCPT = (settings, name) => {
  const postType = select("core/editor").getCurrentPostType();

  if (postType === "diet" && !allowedBlocks.includes(name)) {
    return null; // Unregister the block by returning null
  }

  return settings;
};

// Apply the filter to restrict blocks for the 'diet' CPT
addFilter(
  "blocks.registerBlockType",
  "asim/restrict-blocks-diet-cpt",
  restrictBlocksForDietCPT
);
