<?php
/**
 * File per la registrazione dei widget di Elementor.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Elementor;

// Importiamo le classi di Elementor necessarie
use Elementor\Elements_Manager;
use Elementor\Widgets_Manager;

// Importiamo le classi dei nostri widget specifici
use CRICorsi\Includes\Elementor\Widgets\CRI_Corsi_Widget;
use CRICorsi\Includes\Elementor\Widgets\CRI_Corso_Single_Widget;
use CRICorsi\Includes\Elementor\Widgets\CRI_Ultimi_Corsi_Widget; // <-- NUOVO

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
		// Assicurati che Elementor sia caricato prima di agganciare gli hook
		add_action( 'elementor/init', [ $this, 'register_hooks' ] );
	}

	/**
	 * Registra gli hook di Elementor.
	 * Viene eseguito sull'hook 'elementor/init'.
	 */
	public function register_hooks(): void {
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
				'icon'  => 'eicon-heart', // Icona a forma di cuore per la categoria
			]
		);
	}

	/**
	 * Include e registra i file dei widget.
	 *
	 * @param Widgets_Manager $widgets_manager
	 */
	public function register_widgets( Widgets_Manager $widgets_manager ): void {
		// Recuperiamo il path del plugin in modo sicuro
		$plugin_path = \CRICorsi\CRI_Corsi::instance()->get_plugin_path();

		// Widget Griglia Corsi
		require_once $plugin_path . 'src/Includes/Elementor/Widgets/CRI_Corsi_Widget.php';
		$widgets_manager->register( new CRI_Corsi_Widget() );

		// Widget Contenuto Singolo Corso
		require_once $plugin_path . 'src/Includes/Elementor/Widgets/CRI_Corso_Single_Widget.php';
		$widgets_manager->register( new CRI_Corso_Single_Widget() );

		// **NUOVO**: Widget Ultimi Corsi
		require_once $plugin_path . 'src/Includes/Elementor/Widgets/CRI_Ultimi_Corsi_Widget.php';
		$widgets_manager->register( new CRI_Ultimi_Corsi_Widget() );
	}
}

