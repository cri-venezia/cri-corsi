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
use Elementor\Group_Control_Typography;
use WP_Query;
// Rimosso Form_Handler perché non serve più qui
// use CRICorsi\Includes\Form_Handler;

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
                                'tutti'       => esc_html__( 'Mostra Tutti', 'cri-corsi' ),
                                'popolazione' => esc_html__( 'Solo Popolazione', 'cri-corsi' ),
                                'aziende'     => esc_html__( 'Solo Aziende', 'cri-corsi' ),
                        ],
                        'default' => 'tutti',
                ]
        );

        // Controllo Query per selezione manuale
        $this->add_control(
                'query_posts',
                [
                        'label'   => esc_html__( 'Selettore Corsi', 'cri-corsi' ),
                        'type'    => 'query',
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
                Group_Control_Typography::get_type(),
                [
                        'name'     => 'title_typography',
                        'label'    => esc_html__( 'Tipografia Titolo', 'cri-corsi' ),
                        'selector' => '{{WRAPPER}} .cri-corso-title a', // Applica al link
                ]
        );

        $this->add_control(
                'title_color',
                [
                        'label'     => esc_html__( 'Colore Titolo', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-corso-title a' => 'color: {{VALUE}}', // Applica al link
                        ],
                ]
        );

        // Aggiungi controlli per stile Estratto se necessario
        $this->add_control(
                'heading_excerpt_style',
                [
                        'label' => esc_html__( 'Estratto', 'cri-corsi' ),
                        'type' => Controls_Manager::HEADING,
                        'separator' => 'before',
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                        'name'     => 'excerpt_typography',
                        'label'    => esc_html__( 'Tipografia', 'cri-corsi' ),
                        'selector' => '{{WRAPPER}} .cri-corso-desc',
                ]
        );
        $this->add_control(
                'excerpt_color',
                [
                        'label'     => esc_html__( 'Colore Testo', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-corso-desc' => 'color: {{VALUE}}',
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

        // Rimosso: $form_handler = new Form_Handler();

        if ( $corsi_query->have_posts() ) :
            echo '<div class="cri-corsi-grid">';
            while ( $corsi_query->have_posts() ) :
                $corsi_query->the_post();
                global $post;

                // Recupera solo i meta dati necessari per la card
                $prezzo = get_post_meta( $post->ID, '_cri_course_prezzo', true );
                $prodotto_id = get_post_meta( $post->ID, '_cri_course_prodotto_id', true );
                $is_pagamento = ! empty( $prezzo ) && ! empty( $prodotto_id ) && class_exists( 'WooCommerce' );
                $is_gratuito = ! $is_pagamento;
                ?>
                <div class="cri-corso-card">

                    <!-- Immagine in Evidenza -->
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="cri-corso-image">
                            <a href="<?php echo esc_url( get_permalink() ); // Link alla pagina singola ?>">
                                <?php the_post_thumbnail( 'medium_large' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="cri-corso-content">
                        <h3 class="cri-corso-title">
                            <a href="<?php echo esc_url( get_permalink() ); // Link alla pagina singola ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>

                        <!-- Prezzo (condizionale) -->
                        <div class="cri-corso-prezzo <?php echo $is_gratuito ? 'gratuito' : ''; ?>">
                            <?php if ( $is_pagamento ) : ?>
                                <?php echo '€ ' . esc_html( number_format_i18n( (float) $prezzo, 2 ) ); ?>
                            <?php else : ?>
                                <?php esc_html_e( 'Gratuito', 'cri-corsi' ); ?>
                            <?php endif; ?>
                        </div>

                        <!-- **MODIFICATO**: Mostra Estratto -->
                        <div class="cri-corso-desc">
                            <?php the_excerpt(); ?>
                        </div>

                        <!-- **RIMOSSO**: Form di prenotazione -->
                        <!-- <div class="cri-corso-booking-form"> ... </div> -->

                    </div> <!-- .cri-corso-content -->
                </div> <!-- .cri-corso-card -->

            <?php
            endwhile;
            echo '</div>'; // .cri-corsi-grid
            wp_reset_postdata();
        endif;
    }
}
