<?php
/**
 * Gestisce la personalizzazione delle colonne admin per le Prenotazioni.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Admin_Columns
 */
class Admin_Columns {

	/**
	 * Costruttore. Aggancia i filtri per le colonne admin.
	 */
	public function __construct() {
		add_filter( 'manage_prenotazione_posts_columns', [ $this, 'add_custom_columns' ] );
		add_action( 'manage_prenotazione_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
	}

	/**
	 * Carica gli stili CSS per la bacheca.
	 *
	 * @param string $hook Pagina corrente della bacheca.
	 */
	public function enqueue_admin_styles( string $hook ): void {
		// Carica gli stili solo nelle pagine di modifica o elenco dei nostri CPT
		if ( 'post.php' === $hook || 'post-new.php' === $hook || 'edit.php' === $hook ) {
			$screen = get_current_screen();
			if ( $screen && in_array( $screen->post_type, [ 'cri_corso', 'prenotazione' ], true ) ) {

				$plugin_instance = \CRICorsi\CRI_Corsi::instance();

				wp_enqueue_style(
					'cri-corsi-admin',
					$plugin_instance->get_plugin_url() . 'assets/css/admin.css',
					[],
					$plugin_instance->get_version()
				);
			}
		}
	}

	/**
	 * Aggiunge nuove colonne alla tabella delle Prenotazioni.
	 *
	 * @param array $columns Colonne esistenti.
	 * @return array Colonne modificate.
	 */
	public function add_custom_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;
			if ( 'title' === $key ) {
				$new_columns['corso'] = esc_html__( 'Corso Prenotato', 'cri-corsi' );
				$new_columns['data_ora'] = esc_html__( 'Data e Durata', 'cri-corsi' ); // <-- MODIFICATO
				$new_columns['email'] = esc_html__( 'Email Partecipante', 'cri-corsi' );
			}
		}
		// Aggiungiamo la colonna stato alla fine
		$new_columns['stato'] = esc_html__( 'Stato', 'cri-corsi' );
		return $new_columns;
	}

	/**
	 * Renderizza il contenuto delle colonne personalizzate.
	 *
	 * @param string $column_name Nome della colonna corrente.
	 * @param int $post_id ID del post (prenotazione).
	 */
	public function render_custom_columns( string $column_name, int $post_id ): void {

		// Usiamo match() di PHP 8.0+
		match ( $column_name ) {
			'corso' => $this->render_corso_column( $post_id ),
			'data_ora' => $this->render_data_ora_column( $post_id ),
			'email' => $this->render_email_column( $post_id ),
			'stato' => $this->render_stato_column( $post_id ),
			default => null,
		};
	}

	/**
	 * Renderizza la colonna 'Corso'.
	 * @param int $post_id
	 */
	private function render_corso_column( int $post_id ): void {
		$corso_id = get_post_meta( $post_id, '_corso_id', true );
		if ( ! empty( $corso_id ) ) {
			$edit_link = get_edit_post_link( $corso_id );
			$title = get_the_title( $corso_id );
			echo '<a href="' . esc_url( $edit_link ) . '"><strong>' . esc_html( $title ) . '</strong></a>';
		} else {
			echo 'N/D';
		}
	}

	/**
	 * Renderizza la colonna 'Data e Durata'.
	 * @param int $post_id
	 */
	private function render_data_ora_column( int $post_id ): void {
		$corso_id = get_post_meta( $post_id, '_corso_id', true );
		$scelta_index = get_post_meta( $post_id, '_corso_scelta_index', true );

		if ( $corso_id && $scelta_index !== '' ) {
			$date_orari = get_post_meta( $corso_id, '_cri_course_date_orari', true );

			if ( is_array( $date_orari ) && isset( $date_orari[ $scelta_index ] ) ) {
				$dettagli = $date_orari[ $scelta_index ];
				$data_corso = $dettagli['data_corso'] ?? '';
				$durata = $dettagli['durata_corso'] ?? ''; // <-- MODIFICATO

				if ( $data_corso ) {
					printf(
						'%s<br>(%s)',
						esc_html( date_i18n( 'l d/m/Y', strtotime( $data_corso ) ) ),
						esc_html( $durata ) // <-- MODIFICATO
					);
				} else {
					echo 'N/D';
				}
			} else {
				echo 'Dati corso non trovati.';
			}
		} else {
			echo 'N/D';
		}
	}

	/**
	 * Renderizza la colonna 'Email'.
	 * @param int $post_id
	 */
	private function render_email_column( int $post_id ): void {
		$email = get_post_meta( $post_id, '_email', true );
		if ( ! empty( $email ) ) {
			echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
		} else {
			echo 'N/D';
		}
	}

	/**
	 * Renderizza la colonna 'Stato'.
	 * @param int $post_id
	 */
	private function render_stato_column( int $post_id ): void {
		$status = get_post_status( $post_id );

		$status_label = match( $status ) {
			'publish' => esc_html__( 'Confermato', 'cri-corsi' ),
			'wc-pending' => esc_html__( 'In attesa di Pagamento', 'cri-corsi' ),
			'draft' => esc_html__( 'Bozza', 'cri-corsi' ),
			default => esc_html( $status ),
		};

		$status_class = match( $status ) {
			'publish' => 'status-confirmed',
			'wc-pending' => 'status-pending',
			default => 'status-draft',
		};

		echo '<span class="cri-status-label ' . esc_attr( $status_class ) . '">' . esc_html( $status_label ) . '</span>';
	}
}

