<?php
// controller/aggiungiPreferitaController.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../include/connessione.php';

header('Content-Type: application/json');

$id_utente = $_SESSION['user_id'] ?? null;
if (!$id_utente) { echo json_encode(['success' => false]); exit(); }

$id_ricetta = !empty($_POST['id_ricetta']) ? (int)$_POST['id_ricetta'] : null;
if (!$id_ricetta) { echo json_encode(['success' => false]); exit(); }

$data = date('Y-m-d');
$sql = "INSERT IGNORE INTO ricettePreferite (idRicetta, idUtente, data) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $id_ricetta, $id_utente, $data);
$esito = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $esito]);