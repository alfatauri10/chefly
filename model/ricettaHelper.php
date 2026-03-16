<?php
/*
 * @input:
 *  file_php: file da caricare
 *  id_utente : id dell'utente per la creazione della tabella di upload
 *
 *  La funzione carica il file nella cartella dell'utente
 *
 * @output : URL (path relativo) del file caricato
 */
//uploadFile(file, id_utente) sul fileSystem

// deleteFile(id_file, idUtente) sul fileSystem

function uploadFile($file_php, $id_utente,$id_ricetta,$is_copertina)
{
    // Se non c'è il file o c'è un errore di caricamento, esci subito
    if (!isset($file_php) || $file_php['error'] != UPLOAD_ERR_OK) {
        return null;
    }

    // Percorso della cartella utente
    $cartella = "../uploads/user_" . $id_utente . "/ricetta_".$id_ricetta;

    // Crea la cartella se non esiste
    // 0777 imposta i permessi di lettura e scrittura
    // true crea le sottocartelle
    if (!is_dir($cartella)) {
        mkdir($cartella, 0777, true);
    }

    // Crea un nome file unico con la sua estensione originale
    $estensione = pathinfo($file_php['name'], PATHINFO_EXTENSION);
    $nome_file = ($is_copertina ? "copertina" :  uniqid()) . "." . $estensione;
    $destinazione = $cartella . $nome_file;

    // Sposta il file dal deposito temporaneo (dove PHP salva i file uploadatI) alla cartella finale
    if (move_uploaded_file($file_php['tmp_name'], $destinazione)) {
        return "uploads/user_" . $id_utente . "/ricetta_".$id_ricetta . "/" . $nome_file;
    }

    return null;

}

