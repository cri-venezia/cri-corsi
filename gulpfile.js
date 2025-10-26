/* eslint-disable @typescript-eslint/no-var-requires */
/**
 * Gulpfile per compilare SCSS in CSS.
 */

// Importa i moduli necessari
const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass')); // Usa Dart Sass
const rename = require('gulp-rename'); // Per rinominare i file compilati se necessario

// Definisci i percorsi
const paths = {
    scss: 'assets/scss/**/*.scss', // Tutti i file .scss nella cartella scss e sottocartelle
    css: 'assets/css/' // Cartella di destinazione per i CSS compilati
};

// Task per compilare SCSS
function compileSass() {
    return gulp.src(paths.scss) // Prende tutti i file sorgente SCSS
        .pipe(sass({ outputStyle: 'compressed' }) // Compila in CSS compresso
            .on('error', sass.logError)) // Gestisce eventuali errori di compilazione
        .pipe(gulp.dest(paths.css)); // Salva i file CSS nella cartella di destinazione
}

// Task per monitorare le modifiche ai file SCSS
function watchSass() {
    gulp.watch(paths.scss, compileSass); // Esegue compileSass ogni volta che un file SCSS cambia
}

// Esporta i task per poterli eseguire da riga di comando (es. npx gulp sass)
exports.sass = compileSass;
exports.watch = watchSass;

// Task di default (eseguito con 'npx gulp') - Compila una volta
exports.default = compileSass;
