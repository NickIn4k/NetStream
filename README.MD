# NetStream — Documentazione Ufficiale

**Autori:** Andrea Bassan, Nicola Creazzo  
**Data:** 10/02/2026

---

## Indice
- [Introduzione](#introduzione)
- [Obiettivi e funzionalità](#obiettivi-e-funzionalità)
- [Requisiti](#requisiti)
- [Struttura del progetto](#struttura-del-progetto)
- [Database — schema e tabelle principali](#database--schema-e-tabelle-principali)
- [Vincoli, trigger e procedure](#vincoli-trigger-e-procedure)
- [Installazione e configurazione locale (XAMPP)](#installazione-e-configurazione-locale-xampp)
  - [VirtualHost Apache](#virtualhost-apache)
  - [File hosts (Windows)](#file-hosts-windows)
  - [Import database](#import-database)
- [Sicurezza e privacy](#sicurezza-e-privacy)
- [Pagine principali e struttura file/cartelle](#pagine-principali-e-struttura-filecartelle)
- [Implementazioni future](#implementazioni-future)
- [Collegamenti utili](#collegamenti-utili)
- [Licenza](#licenza)

---

## Introduzione
NetStream è una piattaforma di streaming progettata con finalità didattiche per simulare una piattaforma multimediale realistica.

La piattaforma consente:
- gestione di utenti e profili multipli
- organizzazione gerarchica dei contenuti (Film, Serie, Stagioni, Episodi)
- riproduzione tramite player HTML5
- interazione tramite recensioni e liste personalizzate

Il progetto applica i principi di modellazione concettuale E/R e di progettazione di un database relazionale complesso, includendo vincoli di integrità, trigger e query SQL avanzate.

NetStream permette inoltre il collegamento con un progetto esterno (Bet271), simulando lo scambio di dati tra due localhost distinti in modo controllato e sicuro.

---

## Obiettivi e funzionalità
- Registrazione e autenticazione utenti
- Gestione profili multipli per account
- Catalogo contenuti (Film / Serie / Stagioni / Episodi)
- Ricerca interattiva dei contenuti
- Player HTML5 per la riproduzione
- Liste personali e recensioni
- Tracciamento e scambio dati con progetto esterno
- Interfaccia dinamica e user-friendly
- Scalabilità del database
- Sicurezza base (hash password, sanitizzazione input)

---

## Requisiti
- Chiunque può visualizzare il catalogo e i piani di abbonamento
- Chiunque può registrarsi
- Ogni utente può accedere e gestire il proprio account
- Cambio piano di abbonamento mensile
- Gestione di più profili in base al piano sottoscritto
- Integrazione con piattaforma Bet271
- Avatar personalizzati per utenti condivisi
- Visualizzazione, recensione e salvataggio contenuti preferiti
- (Opzionale) selezione lingua e sottotitoli


---

## Database — schema e tabelle principali

### Utente
- idUtente
- nomeUtente (UNIQUE)
- email (UNIQUE)
- password
- dataRegistrazione
- bet271Points

### Profilo
- idProfilo
- nomeProfilo
- linguaProfilo
- etàProfilo
- idUtente (FK)
- idAvatar (FK)

### Avatar
- idAvatar
- descrittoreAvatar
- pathAvatar
- puntiSblocco

### Abbonamento
- idAbbonamento
- tipoAbbonamento (Base, Medium, Pro)
- prezzo
- qualitàMassima
- maxProfili

### Sottoscrizione
- idSottoscrizione
- dataInizio
- dataFine
- statoSottoscrizione (ATTIVA, SCADUTA)
- idUtente (FK)
- idAbbonamento (FK)

### Contenuto
- idContenuto
- titoloContenuto
- descrizioneContenuto
- dataUscita
- ratingEta
- regista
- tipoContenuto (Film, Serie)
- pathCopertina

#### Film
- idContenuto (FK)
- durata
- path

#### Serie
- idContenuto (FK)
- trailerPath

### Stagione
- idStagione
- numeroStagione
- idSerie (FK)

### Episodio
- idEpisodio
- titoloEpisodio
- durataEpisodio
- numeroEpisodio
- pathEpisodio
- idStagione (FK)

### Altre tabelle
- Genere
- ContenutoGenere
- Lingua
- ContenutoLingua (Audio / Sottotitoli)
- Recensione
- ListaPreferiti

---

## Vincoli, trigger e procedure
- UNIQUE su nomeUtente ed email
- NOT NULL sui campi fondamentali
- ENUM per tipoContenuto, tipoAbbonamento, statoSottoscrizione, tipoLingua
- CHECK e vincoli di integrità referenziale

### Trigger principali
- Impedisce l'associazione di stagioni a contenuti non di tipo Serie
- Aggiornamento automatico dello stato della sottoscrizione
- Blocco di più sottoscrizioni attive per lo stesso utente

---

## Installazione e configurazione locale (XAMPP)

### VirtualHost Apache
File: `xampp/apache/conf/extra/httpd-vhosts.conf`

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs"
    ServerName localhost
</VirtualHost>

<VirtualHost *:80>
    ServerAdmin webmaster@netstream.local
    DocumentRoot "C:/xampp/htdocs/Progetti/NetStream"
    ServerName netstream.local
    ServerAlias www.netstream.local
</VirtualHost>
```

### File Hosts
File: `C:\Windows\System32\drivers\etc\hosts`

```windows
127.0.0.1   netstream.local
::1         netstream.local
```

--- 

## Sicurezza e privacy
- Password hashate con password_hash() e password_verify()
- Query sicure tramite PDO
- Sanitizzazione input lato server e client
- Memorizzazione minima dei dati personali
- Materiale multimediale non protetto da copyright
- gitignore dei file di configurazione

--- 

## Pagine principali e struttura file/cartelle
- Home / Catalogo
- Login / Registrazione
- Gestione profili
- Scheda contenuto
- Player
- Preferiti
- Recensioni

---

## Implementazioni future
- Sistema di pagamento abbonamenti
- Raccomandazioni basate su visualizzazioni e recensioni
- Pannello di amministrazione avanzato
- Gestione avanzata lingue e sottotitoli

---

## Collegamenti utili
- [Schema E/R](https://drive.google.com/file/d/1yxaMvmW_1t03cRt1hPn2aF7)
- [Casi d’uso](https://app.diagrams.net/?src=about#G1rh9B3kzQ6-oLwPishEIbt59a3Tks-BU-#%7B%22pageId%22%3A%22nwiu5D8VHdWwTwha1Pyo%22%7D)
- [Wireframe](https://app.diagrams.net/#G1kOkwGg3M5l6pRRqG8plLpAB2B4_1Hik-#%7B%22pageId%22%3A%22YtazCz1vlDWSd4tnCc49%22%7D)
- [Trello](https://trello.com/b/3B4ggLJY/netstream)
- [Gantt](https://docs.google.com/spreadsheets/d/1FqmwYdQ6B7qnywzwPMZuINLSHChE1eK05GyFGGFuGZ8/edit?gid=1115838130#gid=1115838130)

---

## Licenza
- Progetto scolastico a scopo didattico.
- Nessun utilizzo commerciale senza autorizzazione.

---

## Configurazione e DataBase
- Salvare nella cartella `/assets/` la cartella `/configurations/` che è stata passata in via confidenziale dagli autori
- Importare su PHPMyAdmin il file db_NetStream
- Creare l'utente `netstream_user` secondo le direttive degli autori
