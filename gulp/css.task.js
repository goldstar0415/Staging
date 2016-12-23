'use strict';

const path = require('path');
const gulp = require('gulp');
const config = require('./config');
const stream = require('./css.stream');
const $ = require('gulp-load-plugins')();

gulp.task('css', () => {

  const sassOptions = {
    style: 'compressed',
  };


  return gulp.src(stream)
    .pipe()
    .pipe($.concat('styles.build.css'))
    .pipe(gulp.dest(path.join(config.paths.dist, '/')));
});
