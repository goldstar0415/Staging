'use strict';

const gulp = require('gulp');
const clean = require('gulp-clean');
const config = require('./config');

gulp.task('clean', () => {
  return gulp
    .src(config.paths.tmp, {read: false})
    .pipe(clean());
});
