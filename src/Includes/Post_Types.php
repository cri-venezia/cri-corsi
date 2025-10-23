<?php
/**
 * File per la registrazione dei Custom Post Types (CPT) del plugin.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Gestisce la registrazione dei tipi di post personalizzati 'cri_corso' e 'prenotazione'.
 */
final class Post_Types {

	/**
	 * Costruttore.
	 * Aggancia le funzioni di registrazione all'hook 'init' di WordPress.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_course_cpt' ], 0 );
		add_action( 'init', [ $this, 'register_booking_cpt' ], 0 );
	}

	/**
	 * Registra il Custom Post Type "Corso" (cri_corso).
	 */
	public function register_course_cpt(): void {
		$labels = [
			'name'                  => _x( 'Corsi', 'Post Type General Name', 'cri-corsi' ),
			'singular_name'         => _x( 'Corso', 'Post Type Singular Name', 'cri-corsi' ),
			'menu_name'             => __( 'CRI Corsi', 'cri-corsi' ),
			'name_admin_bar'        => __( 'Corso', 'cri-corsi' ),
			'archives'              => __( 'Archivio Corsi', 'cri-corsi' ),
			'attributes'            => __( 'Attributi Corso', 'cri-corsi' ),
			'parent_item_colon'     => __( 'Corso Genitore:', 'cri-corsi' ),
			'all_items'             => __( 'Tutti i Corsi', 'cri-corsi' ),
			'add_new_item'          => __( 'Aggiungi Nuovo Corso', 'cri-corsi' ),
			'add_new'               => __( 'Aggiungi Nuovo', 'cri-corsi' ),
			'new_item'              => __( 'Nuovo Corso', 'cri-corsi' ),
			'edit_item'             => __( 'Modifica Corso', 'cri-corsi' ),
			'update_item'           => __( 'Aggiorna Corso', 'cri-corsi' ),
			'view_item'             => __( 'Visualizza Corso', 'cri-corsi' ),
			'view_items'            => __( 'Visualizza Corsi', 'cri-corsi' ),
			'search_items'          => __( 'Cerca Corso', 'cri-corsi' ),
			'not_found'             => __( 'Nessun corso trovato', 'cri-corsi' ),
			'not_found_in_trash'    => __( 'Nessun corso trovato nel cestino', 'cri-corsi' ),
			'featured_image'        => __( 'Immagine Copertina Corso', 'cri-corsi' ),
			'set_featured_image'    => __( 'Imposta immagine copertina', 'cri-corsi' ),
			'remove_featured_image' => __( 'Rimuovi immagine copertina', 'cri-corsi' ),
			'use_featured_image'    => __( 'Usa come immagine copertina', 'cri-corsi' ),
			'insert_into_item'      => __( 'Inserisci nel corso', 'cri-corsi' ),
			'uploaded_to_this_item' => __( 'Caricato per questo corso', 'cri-corsi' ),
			'items_list'            => __( 'Elenco corsi', 'cri-corsi' ),
			'items_list_navigation' => __( 'Navigazione elenco corsi', 'cri-corsi' ),
			'filter_items_list'     => __( 'Filtra elenco corsi', 'cri-corsi' ),
		];
		$args   = [
			'label'               => __( 'Corso', 'cri-corsi' ),
			'description'         => __( 'Post Type per i corsi CRI', 'cri-corsi' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'custom-fields' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-welcome-learn-more',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => 'corsi-cri',
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'show_in_rest'        => true,
			'rewrite'             => [ 'slug' => 'corsi-cri', 'with_front' => false ],
		];
		register_post_type( 'cri_corso', $args );
	}

	/**
	 * Registra il Custom Post Type "Prenotazione" (prenotazione).
	 */
	public function register_booking_cpt(): void {
		$labels = [
			'name'               => _x( 'Prenotazioni', 'Post Type General Name', 'cri-corsi' ),
			'singular_name'      => _x( 'Prenotazione', 'Post Type Singular Name', 'cri-corsi' ),
			'menu_name'          => __( 'Prenotazioni', 'cri-corsi' ),
			'all_items'          => __( 'Tutte le Prenotazioni', 'cri-corsi' ),
			'add_new_item'       => __( 'Aggiungi Nuova Prenotazione', 'cri-corsi' ),
			'add_new'            => __( 'Aggiungi Nuova', 'cri-corsi' ),
			'new_item'           => __( 'Nuova Prenotazione', 'cri-corsi' ),
			'edit_item'          => __( 'Modifica Prenotazione', 'cri-corsi' ),
			'view_item'          => __( 'Visualizza Prenotazione', 'cri-corsi' ),
			'search_items'       => __( 'Cerca Prenotazione', 'cri-corsi' ),
			'not_found'          => __( 'Nessuna prenotazione trovata', 'cri-corsi' ),
			'not_found_in_trash' => __( 'Nessuna prenotazione nel cestino', 'cri-corsi' ),
		];
		$args   = [
			'label'               => __( 'Prenotazione', 'cri-corsi' ),
			'description'         => __( 'Archivia le prenotazioni dei corsi', 'cri-corsi' ),
			'labels'              => $labels,
			'supports'            => [ 'title', 'custom-fields' ],
			'hierarchical'        => false,
			'public'              => false, // Non visibile pubblicamente
			'show_ui'             => true,  // Visibile in bacheca
			'show_in_menu'        => 'edit.php?post_type=cri_corso', // Sottomenu di "CRI Corsi"
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'show_in_rest'        => false, // Non esposto via API REST
		];
		register_post_type( 'prenotazione', $args );
	}
}
