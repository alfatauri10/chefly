<?php
function addPasso($conn, $titolo, $tempoCottura, $tempoRiposo, $descrizione, $durata, $id_ricetta)
{
    //uploadFile in locale
    //tramite l'url inserimento sul DB
    //per ultimo dopo aver ricevuto in ingresso l'id del passo
    //aggiungi ingredienti in ingredientiPassi richiamando insertIngredienti
}

function updatePasso($conn)
{

}

function deletePasso($conn )
{

}

function insertIngredientiPassoDb($conn, $idPasso, $idIngredienti, $dose)
{
    $sql = "INSERT INTO passiIngredienti (idPasso, idIngrediente, dose) values (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $idPasso, $idIngredienti, $dose);
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function insertFileDbPasso($conn, $url, $idPasso) {
    $isPasso = 1;

    // Nomi delle colonne corretti in base allo schema (camelCase)
    $sql = "INSERT INTO media (urlMedia, isPasso, idRicetta, idPasso, isCopertina) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiii", $url, $isPasso, $idPasso, null, 0);

    return $stmt->execute();
}

function getListaPassiByIdUtente($conn, $id_utente)
{

}