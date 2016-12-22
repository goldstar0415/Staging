
'use strict';

const gulp = require('gulp');
const clean = require('gulp-clean');
const inject = require('gulp-inject');
const config = require('./config');
// const appStream = require('./app.stream');
// const assetsStream = require('./assets.stream');

gulp.task('serve', ['clean', 'build'], () => {
  gulp.start('run');
});

