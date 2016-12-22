'use strict';

const path = require('path');
const gulp = require('gulp');
const uglify = require('gulp-uglify');
const concat = require('gulp-concat');
const config = require('./config');
const stream = require('./vendor.stream');

gulp.task('vendor', () => {
  return gulp.src(stream)
    .pipe(concat('vendor.build.js'))
    .pipe(uglify({mangle: false, preserveComments: 'license'}))
    .pipe(gulp.dest(path.join(config.paths.dist, '/scripts')));
});
