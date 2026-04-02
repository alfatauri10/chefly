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

    // 1. Recupera e cancella fisicamente i media della ricetta (copertina + galleria)
    $sql_get_media_ricetta = "SELECT urlMedia FROM mediaRicette WHERE idRicetta = ?";
    $stmt_get_media_ricetta = $conn->prepare($sql_get_media_ricetta);
    $stmt_get_media_ricetta->bind_param("i", $id_ricetta);
    $stmt_get_media_ricetta->execute();
    $result_media_ricetta = $stmt_get_media_ricetta->get_result();
    while ($row = $result_media_ricetta->fetch_assoc()) {
        deleteFile($row['urlMedia']);
    }
    $stmt_get_media_ricetta->close();

    $sql_media = "DELETE FROM mediaRicette WHERE idRicetta = ?";
    $stmt_media = $conn->prepare($sql_media);
    $stmt_media->bind_param("i", $id_ricetta);
    $stmt_media->execute();
    $stmt_media->close();

    // 2. Recupera tutti i passi associati alla ricetta
    $sql_get_passi = "SELECT id FROM passi WHERE idRicetta = ?";
    $stmt_get_passi = $conn->prepare($sql_get_passi);
    $stmt_get_passi->bind_param("i", $id_ricetta);
    $stmt_get_passi->execute();
    $result_passi = $stmt_get_passi->get_result();

    // 3. Per ogni passo: cancella i media fisici, poi le righe collegate nel DB
    while ($row = $result_passi->fetch_assoc()) {
        $id_passo = $row['id'];

        // 3a. Recupera e cancella fisicamente i media del passo
        $sql_get_media_passo = "SELECT urlMedia FROM mediaPassi WHERE idPasso = ?";
        $stmt_get_media_passo = $conn->prepare($sql_get_media_passo);
        $stmt_get_media_passo->bind_param("i", $id_passo);
        $stmt_get_media_passo->execute();
        $result_media_passo = $stmt_get_media_passo->get_result();
        while ($media_row = $result_media_passo->fetch_assoc()) {
            deleteFile($media_row['urlMedia']);
        }
        $stmt_get_media_passo->close();

        // 3b. Cancella le righe mediaPassi dal DB
        $sql_del_media_passo = "DELETE FROM mediaPassi WHERE idPasso = ?";
        $stmt_del_media_passo = $conn->prepare($sql_del_media_passo);
        $stmt_del_media_passo->bind_param("i", $id_passo);
        $stmt_del_media_passo->execute();
        $stmt_del_media_passo->close();

        // 3c. Cancella gli ingredienti del passo
        $sql_ing = "DELETE FROM passiIngredienti WHERE idPasso = ?";
        $stmt_ing = $conn->prepare($sql_ing);
        $stmt_ing->bind_param("i", $id_passo);
        $stmt_ing->execute();
        $stmt_ing->close();


    }
    $stmt_get_passi->close();

    // 4. Cancella i passi stessi
    $sql_passi = "DELETE FROM passi WHERE idRicetta = ?";
    $stmt_passi = $conn->prepare($sql_passi);
    $stmt_passi->bind_param("i", $id_ricetta);
    $stmt_passi->execute();
    $stmt_passi->close();

    // 5. Cancella la ricetta
    $sql_ricetta = "DELETE FROM ricette WHERE id = ?";
    $stmt_ricetta = $conn->prepare($sql_ricetta);
    $stmt_ricetta->bind_param("i", $id_ricetta);
    $esito = $stmt_ricetta->execute();
    $stmt_ricetta->close();

    return $esito;
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
            LEFT JOIN mediaRicette m ON r.id = m.idRicetta AND m.isCopertina = 1
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



/**
 * updateRicettaByID()
 * @descrizione: Aggiorna i dati di una ricetta esistente.
 * Gestisce la modifica dei campi testuali, la sostituzione della copertina
 * e l'aggiunta di nuove foto alla galleria (senza toccare quelle esistenti).
 *
 * @param $conn          - Connessione al DB
 * @param $id_ricetta    - ID della ricetta da aggiornare
 * @param $id_utente     - ID dell'utente (per sicurezza: solo il proprietario può modificare)
 * @param $titolo        - Nuovo titolo
 * @param $descrizione   - Nuova descrizione
 * @param $difficolta    - Nuova difficoltà
 * @param $id_nazionalita - Nuova nazionalità (nullable)
 * @param $id_tipologia  - Nuova tipologia (nullable)
 * @param $file_copertina - Nuovo file copertina (da $_FILES, nullable)
 * @param $nuovi_file_gallery - Nuovi file galleria da aggiungere (da $_FILES, nullable)
 * @param $id_foto_da_eliminare - Array di ID di mediaRicette da rimuovere (nullable)
 * @return bool          - true se l'aggiornamento è andato a buon fine, false altrimenti
 */
function updateRicettaByID(
    $conn,
    $id_ricetta,
    $id_utente,
    $titolo,
    $descrizione,
    $difficolta,
    $id_nazionalita,
    $id_tipologia,
    $file_copertina    = null,
    $nuovi_file_gallery = [],
    $id_foto_da_eliminare = []
) {
    // --- SICUREZZA: verifica che la ricetta appartenga all'utente ---
    $ricetta = getRicettaByIdDB($conn, $id_ricetta);
    if (!$ricetta || $ricetta['idCreatore'] != $id_utente) {
        return false;
    }

    // --- 1. Aggiornamento campi testuali ---
    $sql = "UPDATE ricette 
            SET titolo        = ?,
                descrizione   = ?,
                difficolta    = ?,
                idNazionalita = ?,
                idTipologia   = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssiii",
        $titolo,
        $descrizione,
        $difficolta,
        $id_nazionalita,
        $id_tipologia,
        $id_ricetta
    );

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    // --- 2. Sostituzione copertina (se ne è stata caricata una nuova) ---
    if ($file_copertina && isset($file_copertina['error']) && $file_copertina['error'] === UPLOAD_ERR_OK) {

        // 2a. Recupera il path della vecchia copertina dal DB
        $sql_old = "SELECT id, urlMedia FROM mediaRicette WHERE idRicetta = ? AND isCopertina = 1 LIMIT 1";
        $stmt_old = $conn->prepare($sql_old);
        $stmt_old->bind_param("i", $id_ricetta);
        $stmt_old->execute();
        $result_old = $stmt_old->get_result();
        $vecchia_copertina = $result_old->fetch_assoc();
        $stmt_old->close();

        // 2b. Carica il nuovo file fisicamente
        $nuovo_url = uploadFile($file_copertina, $id_utente, $id_ricetta, true);

        if ($nuovo_url) {
            if ($vecchia_copertina) {
                // 2c. Cancella il vecchio file fisico dal server
                deleteFile($vecchia_copertina['urlMedia']);

                // 2d. Aggiorna il path nel DB (UPDATE sulla riga esistente)
                $sql_upd = "UPDATE mediaRicette SET urlMedia = ? WHERE id = ?";
                $stmt_upd = $conn->prepare($sql_upd);
                $stmt_upd->bind_param("si", $nuovo_url, $vecchia_copertina['id']);
                $stmt_upd->execute();
                $stmt_upd->close();
            } else {
                // Non c'era una copertina: la inseriamo come nuova
                insertFileDbRicetta($conn, $nuovo_url, $id_ricetta, 1);
            }
        }
    }

    // --- 3. Eliminazione foto galleria selezionate dall'utente ---
    if (!empty($id_foto_da_eliminare)) {
        foreach ($id_foto_da_eliminare as $id_foto) {
            $id_foto = (int)$id_foto; // Sanitizzazione

            // 3a. Recupera il path prima di cancellare
            $sql_get = "SELECT urlMedia FROM mediaRicette WHERE id = ? AND idRicetta = ? AND isCopertina = 0";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->bind_param("ii", $id_foto, $id_ricetta);
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            $foto = $result_get->fetch_assoc();
            $stmt_get->close();

            if ($foto) {
                // 3b. Cancella fisicamente dal server
                deleteFile($foto['urlMedia']);

                // 3c. Cancella la riga dal DB
                $sql_del = "DELETE FROM mediaRicette WHERE id = ?";
                $stmt_del = $conn->prepare($sql_del);
                $stmt_del->bind_param("i", $id_foto);
                $stmt_del->execute();
                $stmt_del->close();
            }
        }
    }

    // --- 4. Aggiunta nuove foto alla galleria ---
    if (!empty($nuovi_file_gallery) && isset($nuovi_file_gallery['name']) && is_array($nuovi_file_gallery['name'])) {
        foreach ($nuovi_file_gallery['name'] as $key => $name) {
            if ($nuovi_file_gallery['error'][$key] === UPLOAD_ERR_OK) {
                $file_corrente = [
                    'name'     => $nuovi_file_gallery['name'][$key],
                    'tmp_name' => $nuovi_file_gallery['tmp_name'][$key],
                    'error'    => $nuovi_file_gallery['error'][$key],
                    'size'     => $nuovi_file_gallery['size'][$key]
                ];

                $url_foto = uploadFile($file_corrente, $id_utente, $id_ricetta, false);
                if ($url_foto) {
                    insertFileDbRicetta($conn, $url_foto, $id_ricetta, 0);
                }
            }
        }
    }

    return true;
}
?>