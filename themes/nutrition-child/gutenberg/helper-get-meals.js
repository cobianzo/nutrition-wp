// useMealsTerms.js
import { useState, useEffect } from "@wordpress/element";
import apiFetch from "@wordpress/api-fetch";

export function useMealsTerms() {
  const [terms, setTerms] = useState([]);

  useEffect(() => {
    // Fetch terms for the 'meal' taxonomy
    apiFetch({ path: "/wp/v2/meal?orderby=id&order=desc" })
      .then((data) => {
        setTerms(data);
      })
      .catch((error) => {
        console.error("Error fetching terms:", error);
      });
  }, []); // Empty dependency array means this runs once on mount

  return terms;
}
