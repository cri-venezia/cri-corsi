<?php
/**
 * Template per la visualizzazione della pagina singola del Custom Post Type 'cri_corso'.
 *
 * Questo file va posizionato nella cartella del tema attivo (es. /wp-content/themes/nome-tema/).
 * WordPress lo userà automaticamente per mostrare i singoli corsi.
 *
 * CONSIGLIO: Copia questo file dalla cartella /wp-content/plugins/cri-corsi/templates/theme-override/
 * alla cartella principale del tuo tema attivo.
 *
 * @package Nome_Tuo_Tema
 */

get_header(); // Carica l'header del tema
?>

<div id="primary" class="content-area cri-corso-single-page">
	<main id="main" class="site-main">

		<?php
		// Inizia il loop di WordPress
		while ( have_posts() ) :
			the_post();

			// Recupera i nostri metadati personalizzati
			$destinazione = get_post_meta( get_the_ID(), '_cri_course_destinazione', true );
			$prezzo = get_post_meta( get_the_ID(), '_cri_course_prezzo', true );
			$prodotto_id = get_post_meta( get_the_ID(), '_cri_course_prodotto_id', true );
			$indirizzo = get_post_meta( get_the_ID(), '_cri_course_indirizzo', true ); // Recupera indirizzo per mappa
			// **NUOVO (Placeholder):** Recupera contenuto per le tab (dovremo aggiungere questi meta)
			$cosa_imparerai = get_post_meta( get_the_ID(), '_cri_course_learn', true ) ?: 'Contenuto per "Cosa imparerai" da definire...';
			$a_chi_rivolto = get_post_meta( get_the_ID(), '_cri_course_target', true ) ?: 'Contenuto per "A chi è rivolto" da definire...';

			$is_pagamento = ! empty( $prezzo ) && ! empty( $prodotto_id ) && class_exists( 'WooCommerce' );
			$is_gratuito = ! $is_pagamento;

			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class('cri-corso-single'); ?>>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title cri-corso-single-title">', '</h1>' ); ?>
				</header><!-- .entry-header -->

				<!-- Mostra Immagine in Evidenza -->
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="cri-corso-single-image">
						<?php the_post_thumbnail( 'large' ); // Aggiungeremo border-radius via CSS ?>
					</div>
				<?php endif; ?>

				<!-- Meta Dati (Spostati Sotto l'Immagine) -->
				<div class="cri-corso-single-meta">
					<!-- Mostra Destinazione -->
					<?php if ( ! empty( $destinazione ) ) : ?>
						<span class="cri-corso-destinazione">
                            <strong><?php esc_html_e( 'Destinato a:', 'cri-corsi' ); ?></strong>
                            <?php echo esc_html( $destinazione === 'aziende' ? __( 'Aziende', 'cri-corsi' ) : __( 'Popolazione', 'cri-corsi' ) ); ?>
                        </span>
					<?php endif; ?>

					<!-- Mostra Prezzo -->
					<span class="cri-corso-prezzo <?php echo $is_gratuito ? 'gratuito' : ''; // Classe per stile verde ?>">
                        <strong><?php esc_html_e( 'Prezzo:', 'cri-corsi' ); ?></strong>
                        <?php if ( $is_pagamento ) : ?>
	                        <?php echo '€ ' . esc_html( number_format_i18n( (float) $prezzo, 2 ) ); ?>
                        <?php else : ?>
	                        <?php esc_html_e( 'Gratuito', 'cri-corsi' ); ?>
                        <?php endif; ?>
                    </span>
				</div>

				<!-- Contenuto Principale -->
				<div class="entry-content">
					<?php
					// Mostra la descrizione principale inserita nell'editor
					the_content();
					?>
				</div><!-- .entry-content -->

				<!-- **NUOVO**: Struttura per le Tab -->
				<div class="cri-corso-tabs">
					<ul class="cri-tabs-nav">
						<li><a href="#tab-learn" class="active"><?php esc_html_e( 'Cosa Imparerai', 'cri-corsi' ); ?></a></li>
						<li><a href="#tab-target"><?php esc_html_e( 'A Chi è Rivolto', 'cri-corsi' ); ?></a></li>
					</ul>
					<div class="cri-tabs-content">
						<div id="tab-learn" class="cri-tab-pane active">
							<?php echo wp_kses_post( $cosa_imparerai ); // Usiamo wp_kses_post se il campo è WYSIWYG ?>
						</div>
						<div id="tab-target" class="cri-tab-pane">
							<?php echo wp_kses_post( $a_chi_rivolto ); ?>
						</div>
					</div>
				</div>

				<!-- Mappa Leaflet -->
				<?php if ( ! empty( $indirizzo ) ) :
					$map_id = 'cri-map-single-' . get_the_ID();
					?>
					<div class="cri-corso-single-map-container">
						<h2><?php esc_html_e( 'Sede del Corso', 'cri-corsi' ); ?></h2>
						<div id="<?php echo esc_attr( $map_id ); ?>" class="cri-corso-map" data-address="<?php echo esc_attr( $indirizzo ); ?>"></div>
					</div>
				<?php endif; ?>


				<footer class="entry-footer">
					<?php // Potresti voler mostrare qui categorie o tag se li usi ?>
				</footer><!-- .entry-footer -->

			</article><!-- #post-<?php the_ID(); ?> -->

		<?php
			// Se vuoi aggiungere la navigazione tra corsi (precedente/successivo)
			// the_post_navigation();

		endwhile; // Fine del loop.
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer(); // Carica il footer del tema
?>

