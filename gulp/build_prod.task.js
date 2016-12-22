'use strict';

const path = require('path');
const gulp = require('gulp');
const config = require('./config');
const $ = require('gulp-load-plugins')();
const _ = require('lodash');
const hash = require('gulp-hash');

gulp.task('build:prod', ['vendor', 'app'], () => {

  const injectDefaultOptions = {
    transform: (filePath) => {
      return path.normalize( filePath.replace(config.paths.dist, '') );
    },
  };
  // fixme
  return gulp.src( path.join(config.paths.src, '/index.html') )
    .pipe(
      $.inject(
        gulp.src([ path.join(config.paths.dist, '/scripts/vendor.build.js') ], {read: false}),
        _.extend(injectDefaultOptions, {starttag: '<!-- inject:vendor -->'})
      )
    )
    .pipe(
      $.inject(
        gulp.src([ path.join(config.paths.dist, '/scripts/app.build.js') ], {read: false}),
        _.extend(injectDefaultOptions, {starttag: '<!-- inject:app -->'})
      )
    )
    .pipe( gulp.dest(config.paths.dist) );
});
