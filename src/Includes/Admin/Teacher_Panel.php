<?php
/**
 * Gestisce la pagina di amministrazione semplificata per i docenti.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Admin;

use CRICorsi\CRI_Corsi;
use CRICorsi\Includes\User_Roles; // <-- AGGIUNTO
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Teacher_Panel
 * Crea una pagina di menu per la gestione rapida delle date dei corsi.
 */
class Teacher_Panel {

	/**
	 * Hook per la pagina di menu.
	 * @var string
	 */
	private string $menu_hook = '';

	/**
	 * Costruttore. Aggancia le azioni di WordPress.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

		// Handler per l'aggiunta di una nuova data
		add_action( 'admin_post_cri_add_date', [ $this, 'handle_add_date' ] );
		// Handler per l'eliminazione di una data
		add_action( 'admin_post_cri_delete_date', [ $this, 'handle_delete_date' ] );
	}

	/**
	 * Registra la pagina di menu in bacheca.
	 */
	public function register_menu_page(): void {
		$this->menu_hook = add_menu_page(
			esc_html__( 'Gestione Date Corsi', 'cri-corsi' ),
			esc_html__( 'Gestione Date', 'cri-corsi' ),
			User_Roles::CAPABILITY, // <-- MODIFICATO: Usa la nostra capability personalizzata
			'cri-gestione-date',
			[ $this, 'render_page_content' ],
			'dashicons-calendar-alt',
			30
		);
	}

	/**
	 * Carica gli stili CSS per la bacheca (solo nella nostra pagina).
	 */
	public function enqueue_admin_scripts( string $hook ): void {
		// Carica il nostro CSS admin solo in questa pagina
		if ( $hook === $this->menu_hook ) {
			$plugin_instance = CRI_Corsi::instance();
			wp_enqueue_style(
				'cri-corsi-admin',
				$plugin_instance->get_plugin_url() . 'assets/css/admin.css',
				[],
				$plugin_instance->get_version()
			);
		}
	}

	/**
	 * Renderizza il contenuto HTML della pagina.
	 */
	public function render_page_content(): void {
		// Gestisce i messaggi di notifica (es. 'data aggiunta')
		$this->display_admin_notices();

		// Recupera il corso attualmente selezionato (se presente)
		$selected_course_id = (int) ( $_GET['course_id'] ?? 0 );
		?>
		<div class="wrap cri-admin-panel">
			<h1><?php esc_html_e( 'Gestione Date Corsi', 'cri-corsi' ); ?></h1>
			<p><?php esc_html_e( 'Seleziona un corso per visualizzare, aggiungere o eliminare le date programmate.', 'cri-corsi' ); ?></p>

			<!-- Selettore del Corso -->
			<form method="get" class="cri-course-selector-form">
				<input type="hidden" name="page" value="cri-gestione-date">
				<?php $this->render_course_selector( $selected_course_id ); ?>
				<button type="submit" class="button button-secondary"><?php esc_html_e( 'Carica Date', 'cri-corsi' ); ?></button>
			</form>

			<?php if ( $selected_course_id > 0 ) : ?>
				<hr>
				<div class="cri-panel-columns">
					<div class="cri-panel-column-left">
						<!-- Form per Aggiungere Nuove Date -->
						<?php $this->render_add_date_form( $selected_course_id ); ?>
					</div>
					<div class="cri-panel-column-right">
						<!-- Tabella delle Date Esistenti -->
						<?php $this->render_dates_table( $selected_course_id ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Mostra i messaggi di notifica in cima alla pagina.
	 */
	private function display_admin_notices(): void {
		if ( isset( $_GET['message'] ) ) {
			$message = '';
			$type = 'success'; // Tipo di notifica (success, error, warning, info)

			switch ( $_GET['message'] ) {
				case 'date_added':
					$message = esc_html__( 'Data aggiunta con successo.', 'cri-corsi' );
					break;
				case 'date_deleted':
					$message = esc_html__( 'Data eliminata con successo.', 'cri-corsi' );
					break;
				case 'error':
					$message = esc_html__( 'Si è verificato un errore.', 'cri-corsi' );
					$type = 'error';
					break;
			}

			if ( $message ) {
				echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . $message . '</p></div>';
			}
		}
	}

	/**
	 * Renderizza il menu a tendina per selezionare il corso.
	 */
	private function render_course_selector( int $selected_id ): void {
		$query = new WP_Query( [
			'post_type'      => 'cri_corso',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		] );

		echo '<label for="course_id" class="cri-label">' . esc_html__( 'Seleziona Corso:', 'cri-corsi' ) . '</label>';
		echo '<select name="course_id" id="course_id">';
		echo '<option value="">' . esc_html__( '-- Scegli un corso --', 'cri-corsi' ) . '</option>';

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				printf(
					'<option value="%d" %s>%s</option>',
					get_the_ID(),
					selected( $selected_id, get_the_ID(), false ),
					esc_html( get_the_title() )
				);
			}
		}
		wp_reset_postdata();
		echo '</select>';
	}

	/**
	 * Renderizza il form per aggiungere una nuova data.
	 */
	private function render_add_date_form( int $course_id ): void {
		?>
		<h2><?php esc_html_e( 'Aggiungi Nuova Data', 'cri-corsi' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="cri-admin-form">
			<input type="hidden" name="action" value="cri_add_date">
			<input type="hidden" name="course_id" value="<?php echo esc_attr( $course_id ); ?>">
			<?php wp_nonce_field( 'cri_add_date_nonce', 'cri_add_date_nonce' ); ?>

			<div class="cri-form-group">
				<label for="cri_data_corso"><?php esc_html_e( 'Data', 'cri-corsi' ); ?></label>
				<input type="date" id="cri_data_corso" name="cri_data_corso" required>
			</div>
			<div class="cri-form-group">
				<label for="cri_durata_corso"><?php esc_html_e( 'Durata', 'cri-corsi' ); ?></label>
				<input type="text" id="cri_durata_corso" name="cri_durata_corso" placeholder="<?php esc_attr_e( 'Es. 4 ore', 'cri-corsi' ); ?>" required>
			</div>
			<div class="cri-form-group">
				<label for="cri_posti_disponibili"><?php esc_html_e( 'Posti Disponibili', 'cri-corsi' ); ?></label>
				<input type="number" id="cri_posti_disponibili" name="cri_posti_disponibili" min="1" step="1" required>
			</div>
			<button type="submit" class="button button-primary"><?php esc_html_e( 'Aggiungi Data', 'cri-corsi' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Renderizza la tabella con le date già inserite.
	 */
	private function render_dates_table( int $course_id ): void {
		?>
		<h2><?php esc_html_e( 'Date Programmate', 'cri-corsi' ); ?></h2>
		<table class="wp-list-table widefat fixed striped">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Data', 'cri-corsi' ); ?></th>
				<th><?php esc_html_e( 'Durata', 'cri-corsi' ); ?></th>
				<th><?php esc_html_e( 'Posti', 'cri-corsi' ); ?></th>
				<th><?php esc_html_e( 'Azione', 'cri-corsi' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$date_orari = get_post_meta( $course_id, '_cri_course_date_orari', true );
			if ( ! empty( $date_orari ) && is_array( $date_orari ) ) :
				foreach ( $date_orari as $index => $details ) :
					$delete_url = wp_nonce_url(
						add_query_arg( [
							'action'     => 'cri_delete_date',
							'course_id'  => $course_id,
							'date_index' => $index,
						], admin_url( 'admin-post.php' ) ),
						'cri_delete_date_nonce_' . $index,
						'cri_delete_date_nonce'
					);
					?>
					<tr>
						<td><?php echo esc_html( date_i18n( 'l d/m/Y', strtotime( $details['data_corso'] ?? '' ) ) ); ?></td>
						<td><?php echo esc_html( $details['durata_corso'] ?? '' ); ?></td>
						<td><?php echo esc_html( $details['posti_disponibili'] ?? '' ); ?></td>
						<td>
							<a href="<?php echo esc_url( $delete_url ); ?>" class="submitdelete"
							   onclick="return confirm('<?php esc_attr_e( 'Sei sicuro di voler eliminare questa data?', 'cri-corsi' ); ?>');">
								<?php esc_html_e( 'Elimina', 'cri-corsi' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach;
			else : ?>
				<tr>
					<td colspan="4"><?php esc_html_e( 'Nessuna data programmata trovata.', 'cri-corsi' ); ?></td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Gestisce il salvataggio della nuova data.
	 */
	public function handle_add_date(): void {
		// Verifica Nonce e permessi
		if ( ! isset( $_POST['cri_add_date_nonce'] ) || ! wp_verify_nonce( $_POST['cri_add_date_nonce'], 'cri_add_date_nonce' ) ) {
			wp_die( 'Errore di sicurezza.' );
		}
		// <-- MODIFICATO: Usa la nostra capability personalizzata
		if ( ! current_user_can( User_Roles::CAPABILITY ) ) {
			wp_die( 'Non hai i permessi per eseguire questa azione.' );
		}

		// Validazione e Sanificazione
		$course_id = (int) ( $_POST['course_id'] ?? 0 );
		if ( $course_id === 0 || get_post_type( $course_id ) !== 'cri_corso' ) {
			wp_die( 'Corso non valido.' );
		}

		$new_date = [
			'data_corso'        => sanitize_text_field( $_POST['cri_data_corso'] ?? '' ),
			'durata_corso'      => sanitize_text_field( $_POST['cri_durata_corso'] ?? '' ),
			'posti_disponibili' => (int) ( $_POST['cri_posti_disponibili'] ?? 0 ),
		];

		// Verifica che i campi non siano vuoti
		if ( empty( $new_date['data_corso'] ) || empty( $new_date['durata_corso'] ) || empty( $new_date['posti_disponibili'] ) ) {
			wp_die( 'Tutti i campi sono obbligatori.' );
		}

		// Aggiungi la nuova data
		$date_orari = get_post_meta( $course_id, '_cri_course_date_orari', true );
		if ( ! is_array( $date_orari ) ) {
			$date_orari = [];
		}

		$date_orari[] = $new_date; // Aggiunge la nuova riga
		update_post_meta( $course_id, '_cri_course_date_orari', $date_orari );

		// Reindirizza alla pagina del pannello
		wp_redirect( add_query_arg( [
			'page'      => 'cri-gestione-date',
			'course_id' => $course_id,
			'message'   => 'date_added',
		], admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Gestisce l'eliminazione di una data.
	 */
	public function handle_delete_date(): void {
		$course_id = (int) ( $_GET['course_id'] ?? 0 );
		$date_index = (int) ( $_GET['date_index'] ?? -1 );

		// Verifica Nonce, permessi e dati
		if ( $date_index < 0 || ! isset( $_GET['cri_delete_date_nonce'] ) || ! wp_verify_nonce( $_GET['cri_delete_date_nonce'], 'cri_delete_date_nonce_' . $date_index ) ) {
			wp_die( 'Errore di sicurezza.' );
		}
		// <-- MODIFICATO: Usa la nostra capability personalizzata
		if ( ! current_user_can( User_Roles::CAPABILITY ) ) {
			wp_die( 'Non hai i permessi per eseguire questa azione.' );
		}
		if ( $course_id === 0 || get_post_type( $course_id ) !== 'cri_corso' ) {
			wp_die( 'Corso non valido.' );
		}

		// Rimuovi la data
		$date_orari = get_post_meta( $course_id, '_cri_course_date_orari', true );
		if ( is_array( $date_orari ) && isset( $date_orari[ $date_index ] ) ) {
			unset( $date_orari[ $date_index ] ); // Rimuove l'elemento
			// Re-indicizza l'array per evitare buchi
			$date_orari = array_values( $date_orari );
			update_post_meta( $course_id, '_cri_course_date_orari', $date_orari );
		}

		// Reindirizza
		wp_redirect( add_query_arg( [
			'page'      => 'cri-gestione-date',
			'course_id' => $course_id,
			'message'   => 'date_deleted',
		], admin_url( 'admin.php' ) ) );
		exit;
	}
}

