<?php
include "../include/inizio.php";
?>

    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            padding: 30px;
        }

        h2 {
            color: #0d6efd;
        }
    </style>

    <div class="card">
        <h2 class="text-center mb-4">Accesso Utente</h2>

        <?php
        // messaggio di errore semplice (passato via GET)
        if (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">'.htmlspecialchars($_GET['error']).'</div>';
        }
        ?>

        <form action="../controllers/logInController.php" method="post">
            <label class="form-label">Email</label>
            <input type="email" name="mail" class="form-control mb-3"
                   placeholder="inserisci la tua email" required>

            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control mb-3"
                   placeholder="inserisci la password" required>

            <button type="submit" class="btn btn-primary w-100">Accedi</button>
        </form>

        <div class="text-center mt-3">
            <a href="signUp.php">Non hai un account? Registrati</a>
        </div>
    </div>

<?php
include "../include/fine.php";
?>