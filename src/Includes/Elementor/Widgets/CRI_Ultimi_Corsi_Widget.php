<?php
/**
 * File per il widget Elementor "Ultimi Corsi CRI".
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use WP_Query;

if ( ! defined ( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Widget Elementor per mostrare gli ultimi corsi CRI inseriti.
 */
class CRI_Ultimi_Corsi_Widget extends Widget_Base {

    /**
     * Nome univoco del widget.
     * @return string
     */
    public function get_name(): string {
        return 'cri-ultimi-corsi';
    }

    /**
     * Titolo visibile nell'editor.
     * @return string
     */
    public function get_title(): string {
        return esc_html__( 'Ultimi Corsi CRI', 'cri-corsi' );
    }

    /**
     * Icona del widget.
     * @return string
     */
    public function get_icon(): string {
        return 'eicon-post-list'; // Icona diversa per distinguerlo
    }

    /**
     * Categorie del widget.
     * @return array
     */
    public function get_categories(): array {
        return [ 'cri-corsi' ]; // Sempre nella nostra categoria
    }

    /**
     * Registra i controlli del widget.
     */
    protected function register_controls(): void {
        $this->start_controls_section(
                'content_section',
                [
                        'label' => esc_html__( 'Contenuto', 'cri-corsi' ),
                        'tab'   => Controls_Manager::TAB_CONTENT,
                ]
        );

        $this->add_control(
                'number_of_courses',
                [
                        'label'   => esc_html__( 'Numero di Corsi da Mostrare', 'cri-corsi' ),
                        'type'    => Controls_Manager::NUMBER,
                        'min'     => 1,
                        'max'     => 10, // Limite ragionevole
                        'step'    => 1,
                        'default' => 3,
                ]
        );

        // **NUOVO**: Controllo per mostrare/nascondere il prezzo
        $this->add_control(
                'show_price',
                [
                        'label'        => esc_html__( 'Mostra Prezzo', 'cri-corsi' ),
                        'type'         => Controls_Manager::SWITCHER,
                        'label_on'     => esc_html__( 'Sì', 'cri-corsi' ),
                        'label_off'    => esc_html__( 'No', 'cri-corsi' ),
                        'return_value' => 'yes',
                        'default'      => 'yes', // Mostra il prezzo per default
                ]
        );

        // Aggiungere qui altri filtri se necessario (es. per destinazione)

        $this->end_controls_section();

        // Sezione Stili
        $this->start_controls_section(
                'style_section',
                [
                        'label' => esc_html__( 'Stili', 'cri-corsi' ),
                        'tab'   => Controls_Manager::TAB_STYLE,
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                        'name'     => 'title_typography',
                        'label'    => esc_html__( 'Tipografia Titolo', 'cri-corsi' ),
                        'selector' => '{{WRAPPER}} .cri-latest-course-title a',
                ]
        );
        $this->add_control(
                'title_color',
                [
                        'label'     => esc_html__( 'Colore Titolo', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-latest-course-title a' => 'color: {{VALUE}};',
                        ],
                ]
        );

        // Controlli per il prezzo, ora condizionali all'opzione show_price
        $this->add_control(
                'heading_price_style',
                [
                        'label' => esc_html__( 'Prezzo', 'cri-corsi' ),
                        'type' => Controls_Manager::HEADING,
                        'separator' => 'before',
                        'condition' => [ // Mostra solo se l'opzione è attiva
                                'show_price' => 'yes',
                        ],
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                        'name'     => 'price_typography',
                        'label'    => esc_html__( 'Tipografia', 'cri-corsi' ),
                        'selector' => '{{WRAPPER}} .cri-latest-course-price',
                        'condition' => [
                                'show_price' => 'yes',
                        ],
                ]
        );
        $this->add_control(
                'price_color',
                [
                        'label'     => esc_html__( 'Colore Prezzo', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-latest-course-price' => 'color: {{VALUE}};',
                        ],
                        'condition' => [
                                'show_price' => 'yes',
                        ],
                ]
        );
        $this->add_control(
                'price_free_color',
                [
                        'label'     => esc_html__( 'Colore "Gratuito"', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-latest-course-price.gratuito' => 'color: {{VALUE}};',
                        ],
                        'condition' => [
                                'show_price' => 'yes',
                        ],
                ]
        );


        $this->end_controls_section();
    }

    /**
     * Renderizza l'output HTML del widget.
     */
    protected function render(): void {
        $settings = $this->get_settings_for_display();
        $num_posts = $settings['number_of_courses'] ?? 3;
        $show_price = $settings['show_price'] ?? 'yes'; // Recupera l'impostazione

        // Query per gli ultimi corsi
        $query_args = [
                'post_type'      => 'cri_corso',
                'posts_per_page' => $num_posts,
                'orderby'        => 'date', // Ordina per data di pubblicazione
                'order'          => 'DESC', // Dal più recente
                'post_status'    => 'publish',
        ];

        $latest_courses_query = new WP_Query( $query_args );

        if ( $latest_courses_query->have_posts() ) :
            echo '<div class="cri-latest-courses-list">'; // Contenitore per la lista

            while ( $latest_courses_query->have_posts() ) :
                $latest_courses_query->the_post();
                global $post;

                // Recupera prezzo per logica gratuito/pagamento
                $prezzo = get_post_meta( $post->ID, '_cri_course_prezzo', true );
                $prodotto_id = get_post_meta( $post->ID, '_cri_course_prodotto_id', true );
                $is_pagamento = ! empty( $prezzo ) && ! empty( $prodotto_id ) && class_exists( 'WooCommerce' );
                $is_gratuito = ! $is_pagamento;
                ?>
                <div class="cri-latest-course-item"> <?php // Contenitore per singolo item ?>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="cri-latest-course-image">
                            <a href="<?php echo esc_url( get_permalink() ); ?>">
                                <?php the_post_thumbnail('thumbnail'); // Usa dimensione piccola ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="cri-latest-course-content">
                        <h4 class="cri-latest-course-title">
                            <a href="<?php echo esc_url( get_permalink() ); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h4>
                        <?php // **NUOVO**: Condizione per mostrare il prezzo ?>
                        <?php if ( 'yes' === $show_price ) : ?>
                            <div class="cri-latest-course-price <?php echo $is_gratuito ? 'gratuito' : ''; ?>">
                                <?php if ( $is_pagamento ) : ?>
                                    <?php echo '€ ' . esc_html( number_format_i18n( (float) $prezzo, 2 ) ); ?>
                                <?php else : ?>
                                    <?php esc_html_e( 'Gratuito', 'cri-corsi' ); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; // Fine condizione show_price ?>
                    </div>
                </div>
            <?php
            endwhile;

            echo '</div>'; // Fine .cri-latest-courses-list
            wp_reset_postdata();
        else :
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p>' . esc_html__( 'Nessun corso trovato.', 'cri-corsi' ) . '</p>';
            }
        endif;
    }
}

