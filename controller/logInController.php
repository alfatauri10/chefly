<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/user.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = findUserByMail($conn, $_POST['mail']);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id']      = $user['id'];
        $_SESSION['mail']         = $user['mail'];
        $_SESSION['user_nome']    = $user['nome'];
        $_SESSION['user_cognome'] = $user['cognome'];
        $_SESSION['user_ruolo']   = $user['idRuolo'];
        $_SESSION['username']     = $user['userName'];
        $_SESSION['fotoProfilo']  = $user['urlFotoProfilo'];

        header("Location: /index.php");
        exit();
    }

    header("Location: /view/login.php?error=1");
    exit();
}