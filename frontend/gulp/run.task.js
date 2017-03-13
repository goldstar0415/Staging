'use strict';
// deprecated
const gulp = require('gulp');
const serverFactory = require('spa-server');
const config = require('./config');
const serveStatic = require('serve-static');
const path = require('path');

const serverConfig = {
  path: path.join('..', config.paths.src),
  port: process.env.PORT || 8081,
  fallback: {
    'text/html' : '/index.html',
  },
  serveStaticConfig: {},
  middleware: [
    serveStatic('..'),
    serveStatic( path.join('..', config.paths.tmp, 'serve') ),
  ]
};

gulp.task('run', () => {
  const server = serverFactory.create(serverConfig);
  server.start();
});
