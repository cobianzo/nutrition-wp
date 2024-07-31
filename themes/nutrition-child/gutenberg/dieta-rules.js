// Import necessary WordPress packages
import { createHigherOrderComponent } from "@wordpress/compose";
import { addFilter } from "@wordpress/hooks";
import { InspectorControls } from "@wordpress/block-editor";
import { PanelBody } from "@wordpress/components";
import { Fragment } from "@wordpress/element";
import { select } from "@wordpress/data";

// Define the component to restrict blocks
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
      if (
        !parentBlock &&
        !["core/group", "core/paragraph", "asim/alimento-block"].includes(
          props.name
        )
      ) {
        return (
          <Fragment>
            <p style={{ color: "red", marginBottom: "30px" }}>
              Only Group blocks are allowed at the top level.
            </p>
            <InspectorControls>
              <PanelBody title="Block Restrictions">
                <p style={{ color: "red" }}>
                  Only Group blocks are allowed at the top level.
                </p>
              </PanelBody>
            </InspectorControls>
          </Fragment>
        );
      }
    }

    return <BlockEdit {...props} />;
  };
}, "restrictBlocks");

// Add filter to apply the component
addFilter("editor.BlockEdit", "custom/restrict-blocks", restrictBlocks);
