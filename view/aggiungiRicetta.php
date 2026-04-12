<?php
/* view/aggiungiRicetta.php */
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once '../include/connessione.php';
require_once '../model/ricetta.php';
$lista_nazionalita = getTutteLeNazionalita($conn);
$lista_tipologie   = getTutteLeTipologie($conn);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuova Ricetta — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        .recipe-form-wrap { max-width:660px; margin:0 auto; padding:48px 20px 100px; }
        .form-page-header { margin-bottom:36px; }
        .form-page-eyebrow { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:2.5px; color:var(--caramel); margin-bottom:10px; }
        .form-page-title { font-family:var(--font-serif); font-size:1.9rem; font-weight:700; color:var(--brown); margin-bottom:8px; }
        .form-page-sub { font-size:.88rem; color:var(--muted); line-height:1.55; }

        .file-zone { border:2px dashed var(--border); border-radius:10px; padding:18px 16px; text-align:center; cursor:pointer; transition:border-color .2s, background .2s; position:relative; }
        .file-zone:hover { border-color:var(--caramel); background:#FFF3ED; }
        .file-zone input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
        .file-zone-icon { width:36px; height:36px; background:var(--sand); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; color:var(--caramel); }
        .file-zone p   { font-size:.82rem; color:#6B5C48; margin-bottom:3px; font-weight:500; }
        .file-zone small { font-size:.72rem; color:var(--muted-light); }

        .form-actions { display:flex; gap:14px; margin-top:8px; }
        .btn-submit-full { flex:1; padding:14px; background:var(--caramel); color:#FFF; border:none; border-radius:12px; font-family:var(--font-sans); font-size:.95rem; font-weight:600; cursor:pointer; transition:background .2s, transform .1s; }
        .btn-submit-full:hover  { background:var(--caramel-dark); }
        .btn-submit-full:active { transform:scale(.98); }
    </style>
</head>
<body>
<?php include '../include/header.php'; ?>

<main class="page-content">
    <div class="recipe-form-wrap">

        <div class="form-page-header">
            <p class="form-page-eyebrow">Fase 1 di 2</p>
            <h1 class="form-page-title">Nuova ricetta</h1>
            <p class="form-page-sub">Inserisci le informazioni principali. Passi, ingredienti e cotture li aggiungerai nel passaggio successivo.</p>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'campi_mancanti'): ?>
            <div class="flash flash--error" style="margin-bottom:28px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Compila tutti i campi obbligatori prima di procedere.
            </div>
        <?php endif; ?>

        <form action="../controller/aggiungiRicettaController.php" method="POST" enctype="multipart/form-data">

            <!-- Info base -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-header-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
                    </div>
                    <span class="card-header-title">Informazioni base</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Titolo *</label>
                        <input type="text" name="titolo" class="form-control" placeholder="Es: Spaghetti alla Carbonara originale" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descrizione *</label>
                        <textarea name="descrizione" class="form-control" placeholder="Una breve introduzione alla tua ricetta..." required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Difficoltà *</label>
                            <select name="difficolta" class="form-control" required>
                                <option value="" disabled selected>Seleziona</option>
                                <option value="Facile">Facile</option>
                                <option value="Media">Media</option>
                                <option value="Difficile">Difficile</option>
                                <option value="Esperto">Esperto</option>
                            </select>
                        </div>
                        <div></div>
                    </div>
                </div>
            </div>

            <!-- Classificazione -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <div class="card-header-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                    </div>
                    <span class="card-header-title">Classificazione</span>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Nazionalità <span class="opt">(opzionale)</span></label>
                            <select name="id_nazionalita" class="form-control">
                                <option value="">Nessuna</option>
                                <?php foreach ($lista_nazionalita as $n): ?>
                                    <option value="<?php echo $n['id']; ?>"><?php echo htmlspecialchars($n['nome']); ?> (<?php echo htmlspecialchars($n['sigla']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Tipologia <span class="opt">(opzionale)</span></label>
                            <select name="id_tipologia" class="form-control">
                                <option value="">Nessuna</option>
                                <?php foreach ($lista_tipologie as $t): ?>
                                    <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Foto -->
            <div class="card" style="margin-bottom:28px;">
                <div class="card-header">
                    <div class="card-header-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </div>
                    <span class="card-header-title">Foto</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Copertina <span class="opt">(opzionale)</span></label>
                        <div class="file-zone">
                            <input type="file" name="copertina" accept="image/*">
                            <div class="file-zone-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            </div>
                            <p>Clicca o trascina la foto di copertina</p>
                            <small>JPG, PNG, WEBP — consigliata almeno 800×600px</small>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Galleria <span class="opt">(opzionale)</span></label>
                        <div class="file-zone">
                            <input type="file" name="gallery[]" accept="image/*" multiple>
                            <div class="file-zone-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            </div>
                            <p>Aggiungi più foto alla galleria</p>
                            <small>Tieni CTRL / CMD per selezionarne più di una</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="ilMioRistorante.php" class="btn btn-ghost">← Le mie ricette</a>
                <button type="submit" class="btn-submit-full">Salva e procedi ai passi →</button>
            </div>

        </form>
    </div>
</main>

<?php include '../include/footer.php'; ?>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>
</body>
</html>