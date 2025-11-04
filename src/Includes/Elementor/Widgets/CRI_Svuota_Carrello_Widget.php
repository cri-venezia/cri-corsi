<?php
/**
 * File per il widget Elementor "Svuota Carrello (Admin)".
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Widget Elementor per mostrare un pulsante "Svuota Carrello"
 * visibile solo agli amministratori nelle pagine Carrello e Checkout.
 */
class CRI_Svuota_Carrello_Widget extends Widget_Base {

    /**
     * Nome univoco del widget.
     * @return string
     */
    public function get_name(): string {
        return 'cri-svuota-carrello-admin';
    }

    /**
     * Titolo visibile nell'editor.
     * @return string
     */
    public function get_title(): string {
        return esc_html__( 'Svuota Carrello (Admin)', 'cri-corsi' );
    }

    /**
     * Icona del widget.
     * @return string
     */
    public function get_icon(): string {
        return 'eicon-trash'; // Icona cestino
    }

    /**
     * Categorie del widget.
     * @return array
     */
    public function get_categories(): array {
        return [ 'cri-corsi' ]; // Nella nostra categoria
    }

    /**
     * Non ci sono controlli per questo widget, è solo un pulsante funzionale.
     */
    protected function register_controls(): void {
         $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Avviso', 'cri-corsi' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'admin_notice',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__( 'Questo widget mostra un pulsante "Svuota Carrello" visibile solo agli Amministratori. È utile per i test. Posizionalo nella tua pagina Carrello o Checkout.', 'cri-corsi' ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

         $this->end_controls_section();
    }

    /**
     * Renderizza l'output HTML del widget.
     */
    protected function render(): void {
        // **Esegui gli stessi controlli che avevamo nell'hook**
        // Mostra solo se siamo (Carrello O Checkout) E l'utente è Admin E il carrello non è vuoto
        if ( ( ! is_cart() && ( ! is_checkout() || is_order_received_page() ) ) ||
             ! current_user_can('manage_options') ||
             ! function_exists('WC') || ! WC()->cart || WC()->cart->is_empty()
        ) {
            // Se le condizioni non sono soddisfatte (es. utente normale), non mostrare nulla.
            // Mostra un avviso solo nell'editor di Elementor
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() && ! current_user_can('manage_options') ) {
                 echo '<p>' . esc_html__( 'Avviso: Questo pulsante sarà visibile solo agli Amministratori.', 'cri-corsi' ) . '</p>';
            }
            return;
        }

        // Stampa il modulo (identico a quello che avevamo prima)
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
     * Renderizza nell'editor Elementor (per mostrare un segnaposto).
     */
    protected function content_template() {
        ?>
        <div style="border: 1px dashed #CC0000; padding: 10px; text-align: right; opacity: 0.7;">
             <button type="button" class="button"><?php esc_html_e( 'Svuota Carrello (Admin)', 'cri-corsi' ); ?></button>
             <p style="font-size: 11px; text-align: left; margin: 5px 0 0 0; opacity: 0.8;"><?php esc_html_e( 'Segnaposto. Visibile solo agli Admin.', 'cri-corsi' ); ?></p>
        </div>
        <?php
    }
}
