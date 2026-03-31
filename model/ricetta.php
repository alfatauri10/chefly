<?php
// model/ricetta.php

require_once __DIR__ . '/mediaHelper.php';

/**
 * aggiungiRicetta()
 * @descrizione: Inserisce i dati principali della ricetta e le sue immagini.
 * NOTA: La gestione dei passi, ingredienti e cotture andrà fatta in una fase successiva
 * (es. ciclando i passi creati dall'utente e richiamando insertPassoDB).
 */
function aggiungiRicetta($conn, $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia, $file_copertina = null, $altri_files = []) {

    // 1. Inserimento Ricetta nel DB
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

        return $id_ricetta; // Operazione completata con successo
    }

    return false; // Fallito l'inserimento iniziale
}

/**
 * insertFileDbRicetta()
 * @descrizione: Inserisce nella tabella 'media' i percorsi dei file riguardanti le ricette.
 *  Tabella mediaRicette: urlMedia, idRicetta, isCopertina
 */
function insertFileDbRicetta($conn, $url, $id_ricetta, $is_copertina) {


    $sql = "INSERT INTO mediaRicette (urlMedia, idRicetta, isCopertina) 
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $url, $id_ricetta, $is_copertina);
    return $stmt->execute();
}

/**
 * insertRicettaDB()
 * @descrizione: Inserisce tutti i dati di input nella tabella 'ricette' nel DB.
 * Ritorna l'ID della ricetta appena creata (Primary Key).
 */
function insertRicettaDB($conn, $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia) {
    $sql = "INSERT INTO ricette (descrizione, titolo, difficolta, idCreatore, dataCreazione, idNazionalita, idTipologia) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisii", $descrizione, $titolo, $difficolta, $id_utente, $dataCreazione, $id_nazionalita, $id_tipologia);

    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

/**
 * insertPassoDB() [NUOVA FUNZIONE]
 * @descrizione: Inserisce un nuovo passo associato alla ricetta.
 * Ritorna l'ID del passo appena creato, fondamentale per agganciarci poi ingredienti e cotture.
 */
function insertPassoDB($conn, $id_ricetta, $titolo, $descrizione, $numero_passo) {
    $sql = "INSERT INTO Passi (idRicetta, titolo, descrizione, ordine) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    // Assumiamo che 'ordine' o 'numero_passo' sia un intero per ordinare i vari step
    $stmt->bind_param("issi", $id_ricetta, $titolo, $descrizione, $numero_passo);

    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}





/**
 * getRicettaByIdDB()
 * @descrizione: Recupera una singola ricetta tramite il suo ID.
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

/**
 * deleteRicettaDB() [AGGIORNATA PER LA NUOVA STRUTTURA]
 * @descrizione: Elimina una ricetta pulendo le dipendenze in modo strutturato:
 * Elimina i media -> Trova i passi -> Elimina ingredienti/cotture dei passi -> Elimina passi -> Elimina ricetta.
 */
function deleteRicettaDB($conn, $id_ricetta) {
    // 1. Eliminazione dei media collegati
    $sql_media = "DELETE FROM mediaRicette WHERE idRicetta = ?";
    $stmt_media = $conn->prepare($sql_media);
    $stmt_media->bind_param("i", $id_ricetta);
    $stmt_media->execute();

    // 2. Recuperiamo tutti i passi associati alla ricetta
    $sql_get_passi = "SELECT id FROM Passi WHERE idRicetta = ?";
    $stmt_get_passi = $conn->prepare($sql_get_passi);
    $stmt_get_passi->bind_param("i", $id_ricetta);
    $stmt_get_passi->execute();
    $result_passi = $stmt_get_passi->get_result();

    // 3. Eliminiamo i collegamenti per ogni singolo passo
    while ($row = $result_passi->fetch_assoc()) {
        $id_passo = $row['id'];

        // Elimina collegamenti ingredienti
        $sql_ing = "DELETE FROM passiIngredienti WHERE idPasso = ?";
        $stmt_ing = $conn->prepare($sql_ing);
        $stmt_ing->bind_param("i", $id_passo);
        $stmt_ing->execute();

        // Elimina collegamenti cotture
        $sql_cot = "DELETE FROM cotturePassi WHERE idPasso = ?";
        $stmt_cot = $conn->prepare($sql_cot);
        $stmt_cot->bind_param("i", $id_passo);
        $stmt_cot->execute();
    }

    // 4. Eliminazione dei passi stessi
    $sql_passi = "DELETE FROM Passi WHERE idRicetta = ?";
    $stmt_passi = $conn->prepare($sql_passi);
    $stmt_passi->bind_param("i", $id_ricetta);
    $stmt_passi->execute();

    // 5. Infine, eliminazione della ricetta vera e propria
    $sql_ricetta = "DELETE FROM ricette WHERE id = ?";
    $stmt_ricetta = $conn->prepare($sql_ricetta);
    $stmt_ricetta->bind_param("i", $id_ricetta);

    return $stmt_ricetta->execute();
}

/**
 * getListaRicetteUtente()
 * @descrizione: Recupera l'elenco delle ricette create da uno specifico utente (filtro per idCreatore).
 */
function getListaRicetteUtente($conn, $id_utente) {
    $lista = [];
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

/**
 * getTutteLeRicetteDB()
 * @descrizione: Recupera tutte le ricette nel database.
 * Esegue una JOIN con gli utenti (per il nome autore) e con i media (per URL copertina).
 */
function getTutteLeRicetteDB($conn) {
    $ricette = [];
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

/**
 * getTutteLeNazionalita()
 * @descrizione: Recupera tutte le nazionalità dall'anagrafica per i menu a tendina
 */
function getTutteLeNazionalita($conn) {
    $lista = [];
    $sql = "SELECT id, nome, sigla FROM anagNazionalita ORDER BY nome ASC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $lista[] = $row;
        }
    }
    return $lista;
}

/**
 * getTutteLeTipologie()
 * @descrizione: Recupera tutte le tipologie di piatti dall'anagrafica per i menu a tendina
 */
function getTutteLeTipologie($conn) {
    $lista = [];
    $sql = "SELECT id, nome FROM anagTipologiePiatti ORDER BY nome ASC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $lista[] = $row;
        }
    }
    return $lista;
}

?>