<?php
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

$id_ricetta = !empty($_POST['id_ricetta']) ? (int)$_POST['id_ricetta'] : null;

if (!$id_ricetta) {
    header("Location: /view/ilMioRistorante.php?error=errore_cancellazione");
    exit();
}

// Verifica che la ricetta esista e appartenga all'utente loggato
$ricetta = getRicettaByIdDB($conn, $id_ricetta);

if (!$ricetta || $ricetta['idCreatore'] != $id_utente) {
    header("Location: /view/ilMioRistorante.php?error=errore_cancellazione");
    exit();
}

$esito = deleteRicettaDB($conn, $id_ricetta);

if ($esito) {
    header("Location: /view/ilMioRistorante.php?deleted=1");
    exit();
}

header("Location: /view/ilMioRistorante.php?error=errore_cancellazione");
exit();