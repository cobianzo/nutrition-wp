import { registerBlockType } from "@wordpress/blocks";
import { useBlockProps } from "@wordpress/block-editor";
import { InnerBlocks } from "@wordpress/block-editor";

import Edit from "./edit";

registerBlockType("asim/test2-block", {
  title: "TEST 2 Block",
  edit: Edit,
  save: ({ attributes }) => (
    <div {...useBlockProps.save()}>
      <InnerBlocks.Content />
    </div>
  ),
});
