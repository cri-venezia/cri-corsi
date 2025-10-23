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
	 * @param Elements_Manager $elements_manager
	 */
	public function register_widget_categories( Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			'cri-corsi',
			[
				'title' => esc_html__( 'CRI Corsi', 'cri-corsi' ),
				'icon'  => 'eicon-heart',
			]
		);
	}

	/**
	 * Include e registra i file dei widget.
	 *
	 * @param Widgets_Manager $widgets_manager
	 */
	public function register_widgets( Widgets_Manager $widgets_manager ): void {
		// Recuperiamo l'istanza principale del plugin per ottenere il path
		$main_plugin = \CRICorsi\CRI_Corsi::instance();

		// Includiamo il file del widget dal suo NUOVO percorso PSR-4
		$widget_file = $main_plugin->get_plugin_path() . 'src/Includes/Elementor/Widgets/CRI_Corsi_Widget.php';

		// Verifichiamo che il file esista prima di includerlo
		if ( file_exists( $widget_file ) ) {
			require_once $widget_file;

			// Registriamo il widget usando il suo NUOVO nome con namespace
			$widgets_manager->register( new \CRICorsi\Includes\Elementor\Widgets\CRI_Corsi_Widget() );
		}
	}
}
