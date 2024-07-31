// extend default wp-scripts config
const defaultConfig = require("@wordpress/scripts/config/webpack.config");

/**
 * Add a new entry for each eventual script you want to load
 * Currently only our new block config with a simple alert.
 */
module.exports = {
  ...defaultConfig,
  entry: {
    "alimento-block": "./gutenberg/alimento-block/index.js",
    "dieta-rules": "./gutenberg/dieta-rules.js",
  },
  output: {
    ...defaultConfig.output,
  },
};
