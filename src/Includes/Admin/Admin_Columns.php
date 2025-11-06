<?php
/**
 * Gestisce la personalizzazione delle colonne admin per le Prenotazioni.
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes\Admin;

// Importazione necessaria per la WP_Query
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe Admin_Columns
 */
class Admin_Columns {

    /**
     * Costruttore. Aggancia i filtri per le colonne admin.
     */
    public function __construct() {
        add_filter( 'manage_prenotazione_posts_columns', [ $this, 'add_custom_columns' ] );
        add_action( 'manage_prenotazione_posts_custom_column', [ $this, 'render_custom_columns' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
        
        // **NUOVI HOOK PER I FILTRI**
        // Aggiunge i campi filtro HTML sopra la tabella
        add_action( 'restrict_manage_posts', [ $this, 'add_admin_list_filters' ] );
        // Applica la logica di filtro alla query
        add_filter( 'parse_query', [ $this, 'apply_admin_list_filters' ] );
    }

    /**
     * Carica gli stili CSS per la bacheca.
     *
     * @param string $hook Pagina corrente della bacheca.
     */
    public function enqueue_admin_styles( string $hook ): void {
        // Carica gli stili solo nelle pagine di modifica o elenco dei nostri CPT
        if ( 'post.php' === $hook || 'post-new.php' === $hook || 'edit.php' === $hook ) {
            $screen = get_current_screen();
            if ( $screen && in_array( $screen->post_type, [ 'cri_corso', 'prenotazione' ], true ) ) {
                
                $plugin_instance = \CRICorsi\CRI_Corsi::instance();
                
                wp_enqueue_style(
                    'cri-corsi-admin',
                    $plugin_instance->get_plugin_url() . 'assets/css/admin.css',
                    [],
                    $plugin_instance->get_version()
                );
            }
        }
    }

    /**
     * **NUOVO**: Aggiunge i menu a tendina per i filtri sopra la tabella.
     * Si aggancia a 'restrict_manage_posts'.
     *
     * @param string $post_type Il post type corrente.
     */
    public function add_admin_list_filters( string $post_type ): void {
        // Mostra i filtri solo nella pagina elenco delle 'prenotazione'
        if ( 'prenotazione' !== $post_type ) {
            return;
        }

        // --- Filtro per Corso ---
        $corsi_query = new WP_Query( [
            'post_type'      => 'cri_corso',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ] );

        $current_corso = (int) ( $_GET['filter_by_corso'] ?? 0 );
        ?>
        <label for="filter_by_corso" class="screen-reader-text"><?php esc_html_e( 'Filtra per Corso', 'cri-corsi' ); ?></label>
        <select name="filter_by_corso" id="filter_by_corso">
            <option value="0"><?php esc_html_e( 'Tutti i Corsi', 'cri-corsi' ); ?></option>
            <?php
            if ( $corsi_query->have_posts() ) {
                while ( $corsi_query->have_posts() ) {
                    $corsi_query->the_post();
                    printf(
                        '<option value="%d" %s>%s</option>',
                        esc_attr( get_the_ID() ),
                        selected( $current_corso, get_the_ID(), false ),
                        esc_html( get_the_title() )
                    );
                }
            }
            wp_reset_postdata();
            ?>
        </select>
        <?php

        // --- Filtro per Data di Prenotazione ---
        $start_date = esc_attr( $_GET['filter_date_start'] ?? '' );
        $end_date = esc_attr( $_GET['filter_date_end'] ?? '' );
        ?>
        <label for="filter_date_start" style="margin-left: 5px;"><?php esc_html_e( 'Da:', 'cri-corsi' ); ?></label>
        <input type="date" name="filter_date_start" id="filter_date_start" value="<?php echo $start_date; ?>" style="margin-right: 5px;">
        
        <label for="filter_date_end"><?php esc_html_e( 'A:', 'cri-corsi' ); ?></label>
        <input type="date" name="filter_date_end" id="filter_date_end" value="<?php echo $end_date; ?>">
        <?php
        // Il pulsante "Filtra" è già presente in WordPress
    }

    /**
     * **NUOVO**: Applica i filtri alla query principale di WordPress.
     * Si aggancia a 'parse_query'.
     *
     * @param \WP_Query $query La query principale di WordPress.
     */
    public function apply_admin_list_filters( \WP_Query $query ): void {
        global $pagenow;
        $post_type = $query->get('post_type');
        
        // Esegui solo se siamo nella pagina admin 'edit.php',
        // è la query principale e il post type è 'prenotazione'
        if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() || 'prenotazione' !== $post_type ) {
            return;
        }

        // --- Applica filtro Corso ---
        $corso_id = (int) ( $_GET['filter_by_corso'] ?? 0 );
        if ( $corso_id > 0 ) {
            $meta_query = $query->get('meta_query') ?? [];
            if (!is_array($meta_query)) { $meta_query = []; }
            
            $meta_query[] = [
                'key'     => '_corso_id',
                'value'   => $corso_id,
                'compare' => '=',
            ];
            $query->set( 'meta_query', $meta_query );
        }

        // --- Applica filtro Data (sulla data di creazione della prenotazione) ---
        $start_date = sanitize_text_field( $_GET['filter_date_start'] ?? '' );
        $end_date = sanitize_text_field( $_GET['filter_date_end'] ?? '' );

        if ( ! empty( $start_date ) || ! empty( $end_date ) ) {
            $date_query = $query->get('date_query') ?? [];
            if (!is_array($date_query)) { $date_query = []; }

            if ( ! empty( $start_date ) ) {
                $date_query['after'] = $start_date . ' 00:00:00'; // Inizia dalla mezzanotte del giorno
            }
            if ( ! empty( $end_date ) ) {
                $date_query['before'] = $end_date . ' 23:59:59'; // Finisce alla fine del giorno
            }
            $date_query['inclusive'] = true; // Include i giorni di inizio e fine
            
            $query->set( 'date_query', $date_query );
        }
    }


    /**
     * Aggiunge nuove colonne alla tabella delle Prenotazioni.
     * (Metodo esistente)
     */
    public function add_custom_columns( array $columns ): array {
        // ... (codice invariato)
// [Contenuto del metodo add_custom_columns esistente]
        $new_columns = [];
        foreach ( $columns as $key => $title ) {
            $new_columns[ $key ] = $title;
            if ( 'title' === $key ) {
                $new_columns['corso'] = esc_html__( 'Corso Prenotato', 'cri-corsi' );
                $new_columns['data_ora'] = esc_html__( 'Data e Durata', 'cri-corsi' );
                $new_columns['email'] = esc_html__( 'Email Partecipante', 'cri-corsi' );
            }
        }
        // Aggiungiamo la colonna stato alla fine
        $new_columns['stato'] = esc_html__( 'Stato', 'cri-corsi' );
        return $new_columns;
// [Fine contenuto]
    }

    /**
     * Renderizza il contenuto delle colonne personalizzate.
     * (Metodo esistente)
     */
    public function render_custom_columns( string $column_name, int $post_id ): void {
        // ... (codice invariato)
// [Contenuto del metodo render_custom_columns esistente]
        // Usiamo match() di PHP 8.0+
        match ( $column_name ) {
            'corso' => $this->render_corso_column( $post_id ),
            'data_ora' => $this->render_data_ora_column( $post_id ),
            'email' => $this->render_email_column( $post_id ),
            'stato' => $this->render_stato_column( $post_id ),
            default => null,
        };
// [Fine contenuto]
    }

    /**
     * Renderizza la colonna 'Corso'.
     * (Metodo esistente)
     */
    private function render_corso_column( int $post_id ): void {
        // ... (codice invariato)
// [Contenuto del metodo render_corso_column esistente]
        $corso_id = get_post_meta( $post_id, '_corso_id', true );
        if ( ! empty( $corso_id ) ) {
            $edit_link = get_edit_post_link( $corso_id );
            $title = get_the_title( $corso_id );
            echo '<a href="' . esc_url( $edit_link ) . '"><strong>' . esc_html( $title ) . '</strong></a>';
        } else {
            echo 'N/D';
        }
// [Fine contenuto]
    }

    /**
     * Renderizza la colonna 'Data e Durata'.
     * (Metodo esistente)
     */
    private function render_data_ora_column( int $post_id ): void {
        // ... (codice invariato)
// [Contenuto del metodo render_data_ora_column esistente]
        $corso_id = get_post_meta( $post_id, '_corso_id', true );
        $scelta_index = get_post_meta( $post_id, '_corso_scelta_index', true );

        if ( $corso_id && $scelta_index !== '' ) {
            $date_orari = get_post_meta( $corso_id, '_cri_course_date_orari', true );
            
            if ( is_array( $date_orari ) && isset( $date_orari[ $scelta_index ] ) ) {
                $dettagli = $date_orari[ $scelta_index ];
                $data_corso = $dettagli['data_corso'] ?? '';
                $durata = $dettagli['durata_corso'] ?? '';

                if ( $data_corso ) {
                    printf(
                        '%s<br>(%s)',
                        esc_html( date_i18n( 'l d/m/Y', strtotime( $data_corso ) ) ),
                        esc_html( $durata )
                    );
                } else {
                    echo 'N/D';
                }
            } else {
                echo 'Dati corso non trovati.';
            }
        } else {
            echo 'N/D';
        }
// [Fine contenuto]
    }

    /**
     * Renderizza la colonna 'Email'.
     * (Metodo esistente)
     */
    private function render_email_column( int $post_id ): void {
        // ... (codice invariato)
// [Contenuto del metodo render_email_column esistente]
        $email = get_post_meta( $post_id, '_email', true );
        if ( ! empty( $email ) ) {
            echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
        } else {
            echo 'N/D';
        }
// [Fine contenuto]
    }

    /**
     * Renderizza la colonna 'Stato'.
     * (Metodo esistente)
     */
    private function render_stato_column( int $post_id ): void {
        // ... (codice invariato)
// [Contenuto del metodo render_stato_column esistente]
        $status = get_post_status( $post_id );
        
        $status_label = match( $status ) {
            'publish' => esc_html__( 'Confermato', 'cri-corsi' ),
            'wc-pending' => esc_html__( 'In attesa di Pagamento', 'cri-corsi' ),
            'draft' => esc_html__( 'Bozza', 'cri-corsi' ),
            default => esc_html( $status ),
        };
        
        $status_class = match( $status ) {
            'publish' => 'status-confirmed',
            'wc-pending' => 'status-pending',
            default => 'status-draft',
        };

        echo '<span class="cri-status-label ' . esc_attr( $status_class ) . '">' . esc_html( $status_label ) . '</span>';
// [Fine contenuto]
    }
}
