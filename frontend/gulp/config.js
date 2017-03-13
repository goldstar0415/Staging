'use strict';

const gulpUtil = require('gulp-util');

module.exports = {
  paths: {
    src: 'src',
    dist: 'dist',
    tmp: '.tmp',
    e2e: 'e2e',
    bower: 'bower_components',
  },
  errorHandler: function(msg) {
    return function(err) {
      gulpUtil.log(gulpUtil.colors.red(`[${msg}]`), err.toString());
      this.emit('end');
    };
  }
};
