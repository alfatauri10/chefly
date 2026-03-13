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