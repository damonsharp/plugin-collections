let mix = require('laravel-mix');

mix
	.js('assets/js/admin-scripts.js', 'js')
	.css( 'assets/css/admin.css', 'css')
	.version()
	.setPublicPath('dist');
