<?php
/**
 * Gestore della compilazione SCSS.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

use Exception;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Exception\SassException;
use ScssPhp\ScssPhp\OutputStyle;

if ( ! defined( 'ABSPATH' ) ) {
	// Se non siamo in WordPress, definiamo ABSPATH per lo script CLI
	// Questa definizione è usata dal file bin/compile-scss.php
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

/**
 * Classe SCSSCompiler
 *
 * Gestisce la compilazione dei file SCSS in CSS utilizzando scssphp.
 */
class SCSSCompiler {

	/**
	 * @var Compiler
	 */
	private Compiler $compiler;

	/**
	 * @var string
	 */
	private string $plugin_dir;

	/**
	 * @var string
	 */
	private string $scss_dir;

	/**
	 * @var string
	 */
	private string $css_dir;

	/**
	 * Costruttore.
	 *
	 * @param string $plugin_dir Path alla directory principale del plugin.
	 * @throws Exception Se le directory non sono scrivibili.
	 */
	public function __construct(string $plugin_dir) {
		$this->plugin_dir = $plugin_dir;
		$this->scss_dir   = $this->plugin_dir . '/assets/scss/';
		$this->css_dir    = $this->plugin_dir . '/assets/css/';

		if ( ! is_dir( $this->css_dir ) ) {
			if ( ! mkdir( $this->css_dir, 0755, true ) ) {
				throw new Exception( "Impossibile creare la directory CSS '{$this->css_dir}'." );
			}
		}

		if ( ! is_writable( $this->css_dir ) ) {
			throw new Exception( "La directory CSS '{$this->css_dir}' non è scrivibile." );
		}

		$this->compiler = new Compiler();
		// Aggiungiamo il path di importazione per SCSS
		$this->compiler->addImportPath( $this->scss_dir );
		// Impostiamo uno stile compresso per il CSS finale
		$this->compiler->setOutputStyle( OutputStyle::COMPRESSED );
	}

	/**
	 * Compila un singolo file SCSS.
	 *
	 * @param string $filename_no_ext Nome del file senza estensione (es. 'frontend').
	 *
	 * @throws Exception|SassException Se i file non esistono o non sono leggibili/scrivibili.
	 */
	public function compile(string $filename_no_ext): void {
		$scss_file = $this->scss_dir . $filename_no_ext . '.scss';
		$css_file  = $this->css_dir . $filename_no_ext . '.css';

		if ( ! file_exists( $scss_file ) || ! is_readable( $scss_file ) ) {
			throw new Exception( "File SCSS '{$scss_file}' non trovato o non leggibile." );
		}

		try {
			$scss_content = file_get_contents( $scss_file );
			if ( $scss_content === false ) {
				throw new Exception( "Impossibile leggere il contenuto di '{$scss_file}'." );
			}

			$css_content = $this->compiler->compileString( $scss_content )->getCss();

			if ( file_put_contents( $css_file, $css_content ) === false ) {
				throw new Exception( "Impossibile scrivere nel file CSS '{$css_file}'." );
			}

		} catch ( Exception $e ) {
			// Rilancia l'eccezione del compilatore per un debug più chiaro
			throw new Exception( "Errore del compilatore SCSS: " . $e->getMessage() );
		}
	}
}

