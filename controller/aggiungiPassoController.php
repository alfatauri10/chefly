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
    header("Location: ../view/ilMioRistorante.php");
    exit();
}

// --- RECUPERO DATI DAL FORM ---
$id_ricetta   = isset($_POST['id_ricetta'])   ? (int)$_POST['id_ricetta']   : null;
$titolo       = trim($_POST['titolo']         ?? '');
$descrizione  = trim($_POST['descrizione']    ?? '');
$durata       = !empty($_POST['durata'])       ? (int)$_POST['durata']       : 0;
$tempoCottura = !empty($_POST['tempoCottura']) ? (int)$_POST['tempoCottura'] : null;
$tempoRiposo  = !empty($_POST['tempoRiposo'])  ? (int)$_POST['tempoRiposo']  : null;
$idCottura    = !empty($_POST['idCottura'])    ? (int)$_POST['idCottura']    : null;
$is_ultimo    = isset($_POST['is_ultimo_passo']) && $_POST['is_ultimo_passo'] == '1';

// Posizione scelta dall'utente (null = in fondo)
$posizione    = isset($_POST['posizione']) && $_POST['posizione'] !== '' ? (int)$_POST['posizione'] : null;

// File media del passo
$mediaPasso = $_FILES['mediaPasso'] ?? [];

// Ingredienti
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

// --- INSERIMENTO NEL DB ---
$id_passo = insertPasso(
    $conn,
    $id_utente,
    $id_ricetta,
    $titolo,
    $descrizione,
    $durata,
    $tempoCottura,
    $tempoRiposo,
    $idCottura,
    $mediaPasso,
    $ingredienti,
    $posizione  // null = in fondo, numero = in posizione specifica
);

if (!$id_passo) {
    header("Location: ../view/aggiungiPasso.php?id_ricetta={$id_ricetta}&error=inserimento_fallito");
    exit();
}

// Riordina sempre dopo l'inserimento per avere sequenza pulita
riordinaPassi($conn, $id_ricetta);

// --- REDIRECT ---
if ($is_ultimo) {
    header("Location: ../view/ilMioRistorante.php?success=ricetta_completata");
} else {
    header("Location: ../view/aggiungiPasso.php?id_ricetta={$id_ricetta}&success=passo_aggiunto");
}
exit();