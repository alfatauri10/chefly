<?php
session_start(); // riapre la sessione corrente

// Distrugge tutte le variabili di sessione
$_SESSION = [];

// Distrugge la sessione
session_destroy();

// Reindirizza alla pagina di login
header("Location: ../views/logIn.php");
exit();