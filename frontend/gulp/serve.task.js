'use strict';

const gulp = require('gulp');
const serverFactory = require('spa-server');
const config = require('./config');
const serveStatic = require('serve-static');
const path = require('path');
const cssStream = require('./css.stream');
const $ = require('gulp-load-plugins')();
const _ = require('lodash');
const clean = require('gulp-clean');
const bs = require('browser-sync');
const bsSpa = require('browser-sync-spa');
const proxy = require('http-proxy-middleware');
const sequence = require('run-sequence');

let sassScope = _.filter(cssStream, p => /\S+\.scss/i.test(p));
let cssScope = _.filter(cssStream, p => /\S+\.css/i.test(p));

// const APP_SERVE_PORT =  process.env.SERVE_PORT || 18081;
const SYNC_PROXY_PORT = process.env.SERVE_PORT || 8081;

gulp.task('serve:sync', () => {
  bs.init({
    startPath: '/',
    debugInfo: true,
    open: true,
    port: SYNC_PROXY_PORT,
    // watchOptions: {
    //   ignoreInitial: true,
    // },
    // proxy: {
    //   target: `localhost:${APP_SERVE_PORT}`,
    //   ws: true,
    // },
    snippetOptions: {
      rule: {
        match: /<\/head>/i,
        fn: (snippet, match) => `${snippet}${match}`
      }
    },
    // server: {
    //   baseDir: config.paths.src,
    //   // middleware: [
    //   //   proxy('/', {
    //   //     target: `http://localhost:${APP_SERVE_PORT}`,
    //   //     changeOrigin: true,
    //   //   })
    //   // ]
    // },
    ui: false,
  });
});

gulp.task('serve:sync:reload', () => {
  console.log('reload()');
  bs.reload({});
});

gulp.task('serve:watch', () => {

  const options = {
    name: 'serve',
    verbose: true,
    usePolling: true, // true for docker!
  };

  const watchScope = _.map(sassScope, (p) => {
    return `${path.dirname(p)}/*.scss`;
  });

  console.log('Watch scope: ', watchScope);

  gulp.watch(watchScope, options, () => {
    sequence('serve:sass', 'serve:sync:reload');
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
    .pipe( gulp.dest(path.join(config.paths.tmp, '/serve/assets/css')) )
    // .pipe( bs.stream(path.join(config.paths.tmp, '/serve/assets/css', '/main.css')) )
    .pipe( bs.stream() )
    ;
});

gulp.task('serve:clean', () => {
  return gulp.src(path.join(config.paths.tmp, '/serve/index.html'), {read: false})
    .pipe(clean());
});

gulp.task('serve:main', ['serve:sass', 'serve:watch'], () => {

  bs.use(bsSpa({selector: '[ng-app]'}));

  // const serverConfig = {
  //   path: path.join(config.paths.src),
  //   port: APP_SERVE_PORT,
  //   fallback: {'text/html' : '/index.html'},
  //   serveStaticConfig: {},
  //   middleware: [
  //     serveStatic('.'),
  //     serveStatic( path.join(config.paths.tmp, 'serve') ),
  //   ]
  // };
  //
  // const server = serverFactory.create(serverConfig);
  // server.start();

   bs.instance = bs({
     files: [path.join(config.paths.src, '/index.html')],
    startPath: '/',
    debugInfo: true,
    open: false,
    port: SYNC_PROXY_PORT,
    watchOptions: {
      ignoreInitial: false,
    },
    // proxy: {
    //   target: `localhost:${APP_SERVE_PORT}`,
    //   ws: true,
    // },
    snippetOptions: {
      rule: {
        match: /<\/head>/i,
        fn: function (snippet, match) {
          return snippet + match;
        }
      }
    },
    server: {
      baseDir: [config.paths.src, '.', path.join(config.paths.tmp, 'serve')],
      // middleware: [
      //   proxy('/', {
      //     target: `http://localhost:${APP_SERVE_PORT}`,
      //     changeOrigin: true,
      //   })
      // ]
    },
    ui: false,
  });

});

gulp.task('serve', ['serve:clean'], () => {
  gulp.start('serve:main');
});
