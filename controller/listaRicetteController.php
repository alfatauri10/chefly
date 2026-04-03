<?php
// controller/listaRicetteController.php
// IMPORTANTE: Questo file non fa redirect. Va incluso all'inizio delle tue View
// (es. in bacheca.php o in ilMioRistorante.php)
// Esempio di utilizzo: require_once '../controller/listaRicetteController.php';

// 1. Mettiamo in sicurezza la sessione all'inizio del file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/ricetta.php';


// Inizializziamo gli array per evitare errori nelle view se il DB è vuoto
$tutteLeRicette = [];
$mieRicette = [];

// 3. Dati per la HOME / Bacheca pubblica
// Recupera tutte le ricette (con info sull'autore e copertina grazie alla JOIN nel model)
$tutteLeRicette = getTutteLeRicetteDB($conn);

// 4. Dati per l'AREA PERSONALE (Le mie ricette)
$id_utente_loggato = $_SESSION['user_id'] ?? null;

if ($id_utente_loggato) {
    // Recupera solo le ricette create dall'utente loggato
    $mieRicette = getListaRicetteUtente($conn, $id_utente_loggato);
}

// Ora le variabili $tutteLeRicette e $mieRicette sono pronte per essere ciclare
// tramite un foreach all'interno del codice HTML delle tue pagine!
?>