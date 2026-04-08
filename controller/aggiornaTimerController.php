<?php
// controller/aggiornaTimerController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/timer.php';

$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) {
    header("Location: /view/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /view/ilMioRistorante.php");
    exit();
}

// Legge e valida i tre colori hex
$coloreSfondo   = validaColoreHex(trim($_POST['coloreSfondo']   ?? ''));
$coloreLancetta = validaColoreHex(trim($_POST['coloreLancetta'] ?? ''));
$coloreNumeri   = validaColoreHex(trim($_POST['coloreNumeri']   ?? ''));

if (!$coloreSfondo || !$coloreLancetta || !$coloreNumeri) {
    header("Location: /view/ilMioRistorante.php?error=colore_non_valido#sezione-timer");
    exit();
}

$esito = aggiornaTimerUtente($conn, $id_utente, $coloreSfondo, $coloreLancetta, $coloreNumeri);

if ($esito) {
    header("Location: /view/ilMioRistorante.php?success=timer_aggiornato#sezione-timer");
} else {
    header("Location: /view/ilMioRistorante.php?error=timer_fallito#sezione-timer");
}
exit();