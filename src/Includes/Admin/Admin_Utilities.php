<?php
/**
 * Aggiunge funzionalità di utility per l'area admin, come il link "Svuota Carrello".
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe Admin_Utilities
 */
class Admin_Utilities {

    /**
     * Costruttore. Aggancia le azioni.
     */
    public function __construct() {
        // Aggiungi hook solo se siamo in admin e WooCommerce è attivo
        if ( ! is_admin() || ! class_exists('WooCommerce') ) {
            return;
        }

        add_action( 'admin_bar_menu', [ $this, 'add_empty_cart_admin_bar_link' ], 100 );
        add_action( 'admin_init', [ $this, 'handle_empty_cart_action' ] );
    }

    /**
     * Aggiunge un link "Svuota Carrello" alla barra di amministrazione.
     * (Metodo spostato da CRI_Corsi)
     *
     * @param \WP_Admin_Bar $wp_admin_bar Oggetto WP_Admin_Bar.
     */
    public function add_empty_cart_admin_bar_link( \WP_Admin_Bar $wp_admin_bar ): void {
        
        // **CORREZIONE**: Forza l'inizializzazione della sessione e del carrello nell'area admin
        if ( function_exists('WC') && WC()->session && ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }
        if ( function_exists('wc_load_cart') && is_null( WC()->cart ) ) {
            wc_load_cart();
        }

        // Mostra solo se l'utente è admin e il carrello esiste
        // (Modificato il controllo: ora verifichiamo che WC() e WC()->cart esistano)
        if ( ! current_user_can('manage_options') || ! function_exists('WC') || ! WC()->cart ) {
            return;
        }

        // Crea un URL sicuro con un nonce
        $empty_cart_url = wp_nonce_url(
            add_query_arg( 'cri_empty_cart', 'true', wp_get_referer() ?: admin_url() ), // Aggiunto referer
            'cri_empty_cart_nonce',
            '_cri_nonce'
        );

        $wp_admin_bar->add_node( [
            'id'    => 'cri-empty-cart',
            'title' => '<span class="ab-icon dashicons-trash" style="top: 2px;"></span>' . esc_html__( 'Svuota Carrello', 'cri-corsi' ),
            'href'  => $empty_cart_url,
            'meta'  => [
                'title' => esc_html__( 'Svuota il carrello della tua sessione corrente (per test)', 'cri-corsi' ),
            ],
        ] );
    }

    /**
     * Gestisce l'azione di svuotamento del carrello.
     * (Metodo spostato da CRI_Corsi)
     */
    public function handle_empty_cart_action(): void {
        // Verifica se l'azione è stata chiamata, se il nonce è valido e se l'utente è admin
        if ( isset($_GET['cri_empty_cart']) && 
             isset($_GET['_cri_nonce']) && 
             wp_verify_nonce($_GET['_cri_nonce'], 'cri_empty_cart_nonce') && 
             current_user_can('manage_options') 
        ) {
            
            // **CORREZIONE**: Assicura che il carrello sia inizializzato prima di svuotarlo
            if ( function_exists('WC') && WC()->session && ! WC()->session->has_session() ) {
                 WC()->session->set_customer_session_cookie( true );
            }
             if ( function_exists('wc_load_cart') && is_null( WC()->cart ) ) {
                 wc_load_cart();
             }

            // Se tutto è valido, svuota il carrello
            if ( function_exists('WC') && WC()->cart ) {
                WC()->cart->empty_cart();
            }

            // Reindirizza alla stessa pagina rimuovendo i parametri dell'URL per evitare riesecuzioni
            wp_safe_redirect( remove_query_arg( ['cri_empty_cart', '_cri_nonce'] ) );
            exit;
        }
    }
}

