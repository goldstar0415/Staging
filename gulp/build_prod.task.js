'use strict';

const path = require('path');
const gulp = require('gulp');
const config = require('./config');
const $ = require('gulp-load-plugins')();
const gitRevSync = require('git-rev-sync');
const injectString = require('gulp-inject-string');
const injectFile = require('gulp-inject-file');

gulp.task('_pre_build_prod', ['boot', 'vendor', 'app'], () => {
  return gulp.start('mirror');
});

gulp.task('build:prod', ['_pre_build_prod'], () => {

  const rev = gitRevSync.short();

  return gulp.src( path.join(config.paths.src, '/index.html') )
    .pipe( injectJs('/scripts/vendor.build.js') )
    .pipe( injectJs('/scripts/app.build.js') )
    .pipe( injectString.replace('__GULP_GIT_REVISION__', rev) )
    .pipe( injectFile({pattern: '<!--\\s*inject:<filename>-->.+<!--\\s*\\/injectfile\\s*-->'}) )
    .pipe( injectCss() )
    // .pipe( $.minifyHtml({
    //     empty: true,
    //     spare: true,
    //     quotes: true,
    //     // collapseWhitespace: true
    //   }) )
    .pipe( gulp.dest(config.paths.dist) );

  function injectJs(srcPath) {
    return $.inject(
      gulp.src([ path.join(config.paths.dist, srcPath) ], {read: false}),
      {starttag: `<!-- inject:${path.basename(srcPath, '.build.js')}:{{ext}} -->`, transform: transformJs}
    );
  }

  function injectCss() { //fixme
    return $.inject(
      gulp.src([ path.join(config.paths.dist, '/assets/css/styles.build.*') ], {read: false})
      // {starttag: `<!-- inject:{{ext}} -->`, /*transform: transformJs*/}
    );
  }

  function transformJs(filePath) {
    const url = filePath.replace(path.sep, '/').replace('/dist', '');
    return `<script src="${url}?${rev}"></script>`;
  }

});
