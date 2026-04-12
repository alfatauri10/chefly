<?php
/* view/ilMioRistorante.php */
require_once '../controller/ilMioRistoranteController.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il Mio Ristorante — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        /* ── PROFILO ── */
        .profile-section {
            display: flex;
            align-items: center;
            gap: 48px;
            padding-bottom: 36px;
            border-bottom: 1px solid var(--border);
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
            border: 3px solid var(--border);
            display: block;
            transition: filter .25s ease;
        }
        .avatar-overlay {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(26,16,8,.52);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            opacity: 0;
            transition: opacity .25s ease;
            pointer-events: none;
        }
        .profile-avatar-wrap:hover .avatar-overlay { opacity: 1; }
        .profile-avatar-wrap:hover .profile-avatar  { filter: brightness(.7); }
        .avatar-overlay svg   { color: #fff; }
        .avatar-overlay span  { font-size:.6rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#fff; }

        .profile-stats { display:flex; gap:36px; margin-bottom:16px; }
        .stat-item     { text-align:center; }
        .stat-num      { display:block; font-family:var(--font-serif); font-size:1.5rem; font-weight:700; color:var(--brown); line-height:1; }
        .stat-label    { display:block; font-size:.72rem; text-transform:uppercase; letter-spacing:1.2px; color:var(--muted); margin-top:4px; }
        .profile-username      { font-size:1rem; font-weight:600; color:var(--brown); margin-bottom:6px; }
        .profile-username span { color:var(--caramel); }
        .profile-bio           { font-size:.88rem; color:var(--muted); line-height:1.6; max-width:380px; }

        /* ── MODAL FOTO ── */
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
            background:var(--white);
            border-radius:var(--radius-xl);
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
            border:none; background:var(--sand); border-radius:50%;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            color:var(--muted); transition:background .15s, color .15s;
        }
        .modal-close:hover { background:var(--border); color:var(--brown); }
        .modal-title    { font-family:var(--font-serif); font-size:1.2rem; font-weight:600; color:var(--brown); margin-bottom:6px; }
        .modal-subtitle { font-size:.82rem; color:var(--muted); margin-bottom:24px; line-height:1.5; }
        .preview-wrap   { display:flex; justify-content:center; margin-bottom:20px; }
        .preview-circle { width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid var(--border); transition:border-color .2s; }
        .dropzone {
            border:2px dashed var(--border); border-radius:var(--radius-md);
            padding:20px 16px; text-align:center; cursor:pointer;
            transition:border-color .2s, background .2s;
            margin-bottom:14px; position:relative;
        }
        .dropzone:hover,.dropzone.dragover { border-color:var(--caramel); background:#FFF3ED; }
        .dropzone input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
        .dropzone-icon { width:36px; height:36px; background:var(--sand); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; color:var(--caramel); }
        .dropzone p    { font-size:.82rem; color:var(--muted); margin-bottom:4px; font-weight:500; }
        .dropzone small{ font-size:.72rem; color:var(--muted-light); }
        .file-name-label { font-size:.78rem; color:var(--caramel); font-weight:600; text-align:center; margin-bottom:14px; min-height:18px; display:block; }
        .btn-modal-submit {
            width:100%; padding:13px;
            background:var(--brown); color:#FFF;
            border:none; border-radius:10px;
            font-family:var(--font-sans); font-size:.88rem; font-weight:600;
            cursor:pointer; transition:background .2s, transform .1s;
        }
        .btn-modal-submit:hover    { background:#3a2518; }
        .btn-modal-submit:active   { transform:scale(.98); }
        .btn-modal-submit:disabled { background:var(--border); cursor:not-allowed; transform:none; }

        /* ── TIMER ── */
        .timer-section {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 28px;
            margin-bottom: 48px;
        }
        .timer-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border-light);
        }
        .timer-section-icon {
            width: 40px; height: 40px;
            background: #FFF3ED;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: var(--caramel);
        }
        .timer-section-title { font-family:var(--font-serif); font-size:1.1rem; font-weight:600; color:var(--brown); }
        .timer-section-sub   { font-size:.78rem; color:var(--muted); margin-top:2px; }

        .timer-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            align-items: start;
        }
        .clock-preview-wrap { display:flex; flex-direction:column; align-items:center; gap:16px; }
        .clock-preview-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); }
        .clock-preview {
            position:relative; width:160px; height:160px;
            border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 8px 32px rgba(26,16,8,.12);
            transition:background .3s ease, box-shadow .3s ease;
        }
        .clock-preview svg { position:absolute; top:0; left:0; width:100%; height:100%; }
        .preview-hand {
            position:absolute; bottom:50%; left:50%;
            transform-origin:bottom center;
            border-radius:4px;
            transition:background .3s ease;
        }
        .preview-hand-min { width:4px; height:52px; margin-left:-2px; transform:translateX(-50%) rotate(120deg); }
        .preview-hand-sec { width:2.5px; height:62px; margin-left:-1.25px; transform:translateX(-50%) rotate(210deg); }
        .preview-center-dot { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:12px; height:12px; border-radius:50%; z-index:10; transition:background .3s ease; }
        .preview-time-text  { position:absolute; bottom:28px; left:50%; transform:translateX(-50%); font-family:var(--font-sans); font-size:.75rem; font-weight:700; letter-spacing:1px; transition:color .3s ease; white-space:nowrap; }

        .color-form { display:flex; flex-direction:column; gap:18px; }
        .color-field label { display:block; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#6B5C48; margin-bottom:8px; }
        .color-input-row { display:flex; align-items:center; gap:10px; }
        .color-swatch { width:42px; height:42px; border-radius:10px; border:2px solid var(--border); cursor:pointer; flex-shrink:0; transition:border-color .2s, transform .15s; position:relative; overflow:hidden; }
        .color-swatch:hover { border-color:var(--caramel); transform:scale(1.05); }
        .color-swatch input[type="color"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; padding:0; border:none; }
        .color-hex-input { flex:1; padding:10px 14px; border:1px solid var(--border); border-radius:8px; font-family:var(--font-sans); font-size:.88rem; color:var(--brown); background:var(--cream); text-transform:uppercase; letter-spacing:1px; transition:border-color .2s, box-shadow .2s; }
        .color-hex-input:focus { outline:none; border-color:var(--caramel); box-shadow:0 0 0 3px rgba(196,98,45,.12); background:var(--white); }
        .color-hex-input.invalid { border-color:#DC2626; box-shadow:0 0 0 3px rgba(220,38,38,.1); }
        .preset-label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--muted-light); margin-bottom:8px; display:block; }
        .preset-grid  { display:flex; gap:8px; flex-wrap:wrap; }
        .preset-btn   { width:28px; height:28px; border-radius:50%; border:2px solid transparent; cursor:pointer; transition:transform .15s, border-color .15s; flex-shrink:0; }
        .preset-btn:hover { transform:scale(1.15); }
        .btn-save-timer {
            width:100%; padding:13px;
            background:var(--caramel); color:#FFF;
            border:none; border-radius:10px;
            font-family:var(--font-sans); font-size:.88rem; font-weight:600;
            cursor:pointer; transition:background .2s, transform .1s;
            margin-top:4px;
        }
        .btn-save-timer:hover  { background:var(--caramel-dark); }
        .btn-save-timer:active { transform:scale(.98); }
        .form-divider { height:1px; background:var(--border-light); margin:4px 0; }

        /* ── RICETTE ── */
        .recipe-list   { display:flex; flex-direction:column; gap:14px; }
        .recipe-item   {
            background:var(--white); border:1px solid var(--border);
            border-radius:var(--radius-lg); overflow:hidden;
            transition:box-shadow .2s ease;
        }
        .recipe-item:hover { box-shadow:0 4px 20px rgba(26,16,8,.07); }
        .recipe-item-header { display:flex; align-items:center; gap:14px; padding:14px 18px; }
        .recipe-cover-thumb {
            width:60px; height:60px; border-radius:10px;
            flex-shrink:0; background:var(--sand);
            display:flex; align-items:center; justify-content:center; color:#C4C0B8;
        }
        .recipe-cover-thumb img { width:60px; height:60px; border-radius:10px; object-fit:cover; }
        .recipe-item-main { flex:1; min-width:0; }
        .recipe-item-title { font-family:var(--font-serif); font-size:1rem; font-weight:600; color:var(--brown); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:5px; }
        .recipe-item-meta  { display:flex; gap:7px; align-items:center; flex-wrap:wrap; }
        .recipe-item-actions { display:flex; align-items:center; gap:5px; flex-shrink:0; }

        .btn-toggle {
            width:34px; height:34px; border-radius:8px;
            border:1px solid var(--border); background:var(--cream);
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            color:var(--muted); transition:all .15s;
        }
        .btn-toggle:hover { background:var(--sand); }
        .btn-toggle svg { transition:transform .25s ease; }
        .btn-toggle.open svg { transform:rotate(180deg); }

        .passi-section { max-height:0; overflow:hidden; transition:max-height .35s ease; }
        .passi-section.open { max-height:4000px; }
        .passi-inner { border-top:1px solid var(--border); background:var(--cream); padding:0 18px; }

        .passo-row { display:flex; align-items:flex-start; gap:12px; padding:12px 0; border-bottom:1px solid var(--border-light); }
        .passo-row:last-child { border-bottom:none; }
        .passo-num { width:24px; height:24px; border-radius:50%; background:var(--caramel); color:#FFF; font-size:.7rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:3px; }
        .passo-detail { flex:1; min-width:0; }
        .passo-title  { font-size:.88rem; font-weight:500; color:var(--brown); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .passo-desc   { font-size:.78rem; color:var(--muted); line-height:1.5; margin:4px 0 6px; }
        .passo-chips  { display:flex; flex-wrap:wrap; gap:4px; margin-bottom:6px; }
        .pchip { display:inline-flex; align-items:center; gap:3px; font-size:.65rem; font-weight:600; padding:2px 8px; border-radius:20px; }
        .pchip--time { background:#FFF3ED; color:var(--caramel); }
        .pchip--fire { background:#FFFBEB; color:#92400E; }
        .pchip--rest { background:#F0F9FF; color:#0369A1; }
        .pchip--tech { background:var(--sand); color:var(--muted); }
        .passo-ings  { display:flex; flex-wrap:wrap; gap:4px; margin-bottom:6px; }
        .ing-tag { font-size:.7rem; background:var(--cream); border:1px solid var(--border); border-radius:6px; padding:2px 8px; color:var(--brown); font-weight:500; }
        .ing-tag em { font-style:normal; color:var(--muted); margin-left:3px; }
        .passo-thumbs { display:flex; gap:6px; margin-top:6px; }
        .passo-thumb { width:44px; height:44px; border-radius:7px; overflow:hidden; flex-shrink:0; border:1px solid var(--border); }
        .passo-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
        .passo-actions { display:flex; gap:3px; flex-shrink:0; margin-top:2px; }
        .btn-icon--sm { width:30px; height:30px; border-radius:7px; border:none; background:transparent; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--muted); transition:background .15s, color .15s; }
        .btn-icon--sm:hover       { background:var(--sand); color:var(--brown); }
        .btn-icon--sm.danger:hover{ background:#FFF1F0; color:#DC2626; }

        .passi-footer { padding:12px 0 14px; display:flex; justify-content:center; }
        .btn-add-passo {
            display:inline-flex; align-items:center; gap:6px;
            font-size:.78rem; font-weight:600; color:var(--caramel);
            text-decoration:none; padding:7px 16px; border-radius:20px;
            border:1.5px dashed #E8B99A; background:transparent; transition:background .15s;
        }
        .btn-add-passo:hover { background:#FFF3ED; }

        .empty-state-recipes {
            text-align:center; padding:56px 20px;
            background:var(--white); border:1.5px dashed var(--border); border-radius:var(--radius-lg);
        }
        .empty-state-recipes p { color:var(--muted); margin-bottom:20px; }

        .delete-form { margin:0; }

        /* ── FAB ── */
        .fab {
            position:fixed; bottom:36px; right:36px;
            width:58px; height:58px; border-radius:50%;
            background:var(--caramel); color:#FFF; border:none;
            cursor:pointer; display:flex; align-items:center; justify-content:center;
            box-shadow:0 6px 24px rgba(196,98,45,.38);
            text-decoration:none; transition:transform .2s, box-shadow .2s; z-index:100;
        }
        .fab:hover { transform:scale(1.08) translateY(-2px); box-shadow:0 10px 30px rgba(196,98,45,.45); }
        .fab-tooltip {
            position:fixed; bottom:44px; right:106px;
            background:var(--brown); color:#FFF;
            font-size:.78rem; font-weight:500;
            padding:6px 14px; border-radius:20px;
            white-space:nowrap; opacity:0; pointer-events:none; transition:opacity .2s;
        }
        .fab:hover + .fab-tooltip { opacity:1; }

        @media (max-width:640px) {
            .profile-section  { flex-direction:column; gap:20px; text-align:center; }
            .profile-stats    { justify-content:center; }
            .profile-bio      { margin:0 auto; }
            .timer-layout     { grid-template-columns:1fr; }
            .clock-preview-wrap { order:-1; }
            .fab              { bottom:20px; right:18px; }
            .fab-tooltip      { display:none; }
        }
    </style>
</head>
<body>

<?php include '../include/header.php'; ?>

<!-- MODAL CAMBIO FOTO -->
<div class="modal-backdrop" id="modalFoto" role="dialog" aria-modal="true" aria-labelledby="modalFotoTitle">
    <div class="modal-box">
        <button class="modal-close" onclick="chiudiModal()" aria-label="Chiudi">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <h2 class="modal-title" id="modalFotoTitle">Cambia foto profilo</h2>
        <p class="modal-subtitle">Carica una nuova immagine (JPG, PNG, WEBP · max 5 MB)</p>
        <div class="preview-wrap">
            <img id="previewImg" src="<?php echo htmlspecialchars($profilo['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>" alt="Anteprima" class="preview-circle">
        </div>
        <form action="../controller/aggiornafotoprofilocontroller.php" method="POST" enctype="multipart/form-data" id="formFoto">
            <div class="dropzone" id="dropzone">
                <input type="file" name="foto_profilo" id="inputFoto" accept="image/jpeg,image/png,image/webp,image/gif" onchange="onFileSelect(this)">
                <div class="dropzone-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <p>Clicca o trascina qui la foto</p>
                <small>JPG, PNG, WEBP, GIF — max 5 MB</small>
            </div>
            <span class="file-name-label" id="fileNameLabel"></span>
            <button type="submit" class="btn-modal-submit" id="btnConferma" disabled>Salva foto profilo</button>
        </form>
    </div>
</div>

<!-- PAGINA -->
<main class="page-content">
    <div class="page-wrap">

        <!-- Flash -->
        <?php if (isset($_GET['success'])): ?>
            <div class="flash flash--success">
                <?php $msgs=['ricetta_creata'=>'✓ Ricetta aggiunta!','ricetta_modificata'=>'✓ Ricetta aggiornata.','passo_aggiunto'=>'✓ Passo aggiunto.','passo_modificato'=>'✓ Passo aggiornato.','ricetta_completata'=>'✓ Ricetta completata!','foto_aggiornata'=>'✓ Foto profilo aggiornata!','timer_aggiornato'=>'✓ Timer salvato!']; echo htmlspecialchars($msgs[$_GET['success']] ?? 'Operazione completata.'); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="flash flash--warning"><?php echo $_GET['deleted']==='passo' ? '⚑ Passo eliminato.' : '⚑ Ricetta eliminata.'; ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="flash flash--error">
                <?php $errori=['upload_fallito'=>'Errore durante il caricamento.','tipo_non_valido'=>'Formato non supportato.','file_troppo_grande'=>'File supera i 5 MB.','salvataggio_fallito'=>'Impossibile salvare.','db_fallito'=>'Errore database.','colore_non_valido'=>'Colore non valido.','timer_fallito'=>'Errore salvataggio timer.']; echo htmlspecialchars($errori[$_GET['error']] ?? 'Si è verificato un errore.'); ?>
            </div>
        <?php endif; ?>

        <!-- PROFILO -->
        <section class="profile-section">
            <div class="profile-avatar-wrap" onclick="apriModal()" title="Cambia foto profilo">
                <img src="<?php echo htmlspecialchars($profilo['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>" alt="Foto profilo" class="profile-avatar" id="avatarPrincipale">
                <div class="avatar-overlay">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span>Cambia</span>
                </div>
            </div>
            <div>
                <div class="profile-stats">
                    <div class="stat-item"><span class="stat-num"><?php echo $statistiche['num_ricette']; ?></span><span class="stat-label">Post</span></div>
                    <div class="stat-item"><span class="stat-num"><?php echo $statistiche['num_follower']; ?></span><span class="stat-label">Follower</span></div>
                    <div class="stat-item"><span class="stat-num"><?php echo $statistiche['num_seguiti']; ?></span><span class="stat-label">Seguiti</span></div>
                </div>
                <p class="profile-username"><span>@</span><?php echo htmlspecialchars($profilo['username']); ?></p>
                <?php if (!empty($profilo['biografia'])): ?>
                    <p class="profile-bio"><?php echo nl2br(htmlspecialchars($profilo['biografia'])); ?></p>
                <?php endif; ?>
            </div>
        </section>

        <!-- TIMER -->
        <section id="sezione-timer" style="margin-bottom:48px;">
            <div class="section-header">
                <h2 class="section-title">Il mio timer</h2>
            </div>
            <div class="timer-section">
                <div class="timer-section-header">
                    <div class="timer-section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <div class="timer-section-title">Personalizza il tuo orologio</div>
                        <div class="timer-section-sub">Le modifiche si riflettono sui timer durante la cottura</div>
                    </div>
                </div>
                <div class="timer-layout">
                    <!-- Preview orologio -->
                    <div class="clock-preview-wrap">
                        <span class="clock-preview-label">Anteprima</span>
                        <div class="clock-preview" id="clockPreview" style="background:<?php echo htmlspecialchars($timer['coloreSfondo']); ?>;">
                            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" id="clockSvg">
                                <?php for ($i=0;$i<60;$i++): $a=$i*6; $maj=($i%5===0); $r=88; $l=$maj?14:7; $sw=$maj?3:1.5; $rad=$a*M_PI/180; $x1=100+$r*sin($rad); $y1=100-$r*cos($rad); $x2=100+($r-$l)*sin($rad); $y2=100-($r-$l)*cos($rad); $c=$timer['coloreNumeri']; ?>
                                    <line x1="<?php echo round($x1,2);?>" y1="<?php echo round($y1,2);?>" x2="<?php echo round($x2,2);?>" y2="<?php echo round($y2,2);?>" stroke="<?php echo htmlspecialchars($c);?>" stroke-width="<?php echo $sw;?>" stroke-linecap="round" class="clock-mark" data-major="<?php echo $maj?'1':'0';?>"/>
                                <?php endfor; ?>
                                <?php foreach([12,1,2,3,4,5,6,7,8,9,10,11] as $idx=>$n): $rad=$idx*30*M_PI/180; $rx=100+68*sin($rad); $ry=100-68*cos($rad); ?>
                                    <text x="<?php echo round($rx,2);?>" y="<?php echo round($ry,2);?>" text-anchor="middle" dominant-baseline="central" font-size="14" font-family="DM Sans,sans-serif" font-weight="600" fill="<?php echo htmlspecialchars($timer['coloreNumeri']);?>" class="hour-num"><?php echo $n;?></text>
                                <?php endforeach; ?>
                            </svg>
                            <div class="preview-hand preview-hand-min" id="previewHandMin" style="background:<?php echo htmlspecialchars($timer['coloreLancetta']);?>;"></div>
                            <div class="preview-hand preview-hand-sec" id="previewHandSec" style="background:<?php echo htmlspecialchars($timer['coloreLancetta']);?>;"></div>
                            <div class="preview-center-dot" id="previewDot" style="background:<?php echo htmlspecialchars($timer['coloreLancetta']);?>;"></div>
                            <div class="preview-time-text" id="previewTime" style="color:<?php echo htmlspecialchars($timer['coloreNumeri']);?>;">10:10</div>
                        </div>
                        <span style="font-size:.7rem;color:var(--muted-light);text-align:center;line-height:1.4;">Le lancette si muovono<br>durante la cottura</span>
                    </div>
                    <!-- Form colori -->
                    <div>
                        <form action="../controller/aggiornaTimerController.php" method="POST" id="formTimer">
                            <div class="color-form">
                                <?php
                                $campi=[['sfondo','Sfondo orologio','#FFFFFF'],['lancetta','Lancette &amp; perno','#000000'],['numeri','Numeri &amp; segni','#000000']];
                                $valori=['sfondo'=>$timer['coloreSfondo'],'lancetta'=>$timer['coloreLancetta'],'numeri'=>$timer['coloreNumeri']];
                                foreach($campi as [$key,$label,$ph]):
                                    ?>
                                    <div class="color-field">
                                        <label><?php echo $label;?></label>
                                        <div class="color-input-row">
                                            <div class="color-swatch" id="swatch<?php echo ucfirst($key);?>" style="background:<?php echo htmlspecialchars($valori[$key]);?>;">
                                                <input type="color" id="picker<?php echo ucfirst($key);?>" value="<?php echo htmlspecialchars($valori[$key]);?>" oninput="syncColor('<?php echo $key;?>',this.value)">
                                            </div>
                                            <input type="text" class="color-hex-input" id="hex<?php echo ucfirst($key);?>" name="colore<?php echo ucfirst($key);?>" value="<?php echo htmlspecialchars($valori[$key]);?>" maxlength="7" placeholder="<?php echo $ph;?>" oninput="onHexInput('<?php echo $key;?>',this)">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div>
                                    <span class="preset-label">Temi rapidi</span>
                                    <div class="preset-grid">
                                        <button type="button" class="preset-btn" style="background:#FFF;border:2px solid #ddd;" title="Classico"   onclick="applicaPreset('#FFFFFF','#000000','#000000')"></button>
                                        <button type="button" class="preset-btn" style="background:#1A1008;" title="Notte"   onclick="applicaPreset('#1A1008','#F5E6D3','#F5E6D3')"></button>
                                        <button type="button" class="preset-btn" style="background:#C4622D;" title="Chefly"  onclick="applicaPreset('#C4622D','#FFFFFF','#FFFFFF')"></button>
                                        <button type="button" class="preset-btn" style="background:#2D4A22;" title="Bosco"   onclick="applicaPreset('#2D4A22','#A8D5A2','#A8D5A2')"></button>
                                        <button type="button" class="preset-btn" style="background:#E8F4FD;" title="Cielo"   onclick="applicaPreset('#E8F4FD','#2980B9','#2980B9')"></button>
                                        <button type="button" class="preset-btn" style="background:#F5F0E8;" title="Crema"   onclick="applicaPreset('#F5F0E8','#8B6914','#8B6914')"></button>
                                        <button type="button" class="preset-btn" style="background:#6C63FF;" title="Lavanda" onclick="applicaPreset('#6C63FF','#FFFFFF','#FFFFFF')"></button>
                                        <button type="button" class="preset-btn" style="background:#FDE8F0;" title="Rosa"    onclick="applicaPreset('#FDE8F0','#C2185B','#C2185B')"></button>
                                    </div>
                                </div>
                                <div class="form-divider"></div>
                                <button type="submit" class="btn-save-timer">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="display:inline;vertical-align:middle;margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    Salva timer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- RICETTE -->
        <div class="section-header">
            <h2 class="section-title">Le mie ricette</h2>
        </div>

        <?php if (empty($ricette)): ?>
            <div class="empty-state-recipes">
                <p>Non hai ancora aggiunto nessuna ricetta.</p>
                <a href="aggiungiRicetta.php" class="btn btn-caramel">+ Aggiungi la prima ricetta</a>
            </div>
        <?php else: ?>
            <div class="recipe-list">
                <?php foreach ($ricette as $ricetta): ?>
                    <article class="recipe-item">
                        <div class="recipe-item-header">
                            <div class="recipe-cover-thumb">
                                <?php if (!empty($ricetta['url_copertina'])): ?>
                                    <img src="../<?php echo htmlspecialchars($ricetta['url_copertina']); ?>" alt="">
                                <?php else: ?>
                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                                <?php endif; ?>
                            </div>
                            <div class="recipe-item-main">
                                <div class="recipe-item-title"><?php echo htmlspecialchars($ricetta['titolo']); ?></div>
                                <div class="recipe-item-meta">
                                    <span class="badge badge--<?php echo strtolower($ricetta['difficolta']); ?>"><?php echo ucfirst($ricetta['difficolta']); ?></span>
                                    <span class="badge" style="background:#FFF3ED;color:var(--caramel);"><?php echo count($ricetta['passi']); ?> pass<?php echo count($ricetta['passi'])!==1?'i':'o'; ?></span>
                                </div>
                            </div>
                            <div class="recipe-item-actions">
                                <a href="modificaRicetta.php?id_ricetta=<?php echo $ricetta['id']; ?>" class="btn-icon" title="Modifica">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
                                <form class="delete-form" action="../controller/cancellaRicettaController.php" method="POST" onsubmit="return confirm('Eliminare «<?php echo addslashes($ricetta['titolo']); ?>»?');">
                                    <input type="hidden" name="id_ricetta" value="<?php echo $ricetta['id']; ?>">
                                    <button type="submit" class="btn-icon btn-icon--danger" title="Elimina">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    </button>
                                </form>
                                <button class="btn-toggle" onclick="togglePassi(this,'passi-<?php echo $ricetta['id']; ?>')" title="Mostra/nascondi passi">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="passi-section" id="passi-<?php echo $ricetta['id']; ?>">
                            <div class="passi-inner">
                                <?php if (empty($ricetta['passi'])): ?>
                                    <div style="padding:18px 0;text-align:center;color:var(--muted);font-size:.85rem;">Nessun passo ancora.</div>
                                <?php else: ?>
                                    <?php foreach ($ricetta['passi'] as $idx => $passo): ?>
                                        <div class="passo-row">
                                            <div class="passo-num"><?php echo $idx+1; ?></div>
                                            <div class="passo-detail">
                                                <div class="passo-title"><?php echo htmlspecialchars($passo['titolo']); ?></div>
                                                <?php if (!empty($passo['descrizione'])): ?>
                                                    <p class="passo-desc"><?php echo nl2br(htmlspecialchars(mb_substr($passo['descrizione'],0,120).(mb_strlen($passo['descrizione'])>120?'…':''))); ?></p>
                                                <?php endif; ?>
                                                <div class="passo-chips">
                                                    <?php if (!empty($passo['durata'])): ?><span class="pchip pchip--time"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><?php echo $passo['durata']; ?> min</span><?php endif; ?>
                                                    <?php if (!empty($passo['tempoCottura'])): ?><span class="pchip pchip--fire">Cottura <?php echo $passo['tempoCottura']; ?> min</span><?php endif; ?>
                                                    <?php if (!empty($passo['tempoRiposo'])): ?><span class="pchip pchip--rest">Riposo <?php echo $passo['tempoRiposo']; ?> min</span><?php endif; ?>
                                                    <?php if (!empty($passo['nome_cottura'])): ?><span class="pchip pchip--tech"><?php echo htmlspecialchars($passo['nome_cottura']); ?></span><?php endif; ?>
                                                </div>
                                                <?php if (!empty($passo['ingredienti'])): ?>
                                                    <div class="passo-ings">
                                                        <?php foreach ($passo['ingredienti'] as $ing): ?>
                                                            <span class="ing-tag"><?php echo htmlspecialchars($ing['nome']); ?><?php if (!empty($ing['dose'])): ?><em><?php echo htmlspecialchars($ing['dose']); ?></em><?php endif; ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($passo['media'])): ?>
                                                    <div class="passo-thumbs">
                                                        <?php foreach (array_slice($passo['media'],0,4) as $m): ?>
                                                            <div class="passo-thumb"><img src="../<?php echo htmlspecialchars($m['urlMedia']); ?>" alt=""></div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="passo-actions">
                                                <a href="modificaPasso.php?id_passo=<?php echo $passo['id']; ?>&id_ricetta=<?php echo $ricetta['id']; ?>" class="btn-icon--sm" title="Modifica">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                </a>
                                                <form class="delete-form" action="../controller/cancellaPassoController.php" method="POST" onsubmit="return confirm('Eliminare il passo?');">
                                                    <input type="hidden" name="id_passo"   value="<?php echo $passo['id']; ?>">
                                                    <input type="hidden" name="id_ricetta" value="<?php echo $ricetta['id']; ?>">
                                                    <button type="submit" class="btn-icon--sm danger" title="Elimina">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <div class="passi-footer">
                                    <a href="aggiungiPasso.php?id_ricetta=<?php echo $ricetta['id']; ?>" class="btn-add-passo">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        Aggiungi passo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<a href="aggiungiRicetta.php" class="fab" title="Aggiungi ricetta">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
</a>
<span class="fab-tooltip">Nuova ricetta</span>

<?php include '../include/footer.php'; ?>

<script>
    const modal=document.getElementById('modalFoto'),btnConferma=document.getElementById('btnConferma'),inputFoto=document.getElementById('inputFoto'),previewImg=document.getElementById('previewImg'),fileLabel=document.getElementById('fileNameLabel'),dropzone=document.getElementById('dropzone');
    function apriModal(){modal.classList.add('open');document.body.style.overflow='hidden';}
    function chiudiModal(){modal.classList.remove('open');document.body.style.overflow='';}
    modal.addEventListener('click',e=>{if(e.target===modal)chiudiModal();});
    document.addEventListener('keydown',e=>{if(e.key==='Escape')chiudiModal();});
    function onFileSelect(input){if(!input.files||!input.files[0])return;const f=input.files[0];if(f.size>5*1024*1024){alert('File troppo grande. Max 5 MB.');input.value='';return;}fileLabel.textContent=f.name;const r=new FileReader();r.onload=e=>{previewImg.src=e.target.result;};r.readAsDataURL(f);btnConferma.disabled=false;}
    dropzone.addEventListener('dragover',e=>{e.preventDefault();dropzone.classList.add('dragover');});
    dropzone.addEventListener('dragleave',()=>dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop',e=>{e.preventDefault();dropzone.classList.remove('dragover');if(e.dataTransfer.files.length){inputFoto.files=e.dataTransfer.files;onFileSelect(inputFoto);}});

    function togglePassi(btn,id){const s=document.getElementById(id);const o=s.classList.toggle('open');btn.classList.toggle('open',o);}

    function isValidHex(v){return /^#[0-9A-Fa-f]{6}$/.test(v);}
    function syncColor(c,v){if(!isValidHex(v))return;v=v.toUpperCase();
        const els={sfondo:{preview:'clockPreview',swatch:'swatchSfondo',picker:'pickerSfondo',hex:'hexSfondo'},lancetta:{hands:['previewHandMin','previewHandSec','previewDot'],swatch:'swatchLancetta',picker:'pickerLancetta',hex:'hexLancetta'},numeri:{swatch:'swatchNumeri',picker:'pickerNumeri',hex:'hexNumeri',time:'previewTime'}};
        if(c==='sfondo'){document.getElementById('clockPreview').style.background=v;}
        else if(c==='lancetta'){['previewHandMin','previewHandSec','previewDot'].forEach(id=>document.getElementById(id).style.background=v);}
        else if(c==='numeri'){document.getElementById('previewTime').style.color=v;document.querySelectorAll('.hour-num').forEach(el=>el.setAttribute('fill',v));document.querySelectorAll('.clock-mark').forEach(el=>el.setAttribute('stroke',v));}
        document.getElementById('swatch'+c.charAt(0).toUpperCase()+c.slice(1)).style.background=v;
        document.getElementById('picker'+c.charAt(0).toUpperCase()+c.slice(1)).value=v;
        const hi=document.getElementById('hex'+c.charAt(0).toUpperCase()+c.slice(1));hi.value=v;hi.classList.remove('invalid');}
    function onHexInput(c,input){let v=input.value.trim();if(!v.startsWith('#'))v='#'+v;if(isValidHex(v)){input.classList.remove('invalid');syncColor(c,v);}else{input.classList.add('invalid');}}
    function applicaPreset(s,l,n){syncColor('sfondo',s);syncColor('lancetta',l);syncColor('numeri',n);}
    (function anim(){const now=new Date();const s=now.getSeconds();const m=now.getMinutes()+s/60;document.getElementById('previewHandMin').style.transform=`translateX(-50%) rotate(${m*6}deg)`;document.getElementById('previewHandSec').style.transform=`translateX(-50%) rotate(${s*6}deg)`;const hh=String(now.getHours()).padStart(2,'0'),mm=String(now.getMinutes()).padStart(2,'0');document.getElementById('previewTime').textContent=hh+':'+mm;requestAnimationFrame(anim);})();
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>
</body>
</html>