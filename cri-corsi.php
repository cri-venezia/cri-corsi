<?php
/**
 * Plugin Name: CRI Corsi
 * Plugin URI:  https://github.com/cri-venezia/cri-corsi
 * Description: Un plugin per la gestione avanzata e la prenotazione dei corsi della Croce Rossa Italiana, con integrazione Elementor e WooCommerce.
 * Version:     1.1.0
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
use CRICorsi\Includes\Admin\Admin_Columns; // <-- MODIFICATO: Aggiunto \Admin
use CRICorsi\Includes\Admin\Teacher_Panel;
use CRICorsi\Includes\Admin\Admin_Footer;
use CRICorsi\Includes\Admin\Admin_Utilities;
use CRICorsi\Includes\Plugin_Updater;
use CRICorsi\Includes\Elementor\Elementor_Widgets;
use CRICorsi\Includes\User_Roles;
use CRICorsi\Includes\WooCommerce_Integration;

/**
 * Classe principale del plugin CRI Corsi.
 * Implementa il pattern Singleton per garantire un'unica istanza.
 */
final class CRI_Corsi {

    /**
     * Versione corrente del plugin.
     */
    private const VERSION = '1.1.0';

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

        // Hook per aggiungere link ai meta dati del plugin
        add_filter( 'plugin_row_meta', [ $this, 'add_plugin_row_meta' ], 10, 2 );
    }

    /**
     * Inizializza tutte le classi del plugin.
     */
    private function init_plugin(): void {
        // Classi sicure (definizioni di dati)
        new Post_Types();
        new Meta_Boxes();

        // Classi che si agganciano al frontend e/o admin-post
        new Form_Handler();
        new Elementor_Widgets(); // Registra widget

        // Classi che funzionano solo nell'area admin
        if ( is_admin() ) {
            new Admin_Columns(); // Non serve cambiare qui grazie all'istruzione 'use' aggiornata
            new Teacher_Panel();
            new Admin_Footer();
            new User_Roles();
        }

        // Carica l'integrazione WC solo se WooCommerce è attivo
        if ( class_exists( 'WooCommerce' ) ) {
            new WooCommerce_Integration();
            new Admin_Utilities(); 
        }

        // Classe che causava il loop (lasciamo commentata per ora)
        // new Plugin_Updater();
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
     * Carica gli script e gli stili per il frontend in modo condizionale.
     */
    public function enqueue_frontend_scripts(): void {

        // Carica CSS per i widget Elementor solo sul frontend se Elementor è attivo
        if ( ! is_admin() && did_action( 'elementor/loaded' ) ) {
             // CSS per la Griglia
             wp_enqueue_style(
                 'cri-corsi-grid-widget',
                 $this->plugin_url . 'assets/css/grid-widget.css',
                 [],
                 self::VERSION
             );
             // CSS per Ultimi Corsi
              wp_enqueue_style(
                 'cri-corsi-latest-courses',
                 $this->plugin_url . 'assets/css/latest-courses.css',
                 [],
                 self::VERSION
             );
        }

        // Carica CSS e JS per la pagina singola del corso e la mappa
        if ( is_singular('cri_corso') ) {
            // Registra e Accoda le librerie Leaflet
            wp_enqueue_style(
                'cri-corsi-leaflet-css',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                [],
                '1.9.4'
            );
            wp_enqueue_script(
                'cri-corsi-leaflet-js',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                [],
                '1.9.4',
                true // Carica nel footer
            );

            // Carica lo stile per la pagina singola (dipende da Leaflet CSS)
            wp_enqueue_style(
                'cri-corsi-single-course',
                $this->plugin_url . 'assets/css/single-course.css',
                [ 'cri-corsi-leaflet-css' ],
                self::VERSION
            );

            // Carica lo script JS per le mappe (dipende da Leaflet JS)
            wp_enqueue_script(
                'cri-corsi-maps-js',
                $this->plugin_url . 'assets/js/frontend-maps.js',
                [ 'cri-corsi-leaflet-js' ],
                self::VERSION,
                true // Carica nel footer
            );
        }
    }


    /**
     * Aggiunge un link alla documentazione nei meta dati del plugin (accanto a Versione, Autore).
     *
     * @param array  $plugin_meta Array di link meta esistenti.
     * @param string $plugin_file Path del file del plugin.
     * @return array Array modificato con il nuovo link.
     */
    public function add_plugin_row_meta( array $plugin_meta, string $plugin_file ): array {
        // Aggiungi il link solo per il nostro plugin
        if ( plugin_basename( $this->plugin_file ) === $plugin_file ) {
            $doc_link = '<a href="https://docs.crivenezia.it" target="_blank">' . esc_html__( 'Documentazione', 'cri-corsi' ) . '</a>';
            $plugin_meta[] = $doc_link; // Aggiunge il link alla fine dell'array
        }
        return $plugin_meta;
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

// Registra l'hook di attivazione per creare il ruolo utente.
register_activation_hook( __FILE__, [ '\CRICorsi\Includes\User_Roles', 'add_custom_role' ] );
