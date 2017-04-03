'use strict';
const gulp = require('gulp');
const config = require('./config');
const imagemin = require('gulp-imagemin');
const path = require('path');

gulp.task('imagemin', () => {
    return gulp
        .src(path.join(config.paths.dist, '**/*.{png,jpg,jpeg}'))
        .pipe(imagemin([
            imagemin.optipng({optimizationLevel: 5}),
            imagemin.jpegtran({progressive: true}),
            // imagemin.svgo({plugins: [{removeViewBox: true}]})
        ], {
            verbose: true
        }))
        .pipe(gulp.dest(config.paths.dist));
});
