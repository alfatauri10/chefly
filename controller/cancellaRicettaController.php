<?php
// controller/cancellaRicettaController.php
require_once '../model/ricetta.php';
require_once '../include/connessione.php';

// session_start() va SEMPRE all'inizio, prima di ogni logica
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Recuperiamo l'ID dell'utente dalla sessione
$id_utente = $_SESSION['user_id'] ?? null;

if (!$id_utente) {
    header("Location: login.php");
    exit();
}
$id_ricetta = $_POST['id_ricetta'] ?? null;


if ($id_ricetta) {
    // Verifichiamo prima se la ricetta è dell'utente (Sicurezza)
    $ricetta = getRicettaByIdDB($conn, $id_ricetta);

    if ($ricetta && $ricetta['idcreatore'] == $id_utente) {
        $check = rimuoviRicettaCompleta($conn, $id_ricetta, $id_utente);

        if ($check) {
            header("Location: ../view/lsitaRicetteUtente.php?deleted=1");
            exit();
        }
    }
}

header("Location: ../view/lsitaRicetteUtente.php?error=errore_cancellazione");
