<?php
// view/ilMioRistorante.php
require_once '../controller/ilMioRistoranteController.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Mio Ristorante — Chefly</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/timer.css">
    <script src="../js/timer.js" defer></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #FAF8F5;
            color: #1A1008;
            min-height: 100vh;
        }

        .ristorante-wrap {
            max-width: 760px;
            margin: 0 auto;
            padding: 48px 20px 120px;
        }

        /* ── FLASH ─────────────────────────────────────────────── */
        .flash {
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 28px;
            letter-spacing: 0.3px;
        }
        .flash--success { background:#F0FDF4; color:#166534; border:1px solid #BBF7D0; }
        .flash--error   { background:#FFF1F0; color:#991B1B; border:1px solid #FECACA; }
        .flash--deleted { background:#FFFBEB; color:#92400E; border:1px solid #FDE68A; }

        /* ── PROFILO ───────────────────────────────────────────── */
        .profile-section {
            display: flex;
            align-items: center;
            gap: 48px;
            padding-bottom: 36px;
            border-bottom: 1px solid #EDE8E0;
            margin-bottom: 40px;
        }

        .profile-avatar-wrap {
            flex-shrink: 0;
            position: relative;
            width: 110px;
            height: 110px;
            cursor: pointer;
        }

        .profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #EDE8E0;
            display: block;
            transition: filter 0.25s ease;
        }

        .avatar-overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(26,16,8,0.52);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.25s ease;
            pointer-events: none;
        }
        .profile-avatar-wrap:hover .avatar-overlay  { opacity: 1; }
        .profile-avatar-wrap:hover .profile-avatar  { filter: brightness(0.7); }
        .avatar-overlay svg  { color: #fff; }
        .avatar-overlay span { font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#fff; line-height:1; }

        .profile-info { flex: 1; }

        .profile-stats { display:flex; gap:36px; margin-bottom:16px; }
        .stat-item     { text-align:center; }
        .stat-num      { display:block; font-family:'Playfair Display',serif; font-size:1.5rem; font-weight:700; color:#1A1008; line-height:1; }
        .stat-label    { display:block; font-size:.72rem; text-transform:uppercase; letter-spacing:1.2px; color:#8B7355; margin-top:4px; }

        .profile-username      { font-size:1rem; font-weight:600; color:#1A1008; margin-bottom:6px; }
        .profile-username span { color:#C4622D; }
        .profile-bio           { font-size:.9rem; color:#6B5C48; line-height:1.55; max-width:380px; }

        /* ── MODAL FOTO ─────────────────────────────────────────── */
        .modal-backdrop {
            position:fixed; inset:0;
            background:rgba(26,16,8,.55);
            backdrop-filter:blur(4px);
            z-index:2000;
            display:flex; align-items:center; justify-content:center;
            opacity:0; pointer-events:none;
            transition:opacity .25s ease;
        }
        .modal-backdrop.open { opacity:1; pointer-events:all; }

        .modal-box {
            background:#fff;
            border-radius:20px;
            padding:36px 32px 28px;
            width:100%; max-width:380px;
            box-shadow:0 24px 60px rgba(26,16,8,.18);
            transform:translateY(16px) scale(.97);
            transition:transform .25s ease, opacity .25s ease;
            opacity:0; position:relative;
        }
        .modal-backdrop.open .modal-box { transform:translateY(0) scale(1); opacity:1; }

        .modal-close {
            position:absolute; top:14px; right:14px;
            width:32px; height:32px;
            border:none; background:#F5F2EC; border-radius:50%;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            color:#6B5C48; transition:background .15s, color .15s;
        }
        .modal-close:hover { background:#EDE8E0; color:#1A1008; }

        .modal-title    { font-family:'Playfair Display',serif; font-size:1.2rem; font-weight:600; color:#1A1008; margin-bottom:6px; }
        .modal-subtitle { font-size:.82rem; color:#8B7355; margin-bottom:24px; line-height:1.5; }

        .preview-wrap   { display:flex; justify-content:center; margin-bottom:20px; }
        .preview-circle { width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #EDE8E0; transition:border-color .2s; }

        .dropzone {
            border:2px dashed #D6CFC4; border-radius:12px;
            padding:20px 16px; text-align:center; cursor:pointer;
            transition:border-color .2s, background .2s;
            margin-bottom:16px; position:relative;
        }
        .dropzone:hover, .dropzone.dragover { border-color:#C4622D; background:#FFF3ED; }
        .dropzone input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
        .dropzone-icon { width:36px; height:36px; background:#F5F2EC; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; color:#C4622D; }
        .dropzone p    { font-size:.82rem; color:#6B5C48; margin-bottom:4px; font-weight:500; }
        .dropzone small{ font-size:.72rem; color:#A89880; }

        .file-name-label { font-size:.78rem; color:#C4622D; font-weight:600; text-align:center; margin-bottom:16px; min-height:18px; display:block; }

        .btn-modal-submit {
            width:100%; padding:13px;
            background:#1A1008; color:#FFF;
            border:none; border-radius:10px;
            font-family:'DM Sans',sans-serif; font-size:.88rem; font-weight:600;
            cursor:pointer; transition:background .2s, transform .1s; letter-spacing:.3px;
        }
        .btn-modal-submit:hover    { background:#3a2518; }
        .btn-modal-submit:active   { transform:scale(.98); }
        .btn-modal-submit:disabled { background:#D6CFC4; cursor:not-allowed; transform:none; }

        /* ── SEZIONE GENERICA ──────────────────────────────────── */
        .section-header {
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom:24px;
        }
        .section-title {
            font-family:'Playfair Display',serif;
            font-size:1.4rem; font-weight:600; color:#1A1008;
        }

        /* ── SEZIONE TIMER ─────────────────────────────────────── */
        .timer-section {
            background: #FFFFFF;
            border: 1px solid #EDE8E0;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 48px;
        }

        .timer-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            padding-bottom: 20px;
            border-bottom: 1px solid #F5F2EC;
        }

        .timer-section-icon {
            width: 40px; height: 40px;
            background: #FFF3ED;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #C4622D;
            flex-shrink: 0;
        }

        .timer-section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.15rem;
            font-weight: 600;
            color: #1A1008;
        }

        .timer-section-sub {
            font-size: 0.78rem;
            color: #8B7355;
            margin-top: 2px;
        }

        .timer-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            align-items: start;
        }

        /* Preview orologio live */
        .clock-preview-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .clock-preview-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #8B7355;
        }

        /* L'orologio SVG preview */
        .clock-preview {
            position: relative;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(26,16,8,0.12);
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        .clock-preview svg {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
        }

        /* lancette preview */
        .preview-hand {
            position: absolute;
            bottom: 50%;
            left: 50%;
            transform-origin: bottom center;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .preview-hand-min {
            width: 4px;
            height: 52px;
            margin-left: -2px;
            transform: translateX(-50%) rotate(120deg);
        }

        .preview-hand-sec {
            width: 2.5px;
            height: 62px;
            margin-left: -1.25px;
            transform: translateX(-50%) rotate(210deg);
        }

        .preview-center-dot {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 12px; height: 12px;
            border-radius: 50%;
            z-index: 10;
            transition: background 0.3s ease;
        }

        .preview-time-text {
            position: absolute;
            bottom: 28px;
            left: 50%;
            transform: translateX(-50%);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            transition: color 0.3s ease;
            white-space: nowrap;
        }

        /* Numeri ore nel preview SVG */
        .clock-preview .hour-num {
            transition: fill 0.3s ease;
        }

        /* Form colori */
        .color-form { display: flex; flex-direction: column; gap: 18px; }

        .color-field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6B5C48;
            margin-bottom: 8px;
        }

        .color-input-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Swatch cliccabile che apre il color picker nativo */
        .color-swatch {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            border: 2px solid #EDE8E0;
            cursor: pointer;
            flex-shrink: 0;
            transition: border-color 0.2s, transform 0.15s;
            position: relative;
            overflow: hidden;
        }

        .color-swatch:hover {
            border-color: #C4622D;
            transform: scale(1.05);
        }

        .color-swatch input[type="color"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
            padding: 0;
            border: none;
        }

        .color-hex-input {
            flex: 1;
            padding: 10px 14px;
            border: 1px solid #EDE8E0;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            color: #1A1008;
            background: #FAF8F5;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .color-hex-input:focus {
            outline: none;
            border-color: #C4622D;
            box-shadow: 0 0 0 3px rgba(196,98,45,0.12);
            background: #fff;
        }

        .color-hex-input.invalid {
            border-color: #DC2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
        }

        /* Preset palette */
        .preset-section {
            margin-top: 4px;
        }

        .preset-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #A89880;
            margin-bottom: 8px;
            display: block;
        }

        .preset-grid {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .preset-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            transition: transform 0.15s, border-color 0.15s;
            flex-shrink: 0;
        }

        .preset-btn:hover  { transform: scale(1.15); }
        .preset-btn.active { border-color: #C4622D; transform: scale(1.1); }

        /* Bottone salva timer */
        .btn-save-timer {
            width: 100%;
            padding: 13px;
            background: #C4622D;
            color: #FFF;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            margin-top: 8px;
            letter-spacing: 0.3px;
        }
        .btn-save-timer:hover  { background: #A8511F; }
        .btn-save-timer:active { transform: scale(0.98); }

        /* Divider tra preset e salva */
        .form-divider {
            height: 1px;
            background: #F0ECE6;
            margin: 4px 0 4px;
        }

        /* ── RICETTE ────────────────────────────────────────────── */
        .empty-state {
            text-align:center; padding:60px 20px;
            background:#FFFFFF; border:1.5px dashed #D6CFC4; border-radius:16px;
        }
        .empty-state p { color:#8B7355; font-size:.95rem; margin-bottom:20px; }

        .recipe-list { display:flex; flex-direction:column; gap:16px; }

        .recipe-card {
            background:#FFFFFF; border:1px solid #EDE8E0;
            border-radius:16px; overflow:hidden; transition:box-shadow .2s ease;
        }
        .recipe-card:hover { box-shadow:0 4px 20px rgba(26,16,8,.07); }

        .recipe-header { display:flex; align-items:center; gap:16px; padding:16px 20px; }

        .recipe-cover {
            width:64px; height:64px; border-radius:10px;
            flex-shrink:0; background:#F5F2EC;
            display:flex; align-items:center; justify-content:center; color:#C4C0B8;
        }
        .recipe-cover img { width:64px; height:64px; border-radius:10px; object-fit:cover; }

        .recipe-main { flex:1; min-width:0; }

        .recipe-title-text {
            font-family:'Playfair Display',serif; font-size:1.05rem; font-weight:600; color:#1A1008;
            white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:4px;
        }

        .recipe-meta { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

        .badge { font-size:.68rem; font-weight:600; letter-spacing:.8px; text-transform:uppercase; padding:2px 9px; border-radius:20px; }
        .badge--difficolta { background:#F5F2EC; color:#6B5C48; }
        .badge--steps      { background:#FFF3ED; color:#C4622D; }

        .recipe-actions { display:flex; align-items:center; gap:6px; flex-shrink:0; }

        .btn-icon {
            width:36px; height:36px; border-radius:8px; border:none;
            background:transparent; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            color:#8B7355; transition:background .15s, color .15s; text-decoration:none;
        }
        .btn-icon:hover          { background:#F5F2EC; color:#1A1008; }
        .btn-icon--danger:hover  { background:#FFF1F0; color:#DC2626; }

        .btn-toggle {
            width:36px; height:36px; border-radius:8px;
            border:1px solid #EDE8E0; background:#FAF8F5;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            color:#8B7355; transition:all .15s;
        }
        .btn-toggle:hover      { background:#F0EBE3; }
        .btn-toggle svg        { transition:transform .25s ease; }
        .btn-toggle.open svg   { transform:rotate(180deg); }

        .passi-section    { max-height:0; overflow:hidden; transition:max-height .35s ease; }
        .passi-section.open { max-height:4000px; }

        .passi-inner { border-top:1px solid #EDE8E0; background:#FDFCFA; padding:0 20px; }

        .passo-row { display:flex; align-items:flex-start; gap:12px; padding:13px 0; border-bottom:1px solid #F0ECE6; }
        .passo-row:last-child { border-bottom:none; }

        .passo-num {
            width:26px; height:26px; border-radius:50%;
            background:#C4622D; color:#FFF;
            font-size:.72rem; font-weight:700;
            display:flex; align-items:center; justify-content:center;
            flex-shrink:0; margin-top:4px;
        }

        .passo-info  { flex:1; min-width:0; }
        .passo-title { font-size:.88rem; font-weight:500; color:#1A1008; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .passo-durata{ font-size:.75rem; color:#8B7355; margin-top:2px; margin-bottom:4px; }

        .passo-actions { display:flex; gap:4px; flex-shrink:0; margin-top:4px; }

        .btn-icon--sm {
            width:30px; height:30px; border-radius:7px; border:none;
            background:transparent; cursor:pointer;
            display:flex; align-items:center; justify-content:center;
            color:#8B7355; transition:background .15s, color .15s;
        }
        .btn-icon--sm:hover       { background:#F5F2EC; color:#1A1008; }
        .btn-icon--sm.danger:hover{ background:#FFF1F0; color:#DC2626; }

        .passi-footer { padding:12px 0 14px; display:flex; justify-content:center; }

        .btn-add-passo {
            display:inline-flex; align-items:center; gap:6px;
            font-size:.8rem; font-weight:600; color:#C4622D;
            text-decoration:none; padding:7px 16px; border-radius:20px;
            border:1.5px dashed #E8B99A; background:transparent; transition:background .15s;
        }
        .btn-add-passo:hover { background:#FFF3ED; }

        /* ── FAB ────────────────────────────────────────────────── */
        .fab {
            position:fixed; bottom:36px; right:36px;
            width:60px; height:60px; border-radius:50%;
            background:#C4622D; color:#FFF; border:none;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            box-shadow:0 6px 24px rgba(196,98,45,.38);
            text-decoration:none; transition:transform .2s, box-shadow .2s; z-index:100;
        }
        .fab:hover { transform:scale(1.08) translateY(-2px); box-shadow:0 10px 30px rgba(196,98,45,.45); }

        .fab-tooltip {
            position:fixed; bottom:44px; right:106px;
            background:#1A1008; color:#FFF;
            font-size:.78rem; font-weight:500;
            padding:6px 14px; border-radius:20px;
            white-space:nowrap; opacity:0; pointer-events:none; transition:opacity .2s;
        }
        .fab:hover + .fab-tooltip { opacity:1; }

        .delete-form { margin:0; }

        @media (max-width:640px) {
            .profile-section  { flex-direction:column; gap:24px; text-align:center; }
            .profile-stats    { justify-content:center; }
            .profile-bio      { margin:0 auto; }
            .timer-layout     { grid-template-columns:1fr; }
            .clock-preview-wrap { order: -1; }
            .fab              { bottom:24px; right:20px; }
            .fab-tooltip      { display:none; }
            .modal-box        { margin:0 16px; }
        }
    </style>
</head>
<body>

<?php include '../include/header.php'; ?>

<!-- ═══ MODAL CAMBIO FOTO ══════════════════════════════════════ -->
<div class="modal-backdrop" id="modalFoto" role="dialog" aria-modal="true" aria-labelledby="modalFotoTitle">
    <div class="modal-box">
        <button class="modal-close" onclick="chiudiModal()" aria-label="Chiudi">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
        <h2 class="modal-title" id="modalFotoTitle">Cambia foto profilo</h2>
        <p class="modal-subtitle">Carica una nuova immagine (JPG, PNG, WEBP · max 5 MB)</p>
        <div class="preview-wrap">
            <img id="previewImg"
                 src="<?php echo htmlspecialchars($profilo['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>"
                 alt="Anteprima"
                 class="preview-circle">
        </div>
        <form action="../controller/aggiornafotoprofiloController.php" method="POST" enctype="multipart/form-data" id="formFoto">
            <div class="dropzone" id="dropzone">
                <input type="file" name="foto_profilo" id="inputFoto"
                       accept="image/jpeg,image/png,image/webp,image/gif"
                       onchange="onFileSelect(this)">
                <div class="dropzone-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </div>
                <p>Clicca o trascina qui la foto</p>
                <small>JPG, PNG, WEBP, GIF — max 5 MB</small>
            </div>
            <span class="file-name-label" id="fileNameLabel"></span>
            <button type="submit" class="btn-modal-submit" id="btnConferma" disabled>Salva foto profilo</button>
        </form>
    </div>
</div>

<!-- ═══ PAGINA ════════════════════════════════════════════════= -->
<div class="ristorante-wrap">

    <!-- Flash -->
    <?php if (isset($_GET['success'])): ?>
        <div class="flash flash--success">
            <?php
            $msgs = [
                    'ricetta_creata'     => '✓ Ricetta aggiunta con successo!',
                    'ricetta_modificata' => '✓ Ricetta aggiornata.',
                    'passo_aggiunto'     => '✓ Passo aggiunto correttamente.',
                    'passo_modificato'   => '✓ Passo aggiornato.',
                    'ricetta_completata' => '✓ Ricetta completata!',
                    'foto_aggiornata'    => '✓ Foto profilo aggiornata!',
                    'timer_aggiornato'   => '✓ Timer personalizzato salvato!',
            ];
            echo htmlspecialchars($msgs[$_GET['success']] ?? 'Operazione completata.');
            ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="flash flash--deleted">
            <?php echo $_GET['deleted'] === 'passo' ? '⚑ Passo eliminato.' : '⚑ Ricetta eliminata.'; ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="flash flash--error">
            <?php
            $errori = [
                    'upload_fallito'     => 'Errore durante il caricamento.',
                    'tipo_non_valido'    => 'Formato file non supportato.',
                    'file_troppo_grande' => 'Il file supera i 5 MB.',
                    'salvataggio_fallito'=> 'Impossibile salvare il file.',
                    'db_fallito'         => 'Errore nel salvataggio sul database.',
                    'colore_non_valido'  => 'Uno o più colori inseriti non sono validi.',
                    'timer_fallito'      => 'Errore nel salvataggio del timer.',
            ];
            echo htmlspecialchars($errori[$_GET['error']] ?? 'Si è verificato un errore.');
            ?>
        </div>
    <?php endif; ?>

    <!-- ── PROFILO ─────────────────────────────────────────────── -->
    <section class="profile-section">
        <div class="profile-avatar-wrap" onclick="apriModal()" title="Cambia foto profilo">
            <img src="<?php echo htmlspecialchars($profilo['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>"
                 alt="Foto profilo" class="profile-avatar" id="avatarPrincipale">
            <div class="avatar-overlay">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
                <span>Cambia</span>
            </div>
        </div>
        <div class="profile-info">
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-num"><?php echo $statistiche['num_ricette']; ?></span>
                    <span class="stat-label">Post</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num"><?php echo $statistiche['num_follower']; ?></span>
                    <span class="stat-label">Follower</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num"><?php echo $statistiche['num_seguiti']; ?></span>
                    <span class="stat-label">Seguiti</span>
                </div>
            </div>
            <p class="profile-username"><span>@</span><?php echo htmlspecialchars($profilo['userName']); ?></p>
            <?php if (!empty($profilo['biografia'])): ?>
                <p class="profile-bio"><?php echo nl2br(htmlspecialchars($profilo['biografia'])); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════
         SEZIONE TIMER PERSONALIZZABILE
    ═══════════════════════════════════════════════════════════ -->
    <section id="sezione-timer" style="margin-bottom:48px;">

        <div class="section-header">
            <h2 class="section-title">Il mio timer</h2>
        </div>

        <div class="timer-section">

            <div class="timer-section-header">
                <div class="timer-section-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                <div>
                    <div class="timer-section-title">Personalizza il tuo orologio</div>
                    <div class="timer-section-sub">Le modifiche si riflettono sui timer durante la cottura</div>
                </div>
            </div>

            <div class="timer-layout">

                <!-- Preview live orologio -->
                <div class="clock-preview-wrap">
                    <span class="clock-preview-label">Anteprima</span>

                    <div class="clock-preview" id="clockPreview"
                         style="background: <?php echo htmlspecialchars($timer['coloreSfondo']); ?>;">

                        <!-- SVG segni ore -->
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" id="clockSvg">
                            <?php
                            // Segni dei minuti e ore
                            for ($i = 0; $i < 60; $i++):
                                $angle = $i * 6;
                                $isMaj = ($i % 5 === 0);
                                $r     = 88;
                                $len   = $isMaj ? 14 : 7;
                                $sw    = $isMaj ? 3 : 1.5;
                                $rad   = $angle * M_PI / 180;
                                $x1    = 100 + $r * sin($rad);
                                $y1    = 100 - $r * cos($rad);
                                $x2    = 100 + ($r - $len) * sin($rad);
                                $y2    = 100 - ($r - $len) * cos($rad);
                                $color = $timer['coloreNumeri'];
                                ?>
                                <line x1="<?php echo round($x1,2); ?>" y1="<?php echo round($y1,2); ?>"
                                      x2="<?php echo round($x2,2); ?>" y2="<?php echo round($y2,2); ?>"
                                      stroke="<?php echo htmlspecialchars($color); ?>"
                                      stroke-width="<?php echo $sw; ?>"
                                      stroke-linecap="round"
                                      class="clock-mark" data-major="<?php echo $isMaj ? '1':'0'; ?>"/>
                            <?php endfor; ?>

                            <?php
                            // Numeri ore
                            $numeri = [12,1,2,3,4,5,6,7,8,9,10,11];
                            foreach ($numeri as $idx => $n):
                                $rad = $idx * 30 * M_PI / 180;
                                $rx  = 100 + 68 * sin($rad);
                                $ry  = 100 - 68 * cos($rad);
                                ?>
                                <text x="<?php echo round($rx,2); ?>"
                                      y="<?php echo round($ry,2); ?>"
                                      text-anchor="middle"
                                      dominant-baseline="central"
                                      font-size="14"
                                      font-family="DM Sans, sans-serif"
                                      font-weight="600"
                                      fill="<?php echo htmlspecialchars($timer['coloreNumeri']); ?>"
                                      class="hour-num">
                                    <?php echo $n; ?>
                                </text>
                            <?php endforeach; ?>
                        </svg>

                        <!-- Lancette preview -->
                        <div class="preview-hand preview-hand-min" id="previewHandMin"
                             style="background:<?php echo htmlspecialchars($timer['coloreLancetta']); ?>;"></div>
                        <div class="preview-hand preview-hand-sec" id="previewHandSec"
                             style="background:<?php echo htmlspecialchars($timer['coloreLancetta']); ?>;"></div>
                        <div class="preview-center-dot" id="previewDot"
                             style="background:<?php echo htmlspecialchars($timer['coloreLancetta']); ?>;"></div>
                        <div class="preview-time-text" id="previewTime"
                             style="color:<?php echo htmlspecialchars($timer['coloreNumeri']); ?>;">10:10</div>
                    </div>

                    <span style="font-size:0.7rem; color:#A89880; text-align:center; line-height:1.4;">
                        Le lancette si muovono<br>durante la cottura
                    </span>
                </div>

                <!-- Form colori -->
                <div>
                    <form action="../controller/aggiornaTimerController.php" method="POST" id="formTimer">

                        <div class="color-form">

                            <!-- Colore sfondo -->
                            <div class="color-field">
                                <label>Sfondo orologio</label>
                                <div class="color-input-row">
                                    <div class="color-swatch" id="swatchSfondo"
                                         style="background:<?php echo htmlspecialchars($timer['coloreSfondo']); ?>;">
                                        <input type="color"
                                               id="pickerSfondo"
                                               value="<?php echo htmlspecialchars($timer['coloreSfondo']); ?>"
                                               oninput="syncColor('sfondo', this.value)"
                                               title="Scegli colore sfondo">
                                    </div>
                                    <input type="text"
                                           class="color-hex-input"
                                           id="hexSfondo"
                                           name="coloreSfondo"
                                           value="<?php echo htmlspecialchars($timer['coloreSfondo']); ?>"
                                           maxlength="7"
                                           placeholder="#FFFFFF"
                                           oninput="onHexInput('sfondo', this)">
                                </div>
                            </div>

                            <!-- Colore lancette -->
                            <div class="color-field">
                                <label>Lancette &amp; perno</label>
                                <div class="color-input-row">
                                    <div class="color-swatch" id="swatchLancetta"
                                         style="background:<?php echo htmlspecialchars($timer['coloreLancetta']); ?>;">
                                        <input type="color"
                                               id="pickerLancetta"
                                               value="<?php echo htmlspecialchars($timer['coloreLancetta']); ?>"
                                               oninput="syncColor('lancetta', this.value)"
                                               title="Scegli colore lancette">
                                    </div>
                                    <input type="text"
                                           class="color-hex-input"
                                           id="hexLancetta"
                                           name="coloreLancetta"
                                           value="<?php echo htmlspecialchars($timer['coloreLancetta']); ?>"
                                           maxlength="7"
                                           placeholder="#000000"
                                           oninput="onHexInput('lancetta', this)">
                                </div>
                            </div>

                            <!-- Colore numeri / testo -->
                            <div class="color-field">
                                <label>Numeri &amp; segni</label>
                                <div class="color-input-row">
                                    <div class="color-swatch" id="swatchNumeri"
                                         style="background:<?php echo htmlspecialchars($timer['coloreNumeri']); ?>;">
                                        <input type="color"
                                               id="pickerNumeri"
                                               value="<?php echo htmlspecialchars($timer['coloreNumeri']); ?>"
                                               oninput="syncColor('numeri', this.value)"
                                               title="Scegli colore numeri">
                                    </div>
                                    <input type="text"
                                           class="color-hex-input"
                                           id="hexNumeri"
                                           name="coloreNumeri"
                                           value="<?php echo htmlspecialchars($timer['coloreNumeri']); ?>"
                                           maxlength="7"
                                           placeholder="#000000"
                                           oninput="onHexInput('numeri', this)">
                                </div>
                            </div>

                            <!-- Preset temi -->
                            <div class="preset-section">
                                <span class="preset-label">Temi rapidi</span>
                                <div class="preset-grid">
                                    <!-- Classico bianco/nero -->
                                    <button type="button" class="preset-btn"
                                            style="background:#FFFFFF; border:2px solid #ddd;"
                                            title="Classico"
                                            onclick="applicaPreset('#FFFFFF','#000000','#000000')"></button>
                                    <!-- Notte -->
                                    <button type="button" class="preset-btn"
                                            style="background:#1A1008;"
                                            title="Notte"
                                            onclick="applicaPreset('#1A1008','#F5E6D3','#F5E6D3')"></button>
                                    <!-- Chefly arancio -->
                                    <button type="button" class="preset-btn"
                                            style="background:#C4622D;"
                                            title="Chefly"
                                            onclick="applicaPreset('#C4622D','#FFFFFF','#FFFFFF')"></button>
                                    <!-- Bosco -->
                                    <button type="button" class="preset-btn"
                                            style="background:#2D4A22;"
                                            title="Bosco"
                                            onclick="applicaPreset('#2D4A22','#A8D5A2','#A8D5A2')"></button>
                                    <!-- Cielo -->
                                    <button type="button" class="preset-btn"
                                            style="background:#E8F4FD;"
                                            title="Cielo"
                                            onclick="applicaPreset('#E8F4FD','#2980B9','#2980B9')"></button>
                                    <!-- Crema -->
                                    <button type="button" class="preset-btn"
                                            style="background:#F5F0E8;"
                                            title="Crema"
                                            onclick="applicaPreset('#F5F0E8','#8B6914','#8B6914')"></button>
                                    <!-- Lavanda -->
                                    <button type="button" class="preset-btn"
                                            style="background:#6C63FF;"
                                            title="Lavanda"
                                            onclick="applicaPreset('#6C63FF','#FFFFFF','#FFFFFF')"></button>
                                    <!-- Rosa -->
                                    <button type="button" class="preset-btn"
                                            style="background:#FDE8F0;"
                                            title="Rosa"
                                            onclick="applicaPreset('#FDE8F0','#C2185B','#C2185B')"></button>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <button type="submit" class="btn-save-timer">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:6px;">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                                    <polyline points="17 21 17 13 7 13 7 21"/>
                                    <polyline points="7 3 7 8 15 8"/>
                                </svg>
                                Salva timer
                            </button>

                        </div>
                    </form>
                </div>

            </div><!-- /.timer-layout -->
        </div><!-- /.timer-section -->
    </section>

    <!-- ── LISTA RICETTE ──────────────────────────────────────── -->
    <div class="section-header">
        <h2 class="section-title">Le mie ricette</h2>
    </div>

    <?php if (empty($ricette)): ?>
        <div class="empty-state">
            <p>Non hai ancora aggiunto nessuna ricetta.</p>
            <a href="aggiungiRicetta.php" class="btn-add-passo" style="display:inline-flex; border-style:solid; background:#C4622D; color:#FFF; border-color:#C4622D;">
                + Aggiungi la prima ricetta
            </a>
        </div>
    <?php else: ?>
        <div class="recipe-list">
            <?php foreach ($ricette as $ricetta): ?>
                <article class="recipe-card">
                    <div class="recipe-header">
                        <div class="recipe-cover">
                            <?php if (!empty($ricetta['url_copertina'])): ?>
                                <img src="../<?php echo htmlspecialchars($ricetta['url_copertina']); ?>"
                                     alt="Copertina <?php echo htmlspecialchars($ricetta['titolo']); ?>">
                            <?php else: ?>
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                            <?php endif; ?>
                        </div>
                        <div class="recipe-main">
                            <div class="recipe-title-text"><?php echo htmlspecialchars($ricetta['titolo']); ?></div>
                            <div class="recipe-meta">
                                <span class="badge badge--difficolta"><?php echo htmlspecialchars(ucfirst($ricetta['difficolta'])); ?></span>
                                <span class="badge badge--steps"><?php echo count($ricetta['passi']); ?> passo<?php echo count($ricetta['passi']) !== 1 ? 'i' : ''; ?></span>
                            </div>
                        </div>
                        <div class="recipe-actions">
                            <a href="modificaRicetta.php?id_ricetta=<?php echo $ricetta['id']; ?>" class="btn-icon" title="Modifica">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <form class="delete-form" action="../controller/cancellaRicettaController.php" method="POST"
                                  onsubmit="return confirm('Eliminare «<?php echo addslashes($ricetta['titolo']); ?>»?');">
                                <input type="hidden" name="id_ricetta" value="<?php echo $ricetta['id']; ?>">
                                <button type="submit" class="btn-icon btn-icon--danger" title="Elimina">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </form>
                            <button class="btn-toggle" onclick="togglePassi(this,'passi-<?php echo $ricetta['id']; ?>')" title="Mostra/nascondi passi">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="passi-section" id="passi-<?php echo $ricetta['id']; ?>">
                        <div class="passi-inner">
                            <?php if (empty($ricetta['passi'])): ?>
                                <div style="padding:18px 0; text-align:center; color:#8B7355; font-size:.85rem;">Nessun passo ancora.</div>
                            <?php else: ?>
                                <?php foreach ($ricetta['passi'] as $idx => $passo): ?>
                                    <div class="passo-row">
                                        <div class="passo-num"><?php echo $idx + 1; ?></div>
                                        <div class="passo-info">
                                            <div class="passo-title"><?php echo htmlspecialchars($passo['titolo']); ?></div>
                                            <div class="passo-durata">
                                                <?php if ($passo['durata']): ?>⏱ <?php echo $passo['durata']; ?> min<?php endif; ?>
                                                <?php if ($passo['nome_cottura']): ?> · <?php echo htmlspecialchars($passo['nome_cottura']); ?><?php endif; ?>
                                            </div>
                                            <?php if (!empty($passo['durata']) && $passo['durata'] > 0): ?>
                                                <div data-chefly-timer="<?= (int)$passo['id'] ?>"
                                                     data-durata="<?= (int)$passo['durata'] ?>"
                                                     data-label="<?= htmlspecialchars($passo['titolo']) ?>"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="passo-actions">
                                            <a href="modificaPasso.php?id_passo=<?php echo $passo['id']; ?>&id_ricetta=<?php echo $ricetta['id']; ?>" class="btn-icon--sm" title="Modifica passo">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                            <form class="delete-form" action="../controller/cancellaPassoController.php" method="POST"
                                                  onsubmit="return confirm('Eliminare il passo «<?php echo addslashes($passo['titolo']); ?>»?');">
                                                <input type="hidden" name="id_passo"   value="<?php echo $passo['id']; ?>">
                                                <input type="hidden" name="id_ricetta" value="<?php echo $ricetta['id']; ?>">
                                                <button type="submit" class="btn-icon--sm danger" title="Elimina passo">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <div class="passi-footer">
                                <a href="aggiungiPasso.php?id_ricetta=<?php echo $ricetta['id']; ?>" class="btn-add-passo">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Aggiungi passo
                                </a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div><!-- /.ristorante-wrap -->

<a href="aggiungiRicetta.php" class="fab" title="Aggiungi ricetta">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
</a>
<span class="fab-tooltip">Nuova ricetta</span>

<?php include '../include/footer.php'; ?>

<script>
    /* ══════════════════════════════════════════════════════════
       MODAL FOTO PROFILO
    ══════════════════════════════════════════════════════════ */
    const modal      = document.getElementById('modalFoto');
    const btnConferma = document.getElementById('btnConferma');
    const inputFoto  = document.getElementById('inputFoto');
    const previewImg = document.getElementById('previewImg');
    const fileLabel  = document.getElementById('fileNameLabel');
    const dropzone   = document.getElementById('dropzone');

    function apriModal() { modal.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function chiudiModal(){ modal.classList.remove('open'); document.body.style.overflow = ''; }

    modal.addEventListener('click', e => { if (e.target === modal) chiudiModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') chiudiModal(); });

    function onFileSelect(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) { alert('Il file è troppo grande. Massimo 5 MB.'); input.value = ''; return; }
        fileLabel.textContent = file.name;
        const reader = new FileReader();
        reader.onload = e => { previewImg.src = e.target.result; previewImg.style.borderColor = '#C4622D'; };
        reader.readAsDataURL(file);
        btnConferma.disabled = false;
    }

    dropzone.addEventListener('dragover',  e => { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', ()  => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault(); dropzone.classList.remove('dragover');
        if (e.dataTransfer.files.length) { inputFoto.files = e.dataTransfer.files; onFileSelect(inputFoto); }
    });

    /* ══════════════════════════════════════════════════════════
       TIMER — ANTEPRIMA LIVE
    ══════════════════════════════════════════════════════════ */
    const clockPreview  = document.getElementById('clockPreview');
    const handMin       = document.getElementById('previewHandMin');
    const handSec       = document.getElementById('previewHandSec');
    const centerDot     = document.getElementById('previewDot');
    const previewTime   = document.getElementById('previewTime');
    const clockSvg      = document.getElementById('clockSvg');

    // Riferimenti ai campi
    const hexSfondo    = document.getElementById('hexSfondo');
    const hexLancetta  = document.getElementById('hexLancetta');
    const hexNumeri    = document.getElementById('hexNumeri');
    const pickerSfondo   = document.getElementById('pickerSfondo');
    const pickerLancetta = document.getElementById('pickerLancetta');
    const pickerNumeri   = document.getElementById('pickerNumeri');
    const swatchSfondo   = document.getElementById('swatchSfondo');
    const swatchLancetta = document.getElementById('swatchLancetta');
    const swatchNumeri   = document.getElementById('swatchNumeri');

    function isValidHex(v) { return /^#[0-9A-Fa-f]{6}$/.test(v); }

    /**
     * Aggiorna la preview e il swatch per uno dei tre canali:
     * 'sfondo' | 'lancetta' | 'numeri'
     */
    function syncColor(canale, valore) {
        if (!isValidHex(valore)) return;
        const v = valore.toUpperCase();

        if (canale === 'sfondo') {
            clockPreview.style.background = v;
            swatchSfondo.style.background = v;
            pickerSfondo.value            = v;
            hexSfondo.value               = v;
            hexSfondo.classList.remove('invalid');
        } else if (canale === 'lancetta') {
            handMin.style.background      = v;
            handSec.style.background      = v;
            centerDot.style.background    = v;
            swatchLancetta.style.background= v;
            pickerLancetta.value          = v;
            hexLancetta.value             = v;
            hexLancetta.classList.remove('invalid');
        } else if (canale === 'numeri') {
            previewTime.style.color       = v;
            swatchNumeri.style.background = v;
            pickerNumeri.value            = v;
            hexNumeri.value               = v;
            hexNumeri.classList.remove('invalid');
            // Aggiorna tutti i testi e segni SVG
            clockSvg.querySelectorAll('.hour-num').forEach(el => el.setAttribute('fill', v));
            clockSvg.querySelectorAll('.clock-mark').forEach(el => el.setAttribute('stroke', v));
        }
    }

    /**
     * Gestisce l'input manuale nel campo hex — accetta anche senza # e formati parziali.
     */
    function onHexInput(canale, input) {
        let v = input.value.trim();
        if (!v.startsWith('#')) v = '#' + v;
        if (isValidHex(v)) {
            input.classList.remove('invalid');
            syncColor(canale, v);
        } else {
            input.classList.add('invalid');
        }
    }

    /**
     * Applica un tema preset completo ai tre colori.
     */
    function applicaPreset(sfondo, lancetta, numeri) {
        syncColor('sfondo',   sfondo);
        syncColor('lancetta', lancetta);
        syncColor('numeri',   numeri);
    }

    /* Anima le lancette del preview (decorativo) */
    (function animatePreview() {
        const now  = new Date();
        const sec  = now.getSeconds();
        const min  = now.getMinutes() + sec / 60;
        const degMin = min * 6;       // 360/60
        const degSec = sec * 6;

        handMin.style.transform = `translateX(-50%) rotate(${degMin}deg)`;
        handSec.style.transform = `translateX(-50%) rotate(${degSec}deg)`;

        const hh = String(now.getHours()).padStart(2,'0');
        const mm = String(now.getMinutes()).padStart(2,'0');
        previewTime.textContent = hh + ':' + mm;

        requestAnimationFrame(animatePreview);
    })();

    /* ── Accordion passi ─────────────────────────────────────── */
    function togglePassi(btn, id) {
        const section = document.getElementById(id);
        const isOpen  = section.classList.toggle('open');
        btn.classList.toggle('open', isOpen);
        btn.title = isOpen ? 'Nascondi passi' : 'Mostra passi';
    }
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>

</body>
</html>