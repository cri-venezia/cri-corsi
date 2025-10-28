<?php
/**
 * Aggiunge un footer personalizzato ad alcune pagine admin del plugin.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe Admin_Footer
 */
class Admin_Footer {

    /**
     * Costruttore. Aggancia le azioni.
     */
    public function __construct() {
        // Aggancia admin_footer per aggiungere il testo
        add_action( 'admin_footer', [ $this, 'display_custom_admin_footer' ] );
    }

    /**
     * Mostra il footer personalizzato nelle pagine admin corrette.
     */
    public function display_custom_admin_footer(): void {
        $screen = get_current_screen();

        // Se non riusciamo a ottenere lo schermo corrente, esci
        if ( ! $screen ) {
            return;
        }

        // DEBUG: De-commenta la riga seguente per scrivere l'ID della schermata corrente nel debug.log
        // error_log('Current Admin Screen ID: ' . $screen->id);

        // Definisci gli ID delle schermate dove mostrare il footer
        $allowed_screen_ids = [
            'cri_corso',                     // Pagina di modifica/creazione del CPT Corso
            'edit-cri_corso',                // Pagina elenco del CPT Corso
            'toplevel_page_cri-gestione-date', // ID Schermata per il Pannello Docente (menu slug: 'cri-gestione-date')
        ];

        // Mostra il footer solo se siamo in una delle schermate consentite
        if ( in_array( $screen->id, $allowed_screen_ids, true ) ) {
            // Recupera la versione del plugin
            $plugin_version = \CRICorsi\CRI_Corsi::instance()->get_version();
            ?>
            <div style="text-align: center; padding: 15px 0; font-size: 0.9em; color: #555; border-top: 1px solid #ddd; margin-top: 20px;">
                Creato con <span style="color: #CC0000;">♥</span> da
                <a href="mailto:luca.forzutti@veneto.cri.it" target="_blank" style="color: #007cba; text-decoration: none;">Luca Forzutti</a>
                | Versione Plugin: <?php echo esc_html( $plugin_version ); ?>
                <br>
                <a href="https://docs.crivenezia.it/cri-corsi/" target="_blank" style="color: #007cba; text-decoration: none;">
                    <?php esc_html_e( 'Documentazione Plugin', 'cri-corsi' ); ?>
                </a>
            </div>
            <?php
        }
    }
}

