// Set timer
var timer = require("grunt-timer");

module.exports = function(grunt) {
	// Init timer
	timer.init(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		/**
		 * Clean dist files.
		 */
		clean: [
			'web/app/themes/leham/assets/js/dist/*.js',
			'web/app/themes/leham/assets/css/dist/*.css',
		],

		/**
		 * Add hash to all assets.
		 */
		hash: {
			options: {
				hashLength: 8,
				hashFunction: function(source, encoding) {
					return require('crypto').createHash('sha1').update(source, encoding).digest('hex');
				}
			},
			main_js: {
				src: 'web/app/themes/leham/assets/js/compiled/main.js',
				dest: 'web/app/themes/leham/assets/js/dist/'
			},
			main_css: {
				src: 'web/app/themes/leham/assets/css/compiled/main.css',
				dest: 'web/app/themes/leham/assets/css/dist/'
			}
		}
	});

	grunt.loadNpmTasks('grunt-hash');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-requirejs');

	grunt.registerTask('test', ['clean', 'hash']);
	grunt.registerTask('default', ['test']);
};