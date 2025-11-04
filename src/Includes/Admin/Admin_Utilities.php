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
        // Aggiungi hook solo se WooCommerce è attivo
        if ( ! class_exists('WooCommerce') ) {
            return;
        }

        // Hook per aggiungere il pulsante "Svuota Carrello" nella pagina del carrello
        add_action( 'woocommerce_after_cart_totals', [ $this, 'display_empty_cart_button' ] );
        
        // **NUOVO**: Hook per aggiungere il pulsante anche nella pagina di checkout
        add_action( 'woocommerce_before_checkout_form', [ $this, 'display_empty_cart_button' ], 10 );
        
        // Hook per gestire la richiesta POST di svuotamento carrello
        add_action( 'wp_loaded', [ $this, 'handle_empty_cart_post' ] );
    }

    /**
     * Mostra un pulsante "Svuota Carrello" nella pagina del carrello o checkout, solo per gli admin.
     * Agganciato a 'woocommerce_after_cart_totals' e 'woocommerce_before_checkout_form'.
     */
    public function display_empty_cart_button(): void {
        
        // **MODIFICATO**: Controlla se siamo in Carrello OPPURE in Checkout (ma non sulla pagina "Grazie")
        if ( ( ! is_cart() && ( ! is_checkout() || is_order_received_page() ) ) || 
             ! current_user_can('manage_options') || 
             ! function_exists('WC') || ! WC()->cart || WC()->cart->is_empty() 
        ) {
            return;
        }

        // Stampa un modulo separato per l'azione
        // Determina l'URL di action corretto (carrello o checkout)
        $form_action_url = is_cart() ? wc_get_cart_url() : wc_get_checkout_url();
        ?>
        <form action="<?php echo esc_url( $form_action_url ); ?>" method="post" style="text-align: right; margin-top: 15px; margin-bottom: 15px; border: 1px dashed #CC0000; padding: 10px;">
            <?php wp_nonce_field( 'cri_empty_cart_nonce', '_cri_nonce_empty_cart' ); ?>
            <input type="hidden" name="cri_empty_cart_action" value="true" />
            <button type="submit" class="button" name="empty_cart" value="<?php esc_attr_e( 'Svuota Carrello (Admin)', 'cri-corsi' ); ?>">
                <?php esc_html_e( 'Svuota Carrello (Admin)', 'cri-corsi' ); ?>
            </button>
        </form>
        <?php
    }

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

            // **MODIFICATO**: Reindirizza alla pagina da cui è partita l'azione (Carrello o Checkout)
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

