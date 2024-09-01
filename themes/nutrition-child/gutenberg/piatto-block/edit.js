// Internal dependencies
import {
  InspectorControls,
  useBlockProps,
  InnerBlocks,
} from "@wordpress/block-editor";
import { PanelBody, RadioControl } from "@wordpress/components";
import { useSelect, useDispatch } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";
import { __ } from "@wordpress/i18n";

// React wrapper dependencies
import { useState, useEffect } from "@wordpress/element";

// Internal dependencies
import { useMealsTerms } from "../helper-get-meals";
import MealTypeSelectControl from "../component-MealTypeSelectControl";

const Edit = ({ attributes, setAttributes, clientId }) => {
  const [alimentoPost, setAlimentoPost] = useState(null);
  const mealTerms = useMealsTerms();
  const mealOptions = mealTerms.map((term) => {
    return { label: term.name, value: term.slug };
  });

  // Access the inner blocks content using useSelect
  const innerBlocks = useSelect(
    (select) => {
      return select("core/block-editor").getBlocks(clientId);
    },
    [clientId]
  );

  // we like to prefill the content if we select an aliment
  const { replaceInnerBlocks } = useDispatch("core/block-editor");

  // Fetch all posts of the CPT 'aliment'
  const alimentOptions = useSelect((select) => {
    const posts = select("core").getEntityRecords("postType", "aliment");
    return posts
      ? posts.map((post) => ({ label: post.title.raw, value: String(post.id) }))
      : [];
  }, []);

  const { isBlockSelected, isInnerBlocksSelected } = useSelect(
    (select) => {
      const isSelected = select("core/block-editor").isBlockSelected(clientId);

      const { getSelectedBlockClientId, getBlockOrder } =
        select("core/block-editor");
      const selectedBlockClientId = getSelectedBlockClientId();
      const innerBlockClientIds = getBlockOrder(clientId);

      const isInnerBlocksSelected = innerBlockClientIds.includes(
        selectedBlockClientId
      );

      return { isBlockSelected: isSelected, isInnerBlocksSelected };
    },
    [clientId]
  );

  useEffect(() => {
    const fetchPost = async () => {
      let data = await apiFetch({
        path: `/wp/v2/aliment/${attributes.alimentoID}`,
      });
      console.log("post title", data.title);
      if (data.featured_media) {
        apiFetch({ path: `/wp/v2/media/${data.featured_media}` }).then(
          (attachmentPost) => {
            if (
              attachmentPost &&
              attachmentPost.media_details &&
              attachmentPost.media_details.sizes
            ) {
              const imageSource =
                attachmentPost.media_details.sizes.full.source_url;
              data = { ...data, imgSrc: imageSource };
              console.log("post with media", data);
              setAlimentoPost(data);
            } else {
              setAlimentoPost(data);
            }
          }
        );
      } else {
        console.log("post", data);
        setAlimentoPost(data);
      }
    };
    if (attributes.alimentoID) {
      fetchPost();
    }
  }, [attributes.alimentoID]);

  useEffect(() => {
    if (!alimentoPost) return;
    // Check if inner blocks are empty
    if (
      innerBlocks.length === 0 ||
      (innerBlocks.length === 1 &&
        innerBlocks[0].attributes.content &&
        innerBlocks[0].attributes.content.text.trim() === "")
    ) {
      // Set default content if empty
      const defaultBlock = wp.blocks.createBlock("core/paragraph", {
        content: alimentoPost.title.rendered,
      });

      replaceInnerBlocks(clientId, [defaultBlock]);
    }
  }, [alimentoPost]);

  return (
    <div className="wp-block-asim-piatto-block__wrapper">
      <div {...useBlockProps({ className: "" })}>
        <InspectorControls>
          <PanelBody title="Opzioni blocco Piatto">
            <MealTypeSelectControl
              label={__("ALIMENT", "asim")}
              value={attributes.alimentoID}
              mealType={attributes.mealType}
              onChange={(value) => {
                setAttributes({
                  alimentoID: String(value),
                });
              }}
            />

            <RadioControl
              label={__("Meal", "asim")}
              selected={attributes.mealType}
              options={[
                { label: __("--none---", "asim"), value: "" },
                ...mealOptions,
              ]}
              onChange={(value) => {
                setAttributes({
                  mealType: value,
                });
              }}
            />
          </PanelBody>
        </InspectorControls>

        {/* This div is only for the edit.js backend */}
        <div className="wp-block-asim-piatto-block__info">
          {isBlockSelected || isInnerBlocksSelected ? (
            alimentoPost ? (
              <div>
                <p>
                  {
                    new DOMParser().parseFromString(
                      alimentoPost.title.rendered,
                      "text/html"
                    ).documentElement.textContent
                  }
                </p>
              </div>
            ) : (
              <p>Seleziona un alimento nel panello laterale</p>
            )
          ) : null}
        </div>

        <div class="wp-block-asim-piatto-block__piatto-badge">
          <div className="asim-piatto-badge__text">{__("Piatto", "asim")}</div>
          {alimentoPost && alimentoPost.imgSrc && (
            <div className="asim-piatto-badge__icon">
              <img className="asim-alimento-icon" src={alimentoPost.imgSrc} />
            </div>
          )}
        </div>
        <InnerBlocks />
      </div>
    </div>
  );
};

export default Edit;
