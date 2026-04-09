<?php
session_start();
require_once "include/connessione.php";
require_once "model/ricetta.php";

$tutteLeRicette = getTutteLeRicetteDB($conn);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chefly — Scopri le ricette della community</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,500&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="css/index.css">



</head>
<body>

<?php include "include/header.php"; ?>

<main class="page-content">

    <!-- HERO -->
    <div class="hero">
        <p class="hero-eyebrow">La community dei cuochi</p>
        <h1 class="hero-title">Scopri, cucina e<br><em>condividi</em> le tue ricette</h1>
        <p class="hero-sub">Tutte le ricette create dagli utenti Chefly, in un unico posto.</p>

        <div class="hero-cta">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/view/aggiungiRicetta.php" class="btn-primary">+ Aggiungi una ricetta</a>
                <a href="/view/ilMioRistorante.php" class="btn-ghost">Il mio ristorante →</a>
            <?php else: ?>
                <a href="/view/signup.php" class="btn-primary">Inizia gratis</a>
                <a href="/view/login.php" class="btn-ghost">Hai già un account?</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- DIVIDER -->
    <div class="section-divider">
        <div class="section-divider-line"></div>
        <span class="section-divider-label">
            <?php echo count($tutteLeRicette); ?> ricett<?php echo count($tutteLeRicette) !== 1 ? 'e' : 'a'; ?> pubblicate
        </span>
        <div class="section-divider-line"></div>
    </div>

    <!-- GRIGLIA RICETTE -->
    <section class="recipes-section">
        <?php if (empty($tutteLeRicette)): ?>
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                    <line x1="6" y1="17" x2="18" y2="17"/>
                </svg>
                <h3>Ancora nessuna ricetta</h3>
                <p>Sii il primo a condividere qualcosa di buono!</p>
            </div>
        <?php else: ?>
            <div class="masonry-grid">
                <?php foreach ($tutteLeRicette as $ricetta): ?>
                    <a class="recipe-card" href="#">

                        <!-- COVER -->
                        <?php if (!empty($ricetta['url_copertina'])): ?>
                            <img class="card-cover"
                                 src="<?php echo htmlspecialchars($ricetta['url_copertina']); ?>"
                                 alt="<?php echo htmlspecialchars($ricetta['titolo']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="card-cover-placeholder">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                                    <line x1="6" y1="17" x2="18" y2="17"/>
                                </svg>
                            </div>
                        <?php endif; ?>

                        <!-- BODY -->
                        <div class="card-body">
                            <span class="card-badge badge-<?php echo htmlspecialchars(strtolower($ricetta['difficolta'])); ?>">
                                <?php echo htmlspecialchars(ucfirst($ricetta['difficolta'])); ?>
                            </span>

                            <h2 class="card-title"><?php echo htmlspecialchars($ricetta['titolo']); ?></h2>

                            <?php if (!empty($ricetta['descrizione'])): ?>
                                <p class="card-desc"><?php echo htmlspecialchars($ricetta['descrizione']); ?></p>
                            <?php endif; ?>

                            <div class="card-footer">
                                <div class="card-author">
                                    <div class="author-avatar">
                                        <?php echo mb_substr($ricetta['nome_autore'], 0, 2); ?>
                                    </div>
                                    <span class="author-name">@<?php echo htmlspecialchars($ricetta['nome_autore']); ?></span>
                                </div>
                                <span class="card-date"><?php echo date('d M Y', strtotime($ricetta['dataCreazione'])); ?></span>
                            </div>
                        </div>

                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</main>

<?php include "include/footer.php"; ?>

<script><?php include_once "js/dropDownMenu.js"; ?></script>
</body>
</html>