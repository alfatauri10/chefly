<?php
session_start(); // necessario per le sessioni
require_once "../include/connessione.php"; // prima includi la connessione
global $conn;

// Sanificazione base
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mail = filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (!$mail || empty($password)) {
        header("Location: login.php?error=Email o password mancanti");
        exit;
    }

    // Prepara query per prendere l'utente
    $stmt = mysqli_prepare($conn, "SELECT id, nome, cognome, password, idRuolo FROM utenti WHERE mail = ?");
    mysqli_stmt_bind_param($stmt, "s", $mail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 1) {
        mysqli_stmt_bind_result($stmt, $id, $nome, $cognome, $hashedPassword, $idRuolo);
        mysqli_stmt_fetch($stmt);

        if (password_verify($password, $hashedPassword)) {
            // Login corretto → salva dati in sessione
            $_SESSION['user_id'] = $id;
            $_SESSION['user_nome'] = $nome;
            $_SESSION['user_cognome'] = $cognome;
            $_SESSION['user_ruolo'] = $idRuolo;

            header("Location: index.php");
            exit;
        } else {
            // Password errata
            header("Location: login.php?error=Password errata");
            exit;
        }
    } else {
        // Utente non trovato
        header("Location: login.php?error=Email non registrata");
        exit;
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    // Accesso diretto al file non permesso
    header("Location: login.php");
    exit;
}
?>