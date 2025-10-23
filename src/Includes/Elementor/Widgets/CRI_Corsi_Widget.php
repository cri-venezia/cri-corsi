<?php
/**
 * File per il widget Elementor della Griglia Corsi CRI.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Elementor\Widgets;

// Importiamo tutte le classi necessarie
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography; // CLASSE CORRETTA
use WP_Query;
use CRICorsi\Includes\Form_Handler; // Importiamo il nostro Form Handler

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Widget Elementor per mostrare la griglia dei corsi CRI.
 */
class CRI_Corsi_Widget extends Widget_Base {

    /**
     * Restituisce il nome univoco del widget.
     * @return string
     */
    public function get_name(): string {
        return 'cri-corsi-griglia';
    }

    /**
     * Restituisce il titolo del widget visibile nell'editor.
     * @return string
     */
    public function get_title(): string {
        return esc_html__( 'Griglia Corsi CRI', 'cri-corsi' );
    }

    /**
     * Restituisce l'icona del widget.
     * @return string
     */
    public function get_icon(): string {
        return 'eicon-posts-grid';
    }

    /**
     * Restituisce le categorie a cui appartiene il widget.
     * @return array
     */
    public function get_categories(): array {
        return [ 'cri-corsi' ];
    }

    /**
     * Registra i controlli del widget (opzioni nel pannello Elementor).
     */
    protected function register_controls(): void {
        $this->start_controls_section(
                'content_section',
                [
                        'label' => esc_html__( 'Contenuto', 'cri-corsi' ),
                        'tab'   => Controls_Manager::TAB_CONTENT,
                ]
        );

        // Controllo Filtro Destinazione
        $this->add_control(
                'destinazione',
                [
                        'label'   => esc_html__( 'Filtra per Destinazione', 'cri-corsi' ),
                        'type'    => Controls_Manager::SELECT,
                        'options' => [
                                'tutti'      => esc_html__( 'Mostra Tutti', 'cri-corsi' ),
                                'popolazione' => esc_html__( 'Solo Popolazione', 'cri-corsi' ),
                                'aziende'    => esc_html__( 'Solo Aziende', 'cri-corsi' ),
                        ],
                        'default' => 'tutti',
                ]
        );

        // Controllo Query per selezione manuale
        $this->add_control(
                'query_posts',
                [
                        'label'   => esc_html__( 'Selettore Corsi', 'cri-corsi' ),
                        'type'    => 'query', // Tipo di controllo corretto
                        'options' => [
                                'show_label' => false,
                        ],
                        'default' => [
                                'post_type' => 'cri_corso',
                        ],
                        'query' => [
                                'post_type' => 'cri_corso',
                        ],
                ]
        );

        $this->end_controls_section();

        // Sezione Stili
        $this->start_controls_section(
                'style_section',
                [
                        'label' => esc_html__( 'Stili Card', 'cri-corsi' ),
                        'tab'   => Controls_Manager::TAB_STYLE,
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(), // CORRETTO
                [
                        'name'     => 'title_typography',
                        'label'    => esc_html__( 'Tipografia Titolo', 'cri-corsi' ),
                        'selector' => '{{WRAPPER}} .cri-corso-title',
                ]
        );

        $this->add_control(
                'title_color',
                [
                        'label'     => esc_html__( 'Colore Titolo', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-corso-title' => 'color: {{VALUE}}',
                        ],
                ]
        );

        $this->end_controls_section();
    }

    /**
     * Renderizza l'output HTML del widget sul frontend.
     */
    protected function render(): void {
        $settings = $this->get_settings_for_display();

        // Costruiamo la query
        $query_args = [
                'post_type'      => 'cri_corso',
                'posts_per_page' => $settings['query_posts']['posts_per_page'] ?? 10,
                'post_status'    => 'publish',
        ];

        // Filtro per destinazione
        if ( ! empty( $settings['destinazione'] ) && 'tutti' !== $settings['destinazione'] ) {
            $query_args['meta_query'][] = [
                    'key'   => '_cri_course_destinazione',
                    'value' => $settings['destinazione'],
            ];
        }

        // Filtro da selezione manuale (dalla query control)
        if ( ! empty( $settings['query_posts']['post__in'] ) ) {
            $query_args['post__in'] = $settings['query_posts']['post__in'];
        }

        // Ordine (dalla query control)
        if ( ! empty( $settings['query_posts']['orderby'] ) ) {
            $query_args['orderby'] = $settings['query_posts']['orderby'];
            $query_args['order'] = $settings['query_posts']['order'] ?? 'ASC';
        }

        $corsi_query = new WP_Query( $query_args );

        // Creiamo un'istanza del Form_Handler per accedere ai suoi metodi
        $form_handler = new Form_Handler();

        if ( $corsi_query->have_posts() ) :
            echo '<div class="cri-corsi-grid">';
            while ( $corsi_query->have_posts() ) :
                $corsi_query->the_post();
                global $post;

                // Recupera tutti i meta dati
                $date_orari = get_post_meta( $post->ID, '_cri_course_date_orari', true );
                $destinazione = get_post_meta( $post->ID, '_cri_course_destinazione', true );
                $prezzo = get_post_meta( $post->ID, '_cri_course_prezzo', true );
                $indirizzo = get_post_meta( $post->ID, '_cri_course_indirizzo', true );
                $prodotto_id = get_post_meta( $post->ID, '_cri_course_prodotto_id', true );

                // Logica per determinare se il corso è a pagamento
                $is_pagamento = ! empty( $prezzo ) && ! empty( $prodotto_id ) && class_exists( 'WooCommerce' );

                if ( ! is_array( $date_orari ) ) {
                    $date_orari = [];
                }
                ?>
                <div class="cri-corso-card">

                    <!-- Immagine in Evidenza -->
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="cri-corso-image">
                            <?php the_post_thumbnail( 'medium_large' ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="cri-corso-content">
                        <h3 class="cri-corso-title"><?php the_title(); ?></h3>

                        <!-- Prezzo (condizionale) -->
                        <div class="cri-corso-prezzo">
                            <?php if ( $is_pagamento ) : ?>
                                <?php echo '€ ' . esc_html( number_format_i18n( (float) $prezzo, 2 ) ); ?>
                            <?php else : ?>
                                <?php esc_html_e( 'Gratuito', 'cri-corsi' ); ?>
                            <?php endif; ?>
                        </div>

                        <!-- Mappa (condizionale) - REFACTORED -->
                        <?php if ( ! empty( $indirizzo ) ) :
                            $map_id = 'cri-map-' . $post->ID;
                            ?>
                            <div id="<?php echo esc_attr( $map_id ); ?>" class="cri-corso-map" data-address="<?php echo esc_attr( $indirizzo ); ?>"></div>
                        <?php endif; ?>

                        <div class="cri-corso-desc">
                            <?php the_excerpt(); ?>
                        </div>

                        <div class="cri-corso-booking-form">
                            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                                <!-- Campi nascosti -->
                                <input type="hidden" name="action" value="cri_prenota_corso">
                                <input type="hidden" name="corso_id" value="<?php echo esc_attr( $post->ID ); ?>">
                                <?php if ( $is_pagamento ) : ?>
                                    <input type="hidden" name="prodotto_id" value="<?php echo esc_attr( $prodotto_id ); ?>">
                                <?php endif; ?>
                                <?php wp_nonce_field( 'cri_corso_booking_form', 'cri_corso_nonce' ); ?>

                                <!-- Selettore Data/Durata -->
                                <div class="cri-form-group">
                                    <label for="corso_scelta_<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Seleziona data e durata:', 'cri-corsi' ); ?></label>
                                    <select name="corso_scelta" id="corso_scelta_<?php echo esc_attr( $post->ID ); ?>" required>
                                        <option value=""><?php esc_html_e( 'Scegli un\'opzione...', 'cri-corsi' ); ?></option>
                                        <?php
                                        foreach ( $date_orari as $index => $orario ) :
                                            $data_corso = $orario['data_corso'] ?? '';
                                            $durata = $orario['durata_corso'] ?? ''; // <-- NUOVO CAMPO
                                            $posti_totali = (int) ( $orario['posti_disponibili'] ?? 0 );

                                            // MODIFICA CHIAVE: Chiamiamo il metodo del Form_Handler
                                            $posti_occupati = $form_handler->count_booked_seats( $post->ID, $index );

                                            $is_full = $posti_occupati >= $posti_totali;

                                            // Etichetta aggiornata
                                            $label = sprintf( '%s (%s)',
                                                    date_i18n( 'l d/m/Y', strtotime( $data_corso ) ),
                                                    esc_html( $durata ) // Mostra la durata
                                            );
                                            ?>
                                            <option value="<?php echo esc_attr( $index ); ?>" <?php disabled( $is_full, true ); ?>>
                                                <?php echo esc_html( $label ); ?>
                                                <?php if ( $is_full ) echo esc_html__( ' (Completo)', 'cri-corsi' ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Campi Anagrafici -->
                                <div class="cri-form-group">
                                    <label for="nome_<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Nome', 'cri-corsi' ); ?></label>
                                    <input type="text" name="nome" id="nome_<?php echo esc_attr( $post->ID ); ?>" required>
                                </div>
                                <div class="cri-form-group">
                                    <label for="cognome_<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Cognome', 'cri-corsi' ); ?></label>
                                    <input type="text" name="cognome" id="cognome_<?php echo esc_attr( $post->ID ); ?>" required>
                                </div>
                                <div class="cri-form-group">
                                    <label for="email_<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Email', 'cri-corsi' ); ?></label>
                                    <input type="email" name="email" id="email_<?php echo esc_attr( $post->ID ); ?>" required>
                                </div>
                                <div class="cri-form-group">
                                    <label for="telefono_<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Telefono (opzionale)', 'cri-corsi' ); ?></label>
                                    <input type="tel" name="telefono" id="telefono_<?php echo esc_attr( $post->ID ); ?>">
                                </div>

                                <!-- Campi Aziendali Condizionali -->
                                <?php if ( 'aziende' === $destinazione ) : ?>
                                    <div class="cri-form-group">
                                        <label for="ragione_sociale_<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Ragione Sociale', 'cri-corsi' ); ?></label>
                                        <input type="text" name="ragione_sociale" id="ragione_sociale_<?php echo esc_attr( $post->ID ); ?>" required>
                                    </div>
                                    <div class="cri-form-group">
                                        <label for="partita_iva_<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Partita IVA', 'cri-corsi' ); ?></label>
                                        <input type="text" name="partita_iva" id="partita_iva_<?php echo esc_attr( $post->ID ); ?>" required>
                                    </div>
                                <?php endif; ?>

                                <!-- Pulsante Submit -->
                                <div class="cri-form-submit">
                                    <button type="submit" class="cri-corso-button">
                                        <?php
                                        if ( $is_pagamento ) {
                                            esc_html_e( 'Iscriviti e Paga', 'cri-corsi' );
                                        } else {
                                            esc_html_e( 'Prenota Ora', 'cri-corsi' );
                                        }
                                        ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> <!-- .cri-corso-card -->

            <?php
            endwhile;
            echo '</div>'; // .cri-corsi-grid
            wp_reset_postdata();
        endif;
    }
}

