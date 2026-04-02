<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../include/connessione.php';
require_once '../model/passo.php';

$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) {
    header("Location: ../view/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../view/ilMioRistorante.php");
    exit();
}

$id_passo   = !empty($_POST['id_passo'])   ? (int)$_POST['id_passo']   : null;
$id_ricetta = !empty($_POST['id_ricetta']) ? (int)$_POST['id_ricetta'] : null;

if (!$id_passo || !$id_ricetta) {
    header("Location: ../view/ilMioRistorante.php?error=errore_cancellazione");
    exit();
}

$esito = deletePasso($conn, $id_passo, $id_utente);

if ($esito) {
    // Torna alla pagina passi della ricetta, così l'utente vede la lista aggiornata
    header("Location: ../view/aggiungiPasso.php?id_ricetta=" . $id_ricetta . "&deleted=1");
    exit();
}

header("Location: ../view/aggiungiPasso.php?id_ricetta=" . $id_ricetta . "&error=errore_cancellazione");
exit();