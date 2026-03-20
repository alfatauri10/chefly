<?php
// controller/cancellaRicettaController.php

// session_start() va SEMPRE all'inizio
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../model/ricetta.php';
require_once '../include/connessione.php';

// Recuperiamo l'ID dell'utente dalla sessione
$id_utente = $_SESSION['user_id'] ?? null;

if (!$id_utente) {
    header("Location: login.php");
    exit();
}

$id_ricetta = $_POST['id_ricetta'] ?? null;

if ($id_ricetta) {
    // Verifichiamo prima se la ricetta esiste ed è dell'utente loggato (Sicurezza fondamentale)
    $ricetta = getRicettaByIdDB($conn, $id_ricetta);

    if ($ricetta && $ricetta['idCreatore'] == $id_utente) {

        // deleteRicettaDB si occupa di eliminare prima i media (foto) e poi la ricetta
        $check = deleteRicettaDB($conn, $id_ricetta);

        if ($check) {
            header("Location: ../view/listaRicetteUtente.php?deleted=1");
            exit();
        }
    }
}

// In caso di errore o se l'utente prova a cancellare una ricetta non sua
header("Location: ../view/listaRicetteUtente.php?error=errore_cancellazione");
exit();
?>