<?php
/**
 * File per la gestione dell'auto-aggiornamento del plugin da GitHub.
 * Utilizza la libreria Plugin Update Checker di Yahnis Elsts.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

// Importiamo la factory della v5, come da tua correzione
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce l'auto-aggiornamento del plugin tramite un repository GitHub.
 */
final class Plugin_Updater {

	/**
	 * Costruttore.
	 * Inizializza il checker per gli aggiornamenti.
	 */
	public function __construct() {
		// Verifica che la classe v5 sia stata caricata da Composer
		if ( ! class_exists( PucFactory::class ) ) {
			return;
		}

		// Recuperiamo l'istanza principale del plugin
		$main_plugin = \CRICorsi\CRI_Corsi::instance();

		// Il file principale del plugin (es. /.../wp-content/plugins/cri-corsi/cri-corsi.php)
		$plugin_file = $main_plugin->get_plugin_path() . 'cri-corsi.php';

		// URL del repository GitHub
		$repository_url = 'https://github.com/cri-venezia/cri-corsi/';

		// Inizializza il checker usando la factory v5
		$myUpdateChecker = PucFactory::buildUpdateChecker(
			$repository_url,
			$plugin_file,
			'cri-corsi' // Slug del plugin
		);

		// (Opcionale) Imposta il branch da cui controllare gli aggiornamenti
		// Sostituisci 'main' con 'master' se il tuo branch principale si chiama così.
		$myUpdateChecker->setBranch( 'main' );

		// (Opcionale) Se il repository è privato, aggiungi un token di accesso
		// $myUpdateChecker->setAuthentication('IL_TUO_TOKEN_GITHUB');
	}
}

