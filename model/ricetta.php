<?php
// model/ricetta.php

require_once __DIR__ . '/mediaHelper.php';


/* aggiungiRicetta():
 * Coordina l'inserimento della ricetta e di tutti i file multimediali associati.
 */
function aggiungiRicetta($conn, $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia, $file_copertina = null, $altri_files = [], $ingredienti = [], $cotture = [] ) {

    // 1. Inserimento Ricetta
    $id_ricetta = insertRicettaDB($conn, $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia);

    if ($id_ricetta) {
        // 2. Gestione Copertina
        if ($file_copertina && isset($file_copertina['error']) && $file_copertina['error'] === UPLOAD_ERR_OK) {
            $url_copertina = uploadFile($file_copertina, $id_utente, $id_ricetta, true);
            if ($url_copertina) {
                insertFileDbRicetta($conn, $url_copertina, $id_ricetta, 1);
            }
        }

        // 3. Gestione Gallery (se sono stati caricati più file)
        if (!empty($altri_files) && isset($altri_files['name']) && is_array($altri_files['name'])) {
            foreach ($altri_files['name'] as $key => $name) {
                if ($altri_files['error'][$key] === UPLOAD_ERR_OK) {
                    $file_corrente = [
                        'name'     => $altri_files['name'][$key],
                        'tmp_name' => $altri_files['tmp_name'][$key],
                        'error'    => $altri_files['error'][$key],
                        'size'     => $altri_files['size'][$key]
                    ];

                    $url_foto = uploadFile($file_corrente, $id_utente, $id_ricetta, false);
                    if ($url_foto) {
                        insertFileDbRicetta($conn, $url_foto, $id_ricetta, 0);
                    }
                }
            }
        }

        // 4. Gestione Ingredienti (Aggiornato per array associativo)
        if (!empty($ingredienti) && is_array($ingredienti)) {
            foreach ($ingredienti as $ingrediente) {
                // Verifichiamo che l'elemento contenga sia l'id che la dose prima di inserirlo
                if (isset($ingrediente['id']) && isset($ingrediente['dose'])) {
                    insertIngredienteRicettaDB($conn, $id_ricetta, $ingrediente['id'], $ingrediente['dose']);
                }
            }
        }

        // 5. Gestione Cotture
        if (!empty($cotture) && is_array($cotture)) {
            foreach ($cotture as $id_cottura) {
                insertCotturaRicettaDB($conn, $id_ricetta, $id_cottura);
            }
        }


        return $id_ricetta;
    }
    return false;
}

/* insertFileDbRicetta():
 * Tabella mediaRicette: urlMedia, idRicetta, isCopertina
 */
function insertFileDbRicetta($conn, $url, $id_ricetta, $is_copertina) {

    // Nomi delle colonne corretti in base allo schema (camelCase)
    $sql = "INSERT INTO mediaRicette (urlMedia, idRicetta, isCopertina) 
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $url, $id_ricetta, $is_copertina);

    return $stmt->execute();
}

/* insertRicettaDB():
 * Tabella ricette: idCreatore, dataCreazione, idNazionalita, idTipologia
 */
function insertRicettaDB($conn, $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia) {
    // Nomi delle colonne corretti in base allo schema (camelCase)
    $sql = "INSERT INTO ricette (descrizione, titolo, difficolta, idCreatore, dataCreazione, idNazionalita, idTipologia) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisii", $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia);

    if ($stmt->execute()) {
        return $conn->insert_id; // Ritorna l'ID della ricetta appena creata
    }
    return false;
}
/*
 * @descrizione:
 * riceve in ingresso la ricetta, l'ingrediente da inserire e la dose,
 * in seguito lo inserisco nel db
 *
 * IMP:DA RICHIAMARE 1 VOLTA PER INGREDIENTE
 *
 * UGUALE PER  insertCotturaRicettaDB()
 */
function insertIngredienteRicettaDB($conn,$id_ricetta,$idIngrediente,$dose){
    $sql = "INSERT INTO ingredientiRicette (idRicetta, idIngrediente, dose) values (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_ricetta, $idIngrediente, $dose);
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function insertCotturaRicettaDB($conn,$id_ricetta,$idCottura){
    $sql = "INSERT INTO CottureRicette (idRicetta, idCottura) values (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_ricetta, $idCottura);
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}


/* getRicettaByIdDB():
 * Recupera una singola ricetta tramite il suo ID (Utile per controlli di sicurezza prima di eliminare).
 */
function getRicettaByIdDB($conn, $id_ricetta) {
    $sql = "SELECT * FROM ricette WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_ricetta);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return null;
}

/* deleteRicettaDB():
 * Pulisce prima i media collegati e poi la ricetta in modo sicuro.
 */
function deleteRicettaDB($conn, $id_ricetta) {
    // 1. Eliminazione sicura dei media (usando prepared statement invece di concatenazione)
    $sql_media = "DELETE FROM mediaRicette WHERE idRicetta = ?";
    $stmt_media = $conn->prepare($sql_media);
    $stmt_media->bind_param("i", $id_ricetta);
    $stmt_media->execute();

    // 2. Eliminazione della ricetta
    $sql_ricetta = "DELETE FROM ricette WHERE id = ?";
    $stmt_ricetta = $conn->prepare($sql_ricetta);
    $stmt_ricetta->bind_param("i", $id_ricetta);

    return $stmt_ricetta->execute();
}

/* getListaRicetteUtente():
 * Filtra per idCreatore.
 */
function getListaRicetteUtente($conn, $id_utente) {
    $lista = [];
    // Nomi delle colonne corretti (dataCreazione, idCreatore)
    $sql = "SELECT id, titolo, descrizione, difficolta, dataCreazione 
            FROM ricette 
            WHERE idCreatore = ? 
            ORDER BY dataCreazione DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_utente);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $lista[] = $row;
        }
    }
    return $lista;
}

/* getTutteLeRicetteDB():
 * JOIN su idCreatore e idRicetta.
 */
function getTutteLeRicetteDB($conn) {
    $ricette = [];
    // Nomi delle colonne corretti in base allo schema per tutte le tabelle coinvolte
    $sql = "SELECT r.*, u.nome AS nome_autore, m.urlMedia AS url_copertina 
            FROM ricette r
            JOIN utenti u ON r.idCreatore = u.id
            LEFT JOIN media m ON r.id = m.idRicetta AND m.isCopertina = 1
            ORDER BY r.dataCreazione DESC";

    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $ricette[] = $row;
        }
    }
    return $ricette;
}
