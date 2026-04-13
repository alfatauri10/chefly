<?php
/* view/modificaRicetta.php */
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once '../include/connessione.php';
require_once '../model/ricetta.php';

$id_utente  = $_SESSION['user_id'];
$id_ricetta = isset($_GET['id_ricetta']) ? (int)$_GET['id_ricetta'] : null;
if (!$id_ricetta) { header("Location: ilMioRistorante.php"); exit(); }

$ricetta = getRicettaByIdDB($conn, $id_ricetta);
if (!$ricetta || $ricetta['idCreatore'] != $id_utente) { header("Location: ilMioRistorante.php?error=non_autorizzato"); exit(); }

$lista_nazionalita = getTutteLeNazionalita($conn);
$lista_tipologie   = getTutteLeTipologie($conn);

// FIX: get_result() chiamato una sola volta e salvato in variabile
$sql_gallery = "SELECT id, urlMedia, isCopertina FROM mediaRicette WHERE idRicetta=? ORDER BY id ASC";
$stmt = $conn->prepare($sql_gallery);
$stmt->bind_param("i", $id_ricetta);
$stmt->execute();
$result_gallery = $stmt->get_result(); // ← FIX: salvato in variabile, non chiamato nel loop
$foto_esistenti      = [];
$copertina_esistente = null;
while ($row = $result_gallery->fetch_assoc()) {
    if ($row['isCopertina']) $copertina_esistente = $row;
    else $foto_esistenti[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Ricetta — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        .edit-wrap { max-width:680px; margin:0 auto; padding:48px 20px 100px; }
        .breadcrumb { display:flex; align-items:center; gap:8px; font-size:.78rem; color:var(--muted); margin-bottom:32px; flex-wrap:wrap; }
        .breadcrumb a { color:var(--caramel); text-decoration:none; font-weight:500; }
        .breadcrumb a:hover { text-decoration:underline; }
        .page-header { display:flex; align-items:flex-start; gap:16px; margin-bottom:36px; }
        .page-header-icon { width:50px; height:50px; background:var(--caramel); border-radius:14px; display:flex; align-items:center; justify-content:center; color:#FFF; flex-shrink:0; }
        .page-title { font-family:var(--font-serif); font-size:1.8rem; font-weight:600; color:var(--brown); margin-bottom:5px; }
        .page-title em { font-style:italic; color:var(--caramel); }
        .page-sub { font-size:.85rem; color:var(--muted); line-height:1.5; }

        .current-cover { display:flex; align-items:center; gap:14px; background:var(--cream); border:1px solid var(--border); border-radius:10px; padding:14px 16px; margin-bottom:14px; }
        .current-cover img { width:68px; height:68px; object-fit:cover; border-radius:8px; flex-shrink:0; border:1px solid var(--border); }
        .current-cover-placeholder { width:68px; height:68px; background:var(--sand); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#C4C0B8; flex-shrink:0; }
        .current-cover-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--muted); margin-bottom:3px; }
        .current-cover-name { font-size:.82rem; color:var(--brown); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .current-cover-hint { font-size:.74rem; color:var(--muted-light); margin-top:3px; }

        .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(90px,1fr)); gap:10px; margin-bottom:14px; }
        .gallery-item { position:relative; border-radius:10px; overflow:hidden; border:2px solid transparent; transition:border-color .2s; cursor:pointer; }
        .gallery-item img { width:100%; aspect-ratio:1; object-fit:cover; display:block; }
        .gallery-item input[type="checkbox"] { display:none; }
        .gallery-item .overlay { position:absolute; inset:0; background:rgba(220,38,38,0); display:flex; align-items:center; justify-content:center; transition:background .2s; }
        .gallery-item .check-mark { width:26px; height:26px; border-radius:50%; background:rgba(255,255,255,.9); border:2px solid var(--border); display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity .2s, border-color .2s; }
        .gallery-item .check-mark svg { color:#DC2626; opacity:0; transition:opacity .15s; }
        .gallery-item:hover .check-mark { opacity:1; }
        .gallery-item.marked { border-color:#DC2626; }
        .gallery-item.marked .overlay { background:rgba(220,38,38,.18); }
        .gallery-item.marked .check-mark { opacity:1; border-color:#DC2626; }
        .gallery-item.marked .check-mark svg { opacity:1; }
        .gallery-empty { text-align:center; padding:22px; color:var(--muted-light); font-size:.82rem; background:var(--cream); border:1px dashed var(--border); border-radius:10px; margin-bottom:14px; }
        .delete-hint { font-size:.74rem; color:var(--muted-light); margin-bottom:14px; display:flex; align-items:center; gap:6px; }

        .file-zone { border:2px dashed var(--border); border-radius:10px; padding:18px 16px; text-align:center; cursor:pointer; transition:border-color .2s, background .2s; position:relative; }
        .file-zone:hover { border-color:var(--caramel); background:#FFF3ED; }
        .file-zone input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
        .file-zone-icon { width:36px; height:36px; background:var(--sand); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; color:var(--caramel); }
        .file-zone p   { font-size:.82rem; color:#6B5C48; margin-bottom:3px; font-weight:500; }
        .file-zone small { font-size:.72rem; color:var(--muted-light); }

        /* copertina obbligatoria: evidenzia zona se mancante */
        .file-zone--required { border-color: #E8B99A; }
        .required-badge { display:inline-block; font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#C4622D; background:#FFF3ED; border:1px solid #E8B99A; border-radius:20px; padding:2px 8px; margin-left:6px; vertical-align:middle; }

        .form-actions { display:flex; gap:14px; margin-top:8px; }
        .btn-submit-save { flex:1; padding:14px; background:var(--caramel); color:#FFF; border:none; border-radius:12px; font-family:var(--font-sans); font-size:.92rem; font-weight:600; cursor:pointer; transition:background .2s, transform .1s; display:flex; align-items:center; justify-content:center; gap:7px; }
        .btn-submit-save:hover  { background:var(--caramel-dark); }
        .btn-submit-save:active { transform:scale(.98); }

        @media(max-width:580px){ .form-actions { flex-direction:column; } }
    </style>
</head>
<body>
<?php include '../include/header.php'; ?>

<main class="page-content">
    <div class="edit-wrap">

        <nav class="breadcrumb">
            <a href="ilMioRistorante.php">Il mio ristorante</a>
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
            <span>Modifica ricetta</span>
        </nav>

        <div class="page-header">
            <div class="page-header-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </div>
            <div>
                <h1 class="page-title">Modifica <em><?php echo htmlspecialchars($ricetta['titolo']); ?></em></h1>
                <p class="page-sub">Aggiorna le informazioni della tua ricetta. Le modifiche saranno subito visibili.</p>
            </div>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="flash flash--error" style="margin-bottom:24px;">
                <?php $errori=['campi_mancanti'=>'Compila tutti i campi obbligatori.','errore_modifica'=>'Errore durante il salvataggio. Riprova.']; echo htmlspecialchars($errori[$_GET['error']] ?? 'Errore sconosciuto.'); ?>
            </div>
        <?php endif; ?>

        <form action="../controller/modificaRicettaController.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_ricetta" value="<?php echo $id_ricetta; ?>">

            <!-- Info base -->
            <div class="card" style="margin-bottom:18px;">
                <div class="card-header">
                    <div class="card-header-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg></div>
                    <span class="card-header-title">Informazioni base</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Titolo *</label>
                        <input type="text" name="titolo" class="form-control" value="<?php echo htmlspecialchars($ricetta['titolo']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descrizione *</label>
                        <textarea name="descrizione" class="form-control" required><?php echo htmlspecialchars($ricetta['descrizione']); ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Difficoltà *</label>
                            <select name="difficolta" class="form-control" required>
                                <?php foreach (['facile'=>'Facile','media'=>'Media','difficile'=>'Difficile','esperto'=>'Esperto'] as $v=>$l): ?>
                                    <option value="<?php echo $v; ?>" <?php echo ($ricetta['difficolta']===$v)?'selected':''; ?>><?php echo $l; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div></div>
                    </div>
                    <div class="form-row" style="margin-top:16px;">
                        <!-- Nazionalità OBBLIGATORIA -->
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Nazionalità *</label>
                            <select name="id_nazionalita" class="form-control" required>
                                <option value="" disabled <?php echo empty($ricetta['idNazionalita'])?'selected':''; ?>>Seleziona</option>
                                <?php foreach ($lista_nazionalita as $n): ?>
                                    <option value="<?php echo $n['id']; ?>" <?php echo ($ricetta['idNazionalita']==$n['id'])?'selected':''; ?>><?php echo htmlspecialchars($n['nome']); ?> (<?php echo htmlspecialchars($n['sigla']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- Tipologia OBBLIGATORIA -->
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Tipologia *</label>
                            <select name="id_tipologia" class="form-control" required>
                                <option value="" disabled <?php echo empty($ricetta['idTipologia'])?'selected':''; ?>>Seleziona</option>
                                <?php foreach ($lista_tipologie as $t): ?>
                                    <option value="<?php echo $t['id']; ?>" <?php echo ($ricetta['idTipologia']==$t['id'])?'selected':''; ?>><?php echo htmlspecialchars($t['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Copertina OBBLIGATORIA -->
            <div class="card" style="margin-bottom:18px;">
                <div class="card-header">
                    <div class="card-header-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                    <span class="card-header-title">Foto di copertina<?php if (!$copertina_esistente): ?> <span class="required-badge">Richiesta</span><?php endif; ?></span>
                </div>
                <div class="card-body">
                    <?php if ($copertina_esistente): ?>
                        <!-- Copertina già presente: mostrala e permetti sostituzione (opzionale) -->
                        <div class="current-cover">
                            <img src="../<?php echo htmlspecialchars($copertina_esistente['urlMedia']); ?>" alt="Copertina attuale">
                            <div>
                                <div class="current-cover-label">Copertina attuale</div>
                                <div class="current-cover-name"><?php echo basename($copertina_esistente['urlMedia']); ?></div>
                                <div class="current-cover-hint">Carica una nuova immagine per sostituirla (opzionale)</div>
                            </div>
                        </div>
                        <div class="file-zone">
                            <input type="file" name="copertina" accept="image/*">
                            <div class="file-zone-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
                            <p>Clicca per scegliere una nuova copertina</p>
                            <small>JPG, PNG, WEBP — consigliata almeno 800×600px</small>
                        </div>
                    <?php else: ?>
                        <!-- Nessuna copertina: upload obbligatorio -->
                        <div class="file-zone file-zone--required">
                            <input type="file" name="copertina" accept="image/*" required>
                            <div class="file-zone-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
                            <p>Clicca per scegliere la copertina <strong>*</strong></p>
                            <small>JPG, PNG, WEBP — consigliata almeno 800×600px</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Galleria OPZIONALE -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header">
                    <div class="card-header-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg></div>
                    <span class="card-header-title">Galleria fotografica <span class="opt" style="font-weight:400;text-transform:none;letter-spacing:0;font-size:.8rem;">(opzionale)</span></span>
                </div>
                <div class="card-body">
                    <?php if (!empty($foto_esistenti)): ?>
                        <p class="delete-hint">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            Clicca su una foto per selezionarla ed eliminarla
                        </p>
                        <div class="gallery-grid">
                            <?php foreach ($foto_esistenti as $foto): ?>
                                <label class="gallery-item" id="item-<?php echo $foto['id']; ?>">
                                    <input type="checkbox" name="foto_da_eliminare[]" value="<?php echo $foto['id']; ?>" onchange="toggleMarcatura(this,<?php echo $foto['id']; ?>)">
                                    <img src="../<?php echo htmlspecialchars($foto['urlMedia']); ?>" alt="">
                                    <div class="overlay"><div class="check-mark"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="gallery-empty">Nessuna foto nella galleria.</div>
                    <?php endif; ?>
                    <div class="file-zone">
                        <input type="file" name="gallery[]" accept="image/*" multiple>
                        <div class="file-zone-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
                        <p>Aggiungi nuove foto alla galleria</p>
                        <small>CTRL / CMD per selezionarne più di una</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="ilMioRistorante.php" class="btn btn-ghost">← Annulla</a>
                <button type="submit" class="btn-submit-save">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Salva modifiche
                </button>
            </div>

        </form>
    </div>
</main>

<?php include '../include/footer.php'; ?>
<script>
    function toggleMarcatura(cb,id){document.getElementById('item-'+id).classList.toggle('marked',cb.checked);}
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>
</body>
</html>