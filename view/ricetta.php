<?php
/* view/ricetta.php */
session_start();
require_once '../include/connessione.php';
require_once '../model/ricetta.php';
require_once '../model/passo.php';

$id_ricetta = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_ricetta) { header("Location: ../index.php"); exit(); }

$ricetta = getRicettaByIdDB($conn, $id_ricetta);
if (!$ricetta) { header("Location: ../index.php"); exit(); }

// Dati correlati
$passi = getListaPassiByIdRicetta($conn, $id_ricetta);

// Autore
$sql_autore = "SELECT username, urlFotoProfilo, biografia FROM utenti WHERE id = ?";
$stmt_a = $conn->prepare($sql_autore);
$stmt_a->bind_param("i", $ricetta['idCreatore']);
$stmt_a->execute();
$autore = $stmt_a->get_result()->fetch_assoc();
$stmt_a->close();

// Nazionalità
$sql_naz = "SELECT nome, sigla FROM anagNazionalita WHERE id = ?";
$stmt_n = $conn->prepare($sql_naz);
$stmt_n->bind_param("i", $ricetta['idNazionalita']);
$stmt_n->execute();
$nazionalita = $stmt_n->get_result()->fetch_assoc();
$stmt_n->close();

// Tipologia
$sql_tip = "SELECT nome FROM anagTipologiePiatti WHERE id = ?";
$stmt_t = $conn->prepare($sql_tip);
$stmt_t->bind_param("i", $ricetta['idTipologia']);
$stmt_t->execute();
$tipologia = $stmt_t->get_result()->fetch_assoc();
$stmt_t->close();

// Media ricetta
$sql_media = "SELECT id, urlMedia, isCopertina FROM mediaRicette WHERE idRicetta = ? ORDER BY isCopertina DESC, id ASC";
$stmt_m = $conn->prepare($sql_media);
$stmt_m->bind_param("i", $id_ricetta);
$stmt_m->execute();
$result_media = $stmt_m->get_result();
$copertina = null;
$galleria = [];
while ($row = $result_media->fetch_assoc()) {
    if ($row['isCopertina']) $copertina = $row;
    else $galleria[] = $row;
}
$stmt_m->close();

// Preferiti: controlla se l'utente loggato ha già salvato
$is_preferita = false;
$id_utente = $_SESSION['user_id'] ?? null;
if ($id_utente) {
    $sql_pref = "SELECT 1 FROM ricettePreferite WHERE idRicetta = ? AND idUtente = ?";
    $stmt_pref = $conn->prepare($sql_pref);
    $stmt_pref->bind_param("ii", $id_ricetta, $id_utente);
    $stmt_pref->execute();
    $is_preferita = $stmt_pref->get_result()->num_rows > 0;
    $stmt_pref->close();
}

// Durata totale stimata
$durata_totale = array_sum(array_column($passi, 'durata'));

// Timer dell'autore (per "Inizia a cucinare")
$sql_timer = "SELECT t.coloreSfondo, t.coloreLancetta, t.coloreNumeri
              FROM timer t JOIN utenti u ON u.idTimer = t.id WHERE u.id = ?";
$stmt_timer = $conn->prepare($sql_timer);
$stmt_timer->bind_param("i", $ricetta['idCreatore']);
$stmt_timer->execute();
$timer_autore = $stmt_timer->get_result()->fetch_assoc() ?? ['coloreSfondo'=>'#FFFFFF','coloreLancetta'=>'#000000','coloreNumeri'=>'#000000'];
$stmt_timer->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ricetta['titolo']); ?> — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <!-- Timer JS -->
    <style>
        /* ── HERO COPERTINA ── */
        .recipe-hero {
            position: relative;
            width: 100%;
            max-height: 520px;
            overflow: hidden;
            background: var(--sand);
        }
        .recipe-hero-img {
            width: 100%;
            max-height: 520px;
            object-fit: cover;
            display: block;
        }
        .recipe-hero-placeholder {
            width: 100%;
            height: 340px;
            background: linear-gradient(135deg, var(--sand) 0%, #EDE8E0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #C4C0B8;
        }
        .recipe-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(26,16,8,.65) 0%, transparent 55%);
        }
        .recipe-hero-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* ── LAYOUT ── */
        .recipe-page {
            max-width: 860px;
            margin: 0 auto;
            padding: 0 20px 100px;
        }

        /* ── INFO HEADER ── */
        .recipe-info-header {
            padding: 32px 0 28px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 32px;
        }
        .recipe-title {
            font-family: var(--font-serif);
            font-size: clamp(1.7rem, 4vw, 2.4rem);
            font-weight: 700;
            color: var(--brown);
            line-height: 1.15;
            margin-bottom: 12px;
        }
        .recipe-description {
            font-size: .95rem;
            color: var(--muted);
            line-height: 1.7;
            max-width: 620px;
            margin-bottom: 22px;
        }
        .recipe-meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 24px;
        }
        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .78rem;
            font-weight: 600;
            padding: 6px 13px;
            border-radius: 20px;
            background: var(--cream);
            border: 1px solid var(--border);
            color: var(--brown);
        }
        .meta-chip svg { flex-shrink: 0; color: var(--caramel); }

        /* Autore */
        .author-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .author-avatar-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }
        .author-info {}
        .author-username {
            font-size: .85rem;
            font-weight: 700;
            color: var(--brown);
        }
        .author-date {
            font-size: .72rem;
            color: var(--muted-light);
        }

        /* Azioni CTA */
        .recipe-cta-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }
        .btn-cook {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--caramel);
            color: #FFF;
            font-family: var(--font-sans);
            font-size: .9rem;
            font-weight: 700;
            padding: 13px 28px;
            border-radius: var(--radius-pill);
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: background .2s, transform .1s;
        }
        .btn-cook:hover { background: var(--caramel-dark); transform: translateY(-1px); }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: transparent;
            color: var(--brown);
            font-family: var(--font-sans);
            font-size: .88rem;
            font-weight: 600;
            padding: 13px 22px;
            border-radius: var(--radius-pill);
            border: 1.5px solid var(--border);
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }
        .btn-save:hover, .btn-save.saved {
            background: #FFF3ED;
            border-color: var(--caramel);
            color: var(--caramel);
        }
        .btn-save.saved svg { fill: var(--caramel); stroke: var(--caramel); }

        /* ── GALLERIA ── */
        .gallery-section {
            margin-bottom: 40px;
        }
        .gallery-section-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--muted);
            margin-bottom: 14px;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        .gallery-thumb {
            aspect-ratio: 1;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            border: 1px solid var(--border);
            transition: transform .2s, box-shadow .2s;
        }
        .gallery-thumb:hover { transform: scale(1.03); box-shadow: 0 8px 24px rgba(26,16,8,.12); }
        .gallery-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* ── LIGHTBOX ── */
        .lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.88);
            z-index: 9000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s ease;
        }
        .lightbox.open { opacity: 1; pointer-events: all; }
        .lightbox img { max-width: 90vw; max-height: 85vh; border-radius: 12px; object-fit: contain; }
        .lightbox-close {
            position: absolute;
            top: 20px; right: 20px;
            width: 42px; height: 42px;
            border-radius: 50%;
            background: rgba(255,255,255,.15);
            border: none;
            color: #fff;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            transition: background .2s;
        }
        .lightbox-close:hover { background: rgba(255,255,255,.28); }

        /* ── SEZIONE PASSI ── */
        .passi-section { margin-bottom: 60px; }
        .passi-section-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border);
        }
        .passi-count-badge {
            background: var(--brown);
            color: #FFF;
            font-size: .72rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Singolo passo */
        .passo-card {
            display: grid;
            grid-template-columns: 52px 1fr;
            gap: 20px;
            margin-bottom: 28px;
            position: relative;
        }
        .passo-card:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 25px;
            top: 52px;
            bottom: -28px;
            width: 2px;
            background: var(--border);
        }

        .passo-num-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            flex-shrink: 0;
        }
        .passo-circle {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--brown);
            color: #FFF;
            font-family: var(--font-serif);
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(26,16,8,.18);
        }

        .passo-content {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            padding-bottom: 4px;
        }

        .passo-content-header {
            padding: 18px 22px 14px;
            border-bottom: 1px solid var(--border-light);
        }
        .passo-content-title {
            font-family: var(--font-serif);
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--brown);
            margin-bottom: 8px;
        }
        .passo-chips-row {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .pchip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .68rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .pchip--time  { background: #FFF3ED; color: var(--caramel); }
        .pchip--cook  { background: #FFFBEB; color: #92400E; }
        .pchip--rest  { background: #F0F9FF; color: #0369A1; }
        .pchip--tech  { background: var(--sand); color: var(--muted); }

        .passo-content-body { padding: 16px 22px 18px; }
        .passo-description {
            font-size: .92rem;
            color: var(--brown);
            line-height: 1.75;
            margin-bottom: 18px;
        }

        /* Ingredienti del passo */
        .passo-ingredients {
            margin-bottom: 18px;
        }
        .passo-ingredients-label {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .ingredients-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .ing-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: var(--cream);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 5px 12px;
            font-size: .8rem;
            color: var(--brown);
            font-weight: 500;
        }
        .ing-pill em {
            font-style: normal;
            color: var(--caramel);
            font-weight: 700;
            margin-left: 3px;
        }

        /* Foto passo */
        .passo-media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 8px;
            margin-top: 4px;
        }
        .passo-media-thumb {
            aspect-ratio: 1;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 1px solid var(--border);
        }
        .passo-media-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* Timer widget nel passo */
        .timer-wrapper {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-light);
        }

        /* ── CHEFLY TIMER CSS ── */
        .chefly-timer { font-family: var(--font-sans); }
        .ct-inner { display: flex; align-items: center; gap: 20px; }
        .ct-clock-wrap { flex-shrink: 0; }
        .ct-clock-face {
            position: relative;
            width: 90px; height: 90px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: <?php echo htmlspecialchars($timer_autore['coloreSfondo']); ?>;
            box-shadow: 0 4px 20px rgba(45,27,16,.10);
            transition: box-shadow .3s;
        }
        .ct-clock-svg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .ct-hand {
            position: absolute;
            bottom: 50%; left: 50%;
            transform-origin: bottom center;
            border-radius: 3px;
        }
        .ct-hand-minute { width: 3px; height: 28px; margin-left: -1.5px; background: <?php echo htmlspecialchars($timer_autore['coloreLancetta']); ?>; }
        .ct-hand-second { width: 2px; height: 34px; margin-left: -1px; background: <?php echo htmlspecialchars($timer_autore['coloreLancetta']); ?>; }
        .ct-clock-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%); width: 8px; height: 8px; border-radius: 50%; background: <?php echo htmlspecialchars($timer_autore['coloreLancetta']); ?>; z-index: 10; }
        .ct-display { position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%); font-size: .6rem; font-weight: 700; letter-spacing: 1px; white-space: nowrap; color: <?php echo htmlspecialchars($timer_autore['coloreNumeri']); ?>; }

        .ct-controls { flex: 1; }
        .ct-label { font-family: var(--font-serif); font-size: .88rem; font-weight: 600; color: var(--brown); margin-bottom: 2px; }
        .ct-duration { font-size: .75rem; color: var(--muted); margin-bottom: 10px; }
        .ct-btns { display: flex; gap: 7px; margin-bottom: 7px; }
        .ct-btn {
            width: 34px; height: 34px; border-radius: 50%;
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: transform .1s, background .15s;
            flex-shrink: 0;
        }
        .ct-btn:active { transform: scale(.9); }
        .ct-btn-play  { background: var(--caramel); color: #FFF; }
        .ct-btn-pause { background: var(--brown); color: #FFF; }
        .ct-btn-reset { background: var(--sand); color: var(--muted); }
        .ct-btn svg   { width: 14px; height: 14px; }
        .ct-state-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .ct-state-idle    { color: var(--muted-light); }
        .ct-state-running { color: var(--caramel); }
        .ct-state-paused  { color: var(--muted); }
        .ct-state-done    { color: #27ae60; }

        /* ── NO PASSI ── */
        .no-passi {
            text-align: center;
            padding: 48px;
            background: var(--cream);
            border: 1.5px dashed var(--border);
            border-radius: var(--radius-lg);
            color: var(--muted);
        }

        /* ── STICKY CTA FOOTER (mobile) ── */
        .sticky-cta {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: var(--white);
            border-top: 1px solid var(--border);
            padding: 14px 20px;
            display: none;
            gap: 10px;
            z-index: 500;
            box-shadow: 0 -4px 20px rgba(26,16,8,.07);
        }
        @media (max-width: 640px) { .sticky-cta { display: flex; } }

        @media (max-width: 640px) {
            .passo-card { grid-template-columns: 38px 1fr; gap: 12px; }
            .passo-circle { width: 38px; height: 38px; font-size: .85rem; }
            .passo-card:not(:last-child)::before { left: 18px; }
            .recipe-cta-row { display: none; } /* nascosto su mobile — usa sticky */
        }
    </style>
</head>
<body>
<?php include '../include/header.php'; ?>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    <img src="" alt="" id="lightboxImg">
</div>

<main class="page-content">

    <!-- HERO COPERTINA -->
    <?php if ($copertina): ?>
        <div class="recipe-hero">
            <img src="../<?php echo htmlspecialchars($copertina['urlMedia']); ?>"
                 alt="<?php echo htmlspecialchars($ricetta['titolo']); ?>"
                 class="recipe-hero-img">
            <div class="recipe-hero-overlay"></div>
            <div class="recipe-hero-badge">
                <span class="badge badge--<?php echo strtolower($ricetta['difficolta']); ?>">
                    <?php echo ucfirst($ricetta['difficolta']); ?>
                </span>
                <?php if ($tipologia): ?>
                    <span class="badge" style="background:rgba(255,255,255,.9);color:var(--brown);">
                        <?php echo htmlspecialchars($tipologia['nome']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="recipe-hero">
            <div class="recipe-hero-placeholder">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round">
                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                    <line x1="6" y1="17" x2="18" y2="17"/>
                </svg>
            </div>
        </div>
    <?php endif; ?>

    <div class="recipe-page">

        <!-- INFO HEADER -->
        <div class="recipe-info-header">
            <h1 class="recipe-title"><?php echo htmlspecialchars($ricetta['titolo']); ?></h1>
            <p class="recipe-description"><?php echo nl2br(htmlspecialchars($ricetta['descrizione'])); ?></p>

            <!-- Meta chips -->
            <div class="recipe-meta-row">
                <!-- Difficoltà -->
                <span class="meta-chip">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <?php echo ucfirst($ricetta['difficolta']); ?>
                </span>

                <!-- Durata totale -->
                <?php if ($durata_totale > 0): ?>
                    <span class="meta-chip">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?php echo $durata_totale; ?> min
                    </span>
                <?php endif; ?>

                <!-- Nazionalità -->
                <?php if ($nazionalita): ?>
                    <span class="meta-chip">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        <?php echo htmlspecialchars($nazionalita['nome']); ?>
                    </span>
                <?php endif; ?>

                <!-- Tipologia -->
                <?php if ($tipologia): ?>
                    <span class="meta-chip">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        <?php echo htmlspecialchars($tipologia['nome']); ?>
                    </span>
                <?php endif; ?>

                <!-- N° passi -->
                <?php if (count($passi) > 0): ?>
                    <span class="meta-chip">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        <?php echo count($passi); ?> pass<?php echo count($passi) !== 1 ? 'i' : 'o'; ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Autore -->
            <div class="author-row">
                <img src="<?php echo htmlspecialchars($autore['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>"
                     alt="<?php echo htmlspecialchars($autore['username']); ?>"
                     class="author-avatar-img">
                <div class="author-info">
                    <div class="author-username">@<?php echo htmlspecialchars($autore['username']); ?></div>
                    <div class="author-date"><?php echo date('d M Y', strtotime($ricetta['dataCreazione'])); ?></div>
                </div>
            </div>

            <!-- CTA -->
            <div class="recipe-cta-row">
                <?php if (!empty($passi)): ?>
                    <a href="#passi" class="btn-cook" onclick="scrollToCook(event)">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                        Inizia a cucinare
                    </a>
                <?php endif; ?>

                <?php if ($id_utente): ?>
                    <button class="btn-save <?php echo $is_preferita ? 'saved' : ''; ?>" id="btnSalva" onclick="togglePreferita()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $is_preferita ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" id="iconSalva">
                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                        </svg>
                        <span id="labelSalva"><?php echo $is_preferita ? 'Salvata' : 'Salva'; ?></span>
                    </button>
                <?php else: ?>
                    <a href="/view/login.php" class="btn-save">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                        Salva
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- GALLERIA -->
        <?php if (!empty($galleria)): ?>
            <div class="gallery-section">
                <p class="gallery-section-title">Galleria</p>
                <div class="gallery-grid">
                    <?php foreach ($galleria as $foto): ?>
                        <div class="gallery-thumb" onclick="openLightbox('../<?php echo htmlspecialchars($foto['urlMedia']); ?>')">
                            <img src="../<?php echo htmlspecialchars($foto['urlMedia']); ?>" alt="" loading="lazy">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- PASSI -->
        <div class="passi-section" id="passi">
            <div class="passi-section-header">
                <h2 class="section-title">Come si fa</h2>
                <?php if (!empty($passi)): ?>
                    <span class="passi-count-badge"><?php echo count($passi); ?> pass<?php echo count($passi) !== 1 ? 'i' : 'o'; ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($passi)): ?>
                <div class="no-passi">
                    <p>Nessun passo ancora aggiunto per questa ricetta.</p>
                </div>
            <?php else: ?>
                <?php foreach ($passi as $idx => $passo): ?>
                    <div class="passo-card" id="passo-<?php echo $passo['id']; ?>">
                        <!-- Numero -->
                        <div class="passo-num-col">
                            <div class="passo-circle"><?php echo $idx + 1; ?></div>
                        </div>

                        <!-- Contenuto -->
                        <div class="passo-content">
                            <div class="passo-content-header">
                                <div class="passo-content-title"><?php echo htmlspecialchars($passo['titolo']); ?></div>
                                <div class="passo-chips-row">
                                    <?php if (!empty($passo['durata'])): ?>
                                        <span class="pchip pchip--time">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            <?php echo $passo['durata']; ?> min totali
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($passo['tempoCottura'])): ?>
                                        <span class="pchip pchip--cook">🔥 Cottura <?php echo $passo['tempoCottura']; ?> min</span>
                                    <?php endif; ?>
                                    <?php if (!empty($passo['tempoRiposo'])): ?>
                                        <span class="pchip pchip--rest">💤 Riposo <?php echo $passo['tempoRiposo']; ?> min</span>
                                    <?php endif; ?>
                                    <?php if (!empty($passo['nome_cottura'])): ?>
                                        <span class="pchip pchip--tech">
                                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                            <?php echo htmlspecialchars($passo['nome_cottura']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="passo-content-body">
                                <!-- Descrizione -->
                                <p class="passo-description"><?php echo nl2br(htmlspecialchars($passo['descrizione'])); ?></p>

                                <!-- Ingredienti del passo -->
                                <?php if (!empty($passo['ingredienti'])): ?>
                                    <div class="passo-ingredients">
                                        <p class="passo-ingredients-label">Ingredienti per questo passo</p>
                                        <div class="ingredients-list">
                                            <?php foreach ($passo['ingredienti'] as $ing): ?>
                                                <span class="ing-pill">
                                                    <?php echo htmlspecialchars($ing['nome']); ?>
                                                    <?php if (!empty($ing['dose'])): ?>
                                                        <em><?php echo htmlspecialchars($ing['dose']); ?></em>
                                                    <?php endif; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Foto del passo -->
                                <?php if (!empty($passo['media'])): ?>
                                    <div class="passo-media-grid">
                                        <?php foreach ($passo['media'] as $m): ?>
                                            <div class="passo-media-thumb" onclick="openLightbox('../<?php echo htmlspecialchars($m['urlMedia']); ?>')">
                                                <img src="../<?php echo htmlspecialchars($m['urlMedia']); ?>" alt="" loading="lazy">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Timer (solo se ha durata) -->
                                <?php if (!empty($passo['durata'])): ?>
                                    <div class="timer-wrapper">
                                        <div data-chefly-timer="<?php echo $passo['id']; ?>"
                                             data-durata="<?php echo $passo['durata']; ?>"
                                             data-label="<?php echo htmlspecialchars($passo['titolo']); ?>">
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div><!-- .recipe-page -->
</main>

<!-- STICKY MOBILE CTA -->
<div class="sticky-cta">
    <?php if (!empty($passi)): ?>
        <a href="#passi" class="btn-cook" style="flex:1;justify-content:center;" onclick="scrollToCook(event)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="10 8 16 12 10 16 10 8"/></svg>
            Inizia a cucinare
        </a>
    <?php endif; ?>
    <?php if ($id_utente): ?>
        <button class="btn-save <?php echo $is_preferita ? 'saved' : ''; ?>" onclick="togglePreferita()" style="flex-shrink:0;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="<?php echo $is_preferita ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </button>
    <?php endif; ?>
</div>

<?php include '../include/footer.php'; ?>

<!-- TIMER JS (integrato) -->
<script src="../js/timer.js"></script>

<script>
    /* ── Lightbox ── */
    function openLightbox(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

    /* ── Scroll to passi ── */
    function scrollToCook(e) {
        e.preventDefault();
        document.getElementById('passi').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /* ── Toggle Preferiti (AJAX) ── */
    let isSaved = <?php echo json_encode($is_preferita); ?>;

    function togglePreferita() {
        <?php if (!$id_utente): ?>
        window.location.href = '/view/login.php';
        return;
        <?php endif; ?>

        const url = isSaved
            ? '../controller/rimuoviPreferitaController.php'
            : '../controller/aggiungiPreferitaController.php';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_ricetta=<?php echo $id_ricetta; ?>'
        }).then(r => r.json()).then(data => {
            if (data.success) {
                isSaved = !isSaved;
                // Aggiorna tutti i btn-save
                document.querySelectorAll('.btn-save').forEach(btn => {
                    btn.classList.toggle('saved', isSaved);
                    const svg = btn.querySelector('svg');
                    const label = btn.querySelector('#labelSalva');
                    if (svg) svg.setAttribute('fill', isSaved ? 'currentColor' : 'none');
                    if (label) label.textContent = isSaved ? 'Salvata' : 'Salva';
                });
            }
        }).catch(() => {});
    }
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>
</body>
</html>