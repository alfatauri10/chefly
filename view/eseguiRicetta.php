<?php
/* view/eseguiRicetta.php */
session_start();
require_once '../include/connessione.php';
require_once '../model/ricetta.php';
require_once '../model/passo.php';
require_once '../model/timer.php';

$id_ricetta = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_ricetta) { header("Location: ../index.php"); exit(); }

$ricetta = getRicettaByIdDB($conn, $id_ricetta);
if (!$ricetta) { header("Location: ../index.php"); exit(); }

$passi = getListaPassiByIdRicetta($conn, $id_ricetta);
if (empty($passi)) { header("Location: /view/ricetta.php?id=" . $id_ricetta); exit(); }

// Timer personalizzato: utente loggato → suoi colori, altrimenti default
$id_utente = $_SESSION['user_id'] ?? null;
if ($id_utente) {
    $timer = getTimerByUtente($conn, $id_utente);
} else {
    $timer = ['coloreSfondo' => '#FFFFFF', 'coloreLancetta' => '#1A1008', 'coloreNumeri' => '#1A1008'];
}

// Progresso salvato (solo per utenti loggati)
$progresso = null;
$passo_ripresa = null;
if ($id_utente) {
    $sql_prog = "SELECT re.id, re.idUltimoPasso, re.isCompletata
                 FROM ricetteEseguite re
                 WHERE re.idUtente = ? AND re.idRicetta = ?
                 LIMIT 1";
    $stmt_prog = $conn->prepare($sql_prog);
    $stmt_prog->bind_param("ii", $id_utente, $id_ricetta);
    $stmt_prog->execute();
    $progresso = $stmt_prog->get_result()->fetch_assoc();
    $stmt_prog->close();

    // Trova l'indice del passo di ripresa (0-based)
    if ($progresso && !$progresso['isCompletata'] && $progresso['idUltimoPasso']) {
        foreach ($passi as $idx => $p) {
            if ($p['id'] === (int)$progresso['idUltimoPasso']) {
                $passo_ripresa = $idx; // potremmo riprendere dal successivo
                break;
            }
        }
    }
}

// Autore e media
$sql_autore = "SELECT username FROM utenti WHERE id = ?";
$stmt_a = $conn->prepare($sql_autore);
$stmt_a->bind_param("i", $ricetta['idCreatore']);
$stmt_a->execute();
$autore = $stmt_a->get_result()->fetch_assoc();
$stmt_a->close();

$sql_cop = "SELECT urlMedia FROM mediaRicette WHERE idRicetta = ? AND isCopertina = 1 LIMIT 1";
$stmt_cop = $conn->prepare($sql_cop);
$stmt_cop->bind_param("i", $id_ricetta);
$stmt_cop->execute();
$copertina = $stmt_cop->get_result()->fetch_assoc();
$stmt_cop->close();

$durata_totale = array_sum(array_column($passi, 'durata'));
$num_passi     = count($passi);

// Colori timer per CSS inline
$c_sfondo   = htmlspecialchars($timer['coloreSfondo']);
$c_lancetta = htmlspecialchars($timer['coloreLancetta']);
$c_numeri   = htmlspecialchars($timer['coloreNumeri']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cucina: <?php echo htmlspecialchars($ricetta['titolo']); ?> — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        /* ══════════════════════════════════════════════════
           VARIABILI TIMER (colori personalizzati utente)
        ══════════════════════════════════════════════════ */
        :root {
            --timer-sfondo:   <?php echo $c_sfondo; ?>;
            --timer-lancetta: <?php echo $c_lancetta; ?>;
            --timer-numeri:   <?php echo $c_numeri; ?>;
        }

        /* ══════════════════════════════════════════════════
           LAYOUT GENERALE
        ══════════════════════════════════════════════════ */
        body { padding-top: 0; background: #0E0A06; color: #F5F0E8; }

        .cook-shell {
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
        }

        /* ══════════════════════════════════════════════════
           TOPBAR
        ══════════════════════════════════════════════════ */
        .cook-topbar {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 24px;
            background: rgba(14,10,6,.95);
            border-bottom: 1px solid rgba(255,255,255,.08);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 200;
        }
        .cook-back {
            display: flex; align-items: center; justify-content: center;
            width: 36px; height: 36px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,.15);
            background: transparent;
            color: #F5F0E8;
            text-decoration: none;
            flex-shrink: 0;
            transition: background .15s, border-color .15s;
        }
        .cook-back:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.3); }

        .cook-topbar-info { flex: 1; min-width: 0; }
        .cook-recipe-title {
            font-family: 'Playfair Display', serif;
            font-size: .92rem;
            font-weight: 600;
            color: #F5F0E8;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            line-height: 1.2;
            margin-bottom: 2px;
        }
        .cook-recipe-author { font-size: .72rem; color: rgba(245,240,232,.5); }

        /* Progress bar globale */
        .cook-progress-wrap { flex-shrink: 0; display: flex; align-items: center; gap: 10px; }
        .cook-step-count { font-size: .72rem; font-weight: 700; color: #C4622D; white-space: nowrap; }
        .cook-progress-track {
            width: 100px; height: 4px;
            background: rgba(255,255,255,.12);
            border-radius: 2px;
            overflow: hidden;
        }
        .cook-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #C4622D, #E87A40);
            border-radius: 2px;
            transition: width .5s ease;
        }

        /* ══════════════════════════════════════════════════
           RIPRESA BANNER
        ══════════════════════════════════════════════════ */
        .resume-banner {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 24px;
            background: linear-gradient(90deg, rgba(196,98,45,.25), rgba(196,98,45,.08));
            border-bottom: 1px solid rgba(196,98,45,.3);
        }
        .resume-banner-icon {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(196,98,45,.3);
            display: flex; align-items: center; justify-content: center;
            color: #E87A40; flex-shrink: 0;
        }
        .resume-banner-text { flex: 1; }
        .resume-banner-text strong { display: block; font-size: .85rem; font-weight: 700; color: #F5F0E8; margin-bottom: 2px; }
        .resume-banner-text span   { font-size: .75rem; color: rgba(245,240,232,.6); }
        .btn-resume {
            padding: 8px 18px;
            background: #C4622D; color: #FFF;
            border: none; border-radius: 20px;
            font-family: 'DM Sans', sans-serif;
            font-size: .78rem; font-weight: 700;
            cursor: pointer; flex-shrink: 0;
            transition: background .15s;
        }
        .btn-resume:hover { background: #A8511F; }

        /* ══════════════════════════════════════════════════
           STAGE PRINCIPALE
        ══════════════════════════════════════════════════ */
        .cook-stage {
            display: flex;
            overflow: hidden;
            position: relative;
        }

        /* ── SIDEBAR PASSI (desktop) ── */
        .cook-sidebar {
            width: 260px;
            flex-shrink: 0;
            border-right: 1px solid rgba(255,255,255,.07);
            background: rgba(20,14,8,.6);
            overflow-y: auto;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .sidebar-label {
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(245,240,232,.3);
            padding: 0 20px 8px;
        }
        .sidebar-step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 0;
            transition: background .15s;
            position: relative;
        }
        .sidebar-step:hover { background: rgba(255,255,255,.05); }
        .sidebar-step.active { background: rgba(196,98,45,.15); }
        .sidebar-step.done .ss-num { background: #27ae60; color: #FFF; border-color: #27ae60; }
        .sidebar-step.active .ss-num { background: #C4622D; color: #FFF; border-color: #C4622D; }

        .ss-num {
            width: 24px; height: 24px; border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,.2);
            color: rgba(245,240,232,.4);
            font-size: .7rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-top: 1px;
            transition: background .2s, border-color .2s, color .2s;
        }
        .ss-info { flex: 1; min-width: 0; }
        .ss-title {
            font-size: .8rem; font-weight: 600;
            color: rgba(245,240,232,.55);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            margin-bottom: 2px;
            transition: color .2s;
        }
        .sidebar-step.active .ss-title { color: #F5F0E8; }
        .sidebar-step.done  .ss-title { color: rgba(245,240,232,.4); }
        .ss-dur { font-size: .67rem; color: rgba(245,240,232,.3); }

        /* ── MAIN PASSO ── */
        .cook-main {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .passo-view {
            flex: 1;
            padding: 40px 48px 40px;
            max-width: 760px;
            margin: 0 auto;
            width: 100%;
            animation: stepFadeIn .4s ease both;
        }
        @keyframes stepFadeIn {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .passo-num-label {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 18px;
        }
        .passo-num-pill {
            background: #C4622D;
            color: #FFF;
            font-size: .68rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 2px;
            padding: 4px 14px; border-radius: 20px;
        }
        .passo-num-of {
            font-size: .72rem; color: rgba(245,240,232,.4);
        }

        .passo-titolo {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.5rem, 3.5vw, 2.2rem);
            font-weight: 700;
            color: #F5F0E8;
            line-height: 1.2;
            margin-bottom: 18px;
        }

        /* Chips */
        .passo-chips {
            display: flex; flex-wrap: wrap; gap: 8px;
            margin-bottom: 28px;
        }
        .pchip {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: .72rem; font-weight: 700;
            padding: 5px 13px; border-radius: 20px;
        }
        .pchip--time { background: rgba(196,98,45,.2); color: #E87A40; border: 1px solid rgba(196,98,45,.3); }
        .pchip--cook { background: rgba(255,193,7,.12); color: #FFC107; border: 1px solid rgba(255,193,7,.2); }
        .pchip--rest { background: rgba(41,128,185,.15); color: #74B9E8; border: 1px solid rgba(41,128,185,.2); }
        .pchip--tech { background: rgba(255,255,255,.06); color: rgba(245,240,232,.5); border: 1px solid rgba(255,255,255,.1); }

        /* Descrizione */
        .passo-desc {
            font-size: 1.02rem;
            color: rgba(245,240,232,.85);
            line-height: 1.8;
            margin-bottom: 32px;
        }

        /* Ingredienti */
        .passo-ingredients-section { margin-bottom: 28px; }
        .section-micro-label {
            font-size: .65rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 2px;
            color: rgba(245,240,232,.3);
            margin-bottom: 10px;
        }
        .ing-grid { display: flex; flex-wrap: wrap; gap: 8px; }
        .ing-pill {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: .82rem; color: #F5F0E8; font-weight: 500;
        }
        .ing-pill em { font-style: normal; color: #C4622D; font-weight: 700; margin-left: 4px; }

        /* Foto passo */
        .passo-media-section { margin-bottom: 28px; }
        .passo-media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 10px;
        }
        .passo-media-thumb {
            aspect-ratio: 1; border-radius: 12px; overflow: hidden;
            cursor: pointer; border: 1px solid rgba(255,255,255,.1);
            transition: transform .2s, box-shadow .2s;
        }
        .passo-media-thumb:hover { transform: scale(1.04); box-shadow: 0 8px 24px rgba(0,0,0,.4); }
        .passo-media-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* ══════════════════════════════════════════════════
           TIMER — usa variabili CSS dell'utente
        ══════════════════════════════════════════════════ */
        .timer-section-cook {
            margin-bottom: 32px;
            padding: 22px 24px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 20px;
        }
        .timer-section-label {
            font-size: .65rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 2px;
            color: rgba(245,240,232,.3);
            margin-bottom: 16px;
        }

        /* Chefly Timer (override per dark bg) */
        .chefly-timer {}
        .ct-inner { display: flex; align-items: center; gap: 24px; }
        .ct-clock-face {
            position: relative;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: var(--timer-sfondo);
            box-shadow: 0 0 0 1px rgba(255,255,255,.1), 0 8px 32px rgba(0,0,0,.4);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            transition: box-shadow .3s;
        }
        .ct-clock-svg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .ct-hand {
            position: absolute;
            bottom: 50%; left: 50%;
            transform-origin: bottom center;
            border-radius: 4px;
        }
        .ct-hand-minute { width: 3.5px; height: 30px; margin-left: -1.75px; background: var(--timer-lancetta); }
        .ct-hand-second { width: 2px;   height: 38px; margin-left: -1px;    background: var(--timer-lancetta); }
        .ct-clock-center {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            width: 9px; height: 9px; border-radius: 50%;
            background: var(--timer-lancetta); z-index: 10;
        }
        .ct-display {
            position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%);
            font-size: .62rem; font-weight: 800; letter-spacing: 1.5px;
            color: var(--timer-numeri); white-space: nowrap;
        }
        .ct-controls { flex: 1; }
        .ct-label { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 600; color: #F5F0E8; margin-bottom: 3px; }
        .ct-duration { font-size: .75rem; color: rgba(245,240,232,.4); margin-bottom: 12px; }
        .ct-btns { display: flex; gap: 8px; margin-bottom: 8px; }
        .ct-btn {
            width: 38px; height: 38px; border-radius: 50%;
            border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: transform .1s, opacity .2s;
        }
        .ct-btn:active { transform: scale(.9); }
        .ct-btn-play  { background: #C4622D; color: #FFF; }
        .ct-btn-pause { background: rgba(255,255,255,.15); color: #F5F0E8; }
        .ct-btn-reset { background: rgba(255,255,255,.08); color: rgba(245,240,232,.5); }
        .ct-btn svg { width: 15px; height: 15px; }
        .ct-state-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .ct-state-idle    { color: rgba(245,240,232,.3); }
        .ct-state-running { color: #E87A40; }
        .ct-state-paused  { color: rgba(245,240,232,.4); }
        .ct-state-done    { color: #27ae60; }

        /* ══════════════════════════════════════════════════
           SCHERMATA COMPLETAMENTO
        ══════════════════════════════════════════════════ */
        .complete-view {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            text-align: center;
            animation: stepFadeIn .5s ease both;
        }
        .complete-icon {
            width: 90px; height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #27ae60, #1e8449);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 28px;
            box-shadow: 0 0 0 16px rgba(39,174,96,.08), 0 0 0 32px rgba(39,174,96,.04);
        }
        .complete-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem; font-weight: 700;
            color: #F5F0E8; margin-bottom: 12px;
        }
        .complete-sub { font-size: .95rem; color: rgba(245,240,232,.55); line-height: 1.7; max-width: 380px; margin-bottom: 36px; }
        .complete-actions { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
        .btn-comp-primary {
            padding: 13px 30px;
            background: #C4622D; color: #FFF;
            border: none; border-radius: 30px;
            font-family: 'DM Sans', sans-serif;
            font-size: .92rem; font-weight: 700;
            cursor: pointer; text-decoration: none;
            transition: background .2s, transform .1s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-comp-primary:hover { background: #A8511F; transform: translateY(-1px); }
        .btn-comp-ghost {
            padding: 13px 24px;
            background: rgba(255,255,255,.07); color: rgba(245,240,232,.7);
            border: 1px solid rgba(255,255,255,.12); border-radius: 30px;
            font-family: 'DM Sans', sans-serif;
            font-size: .88rem; font-weight: 600;
            cursor: pointer; text-decoration: none;
            transition: background .2s;
            display: inline-flex; align-items: center; gap: 7px;
        }
        .btn-comp-ghost:hover { background: rgba(255,255,255,.12); }

        /* ══════════════════════════════════════════════════
           BOTTOMBAR NAVIGAZIONE
        ══════════════════════════════════════════════════ */
        .cook-bottombar {
            padding: 16px 24px;
            background: rgba(14,10,6,.97);
            border-top: 1px solid rgba(255,255,255,.07);
            display: flex;
            align-items: center;
            gap: 12px;
            backdrop-filter: blur(12px);
        }
        .btn-nav {
            display: flex; align-items: center; gap: 8px;
            padding: 12px 22px;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: .88rem; font-weight: 600;
            cursor: pointer; border: none;
            transition: background .15s, transform .1s, opacity .2s;
        }
        .btn-nav:active { transform: scale(.97); }
        .btn-nav:disabled { opacity: .3; cursor: not-allowed; transform: none !important; }
        .btn-nav-prev {
            background: rgba(255,255,255,.08);
            color: rgba(245,240,232,.7);
        }
        .btn-nav-prev:hover:not(:disabled) { background: rgba(255,255,255,.14); }
        .btn-nav-next {
            background: #C4622D; color: #FFF;
            flex: 1; justify-content: center;
        }
        .btn-nav-next:hover:not(:disabled) { background: #A8511F; }
        .btn-nav-finish {
            background: linear-gradient(135deg, #27ae60, #1e8449); color: #FFF;
            flex: 1; justify-content: center;
        }
        .btn-nav-finish:hover { opacity: .9; }

        /* Saving indicator */
        .save-indicator {
            font-size: .68rem; color: rgba(245,240,232,.3);
            display: flex; align-items: center; gap: 5px;
            white-space: nowrap; flex-shrink: 0;
            transition: color .3s;
        }
        .save-indicator.saving { color: #E87A40; }
        .save-indicator.saved  { color: #27ae60; }
        .save-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: currentColor;
        }

        /* ══════════════════════════════════════════════════
           LIGHTBOX
        ══════════════════════════════════════════════════ */
        .lightbox {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.92);
            z-index: 9000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity .25s ease;
        }
        .lightbox.open { opacity: 1; pointer-events: all; }
        .lightbox img { max-width: 90vw; max-height: 85vh; border-radius: 12px; object-fit: contain; }
        .lightbox-close {
            position: absolute; top: 20px; right: 20px;
            width: 44px; height: 44px; border-radius: 50%;
            background: rgba(255,255,255,.12); border: none;
            color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; transition: background .2s;
        }
        .lightbox-close:hover { background: rgba(255,255,255,.25); }

        /* ══════════════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════════════ */
        @media (max-width: 780px) {
            .cook-sidebar { display: none; }
            .passo-view { padding: 28px 20px 24px; }
        }
        @media (max-width: 480px) {
            .cook-topbar { padding: 12px 16px; }
            .cook-progress-track { width: 64px; }
            .btn-nav { padding: 12px 16px; }
            .cook-bottombar { padding: 12px 16px; gap: 8px; }
        }
    </style>
</head>
<body>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
    <button class="lightbox-close">✕</button>
    <img src="" alt="" id="lightboxImg">
</div>

<div class="cook-shell">

    <!-- ── TOPBAR ── -->
    <header class="cook-topbar">
        <a href="/view/ricetta.php?id=<?php echo $id_ricetta; ?>" class="cook-back" title="Torna alla ricetta">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        </a>
        <div class="cook-topbar-info">
            <div class="cook-recipe-title"><?php echo htmlspecialchars($ricetta['titolo']); ?></div>
            <div class="cook-recipe-author">di @<?php echo htmlspecialchars($autore['username']); ?> · <?php echo $durata_totale; ?> min totali</div>
        </div>
        <div class="cook-progress-wrap">
            <span class="cook-step-count" id="stepCount">1 / <?php echo $num_passi; ?></span>
            <div class="cook-progress-track">
                <div class="cook-progress-fill" id="progressFill" style="width:<?php echo round(100 / $num_passi); ?>%"></div>
            </div>
        </div>
    </header>

    <?php if ($passo_ripresa !== null && $passo_ripresa > 0): ?>
        <!-- ── BANNER RIPRESA ── -->
        <div class="resume-banner" id="resumeBanner">
            <div class="resume-banner-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polygon points="10 8 16 12 10 16 10 8"/><circle cx="12" cy="12" r="10"/></svg>
            </div>
            <div class="resume-banner-text">
                <strong>Hai già iniziato questa ricetta</strong>
                <span>Vuoi riprendere dal passo <?php echo $passo_ripresa + 1; ?>: «<?php echo htmlspecialchars($passi[$passo_ripresa]['titolo']); ?>»?</span>
            </div>
            <button class="btn-resume" onclick="jumpToStep(<?php echo $passo_ripresa; ?>); dismissResumeBanner();">Riprendi</button>
        </div>
    <?php endif; ?>

    <!-- ── STAGE ── -->
    <div class="cook-stage">

        <!-- Sidebar passi (desktop) -->
        <aside class="cook-sidebar">
            <div class="sidebar-label">Passi ricetta</div>
            <?php foreach ($passi as $idx => $p): ?>
                <div class="sidebar-step <?php echo $idx === 0 ? 'active' : ''; ?>"
                     id="ss-<?php echo $idx; ?>"
                     onclick="jumpToStep(<?php echo $idx; ?>)">
                    <div class="ss-num" id="ssn-<?php echo $idx; ?>"><?php echo $idx + 1; ?></div>
                    <div class="ss-info">
                        <div class="ss-title"><?php echo htmlspecialchars($p['titolo']); ?></div>
                        <div class="ss-dur"><?php echo $p['durata']; ?> min</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </aside>

        <!-- Main: area contenuto passo / completamento -->
        <main class="cook-main" id="cookMain">
            <!-- I passi vengono mostrati/nascosti via JS -->
            <?php foreach ($passi as $idx => $p): ?>
                <div class="passo-view" id="pv-<?php echo $idx; ?>" style="<?php echo $idx !== 0 ? 'display:none;' : ''; ?>">

                    <div class="passo-num-label">
                        <span class="passo-num-pill">Passo <?php echo $idx + 1; ?></span>
                        <span class="passo-num-of">di <?php echo $num_passi; ?></span>
                    </div>

                    <h1 class="passo-titolo"><?php echo htmlspecialchars($p['titolo']); ?></h1>

                    <!-- Chips tempi/tecnica -->
                    <div class="passo-chips">
                        <?php if (!empty($p['durata'])): ?>
                            <span class="pchip pchip--time">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <?php echo $p['durata']; ?> min
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($p['tempoCottura'])): ?>
                            <span class="pchip pchip--cook">🔥 Cottura <?php echo $p['tempoCottura']; ?> min</span>
                        <?php endif; ?>
                        <?php if (!empty($p['tempoRiposo'])): ?>
                            <span class="pchip pchip--rest">💤 Riposo <?php echo $p['tempoRiposo']; ?> min</span>
                        <?php endif; ?>
                        <?php if (!empty($p['nome_cottura'])): ?>
                            <span class="pchip pchip--tech"><?php echo htmlspecialchars($p['nome_cottura']); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Timer -->
                    <?php if (!empty($p['durata'])): ?>
                        <div class="timer-section-cook">
                            <div class="timer-section-label">Timer</div>
                            <div data-chefly-timer="<?php echo $p['id']; ?>"
                                 data-durata="<?php echo $p['durata']; ?>"
                                 data-label="<?php echo htmlspecialchars($p['titolo']); ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Descrizione -->
                    <p class="passo-desc"><?php echo nl2br(htmlspecialchars($p['descrizione'])); ?></p>

                    <!-- Ingredienti -->
                    <?php if (!empty($p['ingredienti'])): ?>
                        <div class="passo-ingredients-section">
                            <div class="section-micro-label">Ingredienti per questo passo</div>
                            <div class="ing-grid">
                                <?php foreach ($p['ingredienti'] as $ing): ?>
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

                    <!-- Foto passo -->
                    <?php if (!empty($p['media'])): ?>
                        <div class="passo-media-section">
                            <div class="section-micro-label">Foto del passo</div>
                            <div class="passo-media-grid">
                                <?php foreach ($p['media'] as $m): ?>
                                    <div class="passo-media-thumb" onclick="openLightbox('../<?php echo htmlspecialchars($m['urlMedia']); ?>')">
                                        <img src="../<?php echo htmlspecialchars($m['urlMedia']); ?>" alt="" loading="lazy">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

            <!-- Schermata completamento (nascosta finché non finisce) -->
            <div class="complete-view" id="completeView" style="display:none;">
                <div class="complete-icon">
                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#FFF" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h1 class="complete-title">Buon appetito! 🍽️</h1>
                <p class="complete-sub">
                    Hai completato <strong style="color:#F5F0E8;"><?php echo htmlspecialchars($ricetta['titolo']); ?></strong> in <?php echo $durata_totale; ?> min stimati.<br>
                    Speriamo che sia venuta benissimo!
                </p>
                <div class="complete-actions">
                    <a href="/view/ricetta.php?id=<?php echo $id_ricetta; ?>" class="btn-comp-primary">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="10 15 15 20 20 15"/><path d="M4 4h7a4 4 0 0 1 4 4v12"/></svg>
                        Torna alla ricetta
                    </a>
                    <a href="/index.php" class="btn-comp-ghost">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        Home
                    </a>
                </div>
            </div>

        </main>
    </div>

    <!-- ── BOTTOMBAR ── -->
    <nav class="cook-bottombar" id="cookBottombar">
        <button class="btn-nav btn-nav-prev" id="btnPrev" onclick="prevStep()" disabled>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Indietro
        </button>

        <div class="save-indicator" id="saveIndicator">
            <?php if ($id_utente): ?>
                <div class="save-dot"></div>
                <span id="saveLabel">—</span>
            <?php else: ?>
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Accedi per salvare il progresso
            <?php endif; ?>
        </div>

        <button class="btn-nav btn-nav-next" id="btnNext" onclick="nextStep()">
            Avanti
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </button>
    </nav>

</div><!-- .cook-shell -->

<!-- TIMER JS -->
<script src="../js/timer.js"></script>

<script>
    // ══════════════════════════════════════════════════════
    // DATI PHP → JS
    // ══════════════════════════════════════════════════════
    const PASSI = <?php echo json_encode(array_map(fn($p) => [
        'id'    => $p['id'],
        'titolo'=> $p['titolo'],
        'durata'=> $p['durata'],
    ], $passi)); ?>;
    const NUM_PASSI    = <?php echo $num_passi; ?>;
    const ID_RICETTA   = <?php echo $id_ricetta; ?>;
    const IS_LOGGED_IN = <?php echo $id_utente ? 'true' : 'false'; ?>;

    let currentStep = 0;

    // ══════════════════════════════════════════════════════
    // NAVIGAZIONE
    // ══════════════════════════════════════════════════════
    function showStep(idx, direction) {
        if (idx < 0 || idx >= NUM_PASSI) return;

        // Nascondi passo corrente
        const old = document.getElementById('pv-' + currentStep);
        if (old) old.style.display = 'none';
        document.getElementById('completeView').style.display = 'none';

        // Sidebar: aggiorna stato
        const oldSS = document.getElementById('ss-' + currentStep);
        if (oldSS) {
            oldSS.classList.remove('active');
            if (idx > currentStep || (direction === 'fwd' && idx >= currentStep)) {
                oldSS.classList.add('done');
            }
        }

        currentStep = idx;

        // Mostra nuovo passo
        const el = document.getElementById('pv-' + currentStep);
        if (el) {
            el.style.display = 'flex';
            el.style.flexDirection = 'column';
            // Forza re-animate
            el.style.animation = 'none';
            el.offsetHeight; // reflow
            el.style.animation = '';
        }

        // Sidebar: attiva
        const newSS = document.getElementById('ss-' + currentStep);
        if (newSS) {
            newSS.classList.add('active');
            newSS.classList.remove('done');
            newSS.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }

        // Progress bar
        const pct = Math.round(((currentStep + 1) / NUM_PASSI) * 100);
        document.getElementById('progressFill').style.width = pct + '%';
        document.getElementById('stepCount').textContent = (currentStep + 1) + ' / ' + NUM_PASSI;

        // Bottoni
        document.getElementById('btnPrev').disabled = (currentStep === 0);
        const btnNext = document.getElementById('btnNext');
        if (currentStep === NUM_PASSI - 1) {
            btnNext.className = 'btn-nav btn-nav-finish';
            btnNext.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg> Completa ricetta';
        } else {
            btnNext.className = 'btn-nav btn-nav-next';
            btnNext.innerHTML = 'Avanti <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
        }

        // Scrolla in cima al contenuto
        const main = document.getElementById('cookMain');
        if (main) main.scrollTop = 0;

        // Salva progresso
        salvaProgresso(false);
    }

    function nextStep() {
        if (currentStep === NUM_PASSI - 1) {
            finisciRicetta();
        } else {
            // Segna vecchio come done nella sidebar
            const oldSS = document.getElementById('ss-' + currentStep);
            if (oldSS) oldSS.classList.add('done');
            showStep(currentStep + 1, 'fwd');
        }
    }

    function prevStep() {
        if (currentStep > 0) {
            // Rimuovi done dal corrente nella sidebar
            const curSS = document.getElementById('ss-' + currentStep);
            if (curSS) curSS.classList.remove('done');
            showStep(currentStep - 1, 'bwd');
        }
    }

    function jumpToStep(idx) {
        // Marca tutti i precedenti come done
        for (let i = 0; i < idx; i++) {
            const ss = document.getElementById('ss-' + i);
            if (ss) { ss.classList.remove('active'); ss.classList.add('done'); }
            const sn = document.getElementById('ssn-' + i);
            if (sn) sn.style.color = '';
        }
        showStep(idx);
    }

    // ══════════════════════════════════════════════════════
    // COMPLETAMENTO
    // ══════════════════════════════════════════════════════
    function finisciRicetta() {
        // Nascondi tutto
        for (let i = 0; i < NUM_PASSI; i++) {
            const el = document.getElementById('pv-' + i);
            if (el) el.style.display = 'none';
            const ss = document.getElementById('ss-' + i);
            if (ss) { ss.classList.remove('active'); ss.classList.add('done'); }
        }
        document.getElementById('completeView').style.display = 'flex';
        document.getElementById('completeView').style.flexDirection = 'column';

        // Nascondi bottombar
        document.getElementById('cookBottombar').style.display = 'none';

        // Progress: 100%
        document.getElementById('progressFill').style.width = '100%';
        document.getElementById('stepCount').textContent = '✓ Completata';

        // Salva come completata
        salvaProgresso(true);
    }

    // ══════════════════════════════════════════════════════
    // SALVATAGGIO PROGRESSO (AJAX)
    // ══════════════════════════════════════════════════════
    let saveTimeout = null;
    function salvaProgresso(completata) {
        if (!IS_LOGGED_IN) return;

        const ind = document.getElementById('saveIndicator');
        const lbl = document.getElementById('saveLabel');
        if (ind) ind.className = 'save-indicator saving';
        if (lbl) lbl.textContent = 'Salvataggio…';

        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            const passo = PASSI[currentStep];
            const body = new URLSearchParams({
                id_ricetta:    ID_RICETTA,
                id_passo:      passo.id,
                is_completata: completata ? '1' : '0',
            });

            fetch('../controller/aggiornaPassoEseguito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString(),
            }).then(r => r.json()).then(data => {
                if (ind) ind.className = 'save-indicator ' + (data.success ? 'saved' : '');
                if (lbl) lbl.textContent = data.success ? 'Salvato' : 'Errore';
                setTimeout(() => {
                    if (ind) ind.className = 'save-indicator';
                    if (lbl) lbl.textContent = '—';
                }, 2500);
            }).catch(() => {
                if (ind) ind.className = 'save-indicator';
                if (lbl) lbl.textContent = 'Offline';
            });
        }, 600);
    }

    // ══════════════════════════════════════════════════════
    // BANNER RIPRESA
    // ══════════════════════════════════════════════════════
    function dismissResumeBanner() {
        const b = document.getElementById('resumeBanner');
        if (b) b.style.display = 'none';
    }

    // ══════════════════════════════════════════════════════
    // LIGHTBOX
    // ══════════════════════════════════════════════════════
    function openLightbox(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.getElementById('lightbox').addEventListener('click', closeLightbox);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') nextStep();
        if (e.key === 'ArrowLeft')  prevStep();
    });

    // ══════════════════════════════════════════════════════
    // INIZIALIZZAZIONE
    // ══════════════════════════════════════════════════════
    // (CheflyTimer.init() viene già chiamato da timer.js su DOMContentLoaded)
</script>

</body>
</html>