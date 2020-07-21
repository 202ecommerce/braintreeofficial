/*
 * 2007-2020 PayPal
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author 2007-2020 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

const path = require('path');
const FixStyleOnlyEntriesPlugin = require('webpack-fix-style-only-entries');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');

const minimizers = [];
const plugins = [
	new FixStyleOnlyEntriesPlugin(),
	new MiniCssExtractPlugin({
		filename: '[name].css',
	}),
];

const config = {
	entry: {
		'js/customizeCheckoutAdmin': './_dev/js/customizeCheckoutAdmin.js',
		'js/helpAdmin': './_dev/js/helpAdmin.js',
		'js/setupAdmin': './_dev/js/setupAdmin.js',
		'js/order_confirmation': './_dev/js/order_confirmation.js',
		'js/payment_bt': './_dev/js/payment_bt.js',
		'js/payment_pbt': './_dev/js/payment_pbt.js',
		'css/bt_admin': './_dev/scss/bt_admin.scss',
		'css/braintree': './_dev/scss/bt_main.scss',
		'js/migrationAdmin': './_dev/js/migrationAdmin.js',
        'js/btShortcut': './_dev/js/btShortcut.js',
		'js/shortcutPayment': './_dev/js/shortcutPayment.js'
	},

	output: {
		filename: '[name].js',
		path: path.resolve(__dirname, './views/'),
	},

	module: {
		rules: [{
				test: /\.js$/,
				exclude: /node_modules/,
				use: [{
					loader: 'babel-loader',
					options: {
						presets: ['@babel/preset-env'],
					},
				}, ],
			},

			{
				test: /\.(s)?css$/,
				use: [{
						loader: MiniCssExtractPlugin.loader
					},
					{
						loader: 'css-loader'
					},
					{
						loader: 'postcss-loader'
					},
					{
						loader: 'sass-loader'
					},
				],
			},

		],
	},

	externals: {
		$: '$',
		jquery: 'jQuery',
	},

	plugins,

	optimization: {
		minimizer: minimizers,
	},

	resolve: {
		extensions: ['.js', '.scss', '.css'],
		alias: {
			'~': path.resolve(__dirname, './node_modules'),
			'$img_dir': path.resolve(__dirname, './views/img'),
		},
	},
};

module.exports = (env, argv) => {
	// Production specific settings
	if (argv.mode === 'production') {
		const terserPlugin = new TerserPlugin({
			cache: true,
			extractComments: /^\**!|@preserve|@license|@cc_on/i, // Remove comments except those containing @preserve|@license|@cc_on
			parallel: true,
			terserOptions: {
				drop_console: true,
			},
		});

		config.optimization.minimizer.push(terserPlugin);
	}

	return config;
};
