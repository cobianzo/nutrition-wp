import { useState, useEffect } from "@wordpress/element";
import { SelectControl, ComboboxControl, Spinner } from "@wordpress/components";

import apiFetch from "@wordpress/api-fetch";

/**
 *
 * @param {{ mealType: string, label: string, onChange: (value: string) => void, value: string }} props
 * @returns
 */
const MealTypeSelectControl = ({ mealType, ...props }) => {
  const [options, setOptions] = useState([]);

  useEffect(() => {
    const getTermID = async (slug) => {
      try {
        const terms = await apiFetch({
          path: `/wp/v2/meal?slug=${slug}&_fields=id`,
        });

        if (terms.length > 0) {
          return terms[0].id; // Return the ID of the first term found
        } else {
          console.log("No term found with that slug.");
        }
      } catch (error) {
        console.error("Error fetching term:", error);
      }
    };

    const fetchPosts = async () => {
      try {
        const mealTermID = await getTermID(mealType);
        const posts = await apiFetch({
          path: `/wp/v2/aliment?meal=${mealTermID}&_fields=id,title.rendered`,
        });

        const formattedOptions = posts.map((post) => ({
          label: new DOMParser().parseFromString(
            post.title.rendered,
            "text/html"
          ).documentElement.textContent,
          value: String(post.id),
        }));

        setOptions(formattedOptions);
      } catch (error) {
        console.error("Error fetching posts:", error);
      }
    };

    if (mealType) {
      fetchPosts();
    }
  }, [mealType]);

  if (!mealType) return <p>Select a meal type</p>;
  return options.length ? (
    <ComboboxControl {...props} options={options} />
  ) : (
    <>
      <h4>{props.label ?? "Loading..."}</h4>
      <Spinner />
      <br />
      <br />
    </>
  );
};

export default MealTypeSelectControl;
