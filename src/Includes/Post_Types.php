<?php
/**
 * Registra i Custom Post Types utilizzati dal plugin.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Post_Types
 * Gestisce la registrazione dei CPT 'cri_corso' e 'prenotazione'.
 */
class Post_Types {

	/**
	 * Costruttore. Aggancia le funzioni di registrazione all'hook 'init'.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_course_post_type' ] );
		add_action( 'init', [ $this, 'register_booking_post_type' ] );
	}

	/**
	 * Registra il Custom Post Type 'cri_corso'.
	 */
	public function register_course_post_type(): void {
		$labels = [
			'name'                  => _x( 'Corsi CRI', 'Post Type General Name', 'cri-corsi' ),
			'singular_name'         => _x( 'Corso CRI', 'Post Type Singular Name', 'cri-corsi' ),
			'menu_name'             => __( 'CRI Corsi', 'cri-corsi' ),
			'name_admin_bar'        => __( 'Corso CRI', 'cri-corsi' ),
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
			'search_items'          => __( 'Cerca Corsi', 'cri-corsi' ),
			'not_found'             => __( 'Nessun corso trovato', 'cri-corsi' ),
			'not_found_in_trash'    => __( 'Nessun corso trovato nel cestino', 'cri-corsi' ),
			'featured_image'        => __( 'Immagine in Evidenza', 'cri-corsi' ),
			'set_featured_image'    => __( 'Imposta immagine in evidenza', 'cri-corsi' ),
			'remove_featured_image' => __( 'Rimuovi immagine in evidenza', 'cri-corsi' ),
			'use_featured_image'    => __( 'Usa come immagine in evidenza', 'cri-corsi' ),
			'insert_into_item'      => __( 'Inserisci nel corso', 'cri-corsi' ),
			'uploaded_to_this_item' => __( 'Caricato per questo corso', 'cri-corsi' ),
			'items_list'            => __( 'Elenco corsi', 'cri-corsi' ),
			'items_list_navigation' => __( 'Navigazione elenco corsi', 'cri-corsi' ),
			'filter_items_list'     => __( 'Filtra elenco corsi', 'cri-corsi' ),
		];
		$args = [
			'label'                 => __( 'Corso CRI', 'cri-corsi' ),
			'description'           => __( 'Corsi di formazione della Croce Rossa Italiana', 'cri-corsi' ),
			'labels'                => $labels,
			'supports'              => [ 'title', 'editor', 'thumbnail', 'excerpt' ], // <-- AGGIUNTO 'excerpt'
			'taxonomies'            => [], // Puoi aggiungere categorie o tag se necessario
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-heart', // Icona a forma di cuore
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true, // Permette una pagina archivio tipo /corsi-cri/
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true, // Abilita l'API REST per questo CPT
			'rewrite'               => ['slug' => 'corsi-cri'], // URL personalizzato
		];
		register_post_type( 'cri_corso', $args );
	}

	/**
	 * Registra il Custom Post Type 'prenotazione'.
	 */
	public function register_booking_post_type(): void {
		$labels = [
			'name'               => _x( 'Prenotazioni', 'Post Type General Name', 'cri-corsi' ),
			'singular_name'      => _x( 'Prenotazione', 'Post Type Singular Name', 'cri-corsi' ),
			'menu_name'          => __( 'Prenotazioni', 'cri-corsi' ),
			'parent_item_colon'  => __( 'Prenotazione Genitore:', 'cri-corsi' ),
			'all_items'          => __( 'Tutte le Prenotazioni', 'cri-corsi' ),
			'view_item'          => __( 'Visualizza Prenotazione', 'cri-corsi' ),
			'add_new_item'       => __( 'Aggiungi Nuova Prenotazione', 'cri-corsi' ),
			'add_new'            => __( 'Aggiungi Nuova', 'cri-corsi' ),
			'edit_item'          => __( 'Modifica Prenotazione', 'cri-corsi' ),
			'update_item'        => __( 'Aggiorna Prenotazione', 'cri-corsi' ),
			'search_items'       => __( 'Cerca Prenotazioni', 'cri-corsi' ),
			'not_found'          => __( 'Nessuna prenotazione trovata', 'cri-corsi' ),
			'not_found_in_trash' => __( 'Nessuna prenotazione trovata nel cestino', 'cri-corsi' ),
		];
		$args = [
			'label'              => __( 'Prenotazione', 'cri-corsi' ),
			'description'        => __( 'Registra le prenotazioni ai corsi', 'cri-corsi' ),
			'labels'             => $labels,
			'supports'           => ['title'], // Supporta solo il titolo (generato automaticamente)
			'hierarchical'       => false,
			'public'             => false,  // Non visibile pubblicamente
			'show_ui'            => true,   // Visibile nella bacheca
			// Mostra come sottomenu di 'cri_corso'
			'show_in_menu'       => 'edit.php?post_type=cri_corso',
			'show_in_admin_bar'  => false,
			'show_in_nav_menus'  => false,
			'can_export'         => true,   // Permette l'esportazione
			'has_archive'        => false,
			'exclude_from_search'=> true,
			'publicly_queryable' => false,
			'capability_type'    => 'post',
			'capabilities'       => [ // Impedisce la creazione manuale dall'interfaccia
				'create_posts' => 'do_not_allow',
			],
			'map_meta_cap'       => true, // Necessario per il capability check di 'create_posts'
			'show_in_rest'       => false, // Non esporre via API REST
		];
		register_post_type( 'prenotazione', $args );
	}

}
