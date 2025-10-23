<?php
/**
 * File per la gestione dell'invio del form di prenotazione.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

use WP_Error;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gestisce la validazione e il salvataggio dei dati del form di prenotazione.
 */
final class Form_Handler {

	/**
	 * Costruttore. Aggancia la funzione di gestione all'endpoint di WordPress.
	 */
	public function __construct() {
		// Questi hook intercettano le richieste POST inviate a admin-post.php
		// 'nopriv' è per gli utenti non loggati, l'altro per quelli loggati.
		add_action( 'admin_post_nopriv_cri_prenota_corso', [ $this, 'handle_form_submission' ] );
		add_action( 'admin_post_cri_prenota_corso', [ $this, 'handle_form_submission' ] );

		// Hook di WooCommerce per aggiornare la prenotazione dopo il pagamento
		add_action( 'woocommerce_order_status_completed', [ $this, 'handle_completed_payment' ] );
		add_action( 'woocommerce_order_status_processing', [ $this, 'handle_completed_payment' ] );
	}

	/**
	 * Gestisce l'intero processo di invio del form.
	 */
	public function handle_form_submission(): void {
		// 1. Verifica di sicurezza: Nonce
		if ( ! isset( $_POST['cri_corso_nonce'] ) || ! wp_verify_nonce( $_POST['cri_corso_nonce'], 'cri_corso_booking_form' ) ) {
			wp_die( esc_html__( 'Errore di sicurezza. Per favore, riprova.', 'cri-corsi' ) );
		}

		// 2. Sanificazione e Validazione dei dati
		$required_fields = [
			'corso_id'     => 'absint',
			'corso_scelta' => 'sanitize_text_field',
			'nome'         => 'sanitize_text_field',
			'cognome'      => 'sanitize_text_field',
			'email'        => 'sanitize_email',
		];

		$corso_id = absint( $_POST['corso_id'] ?? 0 );
		$destinazione = get_post_meta( $corso_id, '_cri_course_destinazione', true );

		if ( 'aziende' === $destinazione ) {
			$required_fields['ragione_sociale'] = 'sanitize_text_field';
			$required_fields['partita_iva'] = 'sanitize_text_field';
		}

		$errors = [];
		$sanitized_data = [];

		foreach ( $required_fields as $field => $sanitization_function ) {
			if ( empty( $_POST[ $field ] ) ) {
				$errors[] = sprintf( esc_html__( 'Il campo %s è obbligatorio.', 'cri-corsi' ), $field );
			} else {
				$sanitized_data[ $field ] = call_user_func( $sanitization_function, $_POST[ $field ] );
			}
		}

		if ( ! is_email( $sanitized_data['email'] ) ) {
			$errors[] = esc_html__( 'L\'indirizzo email non è valido.', 'cri-corsi' );
		}

		if ( ! empty( $errors ) ) {
			wp_die( 'Si sono verificati degli errori: <br>' . implode( '<br>', $errors ) );
		}

		// 3. Verifica disponibilità posti (sicurezza lato server)
		$scelta_index = $sanitized_data['corso_scelta'];
		$date_orari = get_post_meta( $corso_id, '_cri_course_date_orari', true );

		if ( ! is_array( $date_orari ) || ! isset( $date_orari[ $scelta_index ] ) ) {
			wp_die( esc_html__( 'La data selezionata non è valida.', 'cri-corsi' ) );
		}

		$dettagli_scelta = $date_orari[ $scelta_index ];
		$posti_disponibili = (int) ( $dettagli_scelta['posti_disponibili'] ?? 0 );
		$posti_occupati = $this->count_booked_seats( $corso_id, $scelta_index );

		if ( $posti_occupati >= $posti_disponibili ) {
			wp_die( esc_html__( 'Spiacenti, il corso per questa data è al completo.', 'cri-corsi' ) );
		}

		// 4. Controlla se è un corso a pagamento
		$prodotto_id = absint( $_POST['prodotto_id'] ?? 0 );
		$is_pagamento = $prodotto_id > 0 && class_exists( 'WooCommerce' );

		// 5. Crea la prenotazione (con stato diverso se a pagamento)
		$stato_prenotazione = $is_pagamento ? 'wc-pending' : 'publish'; // 'wc-pending' per "in attesa di pagamento"
		$prenotazione_id = $this->create_booking( $sanitized_data, $dettagli_scelta, $stato_prenotazione );

		if ( is_wp_error( $prenotazione_id ) ) {
			wp_die( esc_html__( 'Errore durante la creazione della prenotazione.', 'cri-corsi' ) );
		}

		// 6. Logica di reindirizzamento
		if ( $is_pagamento ) {
			// Aggiungi al carrello e reindirizza al checkout
			WC()->cart->empty_cart();
			WC()->cart->add_to_cart( $prodotto_id, 1, 0, [], [ 'cri_prenotazione_id' => $prenotazione_id ] );
			$redirect_url = wc_get_checkout_url();
		} else {
			// Corso gratuito: invia email e reindirizza alla pagina di successo
			$this->send_notification_email_to_admin( $sanitized_data, $dettagli_scelta );
			$this->send_confirmation_email_to_user( $sanitized_data, $dettagli_scelta );
			$redirect_url = home_url( '/prenotazione-confermata/' );
		}

		wp_redirect( $redirect_url );
		exit;
	}

	/**
	 * Crea un nuovo post di tipo 'prenotazione'.
	 *
	 * @param array $data Dati sanificati dal form.
	 * @param array $dettagli_orario Dettagli della data/orario scelti.
	 * @param string $status Stato iniziale della prenotazione.
	 * @return int|WP_Error ID del post creato o oggetto di errore.
	 */
	private function create_booking( array $data, array $dettagli_orario, string $status = 'publish' ): int|WP_Error {
		$post_title = sprintf( 'Prenotazione per %s - %s %s', get_the_title( $data['corso_id'] ), $data['nome'], $data['cognome'] );

		$post_data = [
			'post_title'  => $post_title,
			'post_type'   => 'prenotazione',
			'post_status' => $status,
		];

		$prenotazione_id = wp_insert_post( $post_data, true ); // true per restituire WP_Error in caso di fallimento

		if ( is_wp_error( $prenotazione_id ) ) {
			return $prenotazione_id;
		}

		// Aggiungiamo i dettagli come meta fields
		add_post_meta( $prenotazione_id, '_corso_id', $data['corso_id'] );
		add_post_meta( $prenotazione_id, '_corso_scelta_index', $data['corso_scelta'] );
		add_post_meta( $prenotazione_id, '_nome', $data['nome'] );
		add_post_meta( $prenotazione_id, '_cognome', $data['cognome'] );
		add_post_meta( $prenotazione_id, '_email', $data['email'] );
		add_post_meta( $prenotazione_id, '_telefono', sanitize_text_field( $_POST['telefono'] ?? '' ) );

		if ( isset( $data['ragione_sociale'] ) ) {
			add_post_meta( $prenotazione_id, '_ragione_sociale', $data['ragione_sociale'] );
		}
		if ( isset( $data['partita_iva'] ) ) {
			add_post_meta( $prenotazione_id, '_partita_iva', $data['partita_iva'] );
		}

		return $prenotazione_id;
	}

	/**
	 * Invia un'email di notifica alla segreteria.
	 */
	private function send_notification_email_to_admin( array $data, array $dettagli_orario ): void {
		$to = 'segreteria.comitato@cri-venezia.it';
		$subject = 'Nuova Prenotazione Corso: ' . get_the_title( $data['corso_id'] );

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'Reply-To: ' . esc_html( $data['nome'] . ' ' . $data['cognome'] ) . ' <' . esc_html( $data['email'] ) . '>',
		];

		$body = '<h1>Nuova Prenotazione Ricevuta</h1>';
		$body .= '<p>Una nuova prenotazione è stata effettuata per il seguente corso:</p>';
		$body .= '<ul>';
		$body .= '<li><strong>Corso:</strong> ' . esc_html( get_the_title( $data['corso_id'] ) ) . '</li>';
		$body .= '<li><strong>Data:</strong> ' . esc_html( date_i18n( 'l d F Y', strtotime( $dettagli_orario['data_corso'] ) ) ) . '</li>';
		$body .= '<li><strong>Durata:</strong> ' . esc_html( $dettagli_orario['durata_corso'] ?? 'N/D' ) . '</li>'; // <-- MODIFICATO
		$body .= '</ul>';
		$body .= '<h2>Dettagli del Partecipante</h2>';
		$body .= '<ul>';

		if ( isset( $data['ragione_sociale'] ) ) {
			$body .= '<li><strong>Ragione Sociale:</strong> ' . esc_html( $data['ragione_sociale'] ) . '</li>';
			$body .= '<li><strong>Partita IVA:</strong> ' . esc_html( $data['partita_iva'] ) . '</li>';
			$body .= '<li><strong>Referente:</strong> ' . esc_html( $data['nome'] . ' ' . $data['cognome'] ) . '</li>';
		} else {
			$body .= '<li><strong>Nome:</strong> ' . esc_html( $data['nome'] ) . '</li>';
			$body .= '<li><strong>Cognome:</strong> ' . esc_html( $data['cognome'] ) . '</li>';
		}

		$body .= '<li><strong>Email:</strong> ' . esc_html( $data['email'] ) . '</li>';
		$body .= '<li><strong>Telefono:</strong> ' . esc_html( sanitize_text_field( $_POST['telefono'] ?? 'Non fornito' ) ) . '</li>';
		$body .= '</ul>';

		wp_mail( $to, $subject, $body, $headers );
	}

	/**
	 * Invia un'email di conferma all'utente.
	 */
	private function send_confirmation_email_to_user( array $data, array $dettagli_orario ): void {
		$to = $data['email'];
		$subject = 'Conferma Prenotazione Corso: ' . get_the_title( $data['corso_id'] );

		$from_name = 'CRI Comitato di Venezia';
		$from_email = 'noreply@' . wp_parse_url( home_url(), PHP_URL_HOST );
		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $from_name . ' <' . $from_email . '>',
		];

		$body = '<h1>Grazie per la tua prenotazione!</h1>';
		$body .= '<p>Ciao ' . esc_html( $data['nome'] ) . ',</p>';
		$body .= '<p>Abbiamo ricevuto e confermato la tua prenotazione per il seguente corso. Ecco un riepilogo dei tuoi dati:</p>';
		$body .= '<h2>Dettagli del Corso</h2>';
		$body .= '<ul>';
		$body .= '<li><strong>Corso:</strong> ' . esc_html( get_the_title( $data['corso_id'] ) ) . '</li>';
		$body .= '<li><strong>Data:</strong> ' . esc_html( date_i18n( 'l d F Y', strtotime( $dettagli_orario['data_corso'] ) ) ) . '</li>';
		$body .= '<li><strong>Durata:</strong> ' . esc_html( $dettagli_orario['durata_corso'] ?? 'N/D' ) . '</li>'; // <-- MODIFICATO
		$body .= '</ul>';
		$body .= '<h2>I Tuoi Dati</h2>';
		$body .= '<ul>';

		if ( isset( $data['ragione_sociale'] ) ) {
			$body .= '<li><strong>Ragione Sociale:</strong> ' . esc_html( $data['ragione_sociale'] ) . '</li>';
			$body .= '<li><strong>Partita IVA:</strong> ' . esc_html( $data['partita_iva'] ) . '</li>';
			$body .= '<li><strong>Referente:</strong> ' . esc_html( $data['nome'] . ' ' . $data['cognome'] ) . '</li>';
		} else {
			$body .= '<li><strong>Nome:</strong> ' . esc_html( $data['nome'] ) . '</li>';
			$body .= '<li><strong>Cognome:</strong> ' . esc_html( $data['cognome'] ) . '</li>';
		}

		$body .= '<li><strong>Email:</strong> ' . esc_html( $data['email'] ) . '</li>';
		$body .= '<li><strong>Telefono:</strong> ' . esc_html( sanitize_text_field( $_POST['telefono'] ?? 'Non fornito' ) ) . '</li>';
		$body .= '</ul>';
		$body .= '<p>Per qualsiasi domanda, contatta la nostra segreteria. Ti aspettiamo!</p>';
		$body .= '<p><strong>Croce Rossa Italiana - Comitato di Venezia</strong></p>';

		wp_mail( $to, $subject, $body, $headers );
	}

	/**
	 * Conta i posti già prenotati per un determinato corso e slot di tempo.
	 *
	 * @param int $corso_id ID del post del corso.
	 * @param int|string $scelta_index Indice dell'array date/orari.
	 * @return int Numero di posti occupati.
	 */
	public function count_booked_seats( int $corso_id, int|string $scelta_index ): int {
		$args = [
			'post_type'      => 'prenotazione',
			'post_status'    => [ 'publish', 'wc-completed', 'wc-processing' ], // Contiamo solo le prenotazioni confermate o pagate
			'posts_per_page' => -1,
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'   => '_corso_id',
					'value' => $corso_id,
				],
				[
					'key'   => '_corso_scelta_index',
					'value' => $scelta_index,
				],
			],
			'fields' => 'ids',
		];
		$query = new WP_Query( $args );
		return $query->post_count;
	}

	/**
	 * Aggiorna lo stato della prenotazione quando l'ordine WooCommerce è completato.
	 *
	 * @param int $order_id ID dell'ordine WooCommerce.
	 */
	public function handle_completed_payment( int $order_id ): void {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {
			$prenotazione_id = $item->get_meta( 'cri_prenotazione_id' );

			if ( $prenotazione_id ) {
				// Trovata una prenotazione collegata
				$booking_post = get_post( $prenotazione_id );

				// Se la prenotazione è ancora "in attesa", la confermiamo
				if ( $booking_post && 'wc-pending' === $booking_post->post_status ) {
					// Aggiorniamo lo stato in 'publish' (confermato)
					wp_update_post( [
						'ID'          => $prenotazione_id,
						'post_status' => 'publish',
					] );

					// Ora inviamo le email di notifica, perché il pagamento è confermato
					$data = [
						'corso_id'      => get_post_meta( $prenotazione_id, '_corso_id', true ),
						'nome'          => get_post_meta( $prenotazione_id, '_nome', true ),
						'cognome'       => get_post_meta( $prenotazione_id, '_cognome', true ),
						'email'         => get_post_meta( $prenotazione_id, '_email', true ),
						'ragione_sociale' => get_post_meta( $prenotazione_id, '_ragione_sociale', true ),
						'partita_iva'   => get_post_meta( $prenotazione_id, '_partita_iva', true ),
					];
					$scelta_index = get_post_meta( $prenotazione_id, '_corso_scelta_index', true );
					$date_orari = get_post_meta( $data['corso_id'], '_cri_course_date_orari', true );

					if ( is_array( $date_orari ) && isset( $date_orari[ $scelta_index ] ) ) {
						$dettagli_scelta = $date_orari[ $scelta_index ];

						// Inviamo le email solo ora che il pagamento è certo
						$this->send_notification_email_to_admin( $data, $dettagli_scelta );
						$this->send_confirmation_email_to_user( $data, $dettagli_scelta );
					}
				}
			}
		}
	}
}

