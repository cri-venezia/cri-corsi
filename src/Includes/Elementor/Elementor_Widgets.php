<?php
/**
 * File per la registrazione dei widget di Elementor.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Elementor;

use Elementor\Elements_Manager;
use Elementor\Widgets_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce la registrazione delle categorie e dei widget di Elementor.
 */
final class Elementor_Widgets {

	/**
	 * Costruttore. Aggancia le funzioni agli hook di Elementor.
	 */
	public function __construct() {
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_categories' ] );
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	/**
	 * Registra una nuova categoria di widget in Elementor.
	 *
	 * @param Elements_Manager $elements_manager Gestore degli elementi di Elementor.
	 */
	public function register_widget_categories( Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			'cri-corsi',
			[
				'title' => esc_html__( 'CRI Corsi', 'cri-corsi' ),
				'icon'  => 'eicon-heart', // Puoi scegliere un'icona appropriata
			]
		);
	}

	/**
	 * Include e registra i file dei widget.
	 *
	 * @param Widgets_Manager $widgets_manager Gestore dei widget di Elementor.
	 */
	public function register_widgets( Widgets_Manager $widgets_manager ): void {
		// Recupera il path base del plugin
		$plugin_path = \CRICorsi\CRI_Corsi::instance()->get_plugin_path();

		// **Widget Griglia Corsi (Esistente)**
		$widget_griglia_file = $plugin_path . 'src/Includes/Elementor/Widgets/CRI_Corsi_Widget.php';
		if ( file_exists( $widget_griglia_file ) ) {
			require_once $widget_griglia_file;
			$widgets_manager->register( new Widgets\CRI_Corsi_Widget() );
		} else {
			// Log o notifica se il file non viene trovato
			error_log('File widget CRI_Corsi_Widget non trovato: ' . $widget_griglia_file);
		}

		// **Widget Contenuto Singolo Corso (NUOVO)**
		$widget_singolo_file = $plugin_path . 'src/Includes/Elementor/Widgets/CRI_Corso_Single_Widget.php';
		if ( file_exists( $widget_singolo_file ) ) {
			require_once $widget_singolo_file;
			$widgets_manager->register( new Widgets\CRI_Corso_Single_Widget() );
		} else {
			// Log o notifica se il file non viene trovato
			error_log('File widget CRI_Corso_Single_Widget non trovato: ' . $widget_singolo_file);
		}
	}
}

