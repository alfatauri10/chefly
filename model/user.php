<?php
// model/user.php


/**
 * Registra un nuovo utente con il ruolo USER (ID 1)
 */
function registraUtente($conn, $nome, $cognome, $username, $mail, $password, $biografia) {

    /* Schema tabella Utenti
    1	id Primaria	int(11)			AUTO_INCREMENT
	2	punteggioAttuale	int(11)
     username
	3	nome	varchar(100)	utf8mb4_general_ci
	4	cognome	varchar(100)	utf8mb4_general_ci
	5	mail Indice	varchar(150)	utf8mb4_general_ci
	6	password	varchar(255)	utf8mb4_general_ci
	7	biografia	text	utf8mb4_general_ci		Sì	NULL
	8	idTimer Indice	int(11)
	9	idLivello Indice	int(11)
	10	idRuolo Indice	int(11)
     */
    // variabile punteggioAttuale di default settato a 0 durante la creazione
    $punteggioAttuale = 0;
    $idTimer = 1;
    $idLivello = 1;
    $idRuolo = 1;


    // Criptiamo la password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // 1. Inserimento Utente
    $sql = "INSERT INTO utenti (username, punteggioAttuale, nome, cognome, mail, password, biografia, idTimer, idLivello, idRuolo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // "sssss" indica che passiamo 5 stringhe
    $stmt->bind_param("sisssssiii", $username, $punteggioAttuale, $nome, $cognome, $mail, $password_hash, $biografia, $idTimer, $idLivello, $idRuolo);    $stmt->execute();

    $utente_id = $conn->insert_id; // Recupera l'ultimo ID inserito
    $stmt->close();
    return $utente_id;
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