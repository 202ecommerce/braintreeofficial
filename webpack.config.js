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
		'js/bo_order': './_dev/js/bo_order.js',
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