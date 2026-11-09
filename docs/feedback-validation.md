# Feedback dei moduli

L'applicazione usa l'italiano per etichette, istruzioni e risposte di validazione.
La validazione lato server resta autorevole; i controlli del browser anticipano
il feedback senza sostituire autorizzazioni e vincoli sui dati.

## Superfici da verificare

| Modulo | Campi e condizioni |
| --- | --- |
| Accesso | Email e password obbligatorie; email valida; credenziali errate; remember me opzionale. |
| Registrazione | Nome, email univoca, password valida e conferma corrispondente. |
| Recupero/reset password | Email valida, token valido, password e relativa conferma. |
| Conferma password e account | Password attuale richiesta e corretta per conferme, sessioni e cancellazione. |
| Profilo | Nome ed email obbligatori; email valida e univoca salvo l'utente corrente; nessuna scrittura parziale. |
| Autenticazione a due fattori | Codice OTP o codice di recupero richiesto e valido; feedback della conferma e dei codici errati. |
| Corso di studio | Nome obbligatorio, lunghezza e unicità. |
| Insegnamento | Nome, anno 1–3, corso esistente; titolari opzionali, selezioni valide e appartenenti al ruolo docente. |
| Sessione | Nome, date valide, inizio futuro, durata minima di sette giorni inclusivi; modifiche compatibili con appelli e finestre esistenti. |
| Finestra di inserimento | Date obbligatorie, ordine corretto, chiusura entro la sessione; associazione alla sessione corretta. |
| Appello | Insegnamento consentito, sessione valida/aperta per il docente, data lavorativa entro il margine ammesso; orari 08:00–18:00 con fine successiva all'inizio; aula e note opzionali entro i limiti. |
| Conflitti | Stesso corso/anno o aula occupata; blocco o avviso secondo configurazione; dettagli riservati protetti anche nelle risposte AJAX. |
| Configurazione | Modalità ammessa, anticipo intero 0–60; modifiche compatibili con i dati già pianificati. |
| CSV | File leggibile, formato e dimensione, intestazione, numero colonne, righe, nomi, anno e docenti; errori con riferimento alla riga e importazione atomica. |
| Filtri e selezioni GET | Parametri scalari del formato previsto, gestione di selezioni assenti o non valide senza errori applicativi. |

## Criteri di verifica

- Campi mancanti o non validi producono feedback italiano; gli errori multipli
  sono elencati e associati ai relativi controlli, inclusi gli elementi di array.
- I dati inseriti sono mantenuti dove opportuno, senza ripopolare password.
- Le richieste rifiutate non producono modifiche parziali o relazioni incoerenti.
- Avvisi AJAX falliti o superati da richieste successive non mostrano un esito
  positivo ingannevole.
- Vengono esercitati sia richieste HTTP/Livewire sia controlli nativi nel browser.
- La verifica finale comprende la suite completa, la build e una revisione del
  branch prima della pubblicazione e dell'integrazione.

## Correzioni e verifica finale

- Cataloghi italiani per validazioni, autenticazione, profilo e notifiche email;
  messaggi nativi del browser tradotti anche con sistema operativo in inglese.
- Errori associati ai campi con `aria-invalid` e `aria-describedby`, mantenendo
  valori validi e selezioni dopo un rifiuto. Gli input strutturati non vengono
  riproposti nei controlli scalari: il redirect torna al form senza errori 500.
- Tipi e formati controllati prima di elaborare date, orari, identificativi e
  password; conferme obbligatorie e nessuna scrittura su richieste rifiutate.
- Modifiche a sessioni, margine dei preappelli e struttura didattica rifiutate
  quando renderebbero incompatibili appelli o finestre già presenti. Conservate
  la modalità di avviso e la possibilità di rinominare senza nuovi conflitti.
- CSV validato prima delle scritture, con segnalazione della riga e rollback
  dell'intera importazione in caso di errore.
- Risposte AJAX fallite, incomplete o superate da una modifica non producono
  falsi messaggi di assenza di conflitti.

Verifica del 9 novembre 2026, successiva alle correzioni:

- `composer test`: **214 test superati, 1.364 asserzioni, nessun test saltato**.
- `npm run build`: completata.
- `vendor/bin/pint --test --dirty` e `git diff --check`: superati.
- Playwright, con database e account di prova separati: campi obbligatori nelle
  pagine pubbliche e operative, ordine degli orari e correzione degli errori;
  profilo con email duplicata, password errata/conferma e modifica/ripristino;
  chiusura delle sessioni, conferma password e OTP errati, annullamento 2FA;
  AJAX 422, errore di rete, risposta incompleta, risposta obsoleta e recupero.
  Nessun errore JavaScript nei flussi verificati. Login controllato a 390 e
  1440 pixel, compreso il riposizionamento della sfumatura con gli errori.
- Code review indipendente del branch: corretti l'oggetto dell'email di reset
  ancora in inglese e la ripopolazione non sicura di input scalari malformati;
  nessun rilievo bloccante residuo.

La review segnala come limiti preesistenti le possibili corse fra controlli e
salvataggi concorrenti; la verifica dei nuovi conflitti retroattivi confronta
le coppie di appelli e ha costo quadratico su insiemi molto grandi.
