'use strict';
const gulp = require('gulp');
const config = require('./config');
const imagemin = require('gulp-imagemin');
const path = require('path');
const _ = require('lodash');
const multiProcess = require('gulp-multi-process');

const tasks = parallelize();

gulp.task('imagemin', (cb) => {
    return multiProcess(tasks, cb);
});

function parallelize() {
    const tasks = [];
    let threadNum = 0;
    const parallelJobs = {
        byExtension: ['png', 'jpg', 'jpeg'],
        byPath: [
            ['assets', 'css'], ['assets', 'fonts'], ['assets', 'img'], ['assets', 'libs'], ['assets', 'sass'],
            'bower_components',
            'fonts',
        ],
    };

    _.each(parallelJobs.byPath, (relativePath) => {
        _.each(parallelJobs.byExtension, (ext) => {
            (
                (function () {
                    const relativePath = this.context.relativePath;
                    const ext = this.context.ext;
                    const taskName = `parallel-imagemin-thread-${threadNum++}-${_.isArray(relativePath) ? relativePath.join('_') : relativePath}-${ext}`;
                    gulp.task(taskName, () => {
                        return worker(relativePath, ext);
                    });
                    tasks.push(taskName);

                }).bind({context: {relativePath, ext}})
            )();
        });
    });

    return tasks;
}

function worker(relativePath, ext) {

    if (_.isArray(relativePath)) {
        relativePath = path.join.apply(null, relativePath);
    }

    const pattern = buildSearchPattern(relativePath, ext);
    console.log(`> search pattern: ${pattern}`);

    return gulp
        .src(pattern)
        .pipe(
            imagemin(
                [
                    imagemin.optipng({optimizationLevel: 5}),
                    imagemin.jpegtran({progressive: true}),
                    // imagemin.svgo({plugins: [{removeViewBox: true}]})
                ],
                {verbose: true}
            )
        )
        .pipe(
            gulp.dest(
                path.join(config.paths.dist, relativePath)
            )
        );
}

function buildSearchPattern(relativePath, ext) {
    return path.join(config.paths.dist, relativePath, `**/*.${ext}`);
}
