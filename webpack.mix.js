
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
	.js("assets/src/js/account.js", deploy + '/js/account.min.js')
	.js("assets/src/js/edit/email.js", deploy + '/js/edit/email.min.js')
	.vue()
	.tailwind()
	.setPublicPath('.');
