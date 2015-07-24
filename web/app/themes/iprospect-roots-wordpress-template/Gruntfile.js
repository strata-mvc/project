'use strict';
module.exports = function(grunt) {

  // output task timing
  require('time-grunt')(grunt);

  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),

    paths: {
      assets: 'assets',
      css: 'assets/css',
      less: 'assets/less',
      js: 'assets/js',
      img: 'assets/img',
      fonts: 'assets/fonts',
      grunt_tasks: 'grunt_tasks'
    }

  });

  // Load tasks in grunt_tasks folder
  grunt.loadTasks('grunt_tasks');

  // Register tasks
  grunt.registerTask('default', [
    'clean',
    'less:dev',
    'autoprefixer:dist',
    'uglify:dev',
    'watch'
  ]);

  grunt.registerTask('dist', [
    'clean',
    'less:dist',
    'autoprefixer:dist',
    'uglify:dist'
  ]);

};
