module.exports = function(grunt) {

    grunt.config.set('autoprefixer', {
        options: {
            browsers: ['last 2 versions', 'ie 8', 'ie 9'], 
            diff: false,
            safe: true,
            remove: false
        },
        dist: {
            src: '<%= paths.css %>/main.min.css',
            dest: '<%= paths.css %>/main.min.css'
        }
    });

    grunt.loadNpmTasks('grunt-autoprefixer');

}