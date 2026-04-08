<?php
// controller/salvaRicetta.php

session_start();

require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/ricetta.php';
require_once __DIR__ . '/../model/passo.php';

// --- SICUREZZA: solo utenti loggati ---
if (!isset($_SESSION['id_utente'])) {
    header('Location: ../view/login.php');
    exit;
}

// --- Solo POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/creaRicetta.php');
    exit;
}

$id_utente = (int)$_SESSION['id_utente'];

// ============================================================
// 1. RACCOLTA DATI PRINCIPALI DELLA RICETTA
// ============================================================

$titolo         = trim($_POST['titolo']         ?? '');
$descrizione    = trim($_POST['descrizione']    ?? '');
$difficolta     = trim($_POST['difficolta']     ?? '');
$id_nazionalita = !empty($_POST['id_nazionalita']) ? (int)$_POST['id_nazionalita'] : null;
$id_tipologia   = !empty($_POST['id_tipologia'])   ? (int)$_POST['id_tipologia']   : null;
$dataCreazione  = date('Y-m-d');

// ============================================================
// 2. VALIDAZIONE CAMPI OBBLIGATORI
// ============================================================

if (empty($titolo) || empty($descrizione) || empty($difficolta)) {
    $_SESSION['errore'] = "Titolo, descrizione e difficoltà sono obbligatori.";
    header('Location: ../view/creaRicetta.php');
    exit;
}

// ============================================================
// 3. GESTIONE FILE (copertina + galleria)
// ============================================================

$file_copertina = $_FILES['copertina'] ?? null;
$altri_files    = $_FILES['galleria']  ?? [];

// ============================================================
// 4. INSERIMENTO RICETTA NEL DB
// ============================================================

$id_ricetta = aggiungiRicetta(
    $conn,
    $descrizione,
    $titolo,
    $difficolta,
    $id_utente,
    $dataCreazione,
    $id_nazionalita,
    $id_tipologia,
    $file_copertina,
    $altri_files
);

if (!$id_ricetta) {
    $_SESSION['errore'] = "Errore durante il salvataggio della ricetta. Riprova.";
    header('Location: ../view/creaRicetta.php');
    exit;
}

// ============================================================
// 5. REDIRECT AL SUCCESSO
// ============================================================

$_SESSION['successo'] = "Ricetta salvata con successo!";
header('Location: ../view/ricetta.php?id=' . $id_ricetta);
exit;