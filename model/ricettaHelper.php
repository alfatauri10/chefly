<?php

/* function uploadFile ()
 * @descrizione :
 * Controlla se il file inviato dall'utente è valido.
 * Se lo è, crea fisicamente una cartella sul server con un percorso personalizzato (es: uploads/user_1/ricetta_42/
 */
function uploadFile($file_php, $id_utente, $id_ricetta, $is_copertina) {
    if (!isset($file_php) || $file_php['error'] != UPLOAD_ERR_OK) {
        return null;
    }

    // Aggiunto lo slash finale alla cartella
    $cartella = "../uploads/user_" . $id_utente . "/ricetta_" . $id_ricetta . "/";

    if (!is_dir($cartella)) {
        mkdir($cartella, 0777, true);
    }

    $estensione = pathinfo($file_php['name'], PATHINFO_EXTENSION);
    $nome_file = ($is_copertina ? "copertina" : uniqid()) . "." . $estensione;
    $destinazione = $cartella . $nome_file;

    if (move_uploaded_file($file_php['tmp_name'], $destinazione)) {
        // Ritorno il percorso relativo da salvare nel DB
        return "uploads/user_" . $id_utente . "/ricetta_" . $id_ricetta . "/" . $nome_file;
    }

    return null;
}



