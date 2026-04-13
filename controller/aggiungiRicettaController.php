<?php
// controller/aggiungiRicettaController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/ricetta.php';

$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) {
    header("Location: /view/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /view/ilMioRistorante.php");
    exit();
}

$titolo         = trim($_POST['titolo']      ?? '');
$descrizione    = trim($_POST['descrizione'] ?? '');
$difficolta     = strtolower(trim($_POST['difficolta'] ?? ''));
$id_nazionalita = !empty($_POST['id_nazionalita']) ? (int)$_POST['id_nazionalita'] : null;
$id_tipologia   = !empty($_POST['id_tipologia'])   ? (int)$_POST['id_tipologia']   : null;
$dataCreazione  = date('Y-m-d');

$file_copertina = $_FILES['copertina'] ?? null;
$altri_files    = $_FILES['gallery']   ?? [];

$difficolta_valide = ['facile', 'media', 'difficile', 'esperto'];

// Validazione: tutti i campi obbligatori inclusi nazionalità, tipologia e copertina
$copertina_ok = $file_copertina
    && isset($file_copertina['error'])
    && $file_copertina['error'] === UPLOAD_ERR_OK;

if (
    empty($titolo)
    || empty($descrizione)
    || !in_array($difficolta, $difficolta_valide)
    || empty($id_nazionalita)
    || empty($id_tipologia)
    || !$copertina_ok
) {
    header("Location: /view/aggiungiRicetta.php?error=campi_mancanti");
    exit();
}

$id_nuova_ricetta = aggiungiRicetta(
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

if ($id_nuova_ricetta) {
    header("Location: /view/aggiungiPasso.php?id_ricetta=" . $id_nuova_ricetta);
    exit();
}

header("Location: /view/aggiungiRicetta.php?error=campi_mancanti");
exit();