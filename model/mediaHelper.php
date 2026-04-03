<?php

/**
 * Gestisce il caricamento (upload) di un file sul server.
 * * @param array $file_php     L'elemento dell'array $_FILES (es: $_FILES['immagine'])
 * @param int   $id_utente    ID dell'utente per creare la sottocartella specifica
 * @param int   $id_ricetta   ID della ricetta per organizzare i file
 * @param bool  $is_copertina Se true, il file si chiamerà "copertina", altrimenti avrà un ID univoco
 * @return string|null        Ritorna il percorso del file per il DB, o null in caso di errore
 */
function uploadFile($file_php, $id_utente, $id_ricetta, $is_copertina) {
    if (!isset($file_php) || $file_php['error'] != UPLOAD_ERR_OK) {
        return null;
    }

    $cartella = __DIR__ . "/../uploads/user_" . $id_utente . "/ricetta_" . $id_ricetta . "/";

    if (!is_dir($cartella)) {
        mkdir($cartella, 0777, true);
    }

    $estensione = pathinfo($file_php['name'], PATHINFO_EXTENSION);
    $nome_file  = ($is_copertina ? "copertina" : uniqid()) . "." . $estensione;
    $destinazione = $cartella . $nome_file;

    if (move_uploaded_file($file_php['tmp_name'], $destinazione)) {
        // Il path salvato nel DB rimane relativo alla root (per le img in HTML)
        return "uploads/user_" . $id_utente . "/ricetta_" . $id_ricetta . "/" . $nome_file;
    }
    return null;
}

/**
 * Elimina fisicamente un file dal server.
 * * @param string $percorso_file Il percorso salvato nel database (es: uploads/user_1/...)
 * @return bool                 True se eliminato con successo, false altrimenti
 */
function deleteFile($percorso_file) {
    if (empty($percorso_file)) return false;

    // __DIR__ = model/ → saliamo alla root con /../
    $file_fisico = __DIR__ . "/../" . $percorso_file;

    if (file_exists($file_fisico)) {
        return unlink($file_fisico);
    }
    return false;
}