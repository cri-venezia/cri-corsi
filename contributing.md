# Come Contribuire

Grazie per il tuo interesse nel contribuire a questo progetto! 

Per mantenere il codice pulito, gestibile e sicuro, seguiamo un flusso di lavoro e degli standard precisi.

### Flusso di Lavoro (Fork & Pull Request)

Il branch principale (main) di questo repository è protetto. 

I commit diretti sul branch main sono bloccati.

Per contribuire con modifiche, segui questo flusso di lavoro obbligatorio:

- Fork: Crea un "fork" di questo repository sul tuo account GitHub.

- Branch: Clona il tuo fork in locale e crea un nuovo branch per la tua modifica (es. feat/aggiungi-filtro-docente o fix/errore-mappa).

- Commit: Esegui le tue modifiche e fai commit utilizzando lo standard dei Conventional Commits.

- Pull Request (PR): Fai il push del tuo branch sul tuo fork e apri una "Pull Request" verso il branch main del repository cri-venezia/cri-corsi.

- Review: La tua PR sarà revisionata prima di poter essere unita (merged) al branch principale.

### Standard di Commit (Conventional Commits)

Utilizziamo lo standard Conventional Commits per i messaggi di commit. Questo ci aiuta a mantenere una cronologia chiara e a generare changelog automatici.

Il formato è: `tipo(scope): descrizione`

**tipo**: Indica la natura della modifica:

- **feat**: Aggiunta di una nuova funzionalità (features).

- **fix**: Correzione di un bug.

- **docs**: Modifiche alla documentazione (readme.md, ecc.).

- **style**: Modifiche stilistiche (formattazione, CSS, SCSS).

- **refactor**: Modifiche al codice che non aggiungono funzionalità né correggono bug.

- **ci**: Modifiche ai file di CI/CD (GitHub Actions).

- **build**: Modifiche al processo di build (Composer, script).

**(scope): (Opzionale) L'area del plugin interessata (es. widget, form, admin, docente).**

**descrizione: Una breve descrizione della modifica, in minuscolo e all'imperativo.**

Esempi:

    feat(widget): aggiunta opzione per colore del titolo

    fix(form): corretta la validazione della Partita IVA

    refactor(docente): ottimizzata la query di caricamento corsi

    ci(release): aggiorna i permessi della GitHub Action

### Gestione delle Dipendenze (Renovate)

Questo repository utilizza RenovateBot per la gestione automatica delle dipendenze di Composer.

Renovate scansiona periodicamente il file composer.json e apre automaticamente delle Pull Request per aggiornare le librerie (come plugin-update-checker o scssphp). Queste PR devono essere testate e approvate manualmente.

### Versioning e Release

Il versioning del plugin segue lo standard Semantic Versioning (es. v1.0.5, v1.1.0).

**La creazione di una nuova release è automatizzata:**

**Le modifiche vengono unite a main tramite PR.**

**Per creare una nuova versione, si crea un nuovo tag Git (es. v1.0.6) e si fa il push del tag.**

**Il push del tag scatena la GitHub Action (.github/workflows/release.yml), che automaticamente:**

- Installa le dipendenze di produzione.

- Compila i file .scss in .css.

- Crea un file .zip del plugin pulito (escludendo file di sviluppo).

- Pubblica lo .zip nella sezione "Releases" di GitHub.
