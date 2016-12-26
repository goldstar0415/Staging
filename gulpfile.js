'use strict';

const gulp = require('gulp');
const path = require('path');
const sequence = require('run-sequence').use(gulp);
const requireAll = require('require-all');
const config = require('./gulp/config');

requireAll({
  dirname:  __dirname + '/gulp',
  filter:  /(.+)\.task\.js$/i,
  recursive: false,
});

gulp.task('serve', ['build:dev'], () => {
  gulp.start('run');
});

gulp.task('deploy', (cb) => {
  sequence('mirror', 'build:prod', cb);
});

