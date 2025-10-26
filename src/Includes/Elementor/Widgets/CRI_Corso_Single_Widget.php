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
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;


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
	 * Per questo widget, potremmo non aver bisogno di molti controlli,
	 * dato che prenderà i dati dal post corrente. Aggiungiamo solo opzioni di stile.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'style_section',
			[
				'label' => esc_html__( 'Stili', 'cri-corsi' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		// Stile Titolo
		$this->add_control(
			'heading_title_style',
			[
				'label' => esc_html__( 'Titolo Principale', 'cri-corsi' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
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

		// Stile Meta (Prezzo, Destinazione)
		$this->add_control(
			'heading_meta_style',
			[
				'label' => esc_html__( 'Meta Info (Prezzo/Dest.)', 'cri-corsi' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
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
					// Selettore specifico per il prezzo gratuito
					'{{WRAPPER}} .cri-corso-single-meta .cri-corso-prezzo.gratuito' => 'color: {{VALUE}};',
				],
				'condition' => [ // Mostra solo se abbiamo definito lo stile verde nel CSS
					// Potremmo aggiungere una classe CSS base e controllare quella
				]
			]
		);


		// Stile Tabs
		// (Aggiungeremo controlli per colori, bordi, tipografia delle tab qui)

		// Stile Mappa
		// (Aggiungeremo controlli per altezza, bordi, ecc. della mappa qui)


		$this->end_controls_section();
	}

	/**
	 * Renderizza l'output HTML del widget.
	 * Prende i dati dal post corrente nel loop di WordPress.
	 */
	protected function render(): void {
		// Verifica se siamo in un singolo post valido
		if ( ! is_singular( 'cri_corso' ) ) {
			// Se non siamo su un singolo corso CRI (es. nell'editor Elementor senza preview),
			// mostra un messaggio segnaposto o non mostrare nulla.
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p>' . esc_html__( 'Widget Contenuto Singolo Corso CRI - Visualizza l\'anteprima per vedere il contenuto.', 'cri-corsi' ) . '</p>';
			}
			return;
		}

		// Recupera i dati dal post corrente
		$post_id = get_the_ID();
		$destinazione = get_post_meta( $post_id, '_cri_course_destinazione', true );
		$prezzo = get_post_meta( $post_id, '_cri_course_prezzo', true );
		$prodotto_id = get_post_meta( $post_id, '_cri_course_prodotto_id', true );
		$indirizzo = get_post_meta( $post_id, '_cri_course_indirizzo', true );
		$cosa_imparerai = get_post_meta( $post_id, '_cri_course_learn', true ) ?: ''; // Lascia vuoto se non definito
		$a_chi_rivolto = get_post_meta( $post_id, '_cri_course_target', true ) ?: ''; // Lascia vuoto se non definito

		$is_pagamento = ! empty( $prezzo ) && ! empty( $prodotto_id ) && class_exists( 'WooCommerce' );
		$is_gratuito = ! $is_pagamento;
		?>
		<article id="post-<?php echo esc_attr($post_id); ?>" <?php post_class('cri-corso-single'); ?>>

			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title cri-corso-single-title">', '</h1>' ); ?>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="cri-corso-single-image">
					<?php the_post_thumbnail( 'large' ); ?>
				</div>
			<?php endif; ?>

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

			<div class="entry-content">
				<?php the_content(); ?>
			</div>

			<?php // Mostra le tab solo se almeno uno dei campi ha contenuto ?>
			<?php if ( ! empty( $cosa_imparerai ) || ! empty( $a_chi_rivolto ) ) : ?>
				<div class="cri-corso-tabs">
					<ul class="cri-tabs-nav">
						<?php if ( ! empty( $cosa_imparerai ) ) : ?>
							<li><a href="#tab-learn" class="active"><?php esc_html_e( 'Cosa Imparerai', 'cri-corsi' ); ?></a></li>
						<?php endif; ?>
						<?php if ( ! empty( $a_chi_rivolto ) ) : ?>
							<li <?php if( empty( $cosa_imparerai ) ) echo 'class="active"'; // Rende attiva la seconda se la prima manca ?>>
								<a href="#tab-target"><?php esc_html_e( 'A Chi è Rivolto', 'cri-corsi' ); ?></a>
							</li>
						<?php endif; ?>
					</ul>
					<div class="cri-tabs-content">
						<?php if ( ! empty( $cosa_imparerai ) ) : ?>
							<div id="tab-learn" class="cri-tab-pane active">
								<?php echo wp_kses_post( $cosa_imparerai ); ?>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $a_chi_rivolto ) ) : ?>
							<div id="tab-target" class="cri-tab-pane <?php if( empty( $cosa_imparerai ) ) echo 'active'; ?>">
								<?php echo wp_kses_post( $a_chi_rivolto ); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>


			<?php if ( ! empty( $indirizzo ) ) :
				$map_id = 'cri-map-single-' . $post_id;
				?>
				<div class="cri-corso-single-map-container">
					<h2><?php esc_html_e( 'Sede del Corso', 'cri-corsi' ); ?></h2>
					<div id="<?php echo esc_attr( $map_id ); ?>" class="cri-corso-map" data-address="<?php echo esc_attr( $indirizzo ); ?>"></div>
				</div>
			<?php endif; ?>

			<footer class="entry-footer">
				<?php // Footer del post ?>
			</footer>

		</article>
		<?php
	}

	/**
	 * Renderizza il contenuto nel backend (editor Elementor).
	 * Solitamente non necessario se il widget prende dati dinamici.
	 * Mostriamo un messaggio segnaposto qui.
	 */
	protected function content_template() {
		?>
		<# // Template JavaScript per l'editor (non strettamente necessario qui) #>
		<p><?php esc_html_e( 'Contenuto Singolo Corso CRI - Visualizza l\'anteprima.', 'cri-corsi' ); ?></p>
		<?php
	}
}
