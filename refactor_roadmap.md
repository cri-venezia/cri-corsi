Roadmap Sviluppo Plugin: CRI Corsi

Questa roadmap tiene traccia di tutte le fasi di refactoring e sviluppo del plugin.

Fase 1: Refactoring Core PHP (PSR-4)

Migrazione della logica di base alla nuova struttura src/ e ai namespace.

[X] File Principale: cri-corsi.php (Refattorizzato con Singleton e autoloader).

[X] Post Types: includes/class-post-types.php -> src/Includes/Post_Types.php

[X] Meta Box: includes/class-meta-boxes.php -> src/Includes/Meta_Boxes.php

[X] Form Handler: includes/class-form-handler.php -> src/Includes/Form_Handler.php

[X] Colonne Admin: includes/class-admin-columns.php -> src/Includes/Admin_Columns.php

[X] Updater: includes/class-plugin-updater.php -> src/Includes/Plugin_Updater.php

Fase 2: Refactoring Elementor (PSR-4)

Migrazione dei file specifici di Elementor alla nuova struttura.

[X] Loader Widget: includes/class-elementor-widgets.php -> src/Includes/Elementor/Elementor_Widgets.php

[X] Widget Principale: includes/widgets/class-cri-corsi-widget.php -> src/Includes/Elementor/Widgets/CRI_Corsi_Widget.php

Fase 3: Asset e Flusso di Build (CI/CD)

Impostazione degli asset sorgente (SCSS) e degli script di automazione.

[X] SCSS Frontend: Creato assets/scss/frontend.scss (con wrapper Elementor).

[X] SCSS Admin: Creato assets/scss/admin.scss (per Meta Box e Colonne).

[X] JS Repeater Admin: assets/js/admin-repeater.js (Caricato correttamente da Meta_Boxes.php).

[X] JS Mappe Frontend: Creato assets/js/frontend-maps.js (Refattorizzato dal widget).

[X] Script Compilatore: Creato bin/compile-scss.php.

[X] Classe Compilatore: Creata src/Includes/SCSSCompiler.php.

[X] GitHub Action: Creato .github/workflows/release.yml (per build e release automatiche).

[X] Composer Script: Aggiunto lo script compile-sass a composer.json.

Fase 4: Nuove Funzionalità (Pannello Docente)

Creazione di un'interfaccia semplificata per la gestione delle date.

[X] Pannello Docente: Creata src/Includes/Admin/Teacher_Panel.php (con pagina menu dedicata).

[X] Ruolo Utente: Creata src/Includes/User_Roles.php (Ruolo cri_docente e capability personalizzata).

[X] Aggiornamento Permessi: Aggiornato Teacher_Panel.php per usare la nuova capability.

Fase 5: Modifiche Funzionali (Durata)

Sostituzione dei campi "Orario Inizio/Fine" con un unico campo "Durata".

[X] Meta Box: Aggiornato src/Includes/Meta_Boxes.php.

[X] Widget Elementor: Aggiornato src/Includes/Elementor/Widgets/CRI_Corsi_Widget.php.

[X] Form Handler: Aggiornato src/Includes/Form_Handler.php (Email incluse).

[X] Colonne Admin: Aggiornato src/Includes/Admin_Columns.php.

Stato Attuale

COMPLETATO. Tutte le funzionalità pianificate e i refactoring sono stati implementati.
