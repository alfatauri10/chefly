<?php
include "../include/inizio.php";
?>


    <div class="card">
        <h2 class="text-center mb-4">Accesso Utente</h2>

        <?php
        // messaggio di errore semplice (passato via GET)
        if (isset($_GET['error'])) {
            $msg = ($_GET['error'] == 1) ? "Email o password errati." : "Si è verificato un errore.";
            echo '<div class="alert alert-danger">' . htmlspecialchars($msg) . '</div>';
        }
        ?>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'successRegister'): ?>
            <div class="alert alert-success">Benvenuto nel club, esegui il login!</div>
        <?php endif; ?>

        <form action="../controller/logInController.php" method="post">
            <label class="form-label">Email</label>
            <input type="email"
                   name="mail"
                   class="form-control mb-3"
                   placeholder="inserisci la tua email"
                   value="<?php echo htmlspecialchars($_GET['mail'] ?? ''); ?>"
                   required>


            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control mb-3"
                   placeholder="inserisci la password" required>

            <button type="submit" class="btn btn-primary w-100">Accedi</button>
        </form>

        <div class="text-center mt-3">
            <a href="signup.php">Non hai un account? Registrati</a>
        </div>
    </div>
    <link href="../css/login.css" rel="stylesheet">

<?php
include "../include/fine.php";
?>