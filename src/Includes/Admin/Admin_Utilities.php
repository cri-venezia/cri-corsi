<?php
/**
 * Gestisce le azioni di utility admin, come lo svuotamento del carrello.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe Admin_Utilities
 * Gestisce l'azione POST per svuotare il carrello.
 * La visualizzazione del pulsante è delegata a un widget Elementor.
 */
class Admin_Utilities {

    /**
     * Costruttore. Aggancia le azioni.
     */
    public function __construct() {
        // Aggiungi hook solo se WooCommerce è attivo
        if ( ! class_exists('WooCommerce') ) {
            return;
        }

        // **RIMOSSI**: Hook per visualizzare il pulsante
        // add_action( 'woocommerce_after_cart_totals', [ $this, 'display_empty_cart_button' ] );
        // add_action( 'woocommerce_before_checkout_form', [ $this, 'display_empty_cart_button' ], 10 );
        
        // **MANTENUTO**: Hook per gestire la richiesta POST di svuotamento carrello
        add_action( 'wp_loaded', [ $this, 'handle_empty_cart_post' ] );
    }

    /**
     * **RIMOSSO**: La funzione display_empty_cart_button() è stata spostata
     * nella logica di rendering del nuovo widget CRI_Svuota_Carrello_Widget.
     */
    // public function display_empty_cart_button(): void { ... }

    /**
     * Gestisce la richiesta POST per svuotare il carrello.
     * Agganciato a 'wp_loaded'.
     */
    public function handle_empty_cart_post(): void {
        // Verifica se l'azione è stata chiamata, se il nonce è valido e se l'utente è admin
        if ( isset($_POST['cri_empty_cart_action']) && 
             isset($_POST['_cri_nonce_empty_cart']) && 
             wp_verify_nonce($_POST['_cri_nonce_empty_cart'], 'cri_empty_cart_nonce') && 
             current_user_can('manage_options') 
        ) {
            
            // Assicura che il carrello sia inizializzato
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

            // Reindirizza alla pagina da cui è partita l'azione (Carrello o Checkout)
            $redirect_url = wc_get_cart_url(); // Default
            if ( wp_get_referer() ) {
                $referer = wp_unslash( $_SERVER['HTTP_REFERER'] ); // Recupera l'URL precedente
                // Se il referer è la pagina checkout, torna lì. Altrimenti vai al carrello.
                if ( strpos( $referer, wc_get_checkout_url() ) !== false ) {
                    $redirect_url = wc_get_checkout_url();
                }
            }

            wp_safe_redirect( $redirect_url );
            exit;
        }
    }
}

