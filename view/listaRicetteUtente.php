<?php
// view/listaRicetteUtente.php
session_start();

// Controllo accesso: se non è loggato, rimanda a login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Includiamo il controller che estrarrà le ricette dell'utente dal DB
// Questo renderà disponibile l'array $mieRicette
require_once '../controller/listaRicetteController.php';

// Recuperiamo un nome da mostrare (puoi adattarlo a seconda di come salvi in sessione)
$utente = $_SESSION['nome'] ?? 'Chef';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ricettario di <?php echo htmlspecialchars($utente); ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <!-- Stili aggiuntivi inline per replicare le classi del tuo template -->
    <style>
        .vetrina-container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        .hero-section { text-align: center; margin-bottom: 40px; }
        .hero-section h2 { color: #2d1b10; font-weight: 500; text-transform: uppercase; letter-spacing: 3px; }
        .hero-section p { color: #8c8479; font-size: 0.9rem; letter-spacing: 1px; }
        .recipe-row { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eeeae3; padding: 20px 0; }
        .recipe-info-main { flex: 1; padding: 0 20px; text-align: left; }
        .recipe-info-main h3 { margin: 0 0 5px 0; font-size: 1.2rem; color: #2d1b10; text-transform: capitalize; }
        .recipe-info-main p { margin: 0; color: #a67c52; font-size: 0.9rem; margin-bottom: 5px; }
        .recipe-info-meta { text-align: right; }
        .back-home-link { color: #2d1b10; text-decoration: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        .back-home-link:hover { color: #a67c52; }
    </style>
</head>
<body style="background-color: #fcfbf9; font-family: 'Montserrat', sans-serif;">

<div class="vetrina-container">
    <div class="hero-section">
        <h2>Ricettario di <?php echo htmlspecialchars($utente); ?></h2>
        <p>I tuoi piatti personali</p>
    </div>

    <!-- Gestione dei messaggi (Successo Creazione, Successo Eliminazione, Errori) -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success text-center text-uppercase small fw-bold" style="letter-spacing: 1px; border-radius: 0; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
            Ricetta aggiunta con successo!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
        <div class="alert alert-warning text-center text-uppercase small fw-bold" style="letter-spacing: 1px; border-radius: 0; background: #fffbeb; color: #b45309; border: 1px solid #fde68a;">
            Ricetta eliminata correttamente.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger text-center text-uppercase small fw-bold" style="letter-spacing: 1px; border-radius: 0;">
            Si è verificato un errore durante l'operazione.
        </div>
    <?php endif; ?>


    <!-- Mostriamo le ricette o il messaggio se vuoto -->
    <?php if (empty($mieRicette)): ?>
        <div class="text-center" style="background: #fff; padding: 50px; border: 1px dashed #eeeae3;">
            <p style="color: #a67c52; text-transform: uppercase; letter-spacing: 2px; font-weight: 500;">Non hai ancora inserito nessuna ricetta.</p>
            <a href="aggiungiRicetta.php" class="gold-link">Aggiungi il tuo primo piatto</a>
        </div>
    <?php else: ?>
        <div style="text-align: right; margin-bottom: 30px;">
            <a href="aggiungiRicetta.php" class="btn-login-action" style="padding: 10px 20px; font-size: 0.8rem; text-decoration: none; display: inline-block; width: auto; background-color: #2d1b10; color: #fff; border: none; text-transform: uppercase; letter-spacing: 1px;">+ Aggiungi Ricetta</a>
        </div>

        <div class="recipe-list" style="background: #fff; padding: 0 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
            <?php foreach ($mieRicette as $ricetta): ?>
                <div class="recipe-row">

                    <!-- Placeholder per foto: il query getListaRicetteUtente attuale non estrae l'immagine.
                             Se la estraesse, potremmo mettere <img src="../<?php echo $ricetta['url_copertina']; ?>"> -->
                    <div class="recipe-thumb" style="width: 80px; height: 80px; background: #f4f1ea; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #a67c52; border-radius: 50%;">
                        <!-- Icona Piatto Placeholder -->
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>

                    <div class="recipe-info-main">
                        <h3><?php echo htmlspecialchars($ricetta['titolo']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($ricetta['descrizione'], 0, 60)) . '...'; ?></p>

                        <span style="font-size: 0.7rem; font-weight: 500; background: #eeeae3; padding: 3px 8px; border-radius: 10px; color: #2d1b10; margin-right: 10px;">
                                Difficoltà: <?php echo htmlspecialchars($ricetta['difficolta']); ?>
                            </span>

                        <span style="font-size: 0.7rem; color: #999; text-transform: uppercase;">
                                Inserita il: <?php echo date('d/m/Y', strtotime($ricetta['dataCreazione'])); ?>
                            </span>
                    </div>

                    <div class="recipe-info-meta">
                        <!-- Usiamo un form in POST per sicurezza, come richiede il controller -->
                        <form action="../controller/cancellaRicettaController.php" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questa ricetta? L\'operazione è irreversibile.');" style="margin: 0;">
                            <input type="hidden" name="id_ricetta" value="<?php echo $ricetta['id']; ?>">
                            <button type="submit" style="background: transparent; color: #d9534f; border: 1px solid #f8d7da; padding: 5px 10px; font-size: 0.7rem; font-weight: 500; text-transform: uppercase; cursor: pointer; transition: all 0.3s;">
                                Elimina
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="text-center" style="margin-top: 50px;">
        <a href="../index.php" class="back-home-link">← Torna alla Home</a>
    </div>
</div>

<footer style="text-align: center; padding: 20px; font-size: 0.8rem; color: #8c8479; margin-top: 50px;">
    &copy; <?php echo date('Y'); ?> La Mia Cucina
</footer>

</body>
</html>