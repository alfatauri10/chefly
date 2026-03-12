<?php
// controller/registrazioneController.php
require_once '../include/connessione.php';
require_once '../model/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $res = registraUtente($conn, $_POST['nome'], $_POST['cognome'], $_POST['username'], $_POST['mail'], $_POST['password'], $_POST['biografia']);

    if ($res) {
        header("Location: ../index.php?msg=success");
        exit();
    } else {
        header("Location: ../view/registrazione.php?error=1");
        exit();
    }
}