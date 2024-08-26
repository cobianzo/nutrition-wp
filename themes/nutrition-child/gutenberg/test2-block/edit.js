import {
  InnerBlocks,
  useBlockProps,
  InspectorControls,
} from "@wordpress/block-editor";

import { PanelBody, SelectControl } from "@wordpress/components";

import { useSelect } from "@wordpress/data";
import { __ } from "@wordpress/i18n";

/**
 * Functional component for the Edit.js
 * @param {*} props
 * @returns
 */
export default function edit(props) {
  // Get all Aliments CPT and convert them into Options for the Dropdown
  const aliments = useSelect((select) => {
    const alimenti = select("core").getEntityRecords("postType", "aliment");
    if (alimenti) {
      return alimenti.map((aliment) => {
        return { label: aliment.title.raw, value: aliment.id };
      });
    } else {
      return [];
    }
  }, []);

  return (
    <div
      {...useBlockProps({
        className:
          (props.attributes.imgSrc && props.attributes.hideImage !== true
            ? ` has-image `
            : ` no-image `) + ` is-${props.attributes.mealType}`,
      })}
    >
      <InspectorControls>
        <PanelBody title={__("Select aliment", "asim")}>
          <SelectControl
            label={__("ALIMENT", "asim")}
            value={props.attributes.alimentoID}
            options={[
              { label: __("SELECT ALIMENT", "asim"), value: "" },
              ...aliments,
            ]}
            onChange={(value) => {
              props.setAttributes({
                alimentoID: value,
              });
            }}
          />
        </PanelBody>
      </InspectorControls>

      <InnerBlocks
        allowedBlocks={["core/paragraph", "core/heading", "core/list"]}
        orientation="vertical"
        template={[["core/paragraph"], ["core/paragraph"], ["core/paragraph"]]}
        onChange={(content) => setAttributes({ textContent: content })}
      />
    </div>
  );
}
