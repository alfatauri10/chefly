<?php
// view/aggiungiRicetta.php
session_start();

// Controllo accesso: se non è loggato, rimanda a login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aggiungi Ricetta - La Mia Cucina</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Link al nuovo foglio di stile esterno -->
    <link rel="stylesheet" href="../css/aggiungiRicetta.css">
</head>
<body class="add-recipe-page">

<div class="add-recipe-container">

    <div class="recipe-card">

        <div class="recipe-header">
            <h1 class="recipe-title">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#065fd4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"></path>
                    <line x1="6" y1="17" x2="18" y2="17"></line>
                </svg>
                Nuova Ricetta
            </h1>
            <p class="recipe-subtitle">Condividi la tua creazione culinaria con la community</p>
        </div>

        <!-- Messaggio di errore -->
        <?php if (isset($_GET['error']) && $_GET['error'] == 'campi_mancanti'): ?>
            <div class="alert-error">
                Per favore, compila almeno il titolo e la descrizione.
            </div>
        <?php endif; ?>

        <form action="../controller/aggiungiRicettaController.php" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label class="form-label">Titolo della Ricetta *</label>
                <input type="text" name="titolo" class="form-control" placeholder="Es: Spaghetti alla Carbonara originale" required>
            </div>

            <div class="form-group">
                <label class="form-label">Descrizione e Procedimento *</label>
                <textarea name="descrizione" class="form-control" placeholder="Descrivi gli ingredienti e i passaggi principali..." required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Difficoltà *</label>
                <select name="difficolta" class="form-control" required>
                    <option value="" disabled selected>Seleziona il livello di difficoltà</option>
                    <option value="Facile">Facile</option>
                    <option value="Media">Media</option>
                    <option value="Difficile">Difficile</option>
                </select>
            </div>

            <!-- Riga con due colonne su Desktop, impilata su Mobile -->
            <div class="form-row">
                <div class="form-group form-col">
                    <label class="form-label">ID Nazionalità</label>
                    <input type="number" name="id_nazionalita" class="form-control" placeholder="Es: 1">
                </div>
                <div class="form-group form-col">
                    <label class="form-label">ID Tipologia Piatto</label>
                    <input type="number" name="id_tipologia" class="form-control" placeholder="Es: 2">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Foto di Copertina</label>
                <input type="file" name="copertina" class="form-control" accept="image/*">
                <span class="file-hint">Immagine principale che apparirà nella vetrina.</span>
            </div>

            <div class="form-group">
                <label class="form-label">Galleria Fotografica</label>
                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                <span class="file-hint">Tieni premuto CTRL (o CMD su Mac) per selezionare più foto.</span>
            </div>

            <button type="submit" class="btn-submit">
                Pubblica Ricetta
            </button>

        </form>

        <div class="back-link-container">
            <a href="listaRicetteUtente.php" class="back-link">
                &larr; Torna alle mie ricette
            </a>
        </div>

    </div>
</div>

</body>
</html>