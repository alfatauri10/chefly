<?php

/**
 * function insertPasso
 * @descrizione: Coordina l'inserimento di un passo, i suoi file multimediali e i relativi ingredienti.
 */
function insertPasso($conn, $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $idRicetta, $idCottura, $mediaPasso = [], $ingredienti = [], $idUtente)
{
    // 1. Inserimento dati base
    $idPasso = insertPassoDb($conn, $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $idRicetta, $idCottura);

    if ($idPasso) {
        // 2. Gestione Media - CORRETTO: rimosso '= []' che svuotava l'array
        if (!empty($mediaPasso)) {
            insertFilePasso($mediaPasso, $idUtente, $idRicetta, $conn, $idPasso);
        }

        // 3. Gestione Ingredienti
        if (!empty($ingredienti)) {
            foreach ($ingredienti as $id_ingrediente => $dose) {
                insertIngredientePassoDB($conn, $idPasso, $id_ingrediente, $dose);
            }
        }

        return $idPasso;
    }
    return null;
}

function insertPassoDb($conn, $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $idRicetta, $idCottura) {
    $sql = "INSERT INTO passi (titolo, tempoCottura, tempoRiposo, descrizione, durata, idRicetta, idCottura) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisiii", $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $idRicetta, $idCottura);

    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function insertFilePasso($mediaPasso, $idUtente, $idRicetta, $conn, $idPasso) {
    // Verifichiamo se l'array dei media è strutturato come $_FILES multi-upload
    if (!empty($mediaPasso) && isset($mediaPasso['name']) && is_array($mediaPasso['name'])) {
        foreach ($mediaPasso['name'] as $key => $name) {
            if ($mediaPasso['error'][$key] === UPLOAD_ERR_OK) {
                // Ricostruiamo il singolo file per la funzione uploadFile
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
}

function insertIngredientePassoDB($conn, $idPasso, $id_ingrediente, $dose) {
    $sql = "INSERT INTO passiIngredienti (idPasso, idIngrediente, dose) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $idPasso, $id_ingrediente, $dose);
    return $stmt->execute();
}

function insertFileDbPasso($conn, $urlMedia, $idPasso) {
    $sql = "INSERT INTO mediaPassi (urlMedia, idPasso) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $urlMedia, $idPasso);
    return $stmt->execute();
}

// Funzioni da implementare in seguito...
function updatePasso($conn) {

}
function deletePasso($conn) {

}
function getListaPassiByIdRicetta($conn, $idRicetta) {

}