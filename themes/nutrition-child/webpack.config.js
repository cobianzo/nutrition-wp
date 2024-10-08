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
    "piatto-block": "./gutenberg/piatto-block/index.js",
    "testblock-block": "./gutenberg/testblock-block/index.js",
    "test2-block": "./gutenberg/test2-block/index.js",
    "dieta-rules": "./gutenberg/dieta-rules.js",
    "generic-rules": "./gutenberg/generic-rules.js",
  },
  output: {
    ...defaultConfig.output,
  },
};
