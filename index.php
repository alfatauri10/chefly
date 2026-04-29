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
    <link rel="stylesheet" href="css/chefly.css">
    <style>
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .recipe-card--grid { animation: fadeUp .45s ease both; }
        .recipe-card--grid:nth-child(1)  { animation-delay:.05s }
        .recipe-card--grid:nth-child(2)  { animation-delay:.10s }
        .recipe-card--grid:nth-child(3)  { animation-delay:.15s }
        .recipe-card--grid:nth-child(4)  { animation-delay:.20s }
        .recipe-card--grid:nth-child(5)  { animation-delay:.25s }
        .recipe-card--grid:nth-child(6)  { animation-delay:.30s }
        .recipe-card--grid:nth-child(n+7){ animation-delay:.35s }

        .empty-state { text-align:center; padding:80px 20px; color:var(--muted); }
        .empty-state h3 { font-family:var(--font-serif); font-size:1.4rem; margin-bottom:10px; }
        .empty-state p  { font-size:.9rem; }
    </style>
</head>
<body>

<?php include "include/header.php"; ?>

<main class="page-content">

    <!-- HERO -->
    <div class="hero">
        <p class="eyebrow hero-eyebrow">La community dei cuochi</p>
        <h1 class="hero-title">Scopri, cucina e<br><em>condividi</em> le tue ricette</h1>
        <p class="hero-sub">Tutte le ricette create dagli utenti Chefly, in un unico posto.</p>

        <div class="hero-cta">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/view/aggiungiRicetta.php" class="btn btn-primary">+ Aggiungi una ricetta</a>
                <a href="/view/ilMioRistorante.php" class="btn btn-ghost">Il mio ristorante →</a>
            <?php else: ?>
                <a href="/view/signup.php" class="btn btn-primary">Inizia gratis</a>
                <a href="/view/login.php" class="btn btn-ghost">Hai già un account?</a>
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

    <!-- GRIGLIA -->
    <section class="recipes-section">
        <?php if (empty($tutteLeRicette)): ?>
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:#D6CFC4;margin-bottom:20px;display:block;margin-left:auto;margin-right:auto;">
                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                    <line x1="6" y1="17" x2="18" y2="17"/>
                </svg>
                <h3>Ancora nessuna ricetta</h3>
                <p>Sii il primo a condividere qualcosa di buono!</p>
            </div>
        <?php else: ?>
            <div class="masonry-grid">
                <?php foreach ($tutteLeRicette as $ricetta): ?>
                    <!-- FIX: href ora punta alla pagina ricetta con l'ID -->
                    <a class="recipe-card--grid" href="/view/ricetta.php?id=<?php echo $ricetta['id']; ?>">

                        <?php if (!empty($ricetta['url_copertina'])): ?>
                            <img class="card-cover"
                                 src="<?php echo htmlspecialchars($ricetta['url_copertina']); ?>"
                                 alt="<?php echo htmlspecialchars($ricetta['titolo']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="card-cover-placeholder">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                                    <line x1="6" y1="17" x2="18" y2="17"/>
                                </svg>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <span class="badge badge--<?php echo htmlspecialchars(strtolower($ricetta['difficolta'])); ?>">
                                <?php echo htmlspecialchars(ucfirst($ricetta['difficolta'])); ?>
                            </span>

                            <h2 class="card-title-text"><?php echo htmlspecialchars($ricetta['titolo']); ?></h2>

                            <?php if (!empty($ricetta['descrizione'])): ?>
                                <p class="card-desc-text"><?php echo htmlspecialchars($ricetta['descrizione']); ?></p>
                            <?php endif; ?>

                            <div class="card-footer-row">
                                <div style="display:flex;align-items:center;gap:7px;">
                                    <div class="author-avatar"><?php echo mb_substr($ricetta['nome_autore'], 0, 2); ?></div>
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