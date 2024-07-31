// extend default wp-scripts config
const defaultConfig = require("@wordpress/scripts/config/webpack.config");

/**
 * Add a new entry for each eventual script you want to load
 * Currently only our new block config with a simple alert.
 */
module.exports = {
  ...defaultConfig,
  entry: {
    "block-alimento": "./src/blocks/alimento-block/index.js",
  },
  output: {
    ...defaultConfig.output,
  },
};
