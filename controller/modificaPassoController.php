<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/passo.php';


$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) {
    header("Location: /view/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /view/ilMioRistorante.php");
    exit();
}

$id_passo    = !empty($_POST['id_passo'])    ? (int)$_POST['id_passo']    : null;
$id_ricetta  = !empty($_POST['id_ricetta'])  ? (int)$_POST['id_ricetta']  : null;
$titolo      = trim($_POST['titolo']         ?? '');
$descrizione = trim($_POST['descrizione']    ?? '');
$durata      = !empty($_POST['durata'])      ? (int)$_POST['durata']      : null;
$tempoCottura = !empty($_POST['tempoCottura']) ? (int)$_POST['tempoCottura'] : null;
$tempoRiposo  = !empty($_POST['tempoRiposo'])  ? (int)$_POST['tempoRiposo']  : null;
$idCottura    = !empty($_POST['idCottura'])    ? (int)$_POST['idCottura']    : null;

$ingredienti          = $_POST['ingredienti']       ?? [];
$id_foto_da_eliminare = $_POST['foto_da_eliminare'] ?? [];
$nuovi_media          = $_FILES['mediaPasso']        ?? [];

if (!$id_passo || !$id_ricetta || empty($titolo) || empty($descrizione) || !$durata) {
    header("Location: /view/modificaPasso.php?id_passo=" . ($id_passo ?? '') . "&id_ricetta=" . ($id_ricetta ?? '') . "&error=campi_mancanti");
    exit();
}

$esito = updatePasso(
    $conn,
    $id_passo,
    $id_utente,
    $titolo,
    $descrizione,
    $durata,
    $tempoCottura,
    $tempoRiposo,
    $idCottura,
    $nuovi_media,
    $id_foto_da_eliminare,
    $ingredienti
);

if ($esito) {
    header("Location: /view/aggiungiPasso.php?id_ricetta=" . $id_ricetta . "&success=passo_modificato");
    exit();
}

header("Location: /view/modificaPasso.php?id_passo=" . $id_passo . "&id_ricetta=" . $id_ricetta . "&error=errore_modifica");
exit();