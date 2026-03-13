<?php
// controller/registrazioneController.php
require_once '../include/connessione.php';
require_once '../model/user.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $res = registraUtente($conn, $_POST['nome'], $_POST['cognome'], $_POST['username'], $_POST['mail'], $_POST['password'], $_POST['biografia']);

    if ($res == "success") {
        header("Location: ../index.php?msg=successRegister");
        exit();
    }elseif ($res == "email") {
        header("Location: ../view/signup.php?error=mail");
        exit();
    }elseif ($res == "username") {
        header("Location: ../view/signup.php?error=username");
        exit();
    }else{
        header("Location: ../view/signup.php");
        exit();
    }
}