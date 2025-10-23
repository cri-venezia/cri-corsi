<?php
/**
 * Gestisce la creazione della Meta Box per i dettagli del corso.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

use WP_Post;
use CRICorsi\CRI_Corsi; // Usiamo questo per accedere alla versione

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Classe Meta_Boxes
 *
 * Gestisce l'aggiunta, la visualizzazione e il salvataggio dei campi personalizzati
 * per il Custom Post Type 'cri_corso'.
 */
class Meta_Boxes {

    /**
     * Chiave del meta-campo per il ripetitore di date e orari.
     * @var string
     */
    private readonly string $KEY_DATE_ORARI;

    /**
     * Chiave del meta-campo per la destinazione (popolazione/aziende).
     * @var string
     */
    private readonly string $KEY_DESTINAZIONE;

    /**
     * Chiave del meta-campo per il prezzo.
     * @var string
     */
    private readonly string $KEY_PREZZO;

    /**
     * Chiave del meta-campo per l'indirizzo della mappa.
     * @var string
     */
    private readonly string $KEY_INDIRIZZO;

    /**
     * Chiave del meta-campo per l'ID del prodotto WooCommerce.
     * @var string
     */
    private readonly string $KEY_PRODOTTO_ID;

    /**
     * Costruttore.
     *
     * Aggancia le funzioni principali agli hook di WordPress.
     */
    public function __construct() {
        // Inizializziamo le chiavi
        $this->KEY_DATE_ORARI = '_cri_course_date_orari';
        $this->KEY_DESTINAZIONE = '_cri_course_destinazione';
        $this->KEY_PREZZO = '_cri_course_prezzo';
        $this->KEY_INDIRIZZO = '_cri_course_indirizzo';
        $this->KEY_PRODOTTO_ID = '_cri_course_prodotto_id';

        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_post' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
    }

    /**
     * Registra la meta box per il CPT 'cri_corso'.
     */
    public function add_meta_box(): void {
        add_meta_box(
                'cri_corso_dettagli',
                esc_html__( 'Dettagli Corso', 'cri-corsi' ),
                [ $this, 'render_meta_box_content' ],
                'cri_corso',
                'normal',
                'high'
        );
    }

    /**
     * Carica gli script e gli stili per la pagina di modifica.
     *
     * @param string $hook La pagina corrente dell'admin.
     */
    public function enqueue_admin_scripts( string $hook ): void {
        global $post_type;

        // Carica lo script solo nelle pagine di modifica del nostro CPT
        if ( ( 'post.php' === $hook || 'post-new.php' === $hook ) && 'cri_corso' === $post_type ) {

            // Carichiamo lo script per il ripetitore
            wp_enqueue_script(
                    'cri-admin-repeater',
                    CRI_Corsi::instance()->get_plugin_url() . 'assets/js/admin-repeater.js',
                    [ 'jquery' ],
                    CRI_Corsi::instance()->get_version(),
                    true
            );

            // NUOVO: Carichiamo gli stili admin (per il ripetitore e altri campi)
            wp_enqueue_style(
                    'cri-admin-styles',
                    CRI_Corsi::instance()->get_plugin_url() . 'assets/css/admin.css',
                    [],
                    CRI_Corsi::instance()->get_version()
            );
        }
    }

    /**
     * Renderizza il contenuto HTML della meta box.
     *
     * @param WP_Post $post L'oggetto post corrente.
     */
    public function render_meta_box_content( WP_Post $post ): void {
        // Aggiungiamo un nonce per la sicurezza
        wp_nonce_field( 'cri_corso_meta_box', 'cri_corso_meta_box_nonce' );

        // Recuperiamo i dati salvati
        $date_orari = get_post_meta( $post->ID, $this->KEY_DATE_ORARI, true );
        $destinazione = get_post_meta( $post->ID, $this->KEY_DESTINAZIONE, true );
        $prezzo = get_post_meta( $post->ID, $this->KEY_PREZZO, true );
        $indirizzo = get_post_meta( $post->ID, $this->KEY_INDIRIZZO, true );
        $prodotto_id = get_post_meta( $post->ID, $this->KEY_PRODOTTO_ID, true );

        if ( ! is_array( $date_orari ) ) {
            $date_orari = [];
        }

        // Utilizziamo l'output buffering per un HTML più pulito
        // NOTA: Il blocco <style> è stato rimosso e spostato in admin.css
        ?>
        <h4><?php esc_html_e( 'Date e Orari', 'cri-corsi' ); ?></h4>
        <table id="cri-repeater-table" class="cri-repeater-table">
            <thead>
            <tr>
                <th><?php esc_html_e( 'Data', 'cri-corsi' ); ?></th>
                <th><?php esc_html_e( 'Durata', 'cri-corsi' ); ?></th>
                <th><?php esc_html_e( 'Posti Disponibili', 'cri-corsi' ); ?></th>
                <th><?php esc_html_e( 'Azione', 'cri-corsi' ); ?></th>
            </tr>
            </thead>
            <tbody id="cri-repeater-tbody">
            <?php if ( ! empty( $date_orari ) ) : ?>
                <?php foreach ( $date_orari as $index => $orario ) : ?>
                    <tr>
                        <td>
                            <input type="date"
                                   name="<?php echo esc_attr( $this->KEY_DATE_ORARI ); ?>[<?php echo esc_attr( $index ); ?>][data_corso]"
                                   value="<?php echo esc_attr( $orario['data_corso'] ?? '' ); ?>"
                                   required>
                        </td>
                        <td>
                            <input type="text"
                                   name="<?php echo esc_attr( $this->KEY_DATE_ORARI ); ?>[<?php echo esc_attr( $index ); ?>][durata_corso]"
                                   value="<?php echo esc_attr( $orario['durata_corso'] ?? '' ); ?>"
                                   placeholder="<?php esc_attr_e( 'Es. 4 ore', 'cri-corsi' ); ?>"
                                   required>
                        </td>
                        <td>
                            <input type="number"
                                   name="<?php echo esc_attr( $this->KEY_DATE_ORARI ); ?>[<?php echo esc_attr( $index ); ?>][posti_disponibili]"
                                   value="<?php echo esc_attr( $orario['posti_disponibili'] ?? '' ); ?>"
                                   min="0"
                                   step="1"
                                   required>
                        </td>
                        <td>
                            <a href="#" class="remove-row-button"><?php esc_html_e( 'Rimuovi', 'cri-corsi' ); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <button type="button" id="add-row-button" class="button"><?php esc_html_e( 'Aggiungi Data/Orario', 'cri-corsi' ); ?></button>

        <!-- Template per la nuova riga (usato da JS) -->
        <script type="text/template" id="cri-repeater-template">
            <tr>
                <td>
                    <input type="date" name="<?php echo esc_attr( $this->KEY_DATE_ORARI ); ?>[__INDEX__][data_corso]" required>
                </td>
                <td>
                    <input type="text"
                           name="<?php echo esc_attr( $this->KEY_DATE_ORARI ); ?>[__INDEX__][durata_corso]"
                           placeholder="<?php esc_attr_e( 'Es. 4 ore', 'cri-corsi' ); ?>"
                           required>
                </td>
                <td>
                    <input type="number" name="<?php echo esc_attr( $this->KEY_DATE_ORARI ); ?>[__INDEX__][posti_disponibili]" min="0" step="1" required>
                </td>
                <td>
                    <a href="#" class="remove-row-button"><?php esc_html_e( 'Rimuovi', 'cri-corsi' ); ?></a>
                </td>
            </tr>
        </script>

        <hr>

        <h4><?php esc_html_e( 'Configurazione Aggiuntiva', 'cri-corsi' ); ?></h4>
        <div class="cri-fields-grid">
            <div class="cri-field-group">
                <label for="<?php echo esc_attr( $this->KEY_DESTINAZIONE ); ?>"><?php esc_html_e( 'Destinazione Corso', 'cri-corsi' ); ?></label>
                <select name="<?php echo esc_attr( $this->KEY_DESTINAZIONE ); ?>" id="<?php echo esc_attr( $this->KEY_DESTINAZIONE ); ?>">
                    <option value="popolazione" <?php selected( $destinazione, 'popolazione' ); ?>><?php esc_html_e( 'Popolazione', 'cri-corsi' ); ?></option>
                    <option value="aziende" <?php selected( $destinazione, 'aziende' ); ?>><?php esc_html_e( 'Aziende', 'cri-corsi' ); ?></option>
                </select>
            </div>
            <div class="cri-field-group">
                <label for="<?php echo esc_attr( $this->KEY_PREZZO ); ?>"><?php esc_html_e( 'Prezzo (lasciare vuoto se gratuito)', 'cri-corsi' ); ?></label>
                <input type="number"
                       step="0.01"
                       min="0"
                       name="<?php echo esc_attr( $this->KEY_PREZZO ); ?>"
                       id="<?php echo esc_attr( $this->KEY_PREZZO ); ?>"
                       value="<?php echo esc_attr( $prezzo ); ?>"
                       placeholder="Es. 80.00">
            </div>
            <div class="cri-field-group">
                <label for="<?php echo esc_attr( $this->KEY_INDIRIZZO ); ?>"><?php esc_html_e( 'Indirizzo (per Mappa)', 'cri-corsi' ); ?></label>
                <input type="text"
                       name="<?php echo esc_attr( $this->KEY_INDIRIZZO ); ?>"
                       id="<?php echo esc_attr( $this->KEY_INDIRIZZO ); ?>"
                       value="<?php echo esc_attr( $indirizzo ); ?>"
                       placeholder="Es. Via Porto di Cavergnago, 38/B, Venezia">
            </div>
            <div class="cri-field-group">
                <label for="<?php echo esc_attr( $this->KEY_PRODOTTO_ID ); ?>"><?php esc_html_e( 'ID Prodotto WooCommerce (per corsi a pagamento)', 'cri-corsi' ); ?></label>
                <input type="number"
                       min="0"
                       step="1"
                       name="<?php echo esc_attr( $this->KEY_PRODOTTO_ID ); ?>"
                       id="<?php echo esc_attr( $this->KEY_PRODOTTO_ID ); ?>"
                       value="<?php echo esc_attr( $prodotto_id ); ?>"
                       placeholder="Es. 123">
            </div>
        </div>

        <?php
    }

    /**
     * Salva i dati della meta box quando il post viene salvato.
     *
     * @param int $post_id L'ID del post che viene salvato.
     */
    public function save_post( int $post_id ): void {
        // Verifica il nonce
        if ( ! isset( $_POST['cri_corso_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['cri_corso_meta_box_nonce'], 'cri_corso_meta_box' ) ) {
            return;
        }

        // Se è un autosave, non fare nulla
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Verifica i permessi dell'utente
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // --- Salvataggio Dati Ripetitore ---
        if ( isset( $_POST[ $this->KEY_DATE_ORARI ] ) && is_array( $_POST[ $this->KEY_DATE_ORARI ] ) ) {
            $date_orari_sanitized = [];
            $date_orari_data = (array) $_POST[ $this->KEY_DATE_ORARI ];

            foreach ( $date_orari_data as $orario ) {
                if ( ! is_array( $orario ) ) {
                    continue;
                }
                // Ci assicuriamo che tutti i campi siano presenti e sanificati
                $date_orari_sanitized[] = [
                        'data_corso'        => sanitize_text_field( $orario['data_corso'] ?? '' ),
                        'durata_corso'      => sanitize_text_field( $orario['durata_corso'] ?? '' ),
                        'posti_disponibili' => absint( $orario['posti_disponibili'] ?? 0 ),
                ];
            }
            update_post_meta( $post_id, $this->KEY_DATE_ORARI, $date_orari_sanitized );
        } else {
            // Se non ci sono dati, salva un array vuoto o cancella il meta
            update_post_meta( $post_id, $this->KEY_DATE_ORARI, [] );
        }

        // --- Salvataggio Altri Campi ---
        $campi_da_salvare = [
                $this->KEY_DESTINAZIONE  => 'sanitize_text_field',
                $this->KEY_PREZZO        => 'sanitize_text_field', // Salviamo come testo, gestiamo la formattazione in output
                $this->KEY_INDIRIZZO     => 'sanitize_text_field',
                $this->KEY_PRODOTTO_ID   => 'absint',
        ];

        foreach ( $campi_da_salvare as $key => $sanitization_function ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = call_user_func( $sanitization_function, $_POST[ $key ] );
                update_post_meta( $post_id, $key, $value );
            } else {
                // Opzionale: cancellare il meta se non è settato
                delete_post_meta( $post_id, $key );
            }
        }
    }
}

