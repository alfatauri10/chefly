<?php
// controller/aggiungiRicettaController.php
require_once '../model/ricetta.php';
require_once '../include/connessione.php'; // La tua connessione al DB

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Recuperiamo l'ID dell'utente dalla sessione
$id_utente = $_SESSION['user_id'] ?? null;

if (!$id_utente) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recupero dati testuali
    $titolo = $_POST['titolo'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $difficolta = $_POST['difficolta'] ?? '';
    $id_nazionalita = $_POST['id_nazionalita'] ?? null;
    $id_tipologia = $_POST['id_tipologia'] ?? null;
    $dataCreazione = date('Y-m-d H:i:s');

    // 2. Recupero File
    $file_copertina = $_FILES['copertina'] ?? null;
    $altri_files = $_FILES['gallery'] ?? [];

    // 3. Validazione minima
    if (!empty($titolo) && !empty($descrizione)) {
        $risultato = aggiungiRicetta($conn, $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia, $file_copertina, $altri_files);

        if ($risultato) {
            header("Location: ../view/listaRicetteUtente.php?success=1");
            exit();
        }
    }

    header("Location: ../view/creaRicetta.php?error=campi_mancanti");
}
