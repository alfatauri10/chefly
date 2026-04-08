<?php
// controller/ilMioRistoranteController.php
// Include-controller: va richiamato con require_once all'inizio della view.
// NON fa redirect POST, prepara solo i dati in lettura.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/ricetta.php';
require_once __DIR__ . '/../model/passo.php';
require_once __DIR__ . '/../model/user.php';


$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) {
    header("Location: /view/login.php");
    exit();
}

// Dati profilo
$profilo     = getUtenteById($conn, $id_utente);
$statistiche = getStatisticheUtente($conn, $id_utente);
var_dump($statistiche); exit();

// Ricette con i passi annidati
$ricette_raw = getListaRicetteUtente($conn, $id_utente);
$ricette = [];

foreach ($ricette_raw as $ricetta) {
    // Per ogni ricetta recupera anche copertina e passi
    $sql_cop = "SELECT urlMedia FROM mediaRicette WHERE idRicetta = ? AND isCopertina = 1 LIMIT 1";
    $stmt_cop = $conn->prepare($sql_cop);
    $stmt_cop->bind_param("i", $ricetta['id']);
    $stmt_cop->execute();
    $row_cop = $stmt_cop->get_result()->fetch_assoc();
    $stmt_cop->close();

    $ricetta['url_copertina'] = $row_cop['urlMedia'] ?? null;
    $ricetta['passi']         = getListaPassiByIdRicetta($conn, $ricetta['id']);
    $ricette[] = $ricetta;
}