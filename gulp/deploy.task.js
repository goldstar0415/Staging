'use strict';

const gulp = require('gulp');
const path = require('path');
const sequence = require('run-sequence').use(gulp);
const config = require('./config');

gulp.task('deploy', (cb) => {
  sequence('mirror', 'build:prod', cb);
});
