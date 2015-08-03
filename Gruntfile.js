// var compilePaths = {
// 	app: '..',
// 	can: 'canjs/amd/can',
// 	jquery: '//code.jquery.com/jquery-1.11.3.min'
// };

// Set timer
var timer = require("grunt-timer");

module.exports = function(grunt) {
	// Init timer
	timer.init(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		/**
		 * Clean dist files
		 */
		clean: [
			'web/app/themes/leham/assets/js/dist/*.js',
			'web/app/themes/leham/assets/css/dist/*.css',
		]

	});

	grunt.loadNpmTasks('grunt-sass');
	grunt.loadNpmTasks('grunt-hash');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-requirejs');

	grunt.registerTask('default', ['clean']);
};