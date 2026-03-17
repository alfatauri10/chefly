<?php
// model/ricetta.php

/* aggiungiRicetta():
 * Coordina l'inserimento della ricetta e di tutti i file multimediali associati.
 */
function aggiungiRicetta($conn, $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia, $file_copertina, $altri_files = []) {

    // 1. Inserimento Ricetta (usa idcreatore, datacreazione, idnazionalita, idtipologia)
    $id_ricetta = insertRicettaDB($conn, $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia);

    if ($id_ricetta) {
        // 2. Gestione Copertina
        if ($file_copertina && $file_copertina['error'] === UPLOAD_ERR_OK) {
            $url_copertina = uploadFile($file_copertina, $id_utente, $id_ricetta, true);
            insertFileDbRicetta($conn, $url_copertina, $id_ricetta, true);
        }

        // 3. Gestione Gallery
        if (!empty($altri_files)) {
            foreach ($altri_files['name'] as $key => $name) {
                if ($altri_files['error'][$key] === UPLOAD_ERR_OK) {
                    $file_corrente = [
                        'name'     => $altri_files['name'][$key],
                        'tmp_name' => $altri_files['tmp_name'][$key],
                        'error'    => $altri_files['error'][$key],
                        'size'     => $altri_files['size'][$key]
                    ];

                    $url_foto = uploadFile($file_corrente, $id_utente, $id_ricetta, false);
                    insertFileDbRicetta($conn, $url_foto, $id_ricetta, false);
                }
            }
        }
        return $id_ricetta;
    }
    return false;
}

/* insertFileDbRicetta():
 * Tabella media: urlmedia, ispasso, idricetta, idpasso, iscopertina
 */
function insertFileDbRicetta($conn, $url, $id_ricetta, $is_copertina) {
    $is_passo = 0;
    $id_passo = null;

    $sql = "INSERT INTO media (urlmedia, ispasso, idricetta, idpasso, iscopertina) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiii", $url, $is_passo, $id_ricetta, $id_passo, $is_copertina);

    return $stmt->execute();
}

/* insertRicettaDB():
 * Tabella ricette: idcreatore, datacreazione, idnazionalita, idtipologia
 */
function insertRicettaDB($conn, $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia) {
    $sql = "INSERT INTO ricette (descrizione, titolo, difficolta, idcreatore, datacreazione, idnazionalita, idtipologia) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisii", $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia);

    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

/* deleteRicettaDB():
 * Pulisce prima i media collegati e poi la ricetta.
 */
function deleteRicettaDB($conn, $id_ricetta) {
    // Corretto: idricetta (senza underscore come da schema media)
    $conn->query("DELETE FROM media WHERE idricetta = $id_ricetta");

    $sql = "DELETE FROM ricette WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_ricetta);

    return $stmt->execute();
}

/* getListaRicetteUtente():
 * Filtra per idcreatore.
 */
function getListaRicetteUtente($conn, $id_utente) {
    $lista = [];
    $sql = "SELECT id, titolo, descrizione, difficolta, datacreazione 
            FROM ricette 
            WHERE idcreatore = ? 
            ORDER BY datacreazione DESC";

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
 * JOIN su idcreatore e idricetta.
 */
function getTutteLeRicetteDB($conn) {
    $ricette = [];
    $sql = "SELECT r.*, u.nome AS nome_autore, m.urlmedia AS url_copertina 
            FROM ricette r
            JOIN utenti u ON r.idcreatore = u.id
            LEFT JOIN media m ON r.id = m.idricetta AND m.iscopertina = 1
            ORDER BY r.datacreazione DESC";

    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $ricette[] = $row;
        }
    }
    return $ricette;
}

/* uploadFile():
 * Gestisce la creazione della cartella e il salvataggio fisico.
 */
function uploadFile($file_php, $id_utente, $id_ricetta, $is_copertina) {
    if (!isset($file_php) || $file_php['error'] != UPLOAD_ERR_OK) {
        return null;
    }

    $cartella = "../uploads/user_" . $id_utente . "/ricetta_" . $id_ricetta . "/";

    if (!is_dir($cartella)) {
        mkdir($cartella, 0777, true);
    }

    $estensione = pathinfo($file_php['name'], PATHINFO_EXTENSION);
    $nome_file = ($is_copertina ? "copertina" : uniqid()) . "." . $estensione;
    $destinazione = $cartella . $nome_file;

    if (move_uploaded_file($file_php['tmp_name'], $destinazione)) {
        return "uploads/user_" . $id_utente . "/ricetta_" . $id_ricetta . "/" . $nome_file;
    }
    return null;
}
