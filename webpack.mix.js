
var mix = require('laravel-mix');

mix
	.js('assets/js/totp.js', 'public/assets/js')
	.setPublicPath('public')