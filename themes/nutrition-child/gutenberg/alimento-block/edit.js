import {
  InnerBlocks,
  useBlockProps,
  InspectorControls,
} from "@wordpress/block-editor";

import { PanelBody, SelectControl } from "@wordpress/components";
import { select, useSelect } from "@wordpress/data";
import { __ } from "@wordpress/i18n";

// import useState
import { useState, useEffect } from "@wordpress/element";

import apiFetch from "@wordpress/api-fetch";

export default function edit(props) {
  // helper
  const fetchImage = async () => {
    const fetchPost = async () => {
      const data = await apiFetch({
        path: `/wp/v2/aliment/${props.attributes.alimentoID}`,
      });
      return data;
    };
    const postData = await fetchPost();
    debugger;
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

  // Get all Aliments CPT and convert them into Options
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

  // keep attr imgSrc Up to date.
  useEffect(() => {
    if (props.attributes.alimentoID) {
      fetchImage();
    } else {
      props.setAttributes({ imgSrc: "" }); // TODO : use default?
    }
  }, [props.attributes.alimentoID]);

  // useEffect(fetchImage, []);

  return (
    <div
      {...useBlockProps({
        className: props.attributes.imgSrc ? `has-image` : `no-image`,
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
      <div className="alimento-left-column">
        This is the left column
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
    </div>
  );
}
