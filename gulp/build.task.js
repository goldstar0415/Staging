'use strict';

const path = require('path');
const gulp = require('gulp');
const config = require('./config');
const gitRevSync = require('git-rev-sync');
const injectString = require('gulp-inject-string');
const injectFile = require('gulp-inject-file');
const $ = require('gulp-load-plugins')({ pattern: ['gulp-*', 'main-bower-files'] });
const sequence = require('run-sequence').use(gulp);

gulp.task('build:fonts', function () {
  return gulp.src($.mainBowerFiles())
    .pipe($.filter('**/*.{eot,svg,ttf,woff,woff2}'))
    .pipe($.flatten())
    .pipe(gulp.dest(path.join(config.paths.dist, '/fonts/')));
});

gulp.task('build:service-worker', function () {
  return gulp.src([
      path.join(config.paths.src, '/service-worker.js'),
    ])
    .pipe(gulp.dest(config.paths.dist));
});

gulp.task('build:other', () => {
  const fileFilter = $.filter((file) => {
    return file.stat.isFile();
  });

  return gulp.src([
      path.join(config.paths.src, '/**/*'),
      path.join('!' + config.paths.src, '/**/*.{html,css,js,scss}')
    ])
    .pipe(fileFilter)
    .pipe(gulp.dest(path.join(config.paths.dist, '/')));
});

gulp.task('build:pre-build', ['build:fonts', 'build:service-worker', 'build:other']);

gulp.task('build:main', ['vendor', 'app', 'css', 'build:pre-build'], () => {
  const rev = gitRevSync.short();

  return gulp.src( path.join(config.paths.src, '/index.html') )
    .pipe( setRevision() )
    .pipe( boot() )
    .pipe( injectJs('/scripts/vendor.build.js') )
    .pipe( injectJs('/scripts/app.build.js') )
    .pipe( injectCss() )
    .pipe( minify() )
    .pipe( gulp.dest(config.paths.dist) );

  function setRevision() {
    return injectString.replace('__GULP_GIT_REVISION__', rev);
  }

  function boot() {
    return injectFile({pattern: '<!--\\s*inject:<filename>-->.+<!--\\s*\\/injectfile\\s*-->'});
  }

  function injectJs(srcPath) {
    return $.inject(
      gulp.src([ path.join(config.paths.dist, srcPath) ], {read: false}),
      {starttag: `<!-- inject:${path.basename(srcPath, '.build.js')}:{{ext}} -->`, transform: transformJs}
    );
  }

  function injectCss() {
    return $.inject(
      gulp.src([ path.join(config.paths.dist, '/assets/css/styles.build.*') ], {read: false}),
      {starttag: `<!-- inject:{{ext}} -->`, transform: transformCss}
    );
  }

  function transformJs(filePath) {
    const url = filePath.replace(path.sep, '/').replace('/dist', '');
    return `<script src="${url}?${rev}"></script>`;
  }

  function transformCss(filePath) {
    const url = filePath.replace(path.sep, '/').replace('/dist', '');
    return `<link rel="stylesheet" type="text/css" href="${url}?${rev}">`;
  }

  function minify() {
    return $.minifyHtml({
      empty: true,
      spare: true,
      quotes: true,
    });
  }

});

gulp.task('build', (cb) => {
  sequence('mirror', 'build:main', cb);
});
