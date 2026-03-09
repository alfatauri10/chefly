<?php
session_start();
require_once "../include/connessione.php";

// RIMOSSO 'global $conn': qui non serve e può creare conflitti.
// AGGIUNTO: Controllo se la connessione esiste davvero
if (!isset($conn) || $conn === false) {
    die("Errore: Variabile di connessione non trovata o fallita.");
}

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

    $stmt = mysqli_prepare($conn, "SELECT id, nome, cognome, password, idRuolo FROM utenti WHERE mail = ?");

    // AGGIUNTO: Controllo se lo statement è stato preparato correttamente
    if ($stmt) {
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

                header("Location: index.php");
                exit;
            } else {
                header("Location: login.php?error=Password errata");
                exit; // AGGIUNTO exit per fermare lo script
            }
        } else {
            header("Location: login.php?error=Email non registrata");
            exit; // AGGIUNTO exit per fermare lo script
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
} else {
    header("Location: login.php");
    exit;
}
?>
