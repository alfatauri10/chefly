<?php
// model/passo.php

require_once __DIR__ . '/mediaHelper.php';

/**
 * insertPasso()
 * Ora accetta un parametro $ordine opzionale.
 * Se $ordine = null, il passo viene inserito in fondo (max+1).
 * Se $ordine è un numero, gli altri passi vengono "scalati" per fare spazio.
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
    $ingredienti  = [],
    $ordine       = null
) {
    // Verifica che la ricetta esista
    $sql_check = "SELECT id FROM ricette WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_ricetta);
    $stmt_check->execute();
    $stmt_check->get_result()->fetch_assoc() or die("Ricetta non trovata");
    $stmt_check->close();

    // Calcola l'ordine da assegnare
    if ($ordine === null) {
        // Metti in fondo: recupera il massimo ordine attuale
        $sql_max = "SELECT COALESCE(MAX(ordine), 0) + 1 AS prossimo FROM passi WHERE idRicetta = ?";
        $stmt_max = $conn->prepare($sql_max);
        $stmt_max->bind_param("i", $id_ricetta);
        $stmt_max->execute();
        $ordine = (int)$stmt_max->get_result()->fetch_assoc()['prossimo'];
        $stmt_max->close();
    } else {
        $ordine = (int)$ordine;
        // Scala tutti i passi con ordine >= $ordine per fare spazio
        $sql_shift = "UPDATE passi SET ordine = ordine + 1 WHERE idRicetta = ? AND ordine >= ?";
        $stmt_shift = $conn->prepare($sql_shift);
        $stmt_shift->bind_param("ii", $id_ricetta, $ordine);
        $stmt_shift->execute();
        $stmt_shift->close();
    }

    // 1. Inserimento dati base del passo
    $idPasso = insertPassoDb($conn, $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $id_ricetta, $idCottura, $ordine);

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
 * Inserisce la riga base del passo con il campo ordine.
 */
function insertPassoDb($conn, $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $idRicetta, $idCottura, $ordine) {
    $sql = "INSERT INTO passi (titolo, tempoCottura, tempoRiposo, descrizione, durata, idRicetta, idCottura, ordine) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisisii", $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $idRicetta, $idCottura, $ordine);

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
 * Ora ordina per `ordine ASC` invece di `id ASC`.
 */
function getListaPassiByIdRicetta($conn, $idRicetta) {
    $passi = [];

    $sql = "SELECT p.id, p.titolo, p.descrizione, p.durata, p.tempoCottura, p.tempoRiposo, p.ordine,
                   c.nome AS nome_cottura
            FROM passi p
            LEFT JOIN anagCotture c ON p.idCottura = c.id
            WHERE p.idRicetta = ?
            ORDER BY p.ordine ASC";

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
 * riordinaPassi()
 * Riscrive i valori ordine in modo compatto (1, 2, 3...) senza buchi.
 * Va chiamata dopo ogni delete o insert per mantenere la sequenza pulita.
 */
function riordinaPassi($conn, $idRicetta) {
    $sql = "SELECT id FROM passi WHERE idRicetta = ? ORDER BY ordine ASC, id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idRicetta);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }
    $stmt->close();

    foreach ($ids as $i => $id) {
        $nuovoOrdine = $i + 1;
        $sql_upd = "UPDATE passi SET ordine = ? WHERE id = ?";
        $stmt_upd = $conn->prepare($sql_upd);
        $stmt_upd->bind_param("ii", $nuovoOrdine, $id);
        $stmt_upd->execute();
        $stmt_upd->close();
    }
}

/**
 * updatePasso()
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

    if (!empty($id_foto_da_eliminare)) {
        foreach ($id_foto_da_eliminare as $id_foto) {
            $id_foto = (int)$id_foto;
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

    if (!empty($nuovi_media)) {
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
 * Dopo la cancellazione riordina i passi rimasti.
 */
function deletePasso($conn, $id_passo, $id_utente) {

    $sql_check = "SELECT p.id, p.idRicetta FROM passi p
                  JOIN ricette r ON p.idRicetta = r.id
                  WHERE p.id = ? AND r.idCreatore = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_passo, $id_utente);
    $stmt_check->execute();
    $row_check = $stmt_check->get_result()->fetch_assoc();

    if (!$row_check) {
        $stmt_check->close();
        return false;
    }
    $id_ricetta = $row_check['idRicetta'];
    $stmt_check->close();

    $sql_get_media = "SELECT urlMedia FROM mediaPassi WHERE idPasso = ?";
    $stmt_get_media = $conn->prepare($sql_get_media);
    $stmt_get_media->bind_param("i", $id_passo);
    $stmt_get_media->execute();
    $result_media = $stmt_get_media->get_result();
    while ($row = $result_media->fetch_assoc()) {
        deleteFile($row['urlMedia']);
    }
    $stmt_get_media->close();

    $sql_del_media = "DELETE FROM mediaPassi WHERE idPasso = ?";
    $stmt_del_media = $conn->prepare($sql_del_media);
    $stmt_del_media->bind_param("i", $id_passo);
    $stmt_del_media->execute();
    $stmt_del_media->close();

    $sql_del_ing = "DELETE FROM passiIngredienti WHERE idPasso = ?";
    $stmt_del_ing = $conn->prepare($sql_del_ing);
    $stmt_del_ing->bind_param("i", $id_passo);
    $stmt_del_ing->execute();
    $stmt_del_ing->close();

    $sql_del_passo = "DELETE FROM passi WHERE id = ?";
    $stmt_del_passo = $conn->prepare($sql_del_passo);
    $stmt_del_passo->bind_param("i", $id_passo);
    $esito = $stmt_del_passo->execute();
    $stmt_del_passo->close();

    // Riordina i passi rimasti per chiudere il buco
    if ($esito) {
        riordinaPassi($conn, $id_ricetta);
    }

    return $esito;
}