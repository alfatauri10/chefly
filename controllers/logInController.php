<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../views/logIn.php");
    exit;
}

include "../include/connessione.php";

$mail = filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL);
$password = $_POST['password'];

if (!$mail || empty($password)) {
    header("Location: ../views/logIn.php?error=Email o password mancanti");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT id, nome, cognome, password, idRuolo FROM Utenti WHERE mail = ?");

mysqli_stmt_bind_param($stmt, "s", $mail);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 1) {

    mysqli_stmt_bind_result($stmt, $id, $nome, $cognome, $hashedPassword, $idRuolo);
    mysqli_stmt_fetch($stmt);

    if (password_verify($password, $hashedPassword)) {

        $_SESSION['user_id'] = $id;
        $_SESSION['user_nome'] = $nome;
        $_SESSION['user_cognome'] = $cognome;
        $_SESSION['user_ruolo'] = $idRuolo;

        header("Location: ../index.php");
        exit;

    } else {
        header("Location: ../views/logIn.php?error=Password errata");
        exit;
    }

} else {
    header("Location: ../views/logIn.php?error=Email non registrata");
    exit;
}