module.exports = function(grunt) {

    grunt.config.set('jshint', {
        options: {
           jshintrc: '.jshintrc'
        },
        all: [
           'Gruntfile.js',
           'assets/js/*.js',
           '!assets/js/scripts.min.js',
           '!assets/js/deps.min.js'
        ]
    });

    grunt.loadNpmTasks('grunt-contrib-jshint');

}