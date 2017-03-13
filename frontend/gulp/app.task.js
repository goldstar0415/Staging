'use strict';

const path = require('path');
const gulp = require('gulp');
const uglify = require('gulp-uglify');
const concat = require('gulp-concat');
const sort = require('gulp-angular-filesort');
const config = require('./config');
const stream = require('./app.stream');

gulp.task('app', () => {
  return gulp.src(stream)
    .pipe(sort())
    .pipe(concat('app.build.js'))
    .pipe(uglify({mangle: false}))
    .pipe(gulp.dest(path.join(config.paths.dist, '/scripts')));
});
