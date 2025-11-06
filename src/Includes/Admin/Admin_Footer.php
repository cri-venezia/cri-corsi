<?php
/**
 * Aggiunge un header personalizzato ad alcune pagine admin del plugin.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe Admin_Footer (rinominata mentalmente, il file gestisce l'header ora)
 */
class Admin_Footer {

    /**
     * Costruttore. Aggancia le azioni.
     */
    public function __construct() {
        // **MODIFICATO**: Usiamo l'hook 'in_admin_header' per mostrare il contenuto in alto
        add_action( 'in_admin_header', [ $this, 'display_custom_admin_header' ] );
    }

    /**
     * Mostra l'header personalizzato (copyright) nelle pagine admin corrette.
     *
     * @return void
     */
    public function display_custom_admin_header(): void {
        $screen = get_current_screen();

        // Se non riusciamo a ottenere lo schermo corrente, esci
        if ( ! $screen ) {
            return;
        }

        // Definisci gli ID delle schermate dove mostrare il footer
        $allowed_screen_ids = [
            'cri_corso',                     // Pagina di modifica/creazione del CPT Corso
            'edit-cri_corso',                // Pagina elenco del CPT Corso
            'edit-prenotazione',             // Pagina elenco del CPT Prenotazioni
            'toplevel_page_cri-gestione-date', // ID Schermata per il Pannello Docente
        ];

        // Se non siamo in una delle schermate consentite, esci
        if ( ! in_array( $screen->id, $allowed_screen_ids, true ) ) {
            return;
        }
        
        // Se siamo nella schermata giusta, costruiamo il nostro header
        $plugin_version = \CRICorsi\CRI_Corsi::instance()->get_version();

        $author = sprintf(
            /* translators: %1$s is a heart symbol, %2$s is the linked author name */
            esc_html__( 'Creato con %1$s da %2$s', 'cri-corsi' ),
            '<span style="color: #CC0000;">&hearts;</span>',
            '<a href="mailto:luca.forzutti@veneto.cri.it" target="_blank" style="color: #007cba; text-decoration: none;">Luca Forzutti</a>'
        );
        
        $docs = '<a href="https://docs.crivenezia.it/cri-corsi/" target="_blank" style="color: #007cba; text-decoration: none;">' 
                . esc_html__( 'Documentazione', 'cri-corsi' ) 
                . '</a>';

        $version = sprintf(
            /* translators: %s is the plugin version number */
            esc_html__( 'Versione %s', 'cri-corsi' ),
            esc_html( $plugin_version )
        );

        // Stampa l'HTML
        ?>
        <div class="cri-admin-header-credits" style="padding-bottom: 10px; margin-bottom: 15px; border-bottom: 1px solid #ddd; font-size: 0.9em; color: #555;">
             <p style="margin: 0; display: inline-block; margin-right: 15px;">
                <?php echo $author; // L'HTML è già stato preparato e non necessita di ulteriore escaping ?>
             </p>
             <span style="border-left: 1px solid #ddd; padding-left: 15px; margin-right: 15px;">
                <?php echo $docs; ?>
             </span>
             <span style="border-left: 1px solid #ddd; padding-left: 15px;">
                <?php echo $version; ?>
             </span>
        </div>
        <?php
    }
}
