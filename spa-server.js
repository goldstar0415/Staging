const serverFactory = require('spa-server');
const gulpConfig = require('./gulp/config');
const serveStatic = require('serve-static');
const path = require('path');

const config = {
  path: gulpConfig.paths.src,
  port: process.env.PORT || 8081,
  fallback: {
    'text/html' : '/index.html',
  },
  serveStaticConfig: {},
  middleware: [
    serveStatic('.'),
    serveStatic(path.join(gulpConfig.paths.tmp, 'serve')),
  ]
};

const server = serverFactory.create(config);

server.start();
