<?php
require_once "../include/connessione.php"; // prima includi la connessione
global $conn;



$punteggioAttuale = 0; // di default
$nome = mysqli_real_escape_string($conn, $_POST['nome']);
$cognome = mysqli_real_escape_string($conn, $_POST['cognome']);
$mail = mysqli_real_escape_string($conn, $_POST['mail']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$biografia = mysqli_real_escape_string($conn, $_POST['biografia']);

// idTimer = 1, idLivello = 1, idRuolo = 0
$stringaSql = "INSERT INTO Utenti 
    (punteggioAttuale, nome, cognome, mail, password, biografia, idTimer, idLivello, idRuolo)
    VALUES ($punteggioAttuale,'$nome','$cognome','$mail','$password','$biografia',1,1,1)";

if (mysqli_query($conn, $stringaSql)) {
    header("Location: ../view/logIn.php");
    exit;
} else {
    echo "Errore: " . mysqli_error($conn);
}

mysqli_close($conn);
?>