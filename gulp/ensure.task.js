const gulp = require('gulp');
const serverFactory = require('spa-server');
const config = require('./config');
const path = require('path');

const serverConfig = {
  path: config.paths.dist,
  port: process.env.ENSURE_PORT || 8082,
  fallback: {
    'text/html' : '/index.html',
  },
};

gulp.task('ensure', ['build:prod'], () => {
  console.log('Starting a server for a production version...');
  const server = serverFactory.create(serverConfig);
  server.start();
});
