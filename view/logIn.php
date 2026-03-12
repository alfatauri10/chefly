<?php
include "../include/inizio.php";
?>


    <div class="card">
        <h2 class="text-center mb-4">Accesso Utente</h2>

        <?php
        // messaggio di errore semplice (passato via GET)
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">'.htmlspecialchars($_GET['error']).'</div>';
        }
        ?>

        <form action="../controller/logInController.php" method="post">
            <label class="form-label">Email</label>
            <input type="email" name="mail" class="form-control mb-3"
                   placeholder="inserisci la tua email" required>

            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control mb-3"
                   placeholder="inserisci la password" required>

            <button type="submit" class="btn btn-primary w-100">Accedi</button>
        </form>

        <div class="text-center mt-3">
            <a href="/signUp.php">Non hai un account? Registrati</a>
        </div>
    </div>
    <link href="../css/logIn.css" rel="stylesheet">

<?php
include "../include/fine.php";
?>