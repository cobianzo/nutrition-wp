import {
  InnerBlocks,
  useBlockProps,
  InspectorControls,
  RichText,
} from "@wordpress/block-editor";

import {
  PanelBody,
  SelectControl,
  Button,
  RadioControl,
  CheckboxControl,
} from "@wordpress/components";

import { parse } from "@wordpress/blocks";
import { select, useSelect, useDispatch } from "@wordpress/data";
import { __ } from "@wordpress/i18n";
import apiFetch from "@wordpress/api-fetch";

// import React wrapper dependencies
import { useState, useEffect, Fragment } from "@wordpress/element";

// Internal dependencies
import { useMealsTerms } from "../helper-get-meals";
import MealTypeSelectControl from "../component-MealTypeSelectControl";

/**
 * Functional component for the Edit.js
 * @param {*} props
 * @returns
 */
export default function edit(props) {
  // We'll use it later
  const { replaceInnerBlocks } = useDispatch("core/block-editor");
  const mealTerms = useMealsTerms();
  const mealOptions = mealTerms.map((term) => {
    return { label: term.name, value: term.slug };
  });

  // helper to update the attribute.imgSrc when alimentoID changes.
  const fetchImage = async () => {
    const fetchPost = async () => {
      const data = await apiFetch({
        path: `/wp/v2/aliment/${props.attributes.alimentoID}`,
      });
      return data;
    };
    const postData = await fetchPost();
    if (postData?.featured_media) {
      // featured_media is the ID of the attachment. We grab the media src.
      // NOTE: I tried using `select` but for some reasonit doesnt work.
      apiFetch({ path: `/wp/v2/media/${postData.featured_media}` }).then(
        (attachmentPost) => {
          if (
            attachmentPost &&
            attachmentPost.media_details &&
            attachmentPost.media_details.sizes
          ) {
            const imageSource =
              attachmentPost.media_details.sizes.full.source_url;
            console.log(imageSource);
            props.setAttributes({ imgSrc: imageSource });
          }
        }
      );
    }
  };

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

  useEffect(() => {
    // keep attr imgSrc Up to date.
    if (props.attributes.alimentoID) {
      fetchImage();
    } else {
      props.setAttributes({ imgSrc: "" }); // TODO : use default?
    }

    // Replace the content text with the default one for the aliment.
    if (props.attributes.alimentoID) {
      const innerBlocks = select("core/block-editor").getBlock(
        props.clientId
      ).innerBlocks;

      const innerBlockContent = innerBlocks.map((block) => {
        return block.innerBlocks.map((innerBlock) => {
          return innerBlock.attributes.content;
        });
      });
      // if the inner blocks are empty, we replace them with the default aliment content
      if (innerBlockContent.every((content) => content.length === 0)) {
        prefillInnerBlocks();
      }
    }
  }, [props.attributes.alimentoID]);

  // create a function that grabs the editor content for the current attr alimentoID.
  // And it replaces the inner blocks with the content of the editor
  const prefillInnerBlocks = () => {
    apiFetch({
      path: `/wp/v2/aliment/${props.attributes.alimentoID}?context=edit`,
    }).then((json) => {
      if (json && json.content && json.content.raw) {
        const content = json.content.raw;
        const blocks = parse(content);
        // console.log(blocks);
        replaceInnerBlocks(props.clientId, blocks);
      }
    });
  };

  return (
    <div
      {...useBlockProps({
        className:
          (props.attributes.imgSrc && props.attributes.hideImage !== true
            ? ` has-image `
            : ` no-image `) +
          ` is-${props.attributes.mealType}` +
          (props.attributes.isAlternative ? ` is-alternative` : ``),
      })}
    >
      <InspectorControls>
        <PanelBody title={__("Select aliment", "asim")}>
          <MealTypeSelectControl
            label={__("ALIMENT", "asim")}
            value={props.attributes.alimentoID}
            mealType={props.attributes.mealType}
            onChange={(value) => {
              props.setAttributes({
                alimentoID: String(value),
              });
            }}
          />
          <Button
            isPrimary
            onClick={() => {
              console.log("You clicked the button!");
              // Here we can add the logic to alter the block attributes
              prefillInnerBlocks();
            }}
          >
            {__("Prefill text with defaults", "asim")}
          </Button>
          <br />
          <br />
          <div>
            {
              <RadioControl
                label={__("Meal", "asim")}
                selected={props.attributes.mealType}
                options={[
                  { label: __("--none---", "asim"), value: "" },
                  ...mealOptions,
                ]}
                onChange={(value) => {
                  let translated = props.attributes.title;
                  if (!props.attributes.isAlternative) {
                    const label = mealOptions.find(
                      (meal) => meal.value === value
                    ).label;
                    translated = __(label, "asim");
                  }
                  props.setAttributes({
                    mealType: value,
                    title: translated,
                  });
                }}
              />
            }
          </div>
          <div>
            <CheckboxControl
              label={__("Is Alternative?", "asim")}
              checked={props.attributes.isAlternative}
              onChange={(value) => {
                let newTitle = "";
                if (value === true) {
                  newTitle = __("Alternative", "asim");
                } else {
                  const labelable = mealOptions.find(
                    (meal) => meal.value === props.attributes.mealType
                  );
                  newTitle = __(
                    labelable && labelable.label ? labelable.label : "",
                    "asim"
                  );
                }

                props.setAttributes({
                  isAlternative: value,
                  title: newTitle,
                });
              }}
            />
          </div>
          <div>
            <CheckboxControl
              label={__("Hide Image", "asim")}
              checked={props.attributes.hideImage}
              onChange={(value) => {
                props.setAttributes({
                  hideImage: value,
                });
              }}
            />
          </div>
        </PanelBody>
      </InspectorControls>
      <Fragment>
        <RichText
          tagName="h3"
          className="alimento-title"
          value={props.attributes.title}
          placeholder={__("eg. Breakfast", "asim")}
          disabled={props.attributes.isAlternative}
          onChange={(value) => {
            props.setAttributes({ title: value });
          }}
        />
        <div className="alimento-left-column">
          <InnerBlocks
            allowedBlocks={["core/paragraph", "core/heading", "core/list"]}
            orientation="vertical"
            template={[
              ["core/paragraph"],
              ["core/paragraph"],
              ["core/paragraph"],
            ]}
            onChange={(content) => setAttributes({ textContent: content })}
          />
        </div>
        {props.attributes.hideImage !== true && (
          <div className="alimento-right-column">
            {props.attributes.alimentoID && (
              <div className="alimento-image">
                <img
                  src={
                    props.attributes.imgSrc
                      ? props.attributes.imgSrc
                      : "http://placehold.it/300x300"
                  }
                />
              </div>
            )}
          </div>
        )}
      </Fragment>
    </div>
  );
}
