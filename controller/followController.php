<?php
// controller/followController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../include/connessione.php';

header('Content-Type: application/json');

$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) {
    echo json_encode(['success' => false, 'reason' => 'not_logged_in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'reason' => 'method_not_allowed']);
    exit();
}

$id_seguito = !empty($_POST['id_seguito']) ? (int)$_POST['id_seguito'] : null;
$azione     = trim($_POST['azione'] ?? '');

if (!$id_seguito || !in_array($azione, ['follow', 'unfollow'])) {
    echo json_encode(['success' => false, 'reason' => 'invalid_params']);
    exit();
}

// Non puoi seguire te stesso
if ($id_seguito === (int)$id_utente) {
    echo json_encode(['success' => false, 'reason' => 'self_follow']);
    exit();
}

// Verifica che l'utente da seguire esista
$sql_check = "SELECT id FROM utenti WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $id_seguito);
$stmt_check->execute();
$exists = $stmt_check->get_result()->num_rows > 0;
$stmt_check->close();

if (!$exists) {
    echo json_encode(['success' => false, 'reason' => 'user_not_found']);
    exit();
}

if ($azione === 'follow') {
    $data = date('Y-m-d');
    $sql = "INSERT IGNORE INTO follower (idSeguito, idSegue, data) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_seguito, $id_utente, $data);
    $esito = $stmt->execute();
    $stmt->close();
} else {
    $sql = "DELETE FROM follower WHERE idSeguito = ? AND idSegue = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_seguito, $id_utente);
    $esito = $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => (bool)$esito]);