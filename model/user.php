<?php
// model/user.php


/**
 * Registra un nuovo utente con il ruolo USER (ID 1)
 */
function emailEsiste($conn, $mail) {
    $sql = "SELECT id FROM utenti WHERE mail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();

    $esiste = $result->num_rows > 0;

    $stmt->close();
    return $esiste;
}

function usernameEsiste($conn, $username) {
    $sql = "SELECT id FROM utenti WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $esiste = $result->num_rows > 0;

    $stmt->close();
    return $esiste;
}


function registraUtente($conn, $nome, $cognome, $username, $mail, $password, $biografia) {

    $punteggioAttuale = 0;
    $idTimer = 1;
    $idLivello = 1;
    $idRuolo = 1;

    // Controllo email
    $sql = "SELECT id FROM utenti WHERE mail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "email";
    }

    $stmt->close();

    // Controllo username
    $sql = "SELECT id FROM utenti WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return "username";
    }

    $stmt->close();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO utenti (username, punteggioAttuale, nome, cognome, mail, password, biografia, idTimer, idLivello, idRuolo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sisssssiii",
        $username,
        $punteggioAttuale,
        $nome,
        $cognome,
        $mail,
        $password_hash,
        $biografia,
        $idTimer,
        $idLivello,
        $idRuolo
    );

    $res = $stmt->execute();

    $stmt->close();

    if ($res) {
        return "success";
    }

    return "error";
}
/**
 * Cerca un utente per il Login
 */
function findUserByMail($conn, $mail) {
    $sql = "SELECT * FROM utenti WHERE mail = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $mail);
    $stmt->execute();

    $result = $stmt->get_result(); // Necessario con MySQLi per ottenere i dati
    return $result->fetch_assoc();
}

/**
 * getUtenteById()
 * @descrizione: Recupera tutti i dati di un utente tramite il suo ID.
 * Usata per popolare la sezione profilo di ilMioRistorante.
 */
function getUtenteById($conn, $id_utente) {
    $sql = "SELECT id, nome, cognome, username, mail, biografia, urlFotoProfilo, punteggioAttuale
            FROM utenti
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $result = $stmt->get_result();
    $utente = $result->fetch_assoc();
    $stmt->close();
    return $utente;
}

/**
 * getStatisticheUtente()
 * @descrizione: Recupera in un colpo solo il numero di ricette create,
 * follower (chi segue l'utente) e seguiti (chi l'utente segue).
 * Ritorna array ['num_ricette' => int, 'num_follower' => int, 'num_seguiti' => int]
 */
function getStatisticheUtente($conn, $id_utente) {
    $stats = [
        'num_ricette'  => 0,
        'num_follower' => 0,
        'num_seguiti'  => 0,
    ];

    // Numero ricette create
    $sql_r = "SELECT COUNT(*) AS tot FROM ricette WHERE idCreatore = ?";
    $stmt_r = $conn->prepare($sql_r);
    $stmt_r->bind_param("i", $id_utente);
    $stmt_r->execute();
    $stats['num_ricette'] = (int)$stmt_r->get_result()->fetch_assoc()['tot'];
    $stmt_r->close();

    // Numero follower (altri utenti che seguono questo utente)
    $sql_f = "SELECT COUNT(*) AS tot FROM follower WHERE idSeguito = ?";
    $stmt_f = $conn->prepare($sql_f);
    $stmt_f->bind_param("i", $id_utente);
    $stmt_f->execute();
    $stats['num_follower'] = (int)$stmt_f->get_result()->fetch_assoc()['tot'];
    $stmt_f->close();

    // Numero seguiti (utenti che questo utente segue)
    $sql_s = "SELECT COUNT(*) AS tot FROM follower WHERE idSegue = ?";
    $stmt_s = $conn->prepare($sql_s);
    $stmt_s->bind_param("i", $id_utente);
    $stmt_s->execute();
    $stats['num_seguiti'] = (int)$stmt_s->get_result()->fetch_assoc()['tot'];
    $stmt_s->close();

    return $stats;
}

function aggiornafotoProfilo($conn, $id_utente, $url_foto) {
    $sql  = "UPDATE utenti SET urlFotoProfilo = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $url_foto, $id_utente);
    $esito = $stmt->execute();
    $stmt->close();
    return $esito;
}