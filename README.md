# CRI Corsi

Plugin WordPress per la gestione avanzata dei corsi per il Comitato di Venezia della Croce Rossa Italiana.

## Descrizione

CRI Corsi è un plugin WordPress su misura progettato per gestire l'intero ciclo di vita dei corsi offerti dal Comitato CRI di Venezia. Sostituisce la gestione manuale con un sistema integrato che gestisce le iscrizioni, i pagamenti (tramite WooCommerce) e la visualizzazione dei corsi tramite un widget Elementor dedicato.

Include un pannello di gestione semplificato per i docenti, permettendo loro di aggiungere e gestire le date dei corsi senza dover accedere all'editor completo del sito.

### Caratteristiche Principali

- Gestione Corsi (CPT): Un Custom Post Type dedicato per creare e categorizzare i corsi.

- Campi Personalizzati Avanzati:

- Gestione di date multiple per singolo corso, con durata e posti disponibili.

- Campo Prezzo e Indirizzo (per mappa).

- Selezione della destinazione (Popolazione o Aziende).

- Widget Elementor: Un widget "Griglia Corsi" da trascinare in qualsiasi pagina per mostrare i corsi disponibili.

- Integrazione WooCommerce: I corsi a pagamento vengono gestiti tramite un prodotto WooCommerce. Il plugin aggiunge il prodotto al carrello e reindirizza al checkout. La prenotazione viene confermata solo dopo il pagamento.

- Mappa Leaflet: Visualizzazione automatica di una mappa Leaflet se viene fornito un indirizzo per il corso.

- Pannello Docente: Un'interfaccia di amministrazione separata e semplificata (/wp-admin/admin.php?page=cri-gestione-date) che permette ai docenti di aggiungere o rimuovere date per i corsi esistenti.

- Ruolo Utente "Docente": Un ruolo cri_docente personalizzato che vede solo la bacheca e il pannello di gestione date, per un'esperienza pulita e sicura.

- Notifiche Email: Invio automatico di email di conferma all'utente e di notifica alla segreteria per ogni nuova prenotazione.

- Auto-aggiornamento da GitHub: Il plugin si aggiorna automaticamente dalle "Release" del repository GitHub.

### Stack Tecnico

- PHP 8.2+

- Architettura PSR-4 con autoloader di Composer.

- SCSS per la gestione degli stili (frontend e admin), compilato tramite script Composer.

- JavaScript Vanilla per la logica del frontend (mappe) e del backend (ripetitore meta box).

### Installazione (per Utente Finale)
* Naviga nella sezione "Releases" di questo repository GitHub.

* Scarica il file .zip più recente (es. cri-corsi-v1.1.1.zip).

* Dal tuo sito WordPress, vai su Plugin > Aggiungi nuovo > Carica plugin.

* Seleziona il file .zip scaricato e clicca "Installa ora".

* Attiva il plugin.

### Sviluppo (per Contributori)

* Clona questo repository nella tua macchina di sviluppo.

* Esegui composer install per installare tutte le dipendenze (incluse quelle di sviluppo come Elementor, WooCommerce e scssphp).

* Per compilare i file SCSS durante lo sviluppo, esegui composer compile-sass.

* Per i dettagli su come contribuire, consulta il file [contributing.md](contributing.md).

Licenza: Proprietaria.

Autore: [Luca](mailto:luca.forzutti@veneto.cri.it)

Email: [luca.forzutti@veneto.cri.it](mailto:luca.forzutti@veneto.cri.it)
