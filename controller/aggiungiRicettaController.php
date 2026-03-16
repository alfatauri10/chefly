<?php
// controller/aggiungiVinoController.php
require_once '../include/connessione.php';
require_once '../model/ricetta.php';

// session_start() va SEMPRE all'inizio, prima di ogni logica
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Recuperiamo l'ID dell'utente dalla sessione
$id_utente = $_SESSION['user_id'] ?? null;

if (!$id_utente) {
    header("Location: ../view/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome_ricetta'] ?? ''; // todo: dipende dalla form = campo tabella db
    $cantina = $_POST['cantina'] ?? '';
    $immagine = $_FILES['immagine_vino'] ?? null;

    // chiamo Vino Model che fa tutto (upload + DB)
    $res = aggiungiRicetta($conn, $id_utente, $nome, $cantina, $immagine);

    if ($res) {
        $_SESSION['messaggio'] = "Vino aggiunto con successo!";
        header("Location: ../view/listaVini.php"); // Redirect al SUCCESSO
        exit(); // FERMA TUTTO QUI
    } else {
        $_SESSION['errore'] = "Errore durante l'aggiunta.";
        header("Location: ../view/aggiungiVino.php"); // Riprova in caso di errore
        exit();
    }
}

// Se qualcuno prova ad accedere al controller senza POST (es. via URL), lo rimandiamo alla lista
header("Location: ../view/listaVini.php");
exit();