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
            max-width: 420px; /* larghezza massima del form */
            padding: 30px;
        }

        h2 {
            color: #0d6efd;
        }
    </style>

    <div class="card">
        <h2 class="text-center mb-4">Inserimento nuovo utente</h2>

        <form action="../controllers/addUtente.php" method="post">
            <label class="form-label">Cognome</label>
            <input type="text" name="cognome" class="form-control mb-2"
                   placeholder="inserisci il cognome" required>

            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control mb-2"
                   placeholder="inserisci il nome" required>

            <label class="form-label">Email</label>
            <input type="email" name="mail" class="form-control mb-2"
                   placeholder="inserisci l'email" required>

            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control mb-2"
                   placeholder="inserisci la password" required>

            <label class="form-label">Biografia</label>
            <textarea name="biografia" class="form-control mb-2"
                      placeholder="scrivi una breve biografia"></textarea>
            <!--
                    <label class="form-label">ID Timer</label>
                    <input type="number" name="idTimer" class="form-control mb-2"
                           placeholder="inserisci l'id del timer" required>

                    <label class="form-label">ID Livello</label>
                    <input type="number" name="idLivello" class="form-control mb-2"
                           placeholder="inserisci l'id del livello" required>

                    <label class="form-label">Ruolo utente</label>
                    <select name="idRuolo" class="form-control mb-2">
                        <option value="1" selected>Utente normale</option>
                        <option value="2">Utente amministratore</option>
                    </select>

                    <label class="form-label">Punteggio attuale</label>
                    <input type="number" name="punteggioAttuale" class="form-control mb-3"
                           value="0" required>
                -->
            <button type="submit" class="btn btn-primary w-100">Invia</button>
        </form>
    </div>

<?php
include "../include/fine.php";
?>