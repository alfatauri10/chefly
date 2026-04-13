<?php
// controller/modificaRicettaController.php
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

$id_ricetta     = !empty($_POST['id_ricetta'])  ? (int)$_POST['id_ricetta']  : null;
$titolo         = trim($_POST['titolo']         ?? '');
$descrizione    = trim($_POST['descrizione']    ?? '');
$difficolta     = strtolower(trim($_POST['difficolta'] ?? ''));
$id_nazionalita = !empty($_POST['id_nazionalita']) ? (int)$_POST['id_nazionalita'] : null;
$id_tipologia   = !empty($_POST['id_tipologia'])   ? (int)$_POST['id_tipologia']   : null;

$file_copertina       = $_FILES['copertina']       ?? null;
$nuovi_file_gallery   = $_FILES['gallery']          ?? [];
$id_foto_da_eliminare = $_POST['foto_da_eliminare'] ?? [];

$difficolta_valide = ['facile', 'media', 'difficile', 'esperto'];

// Nazionalità e tipologia ora OBBLIGATORIE
if (
    !$id_ricetta
    || empty($titolo)
    || empty($descrizione)
    || !in_array($difficolta, $difficolta_valide)
    || empty($id_nazionalita)
    || empty($id_tipologia)
) {
    header("Location: /view/modificaRicetta.php?id_ricetta=" . ($id_ricetta ?? '') . "&error=campi_mancanti");
    exit();
}

$esito = updateRicettaByID(
    $conn,
    $id_ricetta,
    $id_utente,
    $titolo,
    $descrizione,
    $difficolta,
    $id_nazionalita,
    $id_tipologia,
    $file_copertina,
    $nuovi_file_gallery,
    $id_foto_da_eliminare
);

if ($esito) {
    header("Location: /view/ilMioRistorante.php?success=ricetta_modificata");
    exit();
}

header("Location: /view/modificaRicetta.php?id_ricetta=" . $id_ricetta . "&error=errore_modifica");
exit();