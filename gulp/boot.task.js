'use strict';

const path = require('path');
const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const config = require('./config');
const stream = require('./boot.stream');

gulp.task('boot:css', () => {
  return gulp.src(stream)
    .pipe($.concat('boot.css.tmp'))
    .pipe($.csso())
    .pipe($.wrap(`<style type="text/css"><%= contents %></style>`, {}, {parse: false}))
    .pipe(gulp.dest(path.join(config.paths.tmp, '/boot')));
});

gulp.task('boot', ['boot:css']);
