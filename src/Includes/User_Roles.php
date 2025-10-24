<?php
/**
 * Gestisce la creazione e le autorizzazioni del ruolo utente "Docente".
 *
 * @package CRI_Corsi
 */

namespace CRICorsi\Includes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe User_Roles
 * Aggiunge il ruolo 'cri_docente' e ne pulisce la bacheca.
 */
class User_Roles {

    /**
     * ID del ruolo personalizzato.
     * @var string
     */
    public const ROLE_ID = 'cri_docente';

    /**
     * Nuova capability personalizzata per il pannello docente.
     * @var string
     */
    public const CAPABILITY = 'manage_cri_course_dates';


    /**
     * Costruttore.
     */
    public function __construct() {
        // NOTA: L'hook di attivazione è stato spostato nel file principale (cri-corsi.php) per evitare loop.
        
        // Agganciamo a 'wp_loaded' (più tardi di 'init') per aggiungere gli hook admin condizionalmente
        add_action( 'wp_loaded', [ $this, 'conditionally_add_hooks' ] );
        
        // RIMOSSO: add_filter( 'login_redirect', [ $this, 'override_login_redirect' ], 99, 3 );
        // RIMOSSO: add_action( 'wp_login', [ $this, 'force_teacher_dashboard_redirect' ], 999, 2 ); 
    }

    /**
     * Aggiunge gli hook per pulire il menu e disabilitare il redirect WooCommerce
     * solo per gli utenti "Docente" (e non admin).
     */
    public function conditionally_add_hooks(): void {
        // Non fare nulla se l'utente non è loggato
        if ( ! is_user_logged_in() ) {
            return;
        }

        $user = wp_get_current_user();
        
        // Se l'utente non esiste ancora, esci
        if ( ! $user || $user->ID === 0 ) {
            return;
        }

        // Aggiungi gli hook solo se l'utente ha il ruolo 'docente' E NON è un amministratore.
        if ( in_array( self::ROLE_ID, $user->roles, true ) && ! user_can( $user, 'manage_options' ) ) {
            // Pulisce il menu admin
            add_action( 'admin_menu', [ $this, 'cleanup_admin_menu' ], 999 );
            
            // **NUOVO**: Disabilita il redirect di WooCommerce per i docenti
            // Ci agganciamo qui (dentro wp_loaded) per assicurarci che WooCommerce sia già caricato
            if ( class_exists('WooCommerce') ) {
                add_filter( 'woocommerce_prevent_admin_access', '__return_false' );
            }
        }
    }
    
    /**
     * Aggiunge il ruolo personalizzato "Docente" al database di WordPress.
     * Si esegue solo all'attivazione del plugin.
     * METODO STATICO CHIAMATO DA cri-corsi.php
     */
    public static function add_custom_role(): void {
        // Aggiungiamo il ruolo solo se non esiste già
        if ( get_role( self::ROLE_ID ) ) {
            return;
        }

        // Diamo al docente le capacità di base di un Sottoscrittore + 'read' esplicito
        $subscriber_caps = get_role( 'subscriber' )->capabilities ?? []; // Aggiunto fallback
        $teacher_caps = array_merge(
            $subscriber_caps,
            [
                self::CAPABILITY => true, // La nostra capability personalizzata
                'read' => true, // Garantisce accesso base alla bacheca
            ]
        );

        add_role(
            self::ROLE_ID,
            esc_html__( 'Docente CRI', 'cri-corsi' ),
            $teacher_caps
        );

        // Aggiungiamo la capability anche agli amministratori, così possono vedere il pannello
        $admin_role = get_role( 'administrator' );
        if ($admin_role) {
            $admin_role->add_cap( self::CAPABILITY );
        }
    }

    /**
     * Pulisce la bacheca per il ruolo "Docente".
     * Questa funzione ora viene chiamata solo per i docenti (non admin).
     */
    public function cleanup_admin_menu(): void {
        // Il controllo sull'utente è già stato fatto in conditionally_add_hooks()
        
        global $menu, $submenu;

        // Lista dei menu da mantenere (slug)
        $allowed_menus = [
            'index.php',             // Bacheca
            'cri-gestione-date',     // Il nostro pannello docente
            'profile.php',           // Profilo utente
        ];

        foreach ( $menu as $key => $menu_item ) {
            // $menu_item[2] è lo slug del menu
            if ( isset( $menu_item[2] ) && ! in_array( $menu_item[2], $allowed_menus, true ) ) {
                // Rimuovi se non è nella lista permessi e non è un separatore
                 if ( strpos( $menu_item[2], 'separator' ) === false ) {
                     remove_menu_page( $menu_item[2] );
                 }
            }
        }

        // Rimuovi sottomenu specifici (es. "Aggiornamenti" e "Home" dalla Bacheca)
        if (isset($submenu['index.php'])) {
            foreach ($submenu['index.php'] as $key => $item) {
                // $item[2] è lo slug del sottomenu
                if (isset($item[2]) && ($item[2] === 'update-core.php' || $item[2] === 'index.php')) {
                    unset($submenu['index.php'][$key]);
                }
            }
        }
    }

    /**
     * RIMOSSO: Impedisce ai docenti di accedere ad altre pagine admin...
     * La logica è stata rimossa per evitare conflitti. Ci affidiamo alla pulizia del menu
     * e alla disattivazione del filtro WooCommerce.
     */
    // public function redirect_non_admin_docente(): void { ... }


    /**
     * RIMOSSO: Filtra l'URL di reindirizzamento dopo il login (tentativo primario).
     * Ci affidiamo alla disattivazione del filtro WooCommerce.
     */
    // public function override_login_redirect( string $redirect_to, string $requested_redirect_to, $user ): string { ... }

    /**
     * RIMOSSO: Forza il reindirizzamento alla bacheca per i docenti dopo il login.
     * Ci affidiamo alla disattivazione del filtro WooCommerce.
     */
    // public function force_teacher_dashboard_redirect( string $user_login, \WP_User $user ): void { ... }
}