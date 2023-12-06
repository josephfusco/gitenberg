const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
  ...defaultConfig,
  entry: {
    'settings': './js/settings.js',
    'gitenberg-sidebar': './js/gitenberg-sidebar.js',
  },
};