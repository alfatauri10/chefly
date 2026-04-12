<?php
/* view/aggiungiPasso.php */
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
require_once '../include/connessione.php';
require_once '../model/passo.php';

$id_ricetta = isset($_GET['id_ricetta']) ? (int)$_GET['id_ricetta'] : null;
if (!$id_ricetta) { header("Location: ilMioRistorante.php"); exit(); }

$sql_count = "SELECT COUNT(*) as tot FROM passi WHERE idRicetta = ?";
$stmt = $conn->prepare($sql_count);
$stmt->bind_param("i", $id_ricetta);
$stmt->execute();
$numero_passo = ($stmt->get_result()->fetch_assoc()['tot'] ?? 0) + 1;

$lista_cotture     = $conn->query("SELECT id, nome FROM anagCotture ORDER BY nome ASC")->fetch_all(MYSQLI_ASSOC);
$lista_ingredienti = $conn->query("SELECT id, nome FROM anagIngredienti ORDER BY nome ASC")->fetch_all(MYSQLI_ASSOC);

$stmt2 = $conn->prepare("SELECT titolo FROM ricette WHERE id = ?");
$stmt2->bind_param("i", $id_ricetta);
$stmt2->execute();
$titolo_ricetta = $stmt2->get_result()->fetch_assoc()['titolo'] ?? 'la tua ricetta';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passo <?php echo $numero_passo; ?> — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        .step-wrap { max-width:680px; margin:0 auto; padding:48px 20px 100px; }

        .step-progress { margin-bottom:32px; }
        .step-progress-meta { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
        .step-context { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:2px; color:var(--caramel); }
        .step-badge   { background:var(--brown); color:#FFF; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; padding:4px 14px; border-radius:var(--radius-pill); }
        .step-bar     { width:100%; height:3px; background:var(--border); border-radius:2px; overflow:hidden; }
        .step-bar-fill{ height:100%; background:linear-gradient(90deg,var(--caramel),var(--brown)); width:<?php echo min(100,$numero_passo*20); ?>%; border-radius:2px; transition:width .4s ease; }

        .page-title-row { margin-bottom:32px; }
        .page-title-row h1 { font-family:var(--font-serif); font-size:1.9rem; font-weight:700; color:var(--brown); margin-bottom:6px; }
        .page-title-row p  { font-size:.88rem; color:var(--muted); line-height:1.55; }

        .ingredienti-list { display:flex; flex-direction:column; gap:10px; margin-bottom:12px; }
        .ingrediente-row  { display:grid; grid-template-columns:1fr 1fr auto; gap:10px; align-items:center; background:var(--cream); border:1px solid var(--border); border-radius:10px; padding:10px 12px; }
        .ingrediente-row select,
        .ingrediente-row input[type="text"] { width:100%; background:var(--white); border:1px solid var(--border); border-radius:7px; padding:8px 12px; font-family:var(--font-sans); font-size:.85rem; color:var(--brown); appearance:none; }
        .ingrediente-row select { background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%238B7355' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; padding-right:28px; }
        .btn-remove-ing { width:32px; height:32px; border-radius:8px; border:none; background:transparent; color:#DC2626; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .15s; flex-shrink:0; }
        .btn-remove-ing:hover { background:#FFF1F0; }
        .btn-add-ing { display:inline-flex; align-items:center; gap:6px; font-family:var(--font-sans); font-size:.78rem; font-weight:600; text-transform:uppercase; letter-spacing:.8px; color:var(--caramel); background:none; border:1.5px dashed #E8B99A; border-radius:8px; padding:8px 16px; cursor:pointer; transition:background .15s, border-color .15s; }
        .btn-add-ing:hover { background:#FFF3ED; border-color:var(--caramel); }

        .file-zone { border:2px dashed var(--border); border-radius:10px; padding:18px 16px; text-align:center; cursor:pointer; transition:border-color .2s, background .2s; position:relative; }
        .file-zone:hover { border-color:var(--caramel); background:#FFF3ED; }
        .file-zone input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
        .file-zone-icon { width:36px; height:36px; background:var(--sand); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px; color:var(--caramel); }
        .file-zone p   { font-size:.82rem; color:#6B5C48; margin-bottom:3px; font-weight:500; }
        .file-zone small { font-size:.72rem; color:var(--muted-light); }

        .ultimo-box { background:linear-gradient(135deg,var(--cream),var(--sand)); border:1px solid var(--border); border-radius:var(--radius-md); padding:18px 20px; display:flex; align-items:center; gap:14px; margin-bottom:24px; }
        .checkbox-wrap { flex-shrink:0; position:relative; width:24px; height:24px; }
        .checkbox-wrap input[type="checkbox"] { opacity:0; width:24px; height:24px; position:absolute; cursor:pointer; margin:0; }
        .checkbox-mark { width:24px; height:24px; border:2px solid var(--caramel); border-radius:6px; background:var(--white); display:flex; align-items:center; justify-content:center; transition:background .2s, border-color .2s; pointer-events:none; }
        .checkbox-wrap input:checked ~ .checkbox-mark { background:var(--brown); border-color:var(--brown); }
        .checkbox-mark svg { display:none; }
        .checkbox-wrap input:checked ~ .checkbox-mark svg { display:block; }
        .ultimo-label strong { display:block; font-size:.9rem; font-weight:700; color:var(--brown); margin-bottom:2px; }
        .ultimo-label span   { font-size:.78rem; color:var(--muted); }

        .form-actions { display:flex; gap:14px; }
        .btn-submit-step { flex:1; padding:14px; background:var(--caramel); color:#FFF; border:none; border-radius:12px; font-family:var(--font-sans); font-size:.92rem; font-weight:600; cursor:pointer; transition:background .2s, transform .1s; }
        .btn-submit-step:hover  { background:var(--caramel-dark); }
        .btn-submit-step:active { transform:scale(.98); }

        @media(max-width:580px){
            .ingrediente-row { grid-template-columns:1fr; }
            .form-actions    { flex-direction:column; }
        }
    </style>
</head>
<body>
<?php include '../include/header.php'; ?>

<main class="page-content">
    <div class="step-wrap">

        <!-- Progress -->
        <div class="step-progress">
            <div class="step-progress-meta">
                <span class="step-context"><?php echo htmlspecialchars($titolo_ricetta); ?></span>
                <span class="step-badge">Passo <?php echo $numero_passo; ?></span>
            </div>
            <div class="step-bar"><div class="step-bar-fill"></div></div>
        </div>

        <div class="page-title-row">
            <h1>Aggiungi il passo <?php echo $numero_passo; ?></h1>
            <p>Descrivi questa fase della ricetta. Potrai aggiungere altri passi o concludere al termine.</p>
        </div>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'passo_aggiunto'): ?>
            <div class="flash flash--success" style="margin-bottom:24px;">✓ Passo salvato! Aggiungi il prossimo.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] === 'campi_mancanti'): ?>
            <div class="flash flash--error" style="margin-bottom:24px;">Compila almeno il titolo, la descrizione e la durata.</div>
        <?php endif; ?>

        <form action="../controller/aggiungiPassoController.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_ricetta"    value="<?php echo $id_ricetta; ?>">
            <input type="hidden" name="numero_passo"  value="<?php echo $numero_passo; ?>">

            <!-- Info base -->
            <div class="card" style="margin-bottom:18px;">
                <div class="card-header">
                    <div class="card-header-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg></div>
                    <span class="card-header-title">Informazioni base</span>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Titolo del passo *</label>
                        <input type="text" name="titolo" class="form-control" placeholder="Es: Preparazione del soffritto" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Descrizione *</label>
                        <textarea name="descrizione" class="form-control" placeholder="Spiega nel dettaglio cosa fare in questo passaggio..." required></textarea>
                    </div>
                </div>
            </div>

            <!-- Tempi -->
            <div class="card" style="margin-bottom:18px;">
                <div class="card-header">
                    <div class="card-header-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                    <span class="card-header-title">Tempi</span>
                </div>
                <div class="card-body">
                    <div class="form-row" style="margin-bottom:16px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Tempo cottura <span class="opt">(min)</span></label>
                            <input type="number" name="tempoCottura" class="form-control" placeholder="Es: 10" min="0">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Tempo riposo <span class="opt">(min)</span></label>
                            <input type="number" name="tempoRiposo" class="form-control" placeholder="Es: 5" min="0">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Durata totale passo * <span class="opt">(min)</span></label>
                        <input type="number" name="durata" class="form-control" placeholder="Es: 15" min="1" required>
                    </div>
                </div>
            </div>

            <!-- Cottura -->
            <div class="card" style="margin-bottom:18px;">
                <div class="card-header">
                    <div class="card-header-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                    <span class="card-header-title">Tecnica di cottura</span>
                </div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Tipo di cottura <span class="opt">(opzionale)</span></label>
                        <select name="idCottura" class="form-control">
                            <option value="">Nessuna cottura specifica</option>
                            <?php foreach ($lista_cotture as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Ingredienti -->
            <div class="card" style="margin-bottom:18px;">
                <div class="card-header">
                    <div class="card-header-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" y1="17" x2="18" y2="17"/></svg></div>
                    <span class="card-header-title">Ingredienti del passo</span>
                </div>
                <div class="card-body">
                    <div class="ingredienti-list" id="ingredienti-container"></div>
                    <button type="button" class="btn-add-ing" onclick="aggiungiIngrediente()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Aggiungi ingrediente
                    </button>
                </div>
            </div>

            <!-- Foto -->
            <div class="card" style="margin-bottom:24px;">
                <div class="card-header">
                    <div class="card-header-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                    <span class="card-header-title">Foto del passo</span>
                </div>
                <div class="card-body">
                    <div class="file-zone">
                        <input type="file" name="mediaPasso[]" accept="image/*" multiple>
                        <div class="file-zone-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg></div>
                        <p>Clicca o trascina le foto del passo</p>
                        <small>CTRL / CMD per selezionarne più di una</small>
                    </div>
                </div>
            </div>

            <!-- Ultimo passo -->
            <div class="ultimo-box">
                <div class="checkbox-wrap">
                    <input type="checkbox" name="is_ultimo_passo" id="is_ultimo_passo" value="1">
                    <div class="checkbox-mark">
                        <svg width="13" height="10" viewBox="0 0 13 10" fill="none"><path d="M1.5 5L5 8.5L11.5 1.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
                <div class="ultimo-label">
                    <strong>Questo è l'ultimo passo</strong>
                    <span>Spunta per concludere la ricetta dopo aver salvato questo passo.</span>
                </div>
            </div>

            <div class="form-actions">
                <a href="ilMioRistorante.php" class="btn btn-ghost">← Le mie ricette</a>
                <button type="submit" class="btn-submit-step">Salva passo →</button>
            </div>

        </form>
    </div>
</main>

<?php include '../include/footer.php'; ?>

<script>
    const ingredienti = <?php echo json_encode($lista_ingredienti); ?>;
    let ingCounter = 0;
    function buildSelect(name){
        let html=`<select name="${name}"><option value="">Scegli ingrediente</option>`;
        ingredienti.forEach(i=>{html+=`<option value="${i.id}">${i.nome}</option>`;});
        return html+`</select>`;
    }
    function aggiungiIngrediente(){
        ingCounter++;
        const c=document.getElementById('ingredienti-container');
        const r=document.createElement('div');
        r.className='ingrediente-row';
        r.innerHTML=`${buildSelect('ingredienti[id][]')}<input type="text" name="ingredienti[dose][]" placeholder="Dose (es: 200g)"><button type="button" class="btn-remove-ing" onclick="this.closest('.ingrediente-row').remove()" title="Rimuovi"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
        c.appendChild(r);
    }
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>
</body>
</html>