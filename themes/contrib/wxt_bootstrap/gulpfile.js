var gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('autoprefixer'),
    postcss = require('gulp-postcss'),
    sourcemaps = require('gulp-sourcemaps'),
    browserSync = require('browser-sync').create();

// Static Server + watching scss/html files
gulp.task('serve', ['sass'], function() {
    browserSync.init({
        proxy: "http://v2tbsproto.dd:8083/"
    });
    gulp.watch("sass/**/*.scss", ['sass']);
});

// Compile sass into CSS & auto-inject into browsers
gulp.task('sass', function() {
    return gulp.src("sass/**/*.scss")
        .pipe(sourcemaps.init())
        .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
        .pipe(postcss([autoprefixer()]))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest("./css/"))
        .pipe(browserSync.stream());
});

gulp.task('default', ['serve']);