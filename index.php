<?php
session_start();

// Controllo se utente logated
if (!isset($_SESSION['user_id'])) {
    header("Location: view/logIn.php");
    exit;
}

include "include/inizio.php";
?>


    <link href="css/index.css" rel="stylesheet">

    <div class="card">
        <h2>Benvenuto, <?php echo htmlspecialchars($_SESSION['user_nome']); ?>!</h2>
        <p>Ciao <?php echo htmlspecialchars($_SESSION['user_nome'] . ' ' . $_SESSION['user_cognome']); ?>, sei loggato con ruolo ID <?php echo $_SESSION['user_ruolo']; ?>.</p>

        <a href="controller/logOutController.php" class="btn btn-danger mt-3 w-100">Logout</a>
    </div>

<?php
include "include/fine.php";
?>