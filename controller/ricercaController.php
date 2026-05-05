<?php
// controller/ricercaController.php
//
// Questo file NON fa redirect. Va incluso all'inizio di view/ricerca.php.
// Espone le variabili:
//   $q            string   testo ricercato
//   $filtri       array    filtri attivi
//   $risultati    array    ricette trovate
//   $totale       int      numero totale risultati (per la paginazione)
//   $pagina       int      pagina corrente (1-based)
//   $per_pagina   int      risultati per pagina
//   $lista_*      array    dati per i menu a tendina dei filtri

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../include/connessione.php';
require_once __DIR__ . '/../model/ricerca.php';
require_once __DIR__ . '/../model/ricetta.php';   // per getTutteLeNazionalita, getTutteLeTipologie

// ── Parametri GET ────────────────────────────────────────────────────────────
$q = trim($_GET['q'] ?? '');

$filtri = [
    'difficolta'     => $_GET['difficolta']    ?? '',
    'id_nazionalita' => !empty($_GET['id_nazionalita']) ? (int)$_GET['id_nazionalita'] : null,
    'id_tipologia'   => !empty($_GET['id_tipologia'])   ? (int)$_GET['id_tipologia']   : null,
    'id_ingrediente' => !empty($_GET['id_ingrediente']) ? (int)$_GET['id_ingrediente'] : null,
    'id_cottura'     => !empty($_GET['id_cottura'])     ? (int)$_GET['id_cottura']     : null,
];

// ── Paginazione ───────────────────────────────────────────────────────────────
$per_pagina = 18;
$pagina     = max(1, (int)($_GET['pagina'] ?? 1));
$offset     = ($pagina - 1) * $per_pagina;

// ── Ricerca ───────────────────────────────────────────────────────────────────
$risultato  = cercaRicette($conn, $q, $filtri, $per_pagina, $offset);
$risultati  = $risultato['ricette'];
$totale     = $risultato['totale'];
$tot_pagine = (int)ceil($totale / $per_pagina);

// ── Dati per i select dei filtri ──────────────────────────────────────────────
$lista_nazionalita  = getTutteLeNazionalita($conn);
$lista_tipologie    = getTutteLeTipologie($conn);

// Ingredienti (lista completa)
$lista_ingredienti  = $conn->query("SELECT id, nome FROM anagIngredienti ORDER BY nome ASC")
    ->fetch_all(MYSQLI_ASSOC);

// Cotture
$lista_cotture      = $conn->query("SELECT id, nome FROM anagCotture ORDER BY nome ASC")
    ->fetch_all(MYSQLI_ASSOC);

// ── Helper: verifica se almeno un filtro è attivo ─────────────────────────────
$ha_filtri_attivi = !empty($filtri['difficolta'])
    || $filtri['id_nazionalita']
    || $filtri['id_tipologia']
    || $filtri['id_ingrediente']
    || $filtri['id_cottura'];