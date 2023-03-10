
let mix = require('laravel-mix');
let deploy = 'assets';

require('laravel-mix-tailwind');

mix
	.options({
		/* https://github.com/laravel-mix/laravel-mix/issues/2090 */
		fileLoaderDirs: {
			images: deploy + '/images',
			fonts: deploy + 'fonts'
		}
	})
	/**
	 * This enables typescript support in vue. Which should allow me to build way
	 * more robust components and modules in the future.
	 */
	.webpackConfig({
		module: {
			rules: [
				{
					test: /\.tsx?$/,
					loader: "ts-loader",
					exclude: /node_modules/,
					options: { appendTsSuffixTo: [/\.vue$/] },
				}
			]
		}
	})
	
	.sass('assets/scss/app.scss', deploy + '/css/')
	.js("assets/src/js/account.js", deploy + '/js/account.min.js')
	.js("assets/src/js/edit/email.js", deploy + '/js/edit/email.min.js')
	.js("assets/src/js/session/ip.ts", deploy + '/js/session/ip.min.js')
	.vue()
	.tailwind()
	.setPublicPath('.');
