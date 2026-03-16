<?php

//model/Vino.php

require_once __DIR__ . '/VinoHelper.php';

// Costante: definisce il valore di default (in Locale) se manca nel Database
define('DEFAULT_SALVATAGGIO_IMMAGINE', '0');

/**
 * Configurazione TIPO SALVATAGGIO IMMAGINI sul DB:
 * Legge dal DB se salvare in Locale (0) o Drive (1)
 */




/**
 * Inserimento nuovo Vino: uploadFile + insert nel DB
 * e' questa la funzione chiamata dal controller
 */
function aggiungiRicetta($conn, $id_utente, $nome_ricetta, $file_php) { //todo campi della view

    //recupero id_ricetta con query sul db $id_ricetta
    // 2. Upload File
    $url = uploadFile($file_php, $id_utente,$id_ricetta,true);

    // 3. Salviamo nel DB
    return insertRicettaDB($conn, $id_utente, $nome_ricetta, $url);
}

/**
 * Cancellazione Vino: eliminaFile + deleteVino DB
 * e' questa la funzione chiamata dal controller
 */
function cancellazioneVino($conn, $id_vino, $id_utente){

    $url_param = getURLImmagineDB($conn, $id_vino, $id_utente);

    // Controllo che l'immagine del vino esista e la cancello
    if ($url_param) {

        $url = $url_param['url'];
        $tipo_url = $url_param['tipo_url'];

        // 2. Elimino fisicamente immagine
        eliminaFile($tipo_url,$url);
    }

    //elimino dal DB
    return deleteVinoDB($conn, $id_vino, $id_utente);
}


/**
 * Salva i dati del vino nel Database
 */
function insertRicettaDB($conn, $id_utente, $nome_ricetta, $url) {

    $sql = "INSERT INTO vini_utenti (id_utente, nome_vino, cantina, url, tipo_Url) 
              VALUES (?, ?, ?, ?, ?)"; // todo: cambia in base ai campi della ricetta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $id_utente, $nome_ricetta, $url);

    return $stmt->execute();
}

/**
 * Cancella il vino dal DB e rimuove il file immagine dal disco
 */
function deleteVinoDB($conn, $id_vino, $id_utente) {

    $sql_delete = "DELETE FROM vini_utenti WHERE id = ? AND id_utente = ?";
    $stmt_del = $conn->prepare($sql_delete);
    $stmt_del->bind_param("ii", $id_vino, $id_utente);

    return $stmt_del->execute();
}

/**
 * Recupero URL immagine vino
 */
function getURLImmagineDB($conn, $id_vino, $id_utente){
    $sql = "SELECT url, tipo_url FROM vini_utenti WHERE id = ? AND id_utente = ?";
    $stmt_info = $conn->prepare($sql);
    $stmt_info->bind_param("ii", $id_vino, $id_utente);
    $stmt_info->execute();
    $url_param = $stmt_info->get_result()->fetch_assoc();

    // Restituisci l'intero array (che contiene sia 'url' che 'tipo_url')
    return $url_param;
}

/**
 * Recupera la lista dei vini dell'utente
 */
function getListaViniByIdUtenteDB($conn, $id_utente) {

    $sql = "SELECT *
              FROM vini_utenti 
              WHERE id_utente = ? 
              ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $result = $stmt->get_result();

    $vini = [];

    while ($row = $result->fetch_assoc()) {
        $vini[] = $row;
    }

    return $vini;
}


/**
 * Recupera un singolo vino per ID con i campi espliciti
 */
function getVinoByIdDB($conn, $id_vino, $id_utente) {

    $sql = "SELECT *
              FROM vini_utenti 
              WHERE id = ? AND id_utente = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_vino, $id_utente);
    $stmt->execute();

    // Restituisce il vino trovato o null
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Recupera tutti i vini e nome utenti
 */
function getListaViniDB($conn) {
    $sql = "SELECT v.*, u.username 
            FROM vini_utenti v 
            JOIN utenti u ON v.id_utente = u.id 
            ORDER BY v.created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}



