<?php
// controller/aggiornaPassoEseguito.php
// Endpoint AJAX per aggiornare/creare il progresso di esecuzione di una ricetta.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../include/connessione.php';

header('Content-Type: application/json');

$id_utente = $_SESSION['user_id'] ?? null;

// Gli utenti non loggati (guest) non persistono i progressi
if (!$id_utente) {
    echo json_encode(['success' => false, 'reason' => 'not_logged_in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'reason' => 'method_not_allowed']);
    exit();
}

$id_ricetta    = !empty($_POST['id_ricetta'])    ? (int)$_POST['id_ricetta']    : null;
$id_passo      = !empty($_POST['id_passo'])      ? (int)$_POST['id_passo']      : null;
$is_completata = isset($_POST['is_completata'])  ? (int)(bool)$_POST['is_completata'] : 0;

if (!$id_ricetta || !$id_passo) {
    echo json_encode(['success' => false, 'reason' => 'missing_params']);
    exit();
}

// Controlla se esiste già una riga per questa coppia utente/ricetta
$sql_check = "SELECT id FROM ricetteEseguite WHERE idUtente = ? AND idRicetta = ? LIMIT 1";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_utente, $id_ricetta);
$stmt_check->execute();
$row = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

if ($row) {
    // Aggiorna la riga esistente
    $sql = "UPDATE ricetteEseguite
            SET idUltimoPasso = ?, isCompletata = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $id_passo, $is_completata, $row['id']);
    $esito = $stmt->execute();
    $stmt->close();
} else {
    // Inserisce una nuova riga
    $sql = "INSERT INTO ricetteEseguite (idUtente, idRicetta, idUltimoPasso, isCompletata, nota)
            VALUES (?, ?, ?, ?, '')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $id_utente, $id_ricetta, $id_passo, $is_completata);
    $esito = $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => (bool)$esito]);