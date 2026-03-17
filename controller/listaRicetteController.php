<?php
// controller/listaRicetteController.php
require_once '../model/ricetta.php';
require_once '../include/connessione.php';

// Se vogliamo la lista pubblica di TUTTE le ricette
$tutteLeRicette = getTutteLeRicetteDB($conn);

// Se invece siamo nell'area privata dell'utente loggato
session_start();
$mieRicette = [];
if (isset($_SESSION['user_id'])) {
    $mieRicette = getListaRicetteUtente($conn, $_SESSION['user_id']);
}

// Qui il controller non fa redirect, ma viene "incluso" nella View
