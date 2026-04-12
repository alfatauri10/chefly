<?php
// view/listaRicetteVetrina.php
session_start();

// Includiamo il controller che estrarrà TUTTE le ricette dal DB.
// Questo renderà disponibile l'array $tutteLeRicette
require_once '../controller/listaRicetteController.php';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vetrina Ricette - La Mia Cucina</title>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/chefly.css">

    <style>
        .vetrina-container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        .hero-section { text-align: center; margin-bottom: 40px; }
        .hero-section h2 { color: #2d1b10; font-weight: 500; text-transform: uppercase; letter-spacing: 3px; }
        .hero-section p { color: #8c8479; font-size: 0.9rem; letter-spacing: 1px; }
        .recipe-row { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eeeae3; padding: 20px 0; }
        .recipe-info-main { flex: 1; padding: 0 20px; text-align: left; }
        .recipe-info-main h3 { margin: 0 0 5px 0; font-size: 1.2rem; color: #2d1b10; text-transform: capitalize; }
        .recipe-info-main p { margin: 0; color: #a67c52; font-size: 0.9rem; margin-bottom: 5px; }
        .recipe-info-meta { text-align: right; min-width: 120px; }
        .recipe-info-meta b { color: #2d1b10; font-size: 0.9rem; display: block; margin-bottom: 3px; }
        .recipe-info-meta span { font-size: 0.75rem; color: #999; text-transform: uppercase; }
        .back-home-link { color: #2d1b10; text-decoration: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        .back-home-link:hover { color: #a67c52; }

        .recipe-thumb-container {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            background: #f4f1ea;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a67c52;
            flex-shrink: 0;
            border: 2px solid #eeeae3;
        }
        .recipe-thumb-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body style="background-color: #fcfbf9; font-family: 'Montserrat', sans-serif;">

<main>
    <div class="vetrina-container">
        <section class="hero-section">
            <h2>La nostra Community</h2>
            <p>Gli ultimi piatti condivisi da tutti i cuochi</p>
        </section>

        <?php if (empty($tutteLeRicette)): ?>
            <div style="text-align: center; padding: 50px; background: #fff; border: 1px dashed #eeeae3; border-radius: 8px;">
                <p style="color: #a67c52; text-transform: uppercase; letter-spacing: 2px; font-weight: 500; margin-bottom: 15px;">La vetrina è ancora vuota.</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="aggiungiRicetta.php" class="gold-link">Inizia tu a condividere una ricetta!</a>
                <?php else: ?>
                    <a href="login.php" class="gold-link">Fai il login per condividere la prima ricetta!</a>
                <?php endif; ?>
            </div>
        <?php else: ?>

            <div class="recipe-list" style="background: #fff; padding: 0 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border-radius: 8px;">
                <?php foreach ($tutteLeRicette as $ricetta): ?>
                    <div class="recipe-row">

                        <!-- Immagine di Copertina -->
                        <div class="recipe-thumb-container">
                            <?php if (!empty($ricetta['url_copertina'])): ?>
                                <img src="../<?php echo htmlspecialchars($ricetta['url_copertina']); ?>" alt="Foto Piatto">
                            <?php else: ?>
                                <!-- Icona Piatto Placeholder -->
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            <?php endif; ?>
                        </div>

                        <!-- Informazioni Principali -->
                        <div class="recipe-info-main">
                            <h3><?php echo htmlspecialchars($ricetta['titolo']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($ricetta['descrizione'], 0, 70)) . (strlen($ricetta['descrizione']) > 70 ? '...' : ''); ?></p>

                            <span style="font-size: 0.7rem; font-weight: 500; background: #eeeae3; padding: 3px 8px; border-radius: 10px; color: #2d1b10;">
                                    Difficoltà: <?php echo htmlspecialchars($ricetta['difficolta']); ?>
                                </span>
                        </div>

                        <!-- Metadati (Autore e Data) -->
                        <div class="recipe-info-meta">
                            <b>@<?php echo htmlspecialchars($ricetta['nome_autore']); ?></b>
                            <span><?php echo date('d/m/Y', strtotime($ricetta['dataCreazione'])); ?></span>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <div class="text-center" style="margin-top: 50px;">
            <a href="../index.php" class="back-home-link">← Torna alla Home</a>
        </div>
    </div>
</main>

<footer style="text-align: center; padding: 20px; font-size: 0.8rem; color: #8c8479; margin-top: 30px;">
    &copy; <?php echo date('Y'); ?> La Mia Cucina
</footer>

</body>
</html>