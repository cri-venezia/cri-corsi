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
     * Hook della pagina del pannello docente, salvato per confronto.
     * @var string
     */
    private string $teacher_panel_hook = '';

    /**
     * Costruttore. Aggancia le azioni.
     */
    public function __construct() {
        // Aggancia admin_footer per aggiungere il testo
        add_action( 'admin_footer', [ $this, 'display_custom_admin_footer' ] );
        // Aggancia admin_menu per recuperare l'hook della pagina del pannello docente
        add_action( 'admin_menu', [ $this, 'capture_teacher_panel_hook' ], 99 ); // Priorità alta per assicurarsi che venga eseguito dopo la creazione del menu
    }

    /**
     * Cattura l'hook della pagina del pannello docente quando viene creata.
     * Necessario per poterla identificare correttamente in admin_footer.
     */
    public function capture_teacher_panel_hook(): void {
        // Usiamo una closure per accedere all'hook generato da add_menu_page nel Teacher_Panel
        // Nota: Questo approccio è un po' avanzato. Un'alternativa sarebbe salvare l'hook in una proprietà statica o tramite il Singleton.
        // O più semplicemente, usare lo slug della pagina 'cri-gestione-date' nel controllo get_current_screen.
        
        // Semplifichiamo: useremo lo slug 'cri-gestione-date' direttamente nel controllo sotto.
        // Non serve questo metodo complesso. Potremmo rimuoverlo o lasciarlo vuoto.
        
        // Recuperiamo l'hook generato dalla funzione add_menu_page di Teacher_Panel
        // Questo richiede che Teacher_Panel salvi l'hook in una proprietà accessibile,
        // o che usiamo lo slug direttamente. Useremo lo slug per semplicità.
        
        // $this->teacher_panel_hook = $hook_generato_da_teacher_panel; // Esempio teorico
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

        // Definisci gli ID delle schermate dove mostrare il footer
        $allowed_screen_ids = [
            'cri_corso',          // Pagina di modifica/creazione del CPT Corso
            'edit-cri_corso',     // Pagina elenco del CPT Corso (opzionale, aggiunto per completezza)
            // L'hook per una pagina creata con add_menu_page è 'toplevel_page_{menu_slug}'
            'toplevel_page_cri-gestione-date', // ID Schermata per il Pannello Docente
        ];

        // Mostra il footer solo se siamo in una delle schermate consentite
        if ( in_array( $screen->id, $allowed_screen_ids, true ) ) {
            ?>
            <div style="text-align: center; padding: 15px 0; font-size: 0.9em; color: #555; border-top: 1px solid #ddd; margin-top: 20px;">
                Creato con <span style="color: #CC0000;">♥</span> da
                <a href="mailto:luca.forzutti@veneto.cri.it" target="_blank" style="color: #007cba; text-decoration: none;">Luca Forzutti</a>
                <br>
                <a href="https://docs.crivenezia.it/cri-corsi/" target="_blank" style="color: #007cba; text-decoration: none;">
                    <?php esc_html_e( 'Documentazione Plugin', 'cri-corsi' ); ?>
                </a>
            </div>
            <?php
        }
    }
}
