/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./gutenberg/alimento-block/edit.js":
/*!******************************************!*\
  !*** ./gutenberg/alimento-block/edit.js ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ edit)
/* harmony export */ });
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _helper_get_meals__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../helper-get-meals */ "./gutenberg/helper-get-meals.js");
/* harmony import */ var _component_MealTypeSelectControl__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../component-MealTypeSelectControl */ "./gutenberg/component-MealTypeSelectControl.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__);







// import React wrapper dependencies


// Internal dependencies



/**
 * Functional component for the Edit.js
 * @param {*} props
 * @returns
 */

function edit(props) {
  // We'll use it later
  const {
    replaceInnerBlocks
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useDispatch)("core/block-editor");
  const mealTerms = (0,_helper_get_meals__WEBPACK_IMPORTED_MODULE_7__.useMealsTerms)();
  const mealOptions = mealTerms.map(term => {
    return {
      label: term.name,
      value: term.slug
    };
  });

  // helper to update the attribute.imgSrc when alimentoID changes.
  const fetchImage = async () => {
    const fetchPost = async () => {
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
        path: `/wp/v2/aliment/${props.attributes.alimentoID}`
      });
      return data;
    };
    const postData = await fetchPost();
    if (postData?.featured_media) {
      // featured_media is the ID of the attachment. We grab the media src.
      // NOTE: I tried using `select` but for some reasonit doesnt work.
      _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
        path: `/wp/v2/media/${postData.featured_media}`
      }).then(attachmentPost => {
        if (attachmentPost && attachmentPost.media_details && attachmentPost.media_details.sizes) {
          const imageSource = attachmentPost.media_details.sizes.full.source_url;
          console.log(imageSource);
          props.setAttributes({
            imgSrc: imageSource
          });
        }
      });
    }
  };

  // Get all Aliments CPT and convert them into Options for the Dropdown
  const aliments = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => {
    const alimenti = select("core").getEntityRecords("postType", "aliment");
    if (alimenti) {
      return alimenti.map(aliment => {
        return {
          label: aliment.title.raw,
          value: aliment.id
        };
      });
    } else {
      return [];
    }
  }, []);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_6__.useEffect)(() => {
    // keep attr imgSrc Up to date.
    if (props.attributes.alimentoID) {
      fetchImage();
    } else {
      props.setAttributes({
        imgSrc: ""
      }); // TODO : use default?
    }

    // Replace the content text with the default one for the aliment.
    if (props.attributes.alimentoID) {
      const innerBlocks = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.select)("core/block-editor").getBlock(props.clientId).innerBlocks;
      const innerBlockContent = innerBlocks.map(block => {
        return block.innerBlocks.map(innerBlock => {
          return innerBlock.attributes.content;
        });
      });
      // if the inner blocks are empty, we replace them with the default aliment content
      if (innerBlockContent.every(content => content.length === 0)) {
        prefillInnerBlocks();
      }
    }
  }, [props.attributes.alimentoID]);

  // create a function that grabs the editor content for the current attr alimentoID.
  // And it replaces the inner blocks with the content of the editor
  const prefillInnerBlocks = () => {
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
      path: `/wp/v2/aliment/${props.attributes.alimentoID}?context=edit`
    }).then(json => {
      if (json && json.content && json.content.raw) {
        const content = json.content.raw;
        const blocks = (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_2__.parse)(content);
        // console.log(blocks);
        replaceInnerBlocks(props.clientId, blocks);
      }
    });
  };
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)("div", {
    ...(0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.useBlockProps)({
      className: (props.attributes.imgSrc && props.attributes.hideImage !== true ? ` has-image ` : ` no-image `) + ` is-${props.attributes.mealType}` + (props.attributes.isAlternative ? ` is-alternative` : ``)
    }),
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.InspectorControls, {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Select aliment", "asim"),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_component_MealTypeSelectControl__WEBPACK_IMPORTED_MODULE_8__["default"], {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("ALIMENT", "asim"),
          value: props.attributes.alimentoID,
          mealType: props.attributes.mealType,
          onChange: value => {
            props.setAttributes({
              alimentoID: String(value)
            });
          }
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
          isPrimary: true,
          onClick: () => {
            console.log("You clicked the button!");
            // Here we can add the logic to alter the block attributes
            prefillInnerBlocks();
          },
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Prefill text with defaults", "asim")
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("br", {}), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("br", {}), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("div", {
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.RadioControl, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Meal", "asim"),
            selected: props.attributes.mealType,
            options: [{
              label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("--none---", "asim"),
              value: ""
            }, ...mealOptions],
            onChange: value => {
              let translated = props.attributes.title;
              if (!props.attributes.isAlternative) {
                const label = mealOptions.find(meal => meal.value === value).label;
                translated = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)(label, "asim");
              }
              props.setAttributes({
                mealType: value,
                title: translated
              });
            }
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("div", {
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CheckboxControl, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Is Alternative?", "asim"),
            checked: props.attributes.isAlternative,
            onChange: value => {
              let newTitle = "";
              if (value === true) {
                newTitle = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Alternative", "asim");
              } else {
                const labelable = mealOptions.find(meal => meal.value === props.attributes.mealType);
                newTitle = (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)(labelable && labelable.label ? labelable.label : "", "asim");
              }
              props.setAttributes({
                isAlternative: value,
                title: newTitle
              });
            }
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("div", {
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CheckboxControl, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("Hide Image", "asim"),
            checked: props.attributes.hideImage,
            onChange: value => {
              props.setAttributes({
                hideImage: value
              });
            }
          })
        })]
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsxs)(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__.Fragment, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.RichText, {
        tagName: "h3",
        className: "alimento-title",
        value: props.attributes.title,
        placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_4__.__)("eg. Breakfast", "asim"),
        disabled: props.attributes.isAlternative,
        onChange: value => {
          props.setAttributes({
            title: value
          });
        }
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("div", {
        className: "alimento-left-column",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_0__.InnerBlocks, {
          allowedBlocks: ["core/paragraph", "core/heading", "core/list"],
          orientation: "vertical",
          template: [["core/paragraph"], ["core/paragraph"], ["core/paragraph"]],
          onChange: content => setAttributes({
            textContent: content
          })
        })
      }), props.attributes.hideImage !== true && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("div", {
        className: "alimento-right-column",
        children: props.attributes.alimentoID && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("div", {
          className: "alimento-image",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_9__.jsx)("img", {
            src: props.attributes.imgSrc ? props.attributes.imgSrc : "http://placehold.it/300x300"
          })
        })
      })]
    })]
  });
}

/***/ }),

/***/ "./gutenberg/component-MealTypeSelectControl.js":
/*!******************************************************!*\
  !*** ./gutenberg/component-MealTypeSelectControl.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__);




/**
 *
 * @param {{ mealType: string, label: string, onChange: (value: string) => void, value: string }} props
 * @returns
 */

const MealTypeSelectControl = ({
  mealType,
  ...props
}) => {
  var _props$label;
  const [options, setOptions] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    const getTermID = async slug => {
      try {
        const terms = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: `/wp/v2/meal?slug=${slug}&_fields=id`
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
        const posts = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_2___default()({
          path: `/wp/v2/aliment?meal=${mealTermID}&_fields=id,title.rendered`
        });
        const formattedOptions = posts.map(post => ({
          label: new DOMParser().parseFromString(post.title.rendered, "text/html").documentElement.textContent,
          value: String(post.id)
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
  if (!mealType) return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("p", {
    children: "Select a meal type"
  });
  return options.length ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.ComboboxControl, {
    ...props,
    options: options
  }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("h4", {
      children: (_props$label = props.label) !== null && _props$label !== void 0 ? _props$label : "Loading..."
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Spinner, {}), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("br", {}), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)("br", {})]
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (MealTypeSelectControl);

/***/ }),

/***/ "./gutenberg/helper-get-meals.js":
/*!***************************************!*\
  !*** ./gutenberg/helper-get-meals.js ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   useMealsTerms: () => (/* binding */ useMealsTerms)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1__);
// useMealsTerms.js


function useMealsTerms() {
  const [terms, setTerms] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)([]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    // Fetch terms for the 'meal' taxonomy
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_1___default()({
      path: "/wp/v2/meal?orderby=id&order=desc"
    }).then(data => {
      setTerms(data);
    }).catch(error => {
      console.error("Error fetching terms:", error);
    });
  }, []); // Empty dependency array means this runs once on mount

  return terms;
}

/***/ }),

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["ReactJSXRuntime"];

/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
/*!*******************************************!*\
  !*** ./gutenberg/alimento-block/index.js ***!
  \*******************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./edit */ "./gutenberg/alimento-block/edit.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__);





// import { addFilter } from "@wordpress/hooks";
// import { getBlockType } from "@wordpress/blocks";
// import { createElement } from "@wordpress/element";



(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)("asim/alimento-block", {
  title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Alimento Block", "asim"),
  edit: _edit__WEBPACK_IMPORTED_MODULE_3__["default"],
  save: function ({
    attributes
  }) {
    const blockProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.useBlockProps.save();

    // Use InnerBlocks.Content to render the inner blocks' content
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("div", {
      ...blockProps,
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("div", {
        className: "alimento-left-column",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InnerBlocks.Content, {})
      })
    });
  }
});

// Function to disable the Styles tab
// @TODELETE: this doesnt work!
// const disableStylesTab = (BlockEdit) => (props) => {
//   // Check if the block is the one you want to modify
//   if (props.name === "asim/alimento-block") {
//     // Remove the Styles tab by deleting the styles from block settings
//     console.log("%c props", "background: #222; color: #bada55", props);
//     delete getBlockType(props.name).styles;
//   }
//   return <BlockEdit {...props} />;
// };

// // Add the filter to modify the BlockEdit component
// addFilter("editor.BlockEdit", "asim/disable-styles-tab", disableStylesTab);
/******/ })()
;
//# sourceMappingURL=alimento-block.js.map