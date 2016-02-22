// Include gulp
var gulp = require('gulp');

 // Define base folders
var src = 'app/';

 // Include plugins
var concat = require('gulp-concat');
//var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var clean = require('less-plugin-clean-css');
var minifyCSS = require('gulp-minify-css');
var minifyHTML = require('gulp-minify-html');
var sourcemaps = require('gulp-sourcemaps');
var imagemin = require('gulp-imagemin');
var cache = require('gulp-cache');
var merge = require('merge-stream');
var debug = require('gulp-debug');
var order = require("gulp-order");
var logger = require('gulp-logger');
var gutil = require('gulp-util');
var less = require('gulp-less');
var path = require('path');
var ignore = require('gulp-ignore');
var plugins = require("gulp-load-plugins")({
	pattern: ['gulp-*', 'gulp.*', 'main-bower-files'],
	replaceString: /\bgulp[\-.]/
});

 // Define default destination folder
var dest = 'public_html/';

var htmlopts = {comments:true,spare:true};

gulp.task('scripts', function() {
  var jsFiles = [src + 'scripts/*.js', 'bower_components/jquery-form-validator/form-validator/jquery.form-validator.js', 'bower_components/jquery-form-validator/form-validator/security.js', 'bower_components/jquery.onoff/dist/jquery.onoff.min.js', 'bower_components/fancybox/source/jquery.fancybox.js', 'bower_components/simple-ajax-uploader/SimpleAjaxUploader.min.js'];
  return gulp.src(plugins.mainBowerFiles().concat(jsFiles))
    .pipe(plugins.filter('*.js'))
		.pipe(debug({title: 'JS Files:'}))
    .pipe(concat('main.js'))
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest(dest + 'js'));
});

gulp.task('less', function () {
  return gulp.src(plugins.mainBowerFiles())
		.pipe(plugins.filter('*.less'))
    .pipe(less({
      paths: [ path.join(__dirname, 'less', 'includes') ]
    }))
		.pipe(plugins.concat('less.css'))
		.pipe(minifyCSS({keepBreaks:true}))
		.pipe(gulp.dest(dest + 'css'));
});

gulp.task('css', function() {
  var cssFiles = [src + 'styles/*.css', 'bower_components/jquery.onoff/dist/jquery.onoff.css', 'bower_components/fancybox/source/jquery.fancybox.css'];
  return gulp.src(plugins.mainBowerFiles().concat(cssFiles))
		.pipe(plugins.filter('*.css'))
		.pipe(order([
      "app/styles/site.css",
      "*"
    ]))
		.pipe(plugins.concat('main.css'))
		.pipe(minifyCSS({keepBreaks:true}))
		.pipe(gulp.dest(dest + 'css'));
});

 gulp.task('images', function() {
  return gulp.src(src + 'assets/img/**/*')
    .pipe(cache(imagemin({ optimizationLevel: 6, progressive: true, interlaced: true })))
    .pipe(gulp.dest(dest + 'img'));
});

 // Default Task
gulp.task('default', ['scripts', 'less', 'css', 'images']);