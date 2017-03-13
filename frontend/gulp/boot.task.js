'use strict';

const path = require('path');
const gulp = require('gulp');
const $ = require('gulp-load-plugins')();
const config = require('./config');
const stream = require('./boot.stream');
const SRC = config.paths.src;

gulp.task('boot:css', () => {
  return gulp.src(stream)
    .pipe($.concat('boot.css.tmp'))
    .pipe($.csso())
    .pipe($.wrap(`<style type="text/css"><%= contents %></style>`, {}, {parse: false}))
    .pipe(gulp.dest(path.join(config.paths.tmp, '/boot')));
});

gulp.task('boot:env', () => {
    return gulp.src([`${SRC}/env.js`])
        .pipe($.concat('boot.env.js.tmp'))
        .pipe($.uglify({mangle:false}))
        .pipe($.wrap(`<script type="text/javascript"><%= contents %></script>`, {}, {parse: false}))
        .pipe(gulp.dest(path.join(config.paths.tmp, '/boot')));
});

gulp.task('boot', ['boot:css', 'boot:env']);
