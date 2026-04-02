<?php
// model/passo.php

require_once __DIR__ . '/mediaHelper.php';

/**
 * insertPasso()
 * @descrizione: Coordina l'inserimento di un passo, i suoi file multimediali e i relativi ingredienti.
 *
 * @param $conn
 * @param $id_utente      - Necessario per creare il path corretto dell'upload
 * @param $id_ricetta     - A quale ricetta appartiene il passo
 * @param $titolo
 * @param $descrizione
 * @param $durata         - Durata totale stimata in minuti
 * @param $tempoCottura   - Minuti di cottura (nullable)
 * @param $tempoRiposo    - Minuti di riposo (nullable)
 * @param $idCottura      - FK a anagCotture (nullable)
 * @param $mediaPasso     - Array $_FILES per i media del passo (nullable)
 * @param $ingredienti    - Array associativo [ id_ingrediente => dose ] (nullable)
 * @return int|null       - ID del passo creato, o null in caso di errore
 */
function insertPasso(
    $conn,
    $id_utente,
    $id_ricetta,
    $titolo,
    $descrizione,
    $durata,
    $tempoCottura = null,
    $tempoRiposo  = null,
    $idCottura    = null,
    $mediaPasso   = [],
    $ingredienti  = []
) {
    // Verifica che la ricetta esista prima di inserire il passo
    $sql_check = "SELECT id FROM ricette WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_ricetta);
    $stmt_check->execute();
    $stmt_check->get_result()->fetch_assoc() or die("Ricetta non trovata");
    $stmt_check->close();

    // 1. Inserimento dati base del passo
    $idPasso = insertPassoDb($conn, $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $id_ricetta, $idCottura);

    if (!$idPasso) {
        return null;
    }

    // 2. Gestione media
    if (!empty($mediaPasso)) {
        insertFilePasso($mediaPasso, $id_utente, $id_ricetta, $conn, $idPasso);
    }

    // 3. Gestione ingredienti
    if (!empty($ingredienti)) {
        foreach ($ingredienti as $id_ingrediente => $dose) {
            insertIngredientePassoDB($conn, $idPasso, (int)$id_ingrediente, $dose);
        }
    }

    return $idPasso;
}

/**
 * insertPassoDb()
 * @descrizione: Inserisce la riga base del passo nella tabella `passi`.
 */
function insertPassoDb($conn, $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $idRicetta, $idCottura) {
    $sql = "INSERT INTO passi (titolo, tempoCottura, tempoRiposo, descrizione, durata, idRicetta, idCottura) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisiii", $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $idRicetta, $idCottura);

    if ($stmt->execute()) {
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }
    $stmt->close();
    return false;
}

/**
 * insertFilePasso()
 * @descrizione: Gestisce l'upload multiplo di file per un passo e li salva nel DB.
 */
function insertFilePasso($mediaPasso, $idUtente, $idRicetta, $conn, $idPasso) {
    if (empty($mediaPasso) || !isset($mediaPasso['name']) || !is_array($mediaPasso['name'])) {
        return;
    }

    foreach ($mediaPasso['name'] as $key => $name) {
        if ($mediaPasso['error'][$key] === UPLOAD_ERR_OK) {
            $file_corrente = [
                'name'     => $mediaPasso['name'][$key],
                'tmp_name' => $mediaPasso['tmp_name'][$key],
                'error'    => $mediaPasso['error'][$key],
                'size'     => $mediaPasso['size'][$key]
            ];

            $url_foto = uploadFile($file_corrente, $idUtente, $idRicetta, false);
            if ($url_foto) {
                insertFileDbPasso($conn, $url_foto, $idPasso);
            }
        }
    }
}

/**
 * insertIngredientePassoDB()
 * @descrizione: Collega un ingrediente (con la sua dose) a un passo.
 */
function insertIngredientePassoDB($conn, $idPasso, $id_ingrediente, $dose) {
    $sql = "INSERT INTO passiIngredienti (idPasso, idIngrediente, dose) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $idPasso, $id_ingrediente, $dose);
    $esito = $stmt->execute();
    $stmt->close();
    return $esito;
}

/**
 * insertFileDbPasso()
 * @descrizione: Salva il path di un media del passo nella tabella `mediaPassi`.
 */
function insertFileDbPasso($conn, $urlMedia, $idPasso) {
    $sql = "INSERT INTO mediaPassi (urlMedia, idPasso) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $urlMedia, $idPasso);
    $esito = $stmt->execute();
    $stmt->close();
    return $esito;
}

// =============================================================================

/**
 * getListaPassiByIdRicetta()
 * @descrizione: Recupera tutti i passi di una ricetta in ordine di inserimento,
 * con il nome della cottura associata (JOIN), i media e gli ingredienti di ciascuno.
 *
 * @return array - Array di passi, ognuno con le chiavi:
 *                 [id, titolo, descrizione, durata, tempoCottura, tempoRiposo,
 *                  nome_cottura, media[], ingredienti[]]
 */
function getListaPassiByIdRicetta($conn, $idRicetta) {
    $passi = [];

    // 1. Recupera i passi con JOIN sulla tabella cotture
    $sql = "SELECT p.id, p.titolo, p.descrizione, p.durata, p.tempoCottura, p.tempoRiposo,
                   c.nome AS nome_cottura
            FROM passi p
            LEFT JOIN anagCotture c ON p.idCottura = c.id
            WHERE p.idRicetta = ?
            ORDER BY p.id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idRicetta);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['media']       = getMediaByIdPasso($conn, $row['id']);
        $row['ingredienti'] = getIngredientiByIdPasso($conn, $row['id']);
        $passi[] = $row;
    }

    $stmt->close();
    return $passi;
}

/**
 * getMediaByIdPasso()
 * @descrizione: Recupera tutti i file media associati a un singolo passo.
 */
function getMediaByIdPasso($conn, $idPasso) {
    $media = [];
    $sql = "SELECT id, urlMedia FROM mediaPassi WHERE idPasso = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idPasso);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $media[] = $row;
    }

    $stmt->close();
    return $media;
}

/**
 * getIngredientiByIdPasso()
 * @descrizione: Recupera tutti gli ingredienti di un passo con nome e dose.
 */
function getIngredientiByIdPasso($conn, $idPasso) {
    $ingredienti = [];
    $sql = "SELECT pi.idIngrediente, pi.dose, ai.nome, ai.prezzoMedio, ai.isGlutenFree
            FROM passiIngredienti pi
            JOIN anagIngredienti ai ON pi.idIngrediente = ai.id
            WHERE pi.idPasso = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idPasso);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $ingredienti[] = $row;
    }

    $stmt->close();
    return $ingredienti;
}

// =============================================================================

/**
 * updatePasso()
 * @descrizione: Aggiorna un passo esistente.
 * - Modifica i campi testuali
 * - Sostituisce completamente la lista ingredienti (delete + re-insert)
 * - Aggiunge nuovi file media
 * - Elimina i media selezionati dall'utente (file fisico + riga DB)
 *
 * @param $id_foto_da_eliminare - Array di ID di mediaPassi da rimuovere
 * @param $nuovi_media          - Array $_FILES per i nuovi media
 * @param $ingredienti          - Array [ id_ingrediente => dose ] (sostituisce tutti)
 * @return bool
 */
function updatePasso(
    $conn,
    $id_passo,
    $id_utente,
    $titolo,
    $descrizione,
    $durata,
    $tempoCottura         = null,
    $tempoRiposo          = null,
    $idCottura            = null,
    $nuovi_media          = [],
    $id_foto_da_eliminare = [],
    $ingredienti          = []
) {
    // Sicurezza: verifica che il passo appartenga a una ricetta dell'utente
    $sql_check = "SELECT p.id FROM passi p
                  JOIN ricette r ON p.idRicetta = r.id
                  WHERE p.id = ? AND r.idCreatore = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_passo, $id_utente);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        $stmt_check->close();
        return false; // Il passo non esiste o non appartiene all'utente
    }
    $stmt_check->close();

    // 1. Aggiornamento campi testuali
    $sql = "UPDATE passi 
            SET titolo       = ?,
                descrizione  = ?,
                durata       = ?,
                tempoCottura = ?,
                tempoRiposo  = ?,
                idCottura    = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiiii", $titolo, $descrizione, $durata, $tempoCottura, $tempoRiposo, $idCottura, $id_passo);

    if (!$stmt->execute()) {
        $stmt->close();
        return false;
    }
    $stmt->close();

    // 2. Sostituzione completa ingredienti (più semplice e sicuro di un diff)
    $sql_del_ing = "DELETE FROM passiIngredienti WHERE idPasso = ?";
    $stmt_del_ing = $conn->prepare($sql_del_ing);
    $stmt_del_ing->bind_param("i", $id_passo);
    $stmt_del_ing->execute();
    $stmt_del_ing->close();

    if (!empty($ingredienti)) {
        foreach ($ingredienti as $id_ingrediente => $dose) {
            insertIngredientePassoDB($conn, $id_passo, (int)$id_ingrediente, $dose);
        }
    }

    // 3. Eliminazione media selezionati dall'utente
    if (!empty($id_foto_da_eliminare)) {
        foreach ($id_foto_da_eliminare as $id_foto) {
            $id_foto = (int)$id_foto;

            // Recupera path (con doppio controllo che appartenga al passo giusto)
            $sql_get = "SELECT urlMedia FROM mediaPassi WHERE id = ? AND idPasso = ?";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->bind_param("ii", $id_foto, $id_passo);
            $stmt_get->execute();
            $foto = $stmt_get->get_result()->fetch_assoc();
            $stmt_get->close();

            if ($foto) {
                deleteFile($foto['urlMedia']);

                $sql_del = "DELETE FROM mediaPassi WHERE id = ?";
                $stmt_del = $conn->prepare($sql_del);
                $stmt_del->bind_param("i", $id_foto);
                $stmt_del->execute();
                $stmt_del->close();
            }
        }
    }

    // 4. Aggiunta nuovi media
    if (!empty($nuovi_media)) {
        // Recupera idRicetta per costruire il path di upload corretto
        $sql_ricetta = "SELECT idRicetta FROM passi WHERE id = ?";
        $stmt_ricetta = $conn->prepare($sql_ricetta);
        $stmt_ricetta->bind_param("i", $id_passo);
        $stmt_ricetta->execute();
        $row_ricetta = $stmt_ricetta->get_result()->fetch_assoc();
        $stmt_ricetta->close();

        if ($row_ricetta) {
            insertFilePasso($nuovi_media, $id_utente, $row_ricetta['idRicetta'], $conn, $id_passo);
        }
    }

    return true;
}

// =============================================================================

/**
 * deletePasso()
 * @descrizione: Elimina un passo con tutte le sue dipendenze.
 * Ordine: file fisici → mediaPassi → passiIngredienti → passi
 * NOTA: NON esiste la tabella cotturePassi, la cottura è una colonna diretta
 * in `passi` (idCottura), quindi non serve nessun DELETE aggiuntivo.
 *
 * @return bool
 */
function deletePasso($conn, $id_passo, $id_utente) {

    // Sicurezza: verifica che il passo appartenga a una ricetta dell'utente
    $sql_check = "SELECT p.id FROM passi p
                  JOIN ricette r ON p.idRicetta = r.id
                  WHERE p.id = ? AND r.idCreatore = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_passo, $id_utente);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        $stmt_check->close();
        return false;
    }
    $stmt_check->close();

    // 1. Recupera e cancella fisicamente i media del passo
    $sql_get_media = "SELECT urlMedia FROM mediaPassi WHERE idPasso = ?";
    $stmt_get_media = $conn->prepare($sql_get_media);
    $stmt_get_media->bind_param("i", $id_passo);
    $stmt_get_media->execute();
    $result_media = $stmt_get_media->get_result();
    while ($row = $result_media->fetch_assoc()) {
        deleteFile($row['urlMedia']);
    }
    $stmt_get_media->close();

    // 2. Cancella le righe mediaPassi dal DB
    $sql_del_media = "DELETE FROM mediaPassi WHERE idPasso = ?";
    $stmt_del_media = $conn->prepare($sql_del_media);
    $stmt_del_media->bind_param("i", $id_passo);
    $stmt_del_media->execute();
    $stmt_del_media->close();

    // 3. Cancella gli ingredienti del passo
    $sql_del_ing = "DELETE FROM passiIngredienti WHERE idPasso = ?";
    $stmt_del_ing = $conn->prepare($sql_del_ing);
    $stmt_del_ing->bind_param("i", $id_passo);
    $stmt_del_ing->execute();
    $stmt_del_ing->close();

    // 4. Cancella il passo
    $sql_del_passo = "DELETE FROM passi WHERE id = ?";
    $stmt_del_passo = $conn->prepare($sql_del_passo);
    $stmt_del_passo->bind_param("i", $id_passo);
    $esito = $stmt_del_passo->execute();
    $stmt_del_passo->close();

    return $esito;
}