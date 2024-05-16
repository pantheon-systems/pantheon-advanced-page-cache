import {deleteAsync} from 'del';
import gulp from 'gulp';
import csso from 'gulp-csso';
import sass from 'gulp-sass';

gulp.task('styles', () => {
  return gulp.src('assets/sass/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(csso())
    .pipe(gulp.dest('assets/css'));
});

gulp.task('clean', () => {
  return deleteAsync('assets/css');
});

gulp.task('build', gulp.series('clean', 'styles'));
gulp.task('watch', () => {
  gulp.watch('assets/sass/**/*.scss', gulp.series('clean', 'styles'));
});
