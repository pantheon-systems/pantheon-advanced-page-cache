import { deleteAsync } from 'del';
import gulp from 'gulp';
import csso from 'gulp-csso';
import * as sassCompiler from 'sass';
import gulpSass from 'gulp-sass';
import rename from 'gulp-rename'

const sass = gulpSass(sassCompiler);

gulp.task('styles', () => {
  return gulp.src('assets/sass/styles.scss') // Only compile the main file
    .pipe(sass().on('error', sass.logError)) // Compile SASS to CSS
	.pipe(gulp.dest('assets/css')) // Save unminified CSS
    .pipe(csso()) // Minify CSS
	.pipe(rename({suffix: '.min'})) // Rename to styles.min.css
    .pipe(gulp.dest('assets/css')); // Save the minified CSS file
});

gulp.task('clean', () => {
  return deleteAsync('assets/css');
});

gulp.task('build', gulp.series('clean', 'styles'));
gulp.task('watch', () => {
  gulp.watch('assets/sass/**/*.scss', gulp.series('clean', 'styles'));
});
