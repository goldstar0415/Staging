'use strict';

const gulp = require('gulp');
const del = require('del');
const config = require('./config');
const path = require('path');

gulp.task('_mirror_clean', () => {
  return del.sync([
    path.join(config.paths.dist, '/app/**/*'),
    path.join(config.paths.dist, '/assets/**/*'),
  ]);
});

gulp.task('_mirror_app', () => {
  return gulp.src(path.join(config.paths.src, '/app/**/*'))
    .pipe(gulp.dest(path.join(config.paths.dist, '/app')));
});

gulp.task('_mirror_assets', () => {
  return gulp.src(path.join(config.paths.src, '/assets/**/*'))
    .pipe(gulp.dest(path.join(config.paths.dist, '/assets')));
});

gulp.task('_mirror_bower', () => {
  return gulp.src('./bower_components/**/*')
    .pipe(gulp.dest(path.join(config.paths.dist, '/bower_components')));
});

gulp.task('_mirror', ['_mirror_app', '_mirror_assets', '_mirror_bower']);

gulp.task('mirror', ['_mirror_clean'], () => {
  gulp.start('_mirror');
});
