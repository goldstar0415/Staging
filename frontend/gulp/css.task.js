'use strict';

const path = require('path');
const gulp = require('gulp');
const config = require('./config');
const stream = require('./css.stream');
const $ = require('gulp-load-plugins')();

gulp.task('css:compile', () => {

  const scssFilter = $.filter('**/*.scss');
  const sassOptions = {
    style: 'compressed',
  };

  return gulp.src(stream)
    .pipe( scssFilter )
    .pipe( $.sourcemaps.init() )
    .pipe( $.sass(sassOptions)).on('error', config.errorHandler('Sass') )
    .pipe( $.autoprefixer()).on('error', config.errorHandler('Autoprefixer') )
    .pipe( $.sourcemaps.write() )
    .pipe( $.concat('main.css') )
    .pipe( gulp.dest(path.join(config.paths.dist, '/assets/css')) );
});

gulp.task('css', ['css:compile'], () => {

  const cssFilter = $.filter('**/*.css');
  stream.push( path.join(config.paths.dist, '/assets/css/main.css') );

  return gulp.src( stream )
    .pipe( cssFilter )
    .pipe( $.concat('styles.build.css') )
    .pipe( $.csso() )
    .pipe( gulp.dest(path.join(config.paths.dist, '/assets/css')) );
});
