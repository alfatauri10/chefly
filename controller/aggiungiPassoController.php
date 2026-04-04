<?php
// controller/aggiungiPassoController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../include/connessione.php';
require_once '../model/passo.php';

// Controllo autenticazione
$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) {
    header("Location: ../view/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/listaRicetteUtente.php");
    exit();
}

// --- RECUPERO DATI DAL FORM ---
$id_ricetta    = isset($_POST['id_ricetta'])    ? (int)$_POST['id_ricetta']    : null;
$numero_passo  = isset($_POST['numero_passo'])  ? (int)$_POST['numero_passo']  : 1;
$titolo        = trim($_POST['titolo']        ?? '');
$descrizione   = trim($_POST['descrizione']   ?? '');
$durata        = !empty($_POST['durata'])        ? (int)$_POST['durata']        : 0;
$tempoCottura  = !empty($_POST['tempoCottura']) ? (int)$_POST['tempoCottura']  : null;
$tempoRiposo   = !empty($_POST['tempoRiposo'])  ? (int)$_POST['tempoRiposo']   : null;
$idCottura     = !empty($_POST['idCottura'])    ? (int)$_POST['idCottura']     : null;
$is_ultimo     = isset($_POST['is_ultimo_passo']) && $_POST['is_ultimo_passo'] == '1';

// File media del passo
$mediaPasso = $_FILES['mediaPasso'] ?? [];

// Ingredienti: il form invia ingredienti[id][] e ingredienti[dose][]
$ingredienti_input = $_POST['ingredienti'] ?? [];
$ingredienti = [];
if (!empty($ingredienti_input['id']) && is_array($ingredienti_input['id'])) {
    foreach ($ingredienti_input['id'] as $key => $id_ing) {
        if (!empty($id_ing)) {
            $dose = $ingredienti_input['dose'][$key] ?? '';
            $ingredienti[(int)$id_ing] = trim($dose);
        }
    }
}

// --- VALIDAZIONE ---
if (!$id_ricetta || empty($titolo) || empty($descrizione) || $durata <= 0) {
    header("Location: ../view/aggiungiPasso.php?id_ricetta={$id_ricetta}&error=campi_mancanti");
    exit();
}

// --- INSERIMENTO NEL DB tramite model ---
$id_passo = insertPasso(
    $conn,
    $titolo,
    $tempoCottura,
    $tempoRiposo,
    $descrizione,
    $durata,
    $id_ricetta,
    $idCottura,
    $mediaPasso,
    $ingredienti,
    $id_utente
);

if (!$id_passo) {
    header("Location: ../view/aggiungiPasso.php?id_ricetta={$id_ricetta}&error=inserimento_fallito");
    exit();
}

// --- REDIRECT in base alla scelta dell'utente ---
if ($is_ultimo) {
    // Ha dichiarato che è l'ultimo passo → torna alla lista con messaggio di successo
    header("Location: ../view/listaRicetteUtente.php?success=ricetta_completata");
} else {
    // Aggiunge un altro passo → ricarica la stessa pagina con lo stesso id_ricetta
    header("Location: ../view/aggiungiPasso.php?id_ricetta={$id_ricetta}&success=passo_aggiunto");
}
exit();
?>