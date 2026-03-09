<?php
    session_start();


    //definizione delle FUNZIONI
    /*
    * TODO:INSERIRLE IN UN FILE APPOSITO DI INCLUSIONE CHE CONTERRA' TUTTE
    * LE DEFINIZIONI DELLE FUNZIONI
    */
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }


    // se sono passato dalla LOGIN
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        include "../include/connessione.php";

        /*
        * Controllo  connessione DB:
        * Se $conn e' NULL oppure la connessione e' fallita (false)
         */
        if (!isset($conn) || !$conn) {
            die("Errore: Variabile di connessione non trovata o fallita.");
        }

        $mail = filter_var($_POST['mail'], FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'];

        if (!$mail || empty($password)) {
            header("Location: logIn.php?error=Email o password mancanti");
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

                    header("Location: ../index.php");
                    exit;
                } else {
                    header("Location: logIn.php?error=Password errata");
                    exit; // AGGIUNTO exit per fermare lo script
                }
            } else {
                header("Location: logIn.php?error=Email non registrata");
                exit; // AGGIUNTO exit per fermare lo script
            }

        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);

    } else { //Forzo passaggio da login
        header("Location: ../views/logIn.php");
    }


?>
