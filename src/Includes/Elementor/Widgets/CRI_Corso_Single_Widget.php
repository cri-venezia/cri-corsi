<?php
/**
 * File per il widget Elementor del Contenuto Singolo Corso CRI.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Utils; // Per background image
use CRICorsi\Includes\Form_Handler;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Widget Elementor per mostrare il contenuto dettagliato di un singolo corso CRI.
 * Questo widget è pensato per essere usato nei template "Singolo Post" di Elementor Pro.
 */
class CRI_Corso_Single_Widget extends Widget_Base {

    /**
     * Nome univoco del widget.
     * @return string
     */
    public function get_name(): string {
        return 'cri-corso-singolo-contenuto';
    }

    /**
     * Titolo visibile nell'editor.
     * @return string
     */
    public function get_title(): string {
        return esc_html__( 'Contenuto Singolo Corso CRI', 'cri-corsi' );
    }

    /**
     * Icona del widget.
     * @return string
     */
    public function get_icon(): string {
        return 'eicon-post-content';
    }

    /**
     * Categorie del widget.
     * @return array
     */
    public function get_categories(): array {
        return [ 'cri-corsi' ]; // Lo mettiamo nella nostra categoria
    }

    /**
     * Registra i controlli (opzioni) del widget.
     */
    protected function register_controls(): void {
        $this->start_controls_section(
                'style_section',
                [
                        'label' => esc_html__( 'Stili', 'cri-corsi' ),
                        'tab'   => Controls_Manager::TAB_STYLE,
                ]
        );

        // --- Stile Titolo ---
        $this->add_control(
                'heading_title_style',
                [
                        'label' => esc_html__( 'Titolo Principale', 'cri-corsi' ),
                        'type' => Controls_Manager::HEADING,
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                        'name'     => 'title_typography',
                        'label'    => esc_html__( 'Tipografia', 'cri-corsi' ),
                        'selector' => '{{WRAPPER}} .cri-corso-single-title',
                ]
        );
        $this->add_control(
                'title_color',
                [
                        'label'     => esc_html__( 'Colore', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-corso-single-title' => 'color: {{VALUE}}',
                        ],
                ]
        );

        // --- Stile Immagine ---
        $this->add_control(
                'heading_image_style',
                [
                        'label' => esc_html__( 'Immagine in Evidenza', 'cri-corsi' ),
                        'type' => Controls_Manager::HEADING,
                        'separator' => 'before',
                ]
        );
        $this->add_control(
                'image_display', // **NUOVO**
                [
                        'label' => esc_html__( 'Visualizza Come', 'cri-corsi' ),
                        'type' => Controls_Manager::SELECT,
                        'options' => [
                                'default' => esc_html__( 'Immagine Standard', 'cri-corsi' ),
                                'background' => esc_html__( 'Sfondo Contenitore', 'cri-corsi' ),
                        ],
                        'default' => 'default',
                        'prefix_class' => 'cri-image-display-', // Aggiunge classe al wrapper del widget
                ]
        );
        $this->add_responsive_control(
                'image_bg_height', // **NUOVO**
                [
                        'label' => esc_html__( 'Altezza Sfondo', 'cri-corsi' ),
                        'type' => Controls_Manager::SLIDER,
                        'size_units' => [ 'px', 'vh' ],
                        'range' => [
                                'px' => [ 'min' => 100, 'max' => 1000 ],
                                'vh' => [ 'min' => 10, 'max' => 100 ],
                        ],
                        'default' => [ 'unit' => 'px', 'size' => 300 ],
                        'selectors' => [
                                '{{WRAPPER}}.cri-image-display-background .cri-corso-single-image-bg' => 'height: {{SIZE}}{{UNIT}};',
                        ],
                        'condition' => [
                                'image_display' => 'background',
                        ],
                ]
        );
        $this->add_control(
                'image_border_radius',
                [
                        'label' => esc_html__( 'Raggio Bordo Immagine', 'cri-corsi' ),
                        'type' => Controls_Manager::SLIDER,
                        'size_units' => [ 'px', '%' ],
                        'range' => [
                                'px' => [ 'min' => 0, 'max' => 100 ],
                                '%' => [ 'min' => 0, 'max' => 50 ],
                        ],
                        'selectors' => [
                            // Selettore per immagine standard
                                '{{WRAPPER}}.cri-image-display-default .elementor .cri-corso-single-image img' => 'border-radius: {{SIZE}}{{UNIT}} !important;',
                                '{{WRAPPER}}.cri-image-display-default .cri-corso-single-image img' => 'border-radius: {{SIZE}}{{UNIT}} !important;',
                            // Selettore per contenitore sfondo
                                '{{WRAPPER}}.cri-image-display-background .cri-corso-single-image-bg' => 'border-radius: {{SIZE}}{{UNIT}};',
                        ],
                    // Nota: `!important` è meno necessario quando lo stile è controllato da Elementor
                ]
        );

        // --- Stile Meta Info ---
        $this->add_control(
                'heading_meta_style',
                [
                        'label' => esc_html__( 'Meta Info (Prezzo/Dest.)', 'cri-corsi' ),
                        'type' => Controls_Manager::HEADING,
                        'separator' => 'before',
                ]
        );
        $this->add_control(
                'meta_position', // **NUOVO**
                [
                        'label' => esc_html__( 'Posizione Meta', 'cri-corsi' ),
                        'type' => Controls_Manager::SWITCHER,
                        'label_on' => esc_html__( 'Dopo Img', 'cri-corsi' ),
                        'label_off' => esc_html__( 'Prima Img', 'cri-corsi' ),
                        'return_value' => 'after_image',
                        'default' => 'after_image', // Default: meta dopo l'immagine
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                        'name'     => 'meta_typography',
                        'label'    => esc_html__( 'Tipografia', 'cri-corsi' ),
                        'selector' => '{{WRAPPER}} .cri-corso-single-meta span',
                ]
        );
        $this->add_control(
                'meta_color',
                [
                        'label'     => esc_html__( 'Colore Testo', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-corso-single-meta span' => 'color: {{VALUE}}',
                        ],
                ]
        );
        $this->add_control(
                'meta_accent_color',
                [
                        'label'     => esc_html__( 'Colore "Gratuito"', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-corso-single-meta .cri-corso-prezzo.gratuito' => 'color: {{VALUE}};',
                        ],
                ]
        );

        // --- Stile Box Contenuto ---
        $this->add_control(
                'heading_box_style',
                [
                        'label' => esc_html__( 'Box Contenuto', 'cri-corsi' ),
                        'type' => Controls_Manager::HEADING,
                        'separator' => 'before',
                ]
        );
        $this->add_group_control(
                Group_Control_Box_Shadow::get_type(),
                [
                        'name' => 'content_box_shadow',
                        'label' => esc_html__( 'Ombra Box', 'cri-corsi' ),
                        'selector' => '{{WRAPPER}} .cri-content-box',
                ]
        );
        // Aggiungere qui controlli per padding, bordo, sfondo dei box se necessario

        // --- Stile Titoli Sezioni (Mappa, Form) ---
        $this->add_control(
                'heading_section_titles_style',
                [
                        'label' => esc_html__( 'Titoli Sezioni (Mappa, Form)', 'cri-corsi' ),
                        'type' => Controls_Manager::HEADING,
                        'separator' => 'before',
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                        'name'     => 'section_title_typography',
                        'label'    => esc_html__( 'Tipografia', 'cri-corsi' ),
                        'selector' => '{{WRAPPER}} .cri-corso-single-map-container h2, {{WRAPPER}} .cri-corso-booking-form h2',
                ]
        );
        $this->add_control(
                'heading_map_color', // **NUOVO**
                [
                        'label'     => esc_html__( 'Colore Titolo Mappa', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-corso-single-map-container h2' => 'color: {{VALUE}}',
                        ],
                ]
        );
        $this->add_control(
                'heading_form_color', // **NUOVO**
                [
                        'label'     => esc_html__( 'Colore Titolo Form', 'cri-corsi' ),
                        'type'      => Controls_Manager::COLOR,
                        'selectors' => [
                                '{{WRAPPER}} .cri-corso-booking-form h2' => 'color: {{VALUE}}',
                        ],
                ]
        );


        $this->end_controls_section();
    }

    /**
     * Renderizza l'output HTML del widget.
     * Prende i dati dal post corrente nel loop di WordPress.
     */
    protected function render(): void {
        $settings = $this->get_settings_for_display();

        if ( ! is_singular( 'cri_corso' ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<p>' . esc_html__( 'Widget Contenuto Singolo Corso CRI - Visualizza l\'anteprima per vedere il contenuto.', 'cri-corsi' ) . '</p>';
            }
            return;
        }

        // Recupera i dati
        $post_id = get_the_ID();
        $destinazione = get_post_meta( $post_id, '_cri_course_destinazione', true );
        $prezzo = get_post_meta( $post_id, '_cri_course_prezzo', true );
        $prodotto_id = get_post_meta( $post_id, '_cri_course_prodotto_id', true );
        $indirizzo = get_post_meta( $post_id, '_cri_course_indirizzo', true );
        $date_orari = get_post_meta( $post_id, '_cri_course_date_orari', true );
        $cosa_imparerai = get_post_meta( $post_id, '_cri_course_learn', true ) ?: '';
        $a_chi_rivolto = get_post_meta( $post_id, '_cri_course_target', true ) ?: '';
        $featured_img_url = get_the_post_thumbnail_url( $post_id, 'large' ); // URL per sfondo

        $is_pagamento = ! empty( $prezzo ) && ! empty( $prodotto_id ) && class_exists( 'WooCommerce' );
        $is_gratuito = ! $is_pagamento;
        $form_handler = new Form_Handler();
        if ( ! is_array( $date_orari ) ) $date_orari = [];

        // Funzione helper per renderizzare i meta
        $render_meta = function() use ($destinazione, $is_gratuito, $is_pagamento, $prezzo) {
            ?>
            <div class="cri-corso-single-meta">
                <?php if ( ! empty( $destinazione ) ) : ?>
                    <span class="cri-corso-destinazione">
                        <strong><?php esc_html_e( 'Destinato a:', 'cri-corsi' ); ?></strong>
                        <?php echo esc_html( $destinazione === 'aziende' ? __( 'Aziende', 'cri-corsi' ) : __( 'Popolazione', 'cri-corsi' ) ); ?>
                    </span>
                <?php endif; ?>
                <span class="cri-corso-prezzo <?php echo $is_gratuito ? 'gratuito' : ''; ?>">
                    <strong><?php esc_html_e( 'Prezzo:', 'cri-corsi' ); ?></strong>
                    <?php if ( $is_pagamento ) : ?>
                        <?php echo '€ ' . esc_html( number_format_i18n( (float) $prezzo, 2 ) ); ?>
                    <?php else : ?>
                        <?php esc_html_e( 'Gratuito', 'cri-corsi' ); ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php
        };

        // Funzione helper per renderizzare l'immagine
        $render_image = function() use ($settings, $featured_img_url) {
            if ( has_post_thumbnail() ) {
                if ( $settings['image_display'] === 'background' ) {
                    // **NUOVO**: Immagine come sfondo
                    printf(
                            '<div class="cri-corso-single-image-bg" style="background-image: url(%s);"></div>',
                            esc_url( $featured_img_url )
                    );
                } else {
                    // Immagine standard
                    echo '<div class="cri-corso-single-image">';
                    the_post_thumbnail( 'large' );
                    echo '</div>';
                }
            }
        };

        ?>
        <article id="post-<?php echo esc_attr($post_id); ?>" <?php post_class('cri-corso-single'); ?>>

            <header class="entry-header">
                <?php the_title( '<h1 class="entry-title cri-corso-single-title">', '</h1>' ); ?>
            </header>

            <?php
            // **NUOVO**: Logica Posizione Meta/Immagine
            if ( $settings['meta_position'] !== 'after_image' ) {
                $render_meta(); // Mostra meta prima
            }
            $render_image(); // Mostra immagine
            if ( $settings['meta_position'] === 'after_image' ) {
                $render_meta(); // Mostra meta dopo
            }
            ?>

            <div class="entry-content cri-content-box">
                <?php the_content(); ?>
            </div>

            <?php if ( ! empty( $cosa_imparerai ) || ! empty( $a_chi_rivolto ) ) : ?>
                <div class="cri-corso-tabs cri-content-box">
                    <ul class="cri-tabs-nav">
                        <?php if ( ! empty( $cosa_imparerai ) ) : ?><li><a href="#tab-learn" class="active"><?php esc_html_e( 'Cosa Imparerai', 'cri-corsi' ); ?></a></li><?php endif; ?>
                        <?php if ( ! empty( $a_chi_rivolto ) ) : ?><li <?php if( empty( $cosa_imparerai ) ) echo 'class="active"'; ?>><a href="#tab-target"><?php esc_html_e( 'A Chi è Rivolto', 'cri-corsi' ); ?></a></li><?php endif; ?>
                    </ul>
                    <div class="cri-tabs-content">
                        <?php if ( ! empty( $cosa_imparerai ) ) : ?><div id="tab-learn" class="cri-tab-pane active"><?php echo wp_kses_post( $cosa_imparerai ); ?></div><?php endif; ?>
                        <?php if ( ! empty( $a_chi_rivolto ) ) : ?><div id="tab-target" class="cri-tab-pane <?php if( empty( $cosa_imparerai ) ) echo 'active'; ?>"><?php echo wp_kses_post( $a_chi_rivolto ); ?></div><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>


            <?php if ( ! empty( $indirizzo ) ) :
                $map_id = 'cri-map-single-' . $post_id;
                ?>
                <div class="cri-corso-single-map-container cri-content-box">
                    <h2 class="cri-map-heading"><?php esc_html_e( 'Sede del Corso', 'cri-corsi' ); ?></h2>
                    <div id="<?php echo esc_attr( $map_id ); ?>" class="cri-corso-map" data-address="<?php echo esc_attr( $indirizzo ); ?>"></div>
                </div>
            <?php endif; ?>

            <div class="cri-corso-booking-form cri-content-box">
                <h2 class="cri-form-heading"><?php esc_html_e( 'Iscriviti al Corso', 'cri-corsi' ); ?></h2>
                <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                    <input type="hidden" name="action" value="cri_prenota_corso">
                    <input type="hidden" name="corso_id" value="<?php echo esc_attr( $post_id ); ?>">
                    <?php if ( $is_pagamento ) : ?><input type="hidden" name="prodotto_id" value="<?php echo esc_attr( $prodotto_id ); ?>"><?php endif; ?>
                    <?php wp_nonce_field( 'cri_corso_booking_form', 'cri_corso_nonce' ); ?>

                    <div class="cri-form-group">
                        <label for="corso_scelta_<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Seleziona data e durata:', 'cri-corsi' ); ?></label>
                        <select name="corso_scelta" id="corso_scelta_<?php echo esc_attr( $post_id ); ?>" required>
                            <option value=""><?php esc_html_e( 'Scegli un\'opzione...', 'cri-corsi' ); ?></option>
                            <?php foreach ( $date_orari as $index => $orario ) :
                                $data_corso = $orario['data_corso'] ?? '';
                                $durata = $orario['durata_corso'] ?? '';
                                $posti_totali = (int) ( $orario['posti_disponibili'] ?? 0 );
                                $posti_occupati = $form_handler->count_booked_seats( $post_id, $index );
                                $is_full = $posti_occupati >= $posti_totali;
                                $label = sprintf( '%s (%s)', date_i18n( 'l d/m/Y', strtotime( $data_corso ) ), esc_html($durata) );
                                ?>
                                <option value="<?php echo esc_attr( $index ); ?>" <?php disabled( $is_full, true ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                    <?php if ( $is_full ) echo esc_html__( ' (Completo)', 'cri-corsi' ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cri-form-group">
                        <label for="nome_<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Nome', 'cri-corsi' ); ?></label>
                        <input type="text" name="nome" id="nome_<?php echo esc_attr( $post_id ); ?>" required>
                    </div>
                    <div class="cri-form-group">
                        <label for="cognome_<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Cognome', 'cri-corsi' ); ?></label>
                        <input type="text" name="cognome" id="cognome_<?php echo esc_attr( $post_id ); ?>" required>
                    </div>
                    <div class="cri-form-group">
                        <label for="email_<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Email', 'cri-corsi' ); ?></label>
                        <input type="email" name="email" id="email_<?php echo esc_attr( $post_id ); ?>" required>
                    </div>
                    <div class="cri-form-group">
                        <label for="telefono_<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Telefono', 'cri-corsi' ); ?></label>
                        <input type="tel" name="telefono" id="telefono_<?php echo esc_attr( $post_id ); ?>">
                    </div>

                    <?php if ( 'aziende' === $destinazione ) : ?>
                        <div class="cri-form-group">
                            <label for="ragione_sociale_<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Ragione Sociale', 'cri-corsi' ); ?></label>
                            <input type="text" name="ragione_sociale" id="ragione_sociale_<?php echo esc_attr( $post_id ); ?>" required>
                        </div>
                        <div class="cri-form-group">
                            <label for="partita_iva_<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Partita IVA', 'cri-corsi' ); ?></label>
                            <input type="text" name="partita_iva" id="partita_iva_<?php echo esc_attr( $post_id ); ?>" required>
                        </div>
                    <?php endif; ?>

                    <div class="cri-form-submit">
                        <button type="submit" class="cri-corso-button">
                            <?php echo $is_pagamento ? esc_html__( 'Iscriviti e Paga', 'cri-corsi' ) : esc_html__( 'Prenota Ora', 'cri-corsi' ); ?>
                        </button>
                    </div>
                </form>
            </div>


            <footer class="entry-footer">
                <?php // Footer del post ?>
            </footer>

        </article>
        <?php
    }

    /**
     * Renderizza il contenuto nel backend (editor Elementor).
     */
    protected function content_template() {
        ?>
        <p><?php esc_html_e( 'Contenuto Singolo Corso CRI - Questo widget mostra dinamicamente il contenuto del corso corrente. Visualizza l\'anteprima per vederlo in azione.', 'cri-corsi' ); ?></p>
        <?php
    }
}

