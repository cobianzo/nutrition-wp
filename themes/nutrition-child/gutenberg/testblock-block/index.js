import { registerBlockType } from "@wordpress/blocks";
import { useBlockProps } from "@wordpress/block-editor";

registerBlockType("asim/testblock-block", {
  title: "TEST BLOCK",
  edit: (props) => {
    return (
      <div>
        This is the Edit, what you see: {props.attributes.test_attribute}
        <button
          onClick={(e) => props.setAttributes({ test_attribute: "TEST click" })}
        >
          Change the store
        </button>
      </div>
    );
  },
  save: () => null,
});
