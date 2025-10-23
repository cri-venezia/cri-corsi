#!/usr/bin/env php
<?php

// Questo script viene eseguito da riga di comando per compilare i file SCSS.
echo "Avvio compilatore SCSS..." . PHP_EOL;

// Bootstrap Composer's autoloader
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
	echo "Errore: Eseguire 'composer install' per installare le dipendenze." . PHP_EOL;
	exit(1);
}
require_once $autoloader;

// Importiamo il nostro compilatore
use CRICorsi\Includes\SCSSCompiler;

$plugin_dir = dirname(__DIR__); // La directory principale del plugin
$compiler = null;

try {
	$compiler = new SCSSCompiler($plugin_dir);
} catch (\Exception $e) {
	echo "Errore nell'inizializzazione del compilatore: " . $e->getMessage() . PHP_EOL;
	exit(1);
}

// Lista dei file da compilare
$files_to_compile = [
	'frontend',
	'admin',
];

$errors = false;

foreach ($files_to_compile as $file) {
	echo "Compilazione di {$file}.scss..." . PHP_EOL;
	try {
		$compiler->compile($file);
		echo " -> {$file}.css compilato con successo!" . PHP_EOL;
	} catch (\Exception $e) {
		echo " ERRORE durante la compilazione di {$file}.scss: " . $e->getMessage() . PHP_EOL;
		$errors = true;
	}
}

if ($errors) {
	echo PHP_EOL . "Compilazione terminata con errori." . PHP_EOL;
	exit(1);
}

echo PHP_EOL . "Compilazione SCSS completata con successo!" . PHP_EOL;
exit(0);
