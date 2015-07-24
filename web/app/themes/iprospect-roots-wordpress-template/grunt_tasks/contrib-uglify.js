module.exports = function(grunt) {

    var files = {
        '<%= paths.js %>/vendor/modernizr.min.js': [
            '<%= paths.js %>/bower_components/modernizr/modernizr.js'
        ],
        '<%= paths.js %>/deps.min.js': [
            '<%= paths.js %>/plugins/compat-console.js',
            '<%= paths.js %>/bower_components/gsap/src/minified/TweenMax.min.js',
            '<%= paths.js %>/plugins/imagesloaded/imagesloaded.pkgd.min.js', //problem with the bower component
            '<%= paths.js %>/bower_components/jQuery.mmenu/dist/js/jquery.mmenu.min.all.js',
            '<%= paths.js %>/bower_components/slick-carousel/slick/slick.min.js',
            '<%= paths.js %>/bower_components/jquery.customSelect/jquery.customSelect.min.js',
        ],
        '<%= paths.js %>/scripts.min.js': [
            '<%= paths.js %>/deps.min.js',
            '<%= paths.js %>/_main.js'
        ],
        '<%= paths.js %>/admin.min.js': [
            '<%= paths.js %>/deps.min.js',
            '<%= paths.js %>/_admin.js'
        ]
    };

    grunt.config.set('uglify', {
        dev: {
            files: files, 
            options: {
                sourceMap: '<%= paths.js %>/scripts.min.js.map',
                sourceMappingURL: '/wp-content/themes/roots/<%= paths.js %>/scripts.min.js.map',
                compress: false,
                beautify: true,
                mangle: false
            }
        },
        dist: {
            files: files,
            options: {
              compress: {
                drop_console: true
              },
              beautify: false,
              mangle: true,
              drop_console: true
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
}