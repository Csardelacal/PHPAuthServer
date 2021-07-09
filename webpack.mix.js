
var mix = require('laravel-mix');

mix
	.js('assets/js/totp.js', 'public/assets/js')
	.sass('assets/src/scss/app.scss', 'assets/css')
	.setPublicPath('public')