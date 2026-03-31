<?php
// controller/aggiungiRicettaController.php

// 1. Avvio sessione sicuro
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Inclusioni necessarie
require_once '../include/connessione.php';
require_once '../model/ricetta.php';

// Recuperiamo l'ID dell'utente dalla sessione
$id_utente = $_SESSION['user_id'] ?? null;

// Se non c'è un utente loggato, rimandiamo al login
if (!$id_utente) {
    header("Location: ../view/login.php");
    exit();
}

// Controlliamo che la richiesta sia effettivamente un POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // -- RECUPERO DATI TESTUALI --
    $titolo = $_POST['titolo'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $difficolta = $_POST['difficolta'] ?? '';
    $id_nazionalita = !empty($_POST['id_nazionalita']) ? (int)$_POST['id_nazionalita'] : null;
    $id_tipologia = !empty($_POST['id_tipologia']) ? (int)$_POST['id_tipologia'] : null;
    $dataCreazione = date('Y-m-d H:i:s');

    // -- RECUPERO FILE --
    $file_copertina = $_FILES['copertina'] ?? null;
    $altri_files = $_FILES['gallery'] ?? [];

    // NOTA: Abbiamo rimosso il recupero di array per "ingredienti" e "cotture".
    // Adesso questi dati verranno richiesti all'utente in un secondo momento,
    // quando creerà i "Passi" (step) per questa specifica ricetta.

    // -- VALIDAZIONE MINIMA E SALVATAGGIO --
    if (!empty($titolo) && !empty($descrizione) && !empty($difficolta)) {

        // Chiamata al modello aggiornato (senza ingredienti e cotture)
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
            // Successo!
            // In futuro potresti voler reindirizzare l'utente a una pagina tipo "aggiungiPassi.php?id_ricetta=" . $id_nuova_ricetta
            // Per ora lo rimandiamo alla lista ricette.
            header("Location: ../view/listaRicetteUtente.php?success=ricetta_creata");
            exit();
        }
    }

    // Se mancano dati o l'inserimento fallisce, torniamo al form con un errore
    header("Location: ../view/aggiungiRicetta.php?error=campi_mancanti");
    exit();
} else {
    // Se qualcuno prova ad accedere a questo file direttamente via URL
    header("Location: ../view/listaRicetteUtente.php");
    exit();
}
?>