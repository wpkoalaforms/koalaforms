const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');

const commonJsRule = {
  test: /\.js$/,
  exclude: /node_modules/,
  use: {
    loader: 'babel-loader',
  },
};

const commonVueRule = {
  test: /\.vue$/,
  loader: 'vue-loader',
};

const commonCssRule = {
  test: /\.css$/,
  use: ['vue-style-loader', 'css-loader'],
};

const resolveConfig = {
  alias: {
    vue: '@vue/runtime-dom',
  },
  extensions: ['.js', '.vue'],
};

module.exports = [
  {
    entry: {
      'form-bundle': './src/vue/vue-app.js',
    },
    output: {
      filename: '[name].js',
      path: path.resolve(__dirname, 'assets/client/js'),
    },
    module: {
      rules: [commonVueRule, commonCssRule, commonJsRule],
    },
    resolve: resolveConfig,
    plugins: [new VueLoaderPlugin()],
  },
  {
    entry: {
      'dashboard-bundle': './src/vue/admin-dashboard-app.js',
      'settings-bundle':  './src/vue/admin-settings-app.js',
      'analytics-bundle': './src/vue/admin-analytics-app.js',
    },
    output: {
      filename: '[name].js',
      path: path.resolve(__dirname, 'assets/admin/js'),
    },
    module: {
      rules: [commonVueRule, commonCssRule, commonJsRule],
    },
    resolve: resolveConfig,
    plugins: [new VueLoaderPlugin()],
  },
];
