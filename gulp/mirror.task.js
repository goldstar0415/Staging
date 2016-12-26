'use strict';

const gulp = require('gulp');
const del = require('del');
const config = require('./config');
const path = require('path');
const sequence = require('run-sequence');

gulp.task('mirror:clean', () => {
  return del.sync([
    path.join(config.paths.dist, '/app/**/*'),
    path.join(config.paths.dist, '/assets/**/*'),
  ]);
});

gulp.task('mirror:app', () => {
  return gulp.src(path.join(config.paths.src, '/app/**/*'))
    .pipe(gulp.dest(path.join(config.paths.dist, '/app')));
});

gulp.task('mirror:assets', () => {
  return gulp.src(path.join(config.paths.src, '/assets/**/*'))
    .pipe(gulp.dest(path.join(config.paths.dist, '/assets')));
});

gulp.task('mirror:bower', () => {
  return gulp.src('./bower_components/**/*')
    .pipe(gulp.dest(path.join(config.paths.dist, '/bower_components')));
});

gulp.task('mirror', (cb) => {
  sequence('mirror:clean', ['mirror:app', 'mirror:assets', 'mirror:bower'], cb);
});
