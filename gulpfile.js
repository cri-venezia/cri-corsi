const gulp = require('gulp');
const gulpSass = require('gulp-sass');
const dartSass = require('sass'); // Usa il compilatore Dart Sass

// Imposta gulp-sass per usare Dart Sass
const sassCompiler = gulpSass(dartSass);

// Definisce i percorsi
const paths = {
    scss: {
        src: 'assets/scss/**/*.scss', // Monitora tutti i file .scss
        dest: 'assets/css/'
    }
};

// Task per compilare SCSS in CSS
function sassTask() {
    return gulp.src(paths.scss.src)
        .pipe(sassCompiler({ outputStyle: 'compressed' }).on('error', sassCompiler.logError)) // Compila in compresso e gestisce errori
        .pipe(gulp.dest(paths.scss.dest)); // Salva in assets/css/
}

// Task per monitorare le modifiche ai file SCSS
function watchTask() {
    gulp.watch(paths.scss.src, sassTask); // Riesegue sassTask quando un file .scss cambia
}

// Esporta i task per poterli eseguire da riga di comando (es. npx gulp sass)
exports.sass = sassTask;
exports.watch = watchTask;

// Task di default (eseguito con 'npx gulp') - compila soltanto
exports.default = sassTask;
