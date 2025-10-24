<?php
/**
 * Gestisce la creazione e le autorizzazioni del ruolo utente "Docente".
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe User_Roles
 * Aggiunge il ruolo 'cri_docente' e ne pulisce la bacheca.
 */
class User_Roles {

	/**
	 * ID del ruolo personalizzato.
	 * @var string
	 */
	public const ROLE_ID = 'cri_docente';

	/**
	 * Nuova capability personalizzata per il pannello docente.
	 * @var string
	 */
	public const CAPABILITY = 'manage_cri_course_dates';


	/**
	 * Costruttore.
	 */
	public function __construct() {
		// NOTA: L'hook di attivazione è stato spostato nel file principale (cri-corsi.php) per evitare loop.

		// **NUOVA LOGICA**: Aggiungi gli hook solo se necessario
		add_action( 'init', [ $this, 'conditionally_add_hooks' ] );
	}

	/**
	 * Aggiunge gli hook di admin solo per gli utenti "Docente" (e non admin).
	 * Questo previene i loop di re-indirizzamento per gli amministratori.
	 */
	public function conditionally_add_hooks(): void {
		// Non fare nulla se l'utente non è loggato
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user = wp_get_current_user();

		// Aggiungi gli hook solo se l'utente ha il ruolo 'docente' E NON è un amministratore.
		if ( in_array( self::ROLE_ID, $user->roles, true ) && ! user_can( $user, 'manage_options' ) ) {
			add_action( 'admin_init', [ $this, 'redirect_non_admin_docente' ] );
			add_action( 'admin_menu', [ $this, 'cleanup_admin_menu' ], 999 );
		}
	}

	/**
	 * Aggiunge il ruolo personalizzato "Docente" al database di WordPress.
	 * Si esegue solo all'attivazione del plugin.
	 * METODO STATICO CHIAMATO DA cri-corsi.php
	 */
	public static function add_custom_role(): void {
		// Aggiungiamo il ruolo solo se non esiste già
		if ( get_role( self::ROLE_ID ) ) {
			return;
		}

		// Diamo al docente le capacità di base di un Sottoscrittore
		$subscriber_caps = get_role( 'subscriber' )->capabilities;
		$teacher_caps = array_merge(
			$subscriber_caps,
			[
				self::CAPABILITY => true, // La nostra capability personalizzata
				'read' => true,
			]
		);

		add_role(
			self::ROLE_ID,
			esc_html__( 'Docente CRI', 'cri-corsi' ),
			$teacher_caps
		);

		// Aggiungiamo la capability anche agli amministratori, così possono vedere il pannello
		$admin_role = get_role( 'administrator' );
		if ($admin_role) {
			$admin_role->add_cap( self::CAPABILITY );
		}
	}

	/**
	 * Pulisce la bacheca per il ruolo "Docente".
	 * Questa funzione ora viene chiamata solo per i docenti (non admin).
	 */
	public function cleanup_admin_menu(): void {
		// Il controllo sull'utente è già stato fatto in conditionally_add_hooks()

		global $menu, $submenu;

		// Lista dei menu da mantenere (slug)
		$allowed_menus = [
			'index.php',             // Bacheca
			'cri-gestione-date',     // Il nostro pannello docente
			'profile.php',           // Profilo utente
		];

		foreach ( $menu as $key => $menu_item ) {
			$slug = $menu_item[2];
			// Rimuovi se non è nella lista permessi
			if ( ! in_array( $slug, $allowed_menus, true ) && $slug !== 'separator1' && $slug !== 'separator2' && $slug !== 'separator-last' ) {
				remove_menu_page( $slug );
			}
		}

		// Rimuovi sottomenu specifici (es. "Aggiornamenti" e "Home" dalla Bacheca)
		if (isset($submenu['index.php'])) {
			foreach ($submenu['index.php'] as $key => $item) {
				if ($item[2] === 'update-core.php' || $item[2] === 'index.php') {
					unset($submenu['index.php'][$key]);
				}
			}
		}
	}

	/**
	 * Impedisce ai docenti di accedere ad altre pagine admin
	 * se provano a raggiungerle via URL diretto.
	 * Questa funzione ora viene chiamata solo per i docenti (non admin).
	 */
	public function redirect_non_admin_docente(): void {
		global $pagenow;

		// Sappiamo già che l'utente è un docente e non un admin.
		// Dobbiamo solo fermare i redirect durante AJAX, CRON o POST.
		if ( wp_doing_ajax() || wp_doing_cron() || $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			return;
		}

		$allowed_pages = [
			'index.php',        // Bacheca
			'profile.php',      // Profilo
			'admin.php',        // Necessario per le nostre pagine custom
		];

		// Se la pagina non è consentita...
		if ( ! in_array( $pagenow, $allowed_pages, true ) ) {
			wp_redirect( admin_url( 'index.php' ) );
			exit;
		}

		// Check specifico per admin.php (che è generico)
		// Permetti solo se è la nostra pagina 'cri-gestione-date'
		if ( $pagenow === 'admin.php' && ( ! isset($_GET['page']) || $_GET['page'] !== 'cri-gestione-date' ) ) {
			wp_redirect( admin_url( 'index.php' ) );
			exit;
		}
	}
}

