'use strict';

const gulp = require('gulp');
const serverFactory = require('spa-server');
const config = require('./config');
const serveStatic = require('serve-static');
const path = require('path');
const cssStream = require('./css.stream');
const $ = require('gulp-load-plugins')();
const _ = require('lodash');

let sassScope = _.filter(cssStream, (path) => {
  return /\S+\.scss/i.test(path);
});

gulp.task('serve:watch', () => {

  const options = {
    name: 'serve',
    verbose: true,
  };

  gulp.watch(sassScope, options, () => {
    gulp.start('serve:sass');
  });

});

gulp.task('serve:sass', () => {
  console.log('Recompile sass:', sassScope);
  return gulp.src(sassScope)
    .pipe( $.sourcemaps.init() )
    .pipe( $.sass()).on('error', config.errorHandler('Sass') )
    .pipe( $.autoprefixer()).on('error', config.errorHandler('Autoprefixer') )
    .pipe( $.sourcemaps.write() )
    .pipe( $.concat('main.css') )
    .pipe( gulp.dest(path.join(config.paths.tmp, '/serve/assets/css')) );
});

gulp.task('serve', ['serve:sass', 'serve:watch'], () => {

  const serverConfig = {
    path: path.join(config.paths.src),
    port: process.env.PORT || 8081,
    fallback: {'text/html' : '/index.html'},
    serveStaticConfig: {},
    middleware: [
      serveStatic('.'),
      serveStatic( path.join(config.paths.tmp, 'serve') ),
    ]
  };

  const server = serverFactory.create(serverConfig);
  server.start();
});
