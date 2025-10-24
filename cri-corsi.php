<?php
/**
 * Plugin Name: CRI Corsi
 * Plugin URI:  https://github.com/cri-venezia/cri-corsi
 * Description: Un plugin per la gestione avanzata e la prenotazione dei corsi della Croce Rossa Italiana, con integrazione Elementor e WooCommerce.
 * Version:     1.0.8
 * Author:      Luca
 * Author URI:  mailto:luca.forzutti@veneto.cri.it
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cri-corsi
 * Domain Path: /languages
 * Requires PHP: 8.2
 */

namespace CRICorsi;

// Impedisce l'accesso diretto al file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Carica l'autoloader di Composer.
if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	// Gestisce il caso in cui le dipendenze non siano installate.
	add_action( 'admin_notices', function() {
		echo '<div class="notice notice-error"><p>';
		// **CORREZIONE**: Aggiunto un backslash per "escapare" l'apostrofo in "l'installazione"
		echo esc_html__( 'Il plugin CRI Corsi richiede l\'installazione delle dipendenze di Composer. Esegui "composer install".', 'cri-corsi' );
		echo '</p></div>';
	});
	return;
}
require_once __DIR__ . '/vendor/autoload.php';

// Importiamo tutte le classi necessarie
use CRICorsi\Includes\Post_Types;
use CRICorsi\Includes\Meta_Boxes;
use CRICorsi\Includes\Form_Handler;
use CRICorsi\Includes\Admin_Columns;
use CRICorsi\Includes\Admin\Teacher_Panel;
use CRICorsi\Includes\Plugin_Updater;
use CRICorsi\Includes\Elementor\Elementor_Widgets;
use CRICorsi\Includes\User_Roles;

/**
 * Classe principale del plugin CRI Corsi.
 * Implementa il pattern Singleton per garantire un'unica istanza.
 */
final class CRI_Corsi {

	/**
	 * Versione corrente del plugin.
	 */
	private const VERSION = '1.0.8';

	/**
	 * URL della directory principale del plugin.
	 * @var string
	 */
	private string $plugin_url;

	/**
	 * Path della directory principale del plugin.
	 * @var string
	 */
	private string $plugin_path;

	/**
	 * Path del file principale del plugin.
	 * @var string
	 */
	private string $plugin_file;

	/**
	 * L'unica istanza della classe (Singleton).
	 * @var CRI_Corsi|null
	 */
	private static ?CRI_Corsi $instance = null;

	/**
	 * Restituisce l'unica istanza della classe.
	 *
	 * @return CRI_Corsi
	 */
	public static function instance(): CRI_Corsi {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Costruttore privato per prevenire istanze multiple (Singleton).
	 */
	private function __construct() {
		$this->define_constants();
		$this->setup_hooks();
		$this->init_plugin();
	}

	/**
	 * Definisce le costanti e le proprietà del plugin.
	 */
	private function define_constants(): void {
		$this->plugin_file = __FILE__;
		$this->plugin_path = plugin_dir_path( $this->plugin_file );
		$this->plugin_url = plugin_dir_url( $this->plugin_file );
	}

	/**
	 * Aggiunge gli hook di WordPress.
	 */
	private function setup_hooks(): void {
		// Hook per caricare script e stili
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );

		// Hook per la traduzione
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
	}

	/**
	 * Inizializza tutte le classi del plugin.
	 */
	private function init_plugin(): void {
		new Post_Types();
		new Meta_Boxes();
		new Form_Handler();
		new Admin_Columns();
		new Plugin_Updater();
		new Elementor_Widgets();
		new Teacher_Panel();
		new User_Roles();
	}

	/**
	 * Carica i file di traduzione (Text Domain).
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'cri-corsi',
			false,
			dirname( plugin_basename( $this->plugin_file ) ) . '/languages'
		);
	}

	/**
	 * Carica gli script e gli stili per il frontend.
	 */
	public function enqueue_frontend_scripts(): void {
		// Registra le librerie Leaflet (per le mappe)
		wp_register_style(
			'cri-corsi-leaflet-css',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
			[],
			'1.9.4'
		);
		wp_register_script(
			'cri-corsi-leaflet-js',
			'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
			[],
			'1.9.4',
			true
		);

		// Carica lo stile principale del plugin
		wp_enqueue_style(
			'cri-corsi-frontend',
			$this->plugin_url . 'assets/css/frontend.css',
			[ 'cri-corsi-leaflet-css' ], // Dipende da Leaflet CSS
			self::VERSION
		);

		// Carica lo script JS per le mappe (Refactored)
		wp_enqueue_script(
			'cri-corsi-maps-js',
			$this->plugin_url . 'assets/js/frontend-maps.js',
			[ 'cri-corsi-leaflet-js' ], // Dipende da Leaflet JS
			self::VERSION,
			true
		);
	}

	/**
	 * Getter per la versione del plugin.
	 * @return string
	 */
	public function get_version(): string {
		return self::VERSION;
	}

	/**
	 * Getter per l'URL del plugin.
	 * @return string
	 */
	public function get_plugin_url(): string {
		return $this->plugin_url;
	}

	/**
	 * Getter per il Path del plugin.
	 * @return string
	 */
	public function get_plugin_path(): string {
		return $this->plugin_path;
	}

	/**
	 * Getter per il file principale del plugin.
	 * @return string
	 */
	public function get_plugin_file(): string {
		return $this->plugin_file;
	}
}

// Avvia il plugin
CRI_Corsi::instance();

// **CORREZIONE**: Registra l'hook di attivazione qui, in modo statico e sicuro.
register_activation_hook( __FILE__, [ '\CRICorsi\Includes\User_Roles', 'add_custom_role' ] );
