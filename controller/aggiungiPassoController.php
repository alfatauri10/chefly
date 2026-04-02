<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../include/connessione.php';
require_once '../model/passo.php';
require_once '../model/ricetta.php';

$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) {
    header("Location: ../view/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/ilMioRistorante.php");
    exit();
}

$id_ricetta  = !empty($_POST['id_ricetta'])  ? (int)$_POST['id_ricetta']  : null;
$titolo      = trim($_POST['titolo']         ?? '');
$descrizione = trim($_POST['descrizione']    ?? '');
$durata      = !empty($_POST['durata'])      ? (int)$_POST['durata']      : null;
$tempoCottura = !empty($_POST['tempoCottura']) ? (int)$_POST['tempoCottura'] : null;
$tempoRiposo  = !empty($_POST['tempoRiposo'])  ? (int)$_POST['tempoRiposo']  : null;
$idCottura    = !empty($_POST['idCottura'])    ? (int)$_POST['idCottura']    : null;

// Ingredienti: array [ id_ingrediente => dose ] costruito nel form
// es. <input name="ingredienti[12]" value="200g">
$ingredienti = $_POST['ingredienti'] ?? [];

$mediaPasso = $_FILES['mediaPasso'] ?? [];

// Validazione minima
if (!$id_ricetta || empty($titolo) || empty($descrizione) || !$durata) {
    header("Location: ../view/aggiungiPasso.php?id_ricetta=" . ($id_ricetta ?? '') . "&error=campi_mancanti");
    exit();
}

// Verifica che la ricetta appartenga all'utente (sicurezza)
$ricetta = getRicettaByIdDB($conn, $id_ricetta);
if (!$ricetta || $ricetta['idCreatore'] != $id_utente) {
    header("Location: ../view/ilMioRistorante.php?error=accesso_negato");
    exit();
}

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
    $ingredienti
);

if ($id_passo) {
    // Torna alla stessa pagina per aggiungere un altro passo, o vai alla lista
    $azione = $_POST['azione'] ?? 'altro_passo';
    if ($azione === 'fine') {
        header("Location: ../view/ilMioRistorante.php?success=ricetta_creata");
    } else {
        header("Location: ../view/aggiungiPasso.php?id_ricetta=" . $id_ricetta . "&success=passo_aggiunto");
    }
    exit();
}

header("Location: ../view/aggiungiPasso.php?id_ricetta=" . $id_ricetta . "&error=errore_inserimento");
exit();