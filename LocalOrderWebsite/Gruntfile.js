module.exports = function (grunt){
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    concurrent: {
      dev: {
        tasks: ['nodemon', 'watch'],
        options: {
          logConcurrentOutput: true
        }
      }
    },

    concat: {
      theme: {
        options: {
          // banner: 'angular.module("brMaterial.core").constant("$BR_THEME_CSS","',
          // footer: '");',
          separator: '',
          // process: function(src, filepath) {
          //   if(filepath.indexOf('theme') >= 0){
          //     return src.replace(/(\r\n|\n|\r)/gm, '');
          //   }

          //   return '';
          // }
        },
        // files: {
        //   './src/brMaterial/theme.js': ['./src/brMaterial/**/*.css'],
        // }
      },

      js: {
        src: ['./src/js/app.js', './src/js/controllers.js', './src/**/*.js'],
        dest: './public/js/app.js'
      },

      css: {
        options: {
          process: function(src, filepath) {
            if(filepath.indexOf('theme') === -1){
              return src;
            }

            return;
          }
        },
        files: {
          './src/final.css': ['./src/css/style.css', './src/css/**/*.css', './src/directives/**/*.css'],
        }
      }
    },

    watch:{
      css: {
        files: ['./src/css/*.css', './src/directives/**/*.css'],
        tasks: ['newer:concat:css']
      },
      js: {
        files: ['./src/**/*.js'],
        tasks: ['newer:concat:js']
      },
      autoprefixcss: {
        files: ['./src/final.css'],
        tasks: ['autoprefixer:multiple_files']
      },
      // copyjs: {
      //   files: ['./bin/javascripts/brMaterial.js'],
      //   tasks: ['copy:js']
      // },
      copycss: {
        files: ['./src/final.css'],
        tasks: ['copy:css']
      },
      copypartials: {
        files: ['./src/**/*.html'],
        tasks: ['newer:copy:partials']
      }
    },

    autoprefixer: {
      options: {
        // Task-specific options go here.
      },
      multiple_files: {
        expand: true,
        flatten: true,
        src: './src/final.css',
        dest: './src/'
      }
    },

    copy: {
      css: {
        src: './src/final.css',
        dest: './public/css/style.css'
      },
      js: {
        src: './bin/javascripts/brMaterial.js',
        dest: './public/javascripts/brMaterial.js'
      },
      partials: {
        expand: true,
        flatten: true,
        src: ['./src/**/*.html'],
        dest: './public/partials/',
        filter: 'isFile'
      }
    },

    nodemon: {
      dev: {
        script: 'server.js',
        nodeArgs: ['--debug'],
        env: {
          PORT: '8080'
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-concurrent');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-nodemon');
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-newer');

  grunt.registerTask('default', [
    'concurrent:dev',
    'concat',
    'autoprefixer',
    'watch',
    'copy',
    'nodemon',
    'newer'
  ]);
};