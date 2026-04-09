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

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brown:    #1A1008;
            --caramel:  #C4622D;
            --sand:     #F5F0E8;
            --cream:    #FDFCFA;
            --border:   #EDE8E0;
            --muted:    #8B7355;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--brown);
            min-height: 100vh;
        }

        /* ── HERO ──────────────────────────────────────────── */
        .hero {
            padding: 64px 40px 48px;
            text-align: center;
            max-width: 640px;
            margin: 0 auto;
        }

        .hero-eyebrow {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--caramel);
            margin-bottom: 16px;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 700;
            color: var(--brown);
            line-height: 1.15;
            margin-bottom: 16px;
        }

        .hero-title em {
            font-style: italic;
            color: var(--caramel);
        }

        .hero-sub {
            font-size: .95rem;
            color: var(--muted);
            line-height: 1.65;
        }

        /* ── MASONRY GRID ───────────────────────────────────── */
        .recipes-section {
            padding: 0 28px 100px;
            max-width: 1280px;
            margin: 0 auto;
        }

        .masonry-grid {
            columns: 4 260px;
            column-gap: 18px;
        }

        /* ── CARD ───────────────────────────────────────────── */
        .recipe-card {
            break-inside: avoid;
            display: inline-block;
            width: 100%;
            margin-bottom: 18px;
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: transform .25s ease, box-shadow .25s ease;
            text-decoration: none;
            color: inherit;
        }

        .recipe-card:hover {
            transform: translateY(-5px) rotate(0.4deg);
            box-shadow: 0 16px 48px rgba(26,16,8,.13);
        }

        /* Ogni terza card ruota leggermente per effetto "sparso" */
        .recipe-card:nth-child(3n)   { transform-origin: top right; }
        .recipe-card:nth-child(3n):hover { transform: translateY(-5px) rotate(-0.4deg); }
        .recipe-card:nth-child(2n+1) { transform-origin: top left; }

        /* Piccola inclinazione statica per il look "scattered" */
        .recipe-card:nth-child(4n+1) { margin-top: 32px; }
        .recipe-card:nth-child(4n+3) { margin-top: -12px; }

        /* ── COVER ──────────────────────────────────────────── */
        .card-cover {
            width: 100%;
            aspect-ratio: 4/3;
            object-fit: cover;
            display: block;
        }

        .card-cover-placeholder {
            width: 100%;
            aspect-ratio: 4/3;
            background: var(--sand);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #D6CFC4;
        }

        /* ── BODY ───────────────────────────────────────────── */
        .card-body {
            padding: 16px 18px 18px;
        }

        .card-badge {
            display: inline-block;
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 10px;
        }

        .badge-facile    { background: #F0FDF4; color: #166534; }
        .badge-media     { background: #FFFBEB; color: #92400E; }
        .badge-difficile { background: #FFF1F0; color: #991B1B; }
        .badge-esperto   { background: #1A1008; color: #F5E6D3; }

        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--brown);
            line-height: 1.3;
            margin-bottom: 7px;
        }

        .card-desc {
            font-size: .8rem;
            color: var(--muted);
            line-height: 1.55;
            margin-bottom: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .card-author {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .author-avatar {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--sand);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .68rem;
            font-weight: 700;
            color: var(--caramel);
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .author-name {
            font-size: .75rem;
            font-weight: 600;
            color: var(--brown);
        }

        .card-date {
            font-size: .7rem;
            color: #A89880;
        }

        /* ── EMPTY STATE ─────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--muted);
        }

        .empty-state svg { margin-bottom: 20px; color: #D6CFC4; }
        .empty-state h3  { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 10px; color: var(--brown); }
        .empty-state p   { font-size: .9rem; }

        /* ── SECTION DIVIDER ─────────────────────────────────── */
        .section-divider {
            display: flex;
            align-items: center;
            gap: 20px;
            max-width: 1280px;
            margin: 0 auto 36px;
            padding: 0 28px;
        }

        .section-divider-line {
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .section-divider-label {
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: #C4C0B8;
        }

        /* ── CTA loggato/non ─────────────────────────────────── */
        .hero-cta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 28px;
        }

        .btn-primary {
            background: var(--brown);
            color: #fff;
            padding: 12px 28px;
            border-radius: 50px;
            font-size: .88rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .2s, transform .1s;
        }
        .btn-primary:hover { background: #3a2518; transform: translateY(-1px); }

        .btn-ghost {
            color: var(--brown);
            padding: 12px 20px;
            font-size: .88rem;
            font-weight: 500;
            text-decoration: none;
            opacity: .6;
            transition: opacity .2s;
        }
        .btn-ghost:hover { opacity: 1; }

        /* ── ANIMAZIONE ENTRATA ──────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .recipe-card {
            animation: fadeUp .5s ease both;
        }

        .recipe-card:nth-child(1)  { animation-delay: .05s; }
        .recipe-card:nth-child(2)  { animation-delay: .10s; }
        .recipe-card:nth-child(3)  { animation-delay: .15s; }
        .recipe-card:nth-child(4)  { animation-delay: .20s; }
        .recipe-card:nth-child(5)  { animation-delay: .25s; }
        .recipe-card:nth-child(6)  { animation-delay: .30s; }
        .recipe-card:nth-child(7)  { animation-delay: .35s; }
        .recipe-card:nth-child(8)  { animation-delay: .40s; }
        .recipe-card:nth-child(n+9){ animation-delay: .45s; }

        /* ── RESPONSIVE ──────────────────────────────────────── */
        @media (max-width: 900px) {
            .masonry-grid { columns: 2 200px; }
        }

        @media (max-width: 520px) {
            .masonry-grid { columns: 1; }
            .hero          { padding: 40px 20px 32px; }
            .recipes-section { padding: 0 16px 80px; }
            .recipe-card:nth-child(4n+1),
            .recipe-card:nth-child(4n+3) { margin-top: 0; }
        }
    </style>
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