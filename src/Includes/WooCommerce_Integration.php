<?php
/**
 * Gestisce l'integrazione specifica con WooCommerce (Carrello, Ordini, Pagamenti).
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe WooCommerce_Integration
 * Si occupa di agganciare WooCommerce per gestire l'aggiunta al carrello,
 * la visualizzazione dei dati e il completamento del pagamento.
 */
class WooCommerce_Integration {

    /**
     * Costruttore. Aggancia gli hook di WooCommerce.
     */
    public function __construct() {
        // Hook di WooCommerce per aggiornare la prenotazione dopo il pagamento
        add_action( 'woocommerce_order_status_completed', [ $this, 'handle_completed_payment' ] );
        add_action( 'woocommerce_order_status_processing', [ $this, 'handle_completed_payment' ] );

        // Mostra i dati personalizzati nel carrello
        add_filter( 'woocommerce_get_item_data', [ $this, 'display_course_data_in_cart' ], 10, 2 );
        // Salva i dati personalizzati negli meta dell'ordine
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'save_course_data_to_order_item' ], 10, 4 );
    }

    /**
     * Aggiorna lo stato della prenotazione quando l'ordine WooCommerce è completato.
     * (Metodo spostato da Form_Handler)
     *
     * @param int $order_id ID dell'ordine WooCommerce.
     */
    public function handle_completed_payment( int $order_id ): void {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        foreach ( $order->get_items() as $item ) {
            $prenotazione_id = $item->get_meta( '_cri_prenotazione_id' );

            if ( $prenotazione_id ) {
                $booking_post = get_post( (int) $prenotazione_id );

                // Se la prenotazione è ancora "in attesa", la confermiamo
                if ( $booking_post && 'wc-pending' === $booking_post->post_status ) {
                    wp_update_post( [
                        'ID'          => $booking_post->ID,
                        'post_status' => 'publish',
                    ] );

                    // Prepara i dati per le email
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
                        
                        // Chiama le funzioni email (rese pubbliche) da Form_Handler
                        // Assumendo che Form_Handler sia disponibile o istanziato
                        // Per SRP potremmo creare una classe Email_Handler, ma per ora usiamo Form_Handler
                        // Nota: Form_Handler deve avere questi metodi come 'public'
                        $form_handler = new Form_Handler();
                        $form_handler->send_notification_email_to_admin( $data, $dettagli_scelta );
                        $form_handler->send_confirmation_email_to_user( $data, $dettagli_scelta );
                    }
                }
            }
        }
    }

    /**
     * Mostra i dati personalizzati del corso nel carrello.
     * (Metodo spostato da Form_Handler)
     *
     * @param array $item_data Dati esistenti dell'item.
     * @param array $cart_item Dati del carrello.
     * @return array Dati modificati.
     */
    public function display_course_data_in_cart( array $item_data, array $cart_item ): array {
        // Controlla se i nostri dati personalizzati esistono
        if ( isset( $cart_item['cri_corso_nome'] ) ) {
            $item_data[] = [
                'key'     => esc_html__( 'Corso', 'cri-corsi' ),
                'value'   => esc_html( $cart_item['cri_corso_nome'] ),
                'display' => '',
            ];
        }
        if ( isset( $cart_item['cri_corso_data'] ) && ! empty( $cart_item['cri_corso_data'] ) ) {
            $item_data[] = [
                'key'     => esc_html__( 'Data Selezionata', 'cri-corsi' ),
                'value'   => esc_html( date_i18n( 'l d/m/Y', strtotime( $cart_item['cri_corso_data'] ) ) ),
                'display' => '',
            ];
        }
        if ( isset( $cart_item['cri_corso_durata'] ) && ! empty( $cart_item['cri_corso_durata'] ) ) {
            $item_data[] = [
                'key'     => esc_html__( 'Durata', 'cri-corsi' ),
                'value'   => esc_html( $cart_item['cri_corso_durata'] ),
                'display' => '',
            ];
        }
        
        // Passiamo 'cri_prenotazione_id' di nascosto per associarlo all'ordine
        if ( isset( $cart_item['cri_prenotazione_id'] ) ) {
             $item_data[] = [
                'key'     => '_cri_prenotazione_id', // Chiave meta nascosta
                'value'   => $cart_item['cri_prenotazione_id'],
                'hidden'  => true, // Nascosto all'utente nel carrello
            ];
        }

        return $item_data;
    }

    /**
     * Salva i dati personalizzati come meta dell'item nell'ordine WooCommerce.
     * (Metodo spostato da Form_Handler)
     *
     * @param \WC_Order_Item_Product $item Oggetto item dell'ordine.
     * @param string $cart_item_key Chiave dell'item nel carrello.
     * @param array $values Valori dell'item nel carrello.
     * @param \WC_Order $order Oggetto ordine.
     */
    public function save_course_data_to_order_item( \WC_Order_Item_Product $item, string $cart_item_key, array $values, \WC_Order $order ): void {
        
        // Salva i dati che abbiamo aggiunto al carrello
        if ( isset( $values['cri_prenotazione_id'] ) ) {
            // Salva l'ID prenotazione come meta *nascosto* dell'item
            $item->add_meta_data( '_cri_prenotazione_id', $values['cri_prenotazione_id'], true );
        }
        if ( isset( $values['cri_corso_nome'] ) ) {
            // Salva i dati *visibili*
            $item->add_meta_data( esc_html__( 'Corso', 'cri-corsi' ), $values['cri_corso_nome'], true );
        }
        if ( isset( $values['cri_corso_data'] ) && ! empty( $values['cri_corso_data'] ) ) {
            $item->add_meta_data( esc_html__( 'Data Selezionata', 'cri-corsi' ), esc_html( date_i18n( 'l d/m/Y', strtotime( $values['cri_corso_data'] ) ) ), true );
        }
        if ( isset( $values['cri_corso_durata'] ) && ! empty( $values['cri_corso_durata'] ) ) {
            $item->add_meta_data( esc_html__( 'Durata', 'cri-corsi' ), $values['cri_corso_durata'], true );
        }
    }

}
