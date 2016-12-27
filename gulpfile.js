'use strict';

const gulp = require('gulp');
const sequence = require('run-sequence').use(gulp);
const requireAll = require('require-all');

requireAll({
  dirname:  __dirname + '/gulp',
  filter:  /(.+)\.task\.js$/i,
  recursive: false,
});
