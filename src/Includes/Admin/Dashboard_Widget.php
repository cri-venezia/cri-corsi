<?php
/**
 * Aggiunge un widget alla bacheca di WordPress.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Admin;

use CRICorsi\Includes\User_Roles;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe Dashboard_Widget
 * Aggiunge un widget riepilogativo alla bacheca principale di WordPress.
 */
class Dashboard_Widget {

    /**
     * Costruttore. Aggancia l'azione per registrare il widget.
     */
    public function __construct() {
        // Aggiungi il widget solo per utenti che possono gestire le date (Docenti e Admin)
        // L'hook 'init' è troppo presto, usiamo 'wp_loaded' per assicurarci che l'utente sia definito
        add_action( 'wp_loaded', [ $this, 'maybe_add_dashboard_widget' ] );
    }

    /**
     * Controlla i permessi e aggiunge l'hook per il widget.
     */
    public function maybe_add_dashboard_widget(): void {
        // Aggiungi il widget solo se l'utente è loggato e ha la nostra capability
        if ( is_user_logged_in() && current_user_can( User_Roles::CAPABILITY ) ) {
            add_action( 'wp_dashboard_setup', [ $this, 'register_widget' ] );
        }
    }


    /**
     * Registra il widget nella bacheca di WordPress.
     */
    public function register_widget(): void {
        wp_add_dashboard_widget(
            'cri_corsi_dashboard_widget', // Slug
            esc_html__( 'Panoramica CRI Corsi', 'cri-corsi' ), // Titolo
            [ $this, 'render_widget_content' ] // Callback per il contenuto
        );
    }

    /**
     * Renderizza il contenuto HTML del widget.
     */
    public function render_widget_content(): void {
        echo '<div class="cri-dashboard-widget">';

        // 1. Ultimi corsi inseriti
        echo '<h4>' . esc_html__( 'Ultimi Corsi Pubblicati', 'cri-corsi' ) . '</h4>';
        $query = new WP_Query( [
            'post_type'      => 'cri_corso',
            'posts_per_page' => 5, // Mostra gli ultimi 5
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        ] );

        if ( $query->have_posts() ) {
            echo '<ul style="margin-top: 0;">';
            while ( $query->have_posts() ) {
                $query->the_post();
                $edit_link = get_edit_post_link( get_the_ID() );
                echo '<li style="margin-bottom: 5px;"><a href="' . esc_url( $edit_link ) . '">' . esc_html( get_the_title() ) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>' . esc_html__( 'Nessun corso pubblicato.', 'cri-corsi' ) . '</p>';
        }
        wp_reset_postdata();

        // 2. Link Rapidi (Gestione Date e Documentazione)
        echo '<hr style="margin: 15px 0;">';
        echo '<p class="cri-dashboard-links" style="display: flex; justify-content: space-around; gap: 10px; margin-bottom: 15px;">';
        // Mostra il link "Gestione Date"
        echo '<a href="' . esc_url( admin_url( 'admin.php?page=cri-gestione-date' ) ) . '" class="button button-secondary">' . esc_html__( 'Gestione Date', 'cri-corsi' ) . '</a>';
        // Mostra il link "Documentazione"
        echo '<a href="https://docs.crivenezia.it/cri-corsi/" target="_blank" class="button button-secondary">' . esc_html__( 'Documentazione', 'cri-corsi' ) . '</a>';
        echo '</p>';

        // 3. Copyright (replicando Admin_Footer.php)
        $plugin_version = \CRICorsi\CRI_Corsi::instance()->get_version();

        $author = sprintf(
        /* translators: %1$s is a heart symbol, %2$s is the linked author name */
            esc_html__( 'Creato con %1$s da %2$s', 'cri-corsi' ),
            '<span style="color: #CC0000;">&hearts;</span>',
            '<a href="mailto:luca.forzutti@veneto.cri.it" target="_blank" style="color: #007cba; text-decoration: none;">Luca Forzutti</a>'
        );
        
        $version = sprintf(
        /* translators: %s is the plugin version number */
            esc_html__( 'Versione %s', 'cri-corsi' ),
            esc_html( $plugin_version )
        );
        
        echo '<div classs="cri-dashboard-footer" style="text-align: center; font-size: 0.9em; color: #555; border-top: 1px solid #ddd; margin-top: 15px; padding-top: 10px;">';
        echo '<p style="margin: 0 0 5px 0;">' . $author . ' | ' . $version . '</p>';
        echo '</div>'; // Chiusura dashboard-footer
        
        echo '</div>'; // Chiusura cri-dashboard-widget
    }
}
