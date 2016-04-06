//***********************************************
// SETTINGS
//***********************************************

// File paths
var root = 'ee3/vz_url/';

// Options for AutoPrefixer
var autoprefixerOpts = [
    'last 2 versions',
    '> 1%',
    'ie >= 8'
];

//***********************************************
// SET UP GULP
//***********************************************

// Include Gulp & Tools We'll Use
var gulp = require('gulp');


//***********************************************
// STYLES
//***********************************************

var autoprefixer = require('gulp-autoprefixer');
var minify = require('gulp-minify-css');
var sass = require('gulp-sass');

gulp.task('styles', function() {
    return gulp.src('src/*.scss')
        .pipe(sass())
        .pipe(autoprefixer({ browsers: autoprefixerOpts, sourceMap: false }))
        .pipe(minify({compatibility: 'ie8'}))
        .pipe(gulp.dest(root+'css'));
});


//***********************************************
// JAVASCRIPT
//***********************************************

var babel = require('gulp-babel');
var eslint = require('gulp-eslint');
var uglify = require('gulp-uglify');

gulp.task('scripts', function() {
    return gulp.src('src/*.js')
        .pipe(babel())
        .pipe(uglify())
        .pipe(gulp.dest(root+'javascript'));
});


//***********************************************
// WATCH FOR CHANGES
//***********************************************

// Watch Files For Changes & Reload
gulp.task('watch', function() {
    gulp.watch(['src/*.scss'], ['styles']);
    gulp.watch(['src/*.js'], ['scripts']);
});


//***********************************************
// DEFAULT
//***********************************************

// Default Task
gulp.task('default', function() {
    gulp.start('styles', 'scripts');
});
