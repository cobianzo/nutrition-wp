// Internal dependencies
import {
  InspectorControls,
  useBlockProps,
  InnerBlocks,
} from "@wordpress/block-editor";
import { PanelBody, ComboboxControl } from "@wordpress/components";
import { useSelect } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";

// React wrapper dependencies
import { useState, useEffect } from "@wordpress/element";

const Edit = ({ attributes, setAttributes }) => {
  const [alimentoPost, setAlimentoPost] = useState(null);

  // Fetch posts of the CPT 'aliment'
  const alimentOptions = useSelect((select) => {
    const posts = select("core").getEntityRecords("postType", "aliment");
    return posts
      ? posts.map((post) => ({ label: post.title.raw, value: post.id }))
      : [];
  }, []);

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

  return (
    <div {...useBlockProps({ className: "wp-block-asim-piatto-block" })}>
      <InspectorControls>
        <PanelBody title="Opzioni blocco Piatto">
          <ComboboxControl
            label="Seleziona Alimento"
            value={attributes.alimentoID}
            options={alimentOptions}
            onChange={(newValue) => setAttributes({ alimentoID: newValue })}
          />
        </PanelBody>
      </InspectorControls>
      <div className="wp-block-asim-piatto-block__info">
        <p onMouseDown={() => setAttributes({ title: "ASSIGNEDtitle" })}>
          TODELTE title: {attributes.title}{" "}
        </p>
        {alimentoPost ? (
          <div>
            {alimentoPost.imgSrc && <img src={alimentoPost.imgSrc} />}
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
        )}
      </div>
      <InnerBlocks />
    </div>
  );
};

export default Edit;
