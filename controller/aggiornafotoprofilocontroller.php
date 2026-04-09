<?php
// controller/aggiornafotoprofiloController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/user.php';

$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) {
    header("Location: /view/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /view/ilMioRistorante.php");
    exit();
}

$file = $_FILES['foto_profilo'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    header("Location: /view/ilMioRistorante.php?error=upload_fallito");
    exit();
}

// Validazione tipo file
$tipi_consentiti = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $tipi_consentiti)) {
    header("Location: /view/ilMioRistorante.php?error=tipo_non_valido");
    exit();
}

// Dimensione max 5MB
if ($file['size'] > 5 * 1024 * 1024) {
    header("Location: /view/ilMioRistorante.php?error=file_troppo_grande");
    exit();
}

// Cartella di destinazione
$cartella = __DIR__ . "/../uploads/profili/";
if (!is_dir($cartella)) {
    mkdir($cartella, 0777, true);
}

$estensione   = pathinfo($file['name'], PATHINFO_EXTENSION);
$nome_file    = "profilo_" . $id_utente . "_" . time() . "." . strtolower($estensione);
$destinazione = $cartella . $nome_file;

if (!move_uploaded_file($file['tmp_name'], $destinazione)) {
    header("Location: /view/ilMioRistorante.php?error=salvataggio_fallito");
    exit();
}

// Path relativo per il DB e per l'HTML
$url_relativa = "uploads/profili/" . $nome_file;

// Elimina la vecchia foto se non è quella di default
$vecchia_url = $_SESSION['fotoProfilo'] ?? '';
if (!empty($vecchia_url) && $vecchia_url !== '/img/fotoProfilo.jpg') {
    $file_vecchio = __DIR__ . "/../" . ltrim($vecchia_url, '/');
    if (file_exists($file_vecchio)) {
        unlink($file_vecchio);
    }
}

// Aggiorna il DB
$esito = aggiornafotoProfilo($conn, $id_utente, '/' . $url_relativa);

if ($esito) {
    $_SESSION['fotoProfilo'] = '/' . $url_relativa;
    header("Location: /view/ilMioRistorante.php?success=foto_aggiornata");
} else {
    header("Location: /view/ilMioRistorante.php?error=db_fallito");
}
exit();