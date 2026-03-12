<?php
// controller/loginController.php
session_start();
require_once '../include/connessione.php';
require_once '../model/user.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = findUserByMail($conn, $_POST['mail']);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        // Login OK: salviamo TUTTI i dati necessari in sessione
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['mail']    = $user['mail'];
        $_SESSION['user_nome']    = $user['nome'];    // Aggiunto
        $_SESSION['user_cognome'] = $user['cognome']; // Aggiunto
        $_SESSION['user_ruolo']   = $user['idRuolo']; // Aggiunto (usa il nome esatto della colonna DB)

        header("Location: ../index.php");
        exit();
    }
    else {
        // Login Fallito
        header("Location: ../view/login.php?error=1");
        exit();
    }
}
?>