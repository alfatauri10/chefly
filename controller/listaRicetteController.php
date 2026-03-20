<?php
// controller/listaRicetteController.php

// Mettiamo in sicurezza la sessione all'inizio del file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../model/ricetta.php';
require_once '../include/connessione.php';

// Se vogliamo la lista pubblica di TUTTE le ricette
$tutteLeRicette = getTutteLeRicetteDB($conn);

// Se invece siamo nell'area privata dell'utente loggato, recuperiamo le SUE ricette
$mieRicette = [];
if (isset($_SESSION['user_id'])) {
    $mieRicette = getListaRicetteUtente($conn, $_SESSION['user_id']);
}

// Questo controller non fa redirect.
// Dovrà essere incluso all'inizio dei file view (es. require_once '../controller/listaRicetteController.php')
// per rendere disponibili gli array $tutteLeRicette e $mieRicette all'HTML della pagina.
?>