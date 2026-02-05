# 🛠️ Brico Management System

Sistema gestionale completo e leggero sviluppato in **PHP Puro (MVC)** per negozi di bricolage, ferramenta e retail.
Include gestione vendite (POS), magazzino, documenti di trasporto (DDT) e anagrafica clienti.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg) ![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4.svg) ![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1.svg) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3.svg)

---

## 📠 Modello di Cassa e Integrazione Hardware

Il sistema utilizza un'architettura **POS Web-Based** con integrazione per Stampanti Fiscali tramite **File Spooling**.

*   **Modello Cassa Riferimento**: P/N 9911002 (Protocollo Generico / Xon-Xoff)
*   **Metodo di Stampa**: Il sistema genera file di testo (`.txt`) nella cartella `public/spool/`.
*   **Driver**: È necessario un driver di sistema (es. ECR Driver, WinECRCom) configurato per monitorare la cartella di spool e inviare i comandi alla stampante fisica.
*   **Funzionalità Supportate**:
    *   Stampa Scontrino Fiscale
    *   Gestione Reparti (tramite mappatura categorie)
    *   Chiusura Giornaliera (non implementata direttamente nel software, demandata alla cassa)

---

## 🚀 Funzionalità Principali

### 🏪 Punto Vendita (POS)
*   **Interfaccia Touch-Friendly**: Ottimizzata per monitor touch screen.
*   **Ricerca Rapida**:
    *   Supporto lettore Barcode (EAN-13).
    *   Ricerca live per nome prodotto (AJAX).
*   **Carrello Dinamico**: Modifica quantità, rimozione righe, calcolo subtotali istantaneo.
*   **Pagamenti**: Gestione Contanti e Carte Elettroniche.
*   **Clienti**: Possibilità di associare la vendita a un cliente registrato o anonimo.

### 📦 Magazzino & Inventario
*   **Tracciamento Real-Time**: Ogni vendita scala automaticamente la giacenza.
*   **Movimenti**:
    *   `Carico`: Ingresso merce da fornitori.
    *   `Scarico`: Uscita merce (vendita, uso interno, rottura).
    *   `Reso`: Rientro merce da cliente.
*   **Sottoscorta**: Avvisi automatici in dashboard per prodotti sotto la soglia minima.

### 🚚 Documenti di Trasporto (DDT)
*   **Generazione**: Creazione DDT con numerazione automatica progressiva (`YYYY-NNN`).
*   **Stati**: Gestione stati `Confermato` e `Annullato`.
    *   L'annullamento ripristina automaticamente le giacenze in magazzino.
    *   Watermark "ANNULLATO" sulle stampe dei documenti revocati.
*   **Stampa**: Generazione layout di stampa A4 professionale.

### 👥 Gestione Clienti
*   Anagrafica completa (Dati fiscali, indirizzi, contatti).
*   Storico acquisti integrato.

### 📊 Dashboard
*   KPI giornalieri (Venduto oggi, Scontrini emessi).
*   Grafici andamento vendite (ultimi 7 giorni).
*   Lista prodotti "Top Seller".

---

## 📂 Struttura del Progetto

```
Brico/
├── config/             # Configurazioni globali (DB, URL)
├── public/             # Entry point web
│   ├── index.php       # Front controller
│   └── spool/          # Cartella output scontrini (per driver fiscale)
├── setup/              # Script di installazione e manutenzione
│   ├── database.sql    # Schema database
│   ├── install_db.php  # Installer
│   └── seed_*.php      # Generatori dati di test
├── src/                # Logica Applicativa
│   ├── Controllers/    # Gestori delle richieste
│   ├── Models/         # Interazione con Database
│   └── Core/           # Database wrapper
├── templates/          # Viste (HTML/PHP)
│   ├── layout/         # Componenti comuni (Header, Sidebar)
│   └── [moduli]/       # Viste specifiche (pos, ddt, sales...)
└── README.md           # Documentazione
```

---

## 🛠️ Guida all'Installazione

Segui questi passaggi per configurare il sistema sul tuo ambiente locale o server.

### 1. Requisiti di Sistema
Assicurati di avere installato:
*   **Web Server**: Apache o Nginx (consigliato: **Laragon** su Windows per un setup rapido).
*   **PHP**: Versione 8.1 o superiore.
*   **Database**: MySQL 8.0+ o MariaDB.
*   **Estensioni PHP**: `pdo`, `pdo_mysql`.

### 2. Configurazione Database
1.  Accedi al tuo gestore database (es. phpMyAdmin o HeidiSQL).
2.  Crea un nuovo database vuoto (es. `brico_db`).
3.  **Importazione Dati**:
    *   **Opzione A (Consigliata - Backup Completo)**: Importa il file `setup/full_backup.sql` che contiene struttura e dati di esempio pronti all'uso.
    *   **Opzione B (Da Zero)**: Esegui lo script di installazione via browser o terminale:
        ```bash
        php setup/install_db.php
        ```

### 3. Configurazione Applicazione
1.  Vai nella cartella `config/`.
2.  Copia il file `config.sample.php` e rinominalo in `config.php`.
3.  Apri `config.php` con un editor di testo e imposta i parametri di connessione:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'brico_db'); // Il nome del database creato
    define('DB_USER', 'root');     // Il tuo utente MySQL
    define('DB_PASS', '');         // La tua password MySQL
    ```

### 4. Avvio
1.  Configura il tuo Web Server per puntare alla cartella `public/` del progetto come **Document Root**.
2.  Apri il browser e vai all'indirizzo configurato (es. `http://brico.test`).
3.  **Credenziali di Accesso Default**:
    *   Email: `admin@brico.local`
    *   Password: `password`

### 5. Configurazione Stampante (Opzionale)
Per abilitare la stampa scontrini:
1.  Installa il driver della tua stampante fiscale (es. WinECRCom).
2.  Configura il driver per monitorare la cartella `public/spool/`.
3.  Assicurati che PHP abbia i permessi di scrittura su questa cartella.
        php install_db.php
        ```

4.  **Dati di Esempio (Opzionale)**:
    *   Per popolare il DB con prodotti, clienti e vendite fittizie:
        ```bash
        cd setup
        php seed_products.php  # Crea prodotti
        php seed_data.php      # Crea clienti
        php seed_sales.php     # Crea storico vendite
        php seed_ddt.php       # Crea DDT
        ```

---

## 📖 Guida Rapida Operatore

### Vendita al Banco
1.  Apri il menu **Punto Vendita**.
2.  (Opzionale) Cerca il cliente con la barra in alto.
3.  Spara il codice a barre del prodotto o cercalo per nome.
4.  Premi **Conferma Vendita** e scegli il metodo di pagamento.
5.  Lo scontrino verrà inviato alla stampante.

### Annullamento DDT
1.  Vai su **Gestione DDT**.
2.  Clicca sull'icona **Occhio** (Dettaglio) del DDT desiderato.
3.  Premi il pulsante rosso **Annulla DDT**.
4.  Conferma l'operazione: la merce tornerà in magazzino e il documento sarà marcato come Annullato.

---

Made with ❤️ by Trae AI Assistant
