module.exports = function(grunt) {

    var files = {
       '<%= paths.css %>/main.min.css': [
            '<%= paths.less %>/app.less'
        ],
        '<%= paths.css %>/admin.min.css': [
            '<%= paths.less %>/admin.less'
        ]
    }

    grunt.config.set('less', {
        dev: {
            files: files,
            options: {
                compress: false,
                sourceMap: true,
                sourceMapFilename: '<%= paths.css %>/main.min.css.map',
            }
        },
        dist: {
            files: files,
            options: {
                compress: true,
                sourceMap: false,
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-less');

}