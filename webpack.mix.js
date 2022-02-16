
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
	.sass('assets/scss/app.scss', deploy + '/css/')
	.vue()
	.tailwind()
	.setPublicPath('.');
