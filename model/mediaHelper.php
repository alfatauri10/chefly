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

    // Costruzione del percorso fisico (assicurati che ../uploads abbia i permessi di scrittura)
    $cartella = "../uploads/user_" . $id_utente . "/ricetta_" . $id_ricetta . "/";

    if (!is_dir($cartella)) {
        // 0777 crea la cartella con permessi massimi, in produzione valuta 0755
        mkdir($cartella, 0777, true);
    }

    $estensione = pathinfo($file_php['name'], PATHINFO_EXTENSION);
    $nome_file = ($is_copertina ? "copertina" : uniqid()) . "." . $estensione;
    $destinazione = $cartella . $nome_file;

    if (move_uploaded_file($file_php['tmp_name'], $destinazione)) {
        // Ritorna il percorso relativo che verrà letto dal frontend (senza i ../)
        return "uploads/user_" . $id_utente . "/ricetta_" . $id_ricetta . "/" . $nome_file;
    }

    return null;
}

/* function deleteFile ()
 * @descrizione:
 * Elimina fisicamente il file dal server
 */
function deleteFile($percorso_file) {
    if (empty($percorso_file)) {
        return false;
    }

    $file_fisico = "../" . $percorso_file;
    if (file_exists($file_fisico)) {
        return unlink($file_fisico);
    }

    return false;
}




