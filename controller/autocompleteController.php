<?php
// controller/autocompleteController.php
// Endpoint AJAX – restituisce JSON con suggerimenti titoli ricette.
// Chiamato dal JS della pagina ricerca.php.

require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/ricerca.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit();
}

$suggerimenti = getSuggerimentiRicerca($conn, $q, 8);
echo json_encode($suggerimenti);