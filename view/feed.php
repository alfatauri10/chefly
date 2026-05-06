<?php
/* view/feed.php — Feed ricette degli utenti seguiti */
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once '../include/connessione.php';
require_once '../model/ricetta.php';

$id_utente = $_SESSION['user_id'];

// Ricette degli utenti seguiti
$sql_feed = "SELECT r.id, r.titolo, r.descrizione, r.difficolta, r.dataCreazione,
                    u.id AS id_autore, u.username AS nome_autore, u.urlFotoProfilo AS foto_autore,
                    m.urlMedia AS url_copertina
             FROM ricette r
             JOIN utenti u ON r.idCreatore = u.id
             JOIN follower f ON f.idSeguito = r.idCreatore
             LEFT JOIN mediaRicette m ON r.id = m.idRicetta AND m.isCopertina = 1
             WHERE f.idSegue = ?
             GROUP BY r.id
             ORDER BY r.dataCreazione DESC
             LIMIT 40";
$stmt = $conn->prepare($sql_feed);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();
$feed = [];
while ($row = $result->fetch_assoc()) { $feed[] = $row; }
$stmt->close();

// Suggerimenti: utenti popolari che non segui ancora
$sql_sug = "SELECT u.id, u.username, u.urlFotoProfilo,
                   COUNT(f2.idSegue) AS num_follower
            FROM utenti u
            LEFT JOIN follower f2 ON f2.idSeguito = u.id
            WHERE u.id != ?
              AND u.id NOT IN (
                  SELECT idSeguito FROM follower WHERE idSegue = ?
              )
            GROUP BY u.id
            ORDER BY num_follower DESC
            LIMIT 6";
$stmt_s = $conn->prepare($sql_sug);
$stmt_s->bind_param("ii", $id_utente, $id_utente);
$stmt_s->execute();
$suggeriti = $stmt_s->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_s->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il tuo feed — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        /* ── LAYOUT ── */
        .feed-layout {
            max-width: 1080px;
            margin: 0 auto;
            padding: 40px 28px 100px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 40px;
            align-items: start;
        }

        /* ── HEADER ── */
        .feed-header {
            margin-bottom: 28px;
        }
        .feed-eyebrow {
            font-size: .68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: var(--caramel);
            margin-bottom: 6px;
        }
        .feed-title {
            font-family: var(--font-serif);
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--brown);
        }

        /* ── CARD RICETTA FEED ── */
        .feed-list { display: flex; flex-direction: column; gap: 18px; }

        .feed-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: flex;
            gap: 0;
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .feed-card:hover {
            box-shadow: 0 8px 28px rgba(26,16,8,.09);
            transform: translateY(-2px);
        }
        .feed-card-cover {
            width: 180px;
            flex-shrink: 0;
            background: var(--sand);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #C4C0B8;
        }
        .feed-card-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .3s ease;
        }
        .feed-card:hover .feed-card-cover img { transform: scale(1.04); }
        .feed-card-body { flex: 1; padding: 20px 22px; display: flex; flex-direction: column; }
        .feed-card-author {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .feed-author-avatar {
            width: 28px; height: 28px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
        }
        .feed-author-name {
            font-size: .78rem;
            font-weight: 700;
            color: var(--brown);
            text-decoration: none;
        }
        .feed-author-name:hover { color: var(--caramel); }
        .feed-card-date { font-size: .7rem; color: var(--muted-light); margin-left: auto; }
        .feed-card-title {
            font-family: var(--font-serif);
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--brown);
            line-height: 1.3;
            margin-bottom: 8px;
        }
        .feed-card-desc {
            font-size: .82rem;
            color: var(--muted);
            line-height: 1.55;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
            margin-bottom: 12px;
        }
        .feed-card-footer {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        /* ── EMPTY FEED ── */
        .empty-feed {
            background: var(--white);
            border: 1.5px dashed var(--border);
            border-radius: var(--radius-lg);
            padding: 64px 24px;
            text-align: center;
        }
        .empty-feed h3 {
            font-family: var(--font-serif);
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--brown);
            margin-bottom: 8px;
            margin-top: 16px;
        }
        .empty-feed p { font-size: .88rem; color: var(--muted); line-height: 1.6; margin-bottom: 0; }

        /* ── SIDEBAR ── */
        .feed-sidebar { position: sticky; top: 110px; }

        .sidebar-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            overflow: hidden;
        }
        .sidebar-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-light);
        }
        .sidebar-card-title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--muted);
        }
        .sidebar-card-body { padding: 8px 0; }
        .suggest-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            transition: background .12s;
        }
        .suggest-item:hover { background: var(--cream); }
        .suggest-avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
            flex-shrink: 0;
        }
        .suggest-info { flex: 1; min-width: 0; }
        .suggest-username {
            font-size: .84rem;
            font-weight: 700;
            color: var(--brown);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-decoration: none;
            display: block;
            margin-bottom: 2px;
        }
        .suggest-username:hover { color: var(--caramel); }
        .suggest-followers { font-size: .7rem; color: var(--muted-light); }
        .btn-follow-small {
            padding: 5px 14px;
            background: var(--brown);
            color: #FFF;
            border: none;
            border-radius: 20px;
            font-family: var(--font-sans);
            font-size: .72rem;
            font-weight: 700;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .15s, transform .1s;
            text-decoration: none;
        }
        .btn-follow-small:hover { background: #3a2518; }
        .btn-follow-small.done {
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 860px) {
            .feed-layout { grid-template-columns: 1fr; }
            .feed-sidebar { position: static; }
        }
        @media (max-width: 560px) {
            .feed-card { flex-direction: column; }
            .feed-card-cover { width: 100%; height: 200px; }
            .feed-layout { padding: 24px 16px 80px; }
        }
    </style>
</head>
<body>
<?php include '../include/header.php'; ?>

<main class="page-content">
    <div class="feed-layout">

        <!-- FEED PRINCIPALE -->
        <div>
            <div class="feed-header">
                <p class="feed-eyebrow">Aggiornamenti</p>
                <h1 class="feed-title">Il tuo feed</h1>
            </div>

            <?php if (empty($feed)): ?>
                <div class="empty-feed">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" style="color:#D6CFC4;display:block;margin:0 auto;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <h3>Il feed è vuoto</h3>
                    <p>Inizia a seguire altri cuochi per vedere le loro ricette qui.<br>Scopri chi segue la community nella colonna a destra.</p>
                </div>
            <?php else: ?>
                <div class="feed-list">
                    <?php foreach ($feed as $r): ?>
                        <a class="feed-card" href="/view/ricetta.php?id=<?php echo $r['id']; ?>">
                            <div class="feed-card-cover">
                                <?php if (!empty($r['url_copertina'])): ?>
                                    <img src="../<?php echo htmlspecialchars($r['url_copertina']); ?>"
                                         alt="<?php echo htmlspecialchars($r['titolo']); ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round">
                                        <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                                        <line x1="6" y1="17" x2="18" y2="17"/>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="feed-card-body">
                                <div class="feed-card-author">
                                    <img src="<?php echo htmlspecialchars($r['foto_autore'] ?? '/img/fotoProfilo.jpg'); ?>"
                                         alt="<?php echo htmlspecialchars($r['nome_autore']); ?>"
                                         class="feed-author-avatar">
                                    <a href="/view/profilo.php?id=<?php echo $r['id_autore']; ?>"
                                       class="feed-author-name"
                                       onclick="event.stopPropagation();">
                                        @<?php echo htmlspecialchars($r['nome_autore']); ?>
                                    </a>
                                    <span class="feed-card-date"><?php echo date('d M Y', strtotime($r['dataCreazione'])); ?></span>
                                </div>
                                <div class="feed-card-title"><?php echo htmlspecialchars($r['titolo']); ?></div>
                                <?php if (!empty($r['descrizione'])): ?>
                                    <p class="feed-card-desc"><?php echo htmlspecialchars($r['descrizione']); ?></p>
                                <?php endif; ?>
                                <div class="feed-card-footer">
                                    <span class="badge badge--<?php echo strtolower($r['difficolta']); ?>">
                                        <?php echo ucfirst($r['difficolta']); ?>
                                    </span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- SIDEBAR: suggerimenti da seguire -->
        <aside class="feed-sidebar">
            <?php if (!empty($suggeriti)): ?>
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <div class="sidebar-card-title">Cuochi da scoprire</div>
                    </div>
                    <div class="sidebar-card-body">
                        <?php foreach ($suggeriti as $u): ?>
                            <div class="suggest-item">
                                <a href="/view/profilo.php?id=<?php echo $u['id']; ?>">
                                    <img src="<?php echo htmlspecialchars($u['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>"
                                         alt="<?php echo htmlspecialchars($u['username']); ?>"
                                         class="suggest-avatar">
                                </a>
                                <div class="suggest-info">
                                    <a href="/view/profilo.php?id=<?php echo $u['id']; ?>" class="suggest-username">
                                        @<?php echo htmlspecialchars($u['username']); ?>
                                    </a>
                                    <div class="suggest-followers"><?php echo $u['num_follower']; ?> follower</div>
                                </div>
                                <button class="btn-follow-small"
                                        data-id="<?php echo $u['id']; ?>"
                                        onclick="quickFollow(this, <?php echo $u['id']; ?>)">
                                    Segui
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div style="margin-top:16px;padding:16px 20px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);text-align:center;">
                <a href="/index.php" style="font-size:.78rem;color:var(--caramel);font-weight:600;text-decoration:none;">
                    Esplora tutte le ricette →
                </a>
            </div>
        </aside>

    </div>
</main>

<?php include '../include/footer.php'; ?>

<script>
    function quickFollow(btn, id) {
        fetch('../controller/followController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_seguito=' + id + '&azione=follow'
        }).then(r => r.json()).then(data => {
            if (data.success) {
                btn.textContent = 'Seguito';
                btn.classList.add('done');
                btn.disabled = true;
            }
        }).catch(() => {});
    }
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>
</body>
</html>