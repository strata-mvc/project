module.exports = function(grunt) {

    grunt.config.set('clean', {
        dist: [
            '<%= paths.css %>/custom.min.css',
            '<%= paths.js %>/scripts.min.js'
        ]
    });

    grunt.loadNpmTasks('grunt-contrib-clean');
}