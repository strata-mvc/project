module.exports = function(grunt) {

    grunt.config.set('watch', {
        less: {
           files: [
               '<%= paths.less %>/**/*.less',
           ],
           tasks: ['less:dev', 'autoprefixer:dist']
        },
        js: {
            files: [
                '<%= paths.js %>/**/*.js',
                '<%= paths.grunt_tasks %>/*.js'
            ],
            tasks: ['uglify:dev']
        }
    });

    grunt.loadNpmTasks('grunt-contrib-watch');
}