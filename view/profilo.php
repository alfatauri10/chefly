<?php
/* view/profilo.php */
session_start();
require_once '../include/connessione.php';
require_once '../model/user.php';
require_once '../model/ricetta.php';

$id_profilo = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$id_profilo) { header("Location: ../index.php"); exit(); }

$profilo = getUtenteById($conn, $id_profilo);
if (!$profilo) { header("Location: ../index.php"); exit(); }

$statistiche = getStatisticheUtente($conn, $id_profilo);

// Ricette pubblicate con copertine
$ricette_raw = getListaRicetteUtente($conn, $id_profilo);
$ricette = [];
foreach ($ricette_raw as $ricetta) {
    $sql_cop = "SELECT urlMedia FROM mediaRicette WHERE idRicetta = ? AND isCopertina = 1 LIMIT 1";
    $stmt_cop = $conn->prepare($sql_cop);
    $stmt_cop->bind_param("i", $ricetta['id']);
    $stmt_cop->execute();
    $row_cop = $stmt_cop->get_result()->fetch_assoc();
    $stmt_cop->close();
    $ricetta['url_copertina'] = $row_cop['urlMedia'] ?? null;
    $ricette[] = $ricetta;
}

// Stato follow
$id_utente = $_SESSION['user_id'] ?? null;
$is_following = false;
$is_own_profile = ($id_utente && $id_utente == $id_profilo);

if ($id_utente && !$is_own_profile) {
    $sql_f = "SELECT 1 FROM follower WHERE idSegue = ? AND idSeguito = ?";
    $stmt_f = $conn->prepare($sql_f);
    $stmt_f->bind_param("ii", $id_utente, $id_profilo);
    $stmt_f->execute();
    $is_following = $stmt_f->get_result()->num_rows > 0;
    $stmt_f->close();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@<?php echo htmlspecialchars($profilo['username']); ?> — Chefly</title>
    <link rel="stylesheet" href="../css/chefly.css">
    <style>
        /* ── HERO PROFILO ── */
        .profile-hero {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 48px 28px 0;
        }
        .profile-hero-inner {
            max-width: 860px;
            margin: 0 auto;
        }

        /* Avatar + info row */
        .profile-top {
            display: flex;
            align-items: flex-start;
            gap: 32px;
            margin-bottom: 28px;
        }
        .profile-avatar-wrap {
            flex-shrink: 0;
        }
        .profile-avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--border);
            display: block;
        }
        .profile-main { flex: 1; min-width: 0; }
        .profile-username-row {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .profile-username {
            font-family: var(--font-serif);
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--brown);
            line-height: 1.1;
        }
        .profile-fullname {
            font-size: .85rem;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .profile-bio {
            font-size: .9rem;
            color: var(--brown);
            line-height: 1.65;
            max-width: 480px;
            margin-bottom: 14px;
        }

        /* Stats */
        .profile-stats {
            display: flex;
            gap: 32px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-item { text-align: center; }
        .stat-num {
            display: block;
            font-family: var(--font-serif);
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--brown);
            line-height: 1;
        }
        .stat-label {
            display: block;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* Pulsante Follow */
        .btn-follow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: var(--radius-pill);
            font-family: var(--font-sans);
            font-size: .88rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .2s ease;
        }
        .btn-follow--seguire {
            background: var(--brown);
            color: #FFF;
        }
        .btn-follow--seguire:hover { background: #3a2518; transform: translateY(-1px); }
        .btn-follow--following {
            background: transparent;
            color: var(--muted);
            border: 1.5px solid var(--border);
        }
        .btn-follow--following:hover {
            background: #FFF1F0;
            border-color: #FECACA;
            color: #DC2626;
        }
        .btn-own-profile {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: var(--radius-pill);
            font-family: var(--font-sans);
            font-size: .88rem;
            font-weight: 600;
            color: var(--brown);
            border: 1.5px solid var(--border);
            background: transparent;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-own-profile:hover { background: var(--cream); }

        /* ── TAB BAR RICETTE ── */
        .profile-tab-bar {
            display: flex;
            border-top: 1px solid var(--border);
            margin-top: 4px;
        }
        .profile-tab {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 14px 24px;
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--muted);
            text-decoration: none;
            border-top: 2px solid transparent;
            margin-top: -1px;
            transition: color .15s, border-color .15s;
        }
        .profile-tab.active {
            color: var(--brown);
            border-top-color: var(--brown);
        }
        .profile-tab:hover:not(.active) { color: var(--brown); }
        .tab-count {
            background: var(--sand);
            color: var(--muted);
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
        }

        /* ── GRIGLIA RICETTE ── */
        .profile-content {
            max-width: 860px;
            margin: 0 auto;
            padding: 36px 28px 100px;
        }

        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3px;
        }

        .recipe-tile {
            aspect-ratio: 1;
            position: relative;
            overflow: hidden;
            background: var(--sand);
            cursor: pointer;
            text-decoration: none;
            display: block;
        }
        .recipe-tile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .35s ease;
        }
        .recipe-tile:hover img { transform: scale(1.06); }
        .recipe-tile-overlay {
            position: absolute;
            inset: 0;
            background: rgba(26,16,8,0);
            display: flex;
            align-items: flex-end;
            padding: 14px;
            transition: background .25s ease;
        }
        .recipe-tile:hover .recipe-tile-overlay { background: rgba(26,16,8,.55); }
        .recipe-tile-info {
            opacity: 0;
            transform: translateY(8px);
            transition: opacity .25s ease, transform .25s ease;
        }
        .recipe-tile:hover .recipe-tile-info { opacity: 1; transform: translateY(0); }
        .recipe-tile-title {
            font-family: var(--font-serif);
            font-size: .9rem;
            font-weight: 600;
            color: #FFF;
            line-height: 1.3;
            margin-bottom: 4px;
        }
        .recipe-tile-badge {
            display: inline-block;
            font-size: .62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding: 2px 8px;
            border-radius: 20px;
            background: rgba(255,255,255,.2);
            color: #FFF;
            backdrop-filter: blur(4px);
        }
        .recipe-tile-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: #C4C0B8;
            background: var(--sand);
        }
        .recipe-tile-placeholder span {
            font-size: .72rem;
            font-weight: 500;
            color: var(--muted-light);
            text-align: center;
            padding: 0 16px;
        }

        /* Empty state */
        .empty-profile {
            grid-column: 1 / -1;
            text-align: center;
            padding: 72px 20px;
        }
        .empty-profile p {
            font-size: .9rem;
            color: var(--muted);
            margin-top: 16px;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 680px) {
            .profile-hero { padding: 32px 16px 0; }
            .profile-top { flex-direction: column; align-items: center; text-align: center; gap: 18px; }
            .profile-username-row { justify-content: center; }
            .profile-stats { justify-content: center; }
            .profile-bio { margin-left: auto; margin-right: auto; }
            .recipes-grid { grid-template-columns: repeat(3, 1fr); gap: 2px; }
            .profile-content { padding: 24px 0 80px; }
            .profile-tab { padding: 14px 16px; }
        }
    </style>
</head>
<body>
<?php include '../include/header.php'; ?>

<main class="page-content">

    <!-- HERO PROFILO -->
    <div class="profile-hero">
        <div class="profile-hero-inner">

            <div class="profile-top">

                <div class="profile-avatar-wrap">
                    <img src="<?php echo htmlspecialchars($profilo['urlFotoProfilo'] ?? '/img/fotoProfilo.jpg'); ?>"
                         alt="@<?php echo htmlspecialchars($profilo['username']); ?>"
                         class="profile-avatar">
                </div>

                <div class="profile-main">
                    <div class="profile-username-row">
                        <h1 class="profile-username">@<?php echo htmlspecialchars($profilo['username']); ?></h1>

                        <?php if ($is_own_profile): ?>
                            <a href="/view/ilMioRistorante.php" class="btn-own-profile">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Modifica profilo
                            </a>
                        <?php elseif ($id_utente): ?>
                            <button class="btn-follow <?php echo $is_following ? 'btn-follow--following' : 'btn-follow--seguire'; ?>"
                                    id="btnFollow"
                                    onclick="toggleFollow()">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" id="followIcon">
                                    <?php if ($is_following): ?>
                                        <polyline points="20 6 9 17 4 12"/>
                                    <?php else: ?>
                                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                    <?php endif; ?>
                                </svg>
                                <span id="followLabel"><?php echo $is_following ? 'Seguito' : 'Segui'; ?></span>
                            </button>
                        <?php else: ?>
                            <a href="/view/login.php" class="btn-follow btn-follow--seguire">Segui</a>
                        <?php endif; ?>
                    </div>

                    <p class="profile-fullname"><?php echo htmlspecialchars($profilo['nome'] . ' ' . $profilo['cognome']); ?></p>

                    <?php if (!empty($profilo['biografia'])): ?>
                        <p class="profile-bio"><?php echo nl2br(htmlspecialchars($profilo['biografia'])); ?></p>
                    <?php endif; ?>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-num"><?php echo $statistiche['num_ricette']; ?></span>
                            <span class="stat-label">Ricette</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-num" id="statFollower"><?php echo $statistiche['num_follower']; ?></span>
                            <span class="stat-label">Follower</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-num"><?php echo $statistiche['num_seguiti']; ?></span>
                            <span class="stat-label">Seguiti</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab bar -->
            <div class="profile-tab-bar">
                <span class="profile-tab active">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Ricette
                    <span class="tab-count"><?php echo count($ricette); ?></span>
                </span>
            </div>
        </div>
    </div>

    <!-- GRIGLIA RICETTE -->
    <div class="profile-content">
        <div class="recipes-grid">
            <?php if (empty($ricette)): ?>
                <div class="empty-profile">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" style="color:#D6CFC4;display:block;margin:0 auto;">
                        <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                        <line x1="6" y1="17" x2="18" y2="17"/>
                    </svg>
                    <p>Nessuna ricetta pubblicata ancora.</p>
                </div>
            <?php else: ?>
                <?php foreach ($ricette as $r): ?>
                    <a class="recipe-tile" href="/view/ricetta.php?id=<?php echo $r['id']; ?>">
                        <?php if (!empty($r['url_copertina'])): ?>
                            <img src="../<?php echo htmlspecialchars($r['url_copertina']); ?>"
                                 alt="<?php echo htmlspecialchars($r['titolo']); ?>"
                                 loading="lazy">
                            <div class="recipe-tile-overlay">
                                <div class="recipe-tile-info">
                                    <div class="recipe-tile-title"><?php echo htmlspecialchars($r['titolo']); ?></div>
                                    <span class="recipe-tile-badge"><?php echo ucfirst($r['difficolta']); ?></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="recipe-tile-placeholder">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round">
                                    <path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/>
                                    <line x1="6" y1="17" x2="18" y2="17"/>
                                </svg>
                                <span><?php echo htmlspecialchars($r['titolo']); ?></span>
                            </div>
                            <div class="recipe-tile-overlay">
                                <div class="recipe-tile-info">
                                    <div class="recipe-tile-title"><?php echo htmlspecialchars($r['titolo']); ?></div>
                                    <span class="recipe-tile-badge"><?php echo ucfirst($r['difficolta']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</main>

<?php include '../include/footer.php'; ?>

<script>
    let isFollowing = <?php echo json_encode($is_following); ?>;
    let followerCount = <?php echo (int)$statistiche['num_follower']; ?>;
    const idProfilo = <?php echo $id_profilo; ?>;

    function toggleFollow() {
        const url = isFollowing
            ? '../controller/followController.php'
            : '../controller/followController.php';

        fetch('../controller/followController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id_seguito=' + idProfilo + '&azione=' + (isFollowing ? 'unfollow' : 'follow')
        }).then(r => r.json()).then(data => {
            if (!data.success) return;
            isFollowing = !isFollowing;
            followerCount += isFollowing ? 1 : -1;

            const btn   = document.getElementById('btnFollow');
            const label = document.getElementById('followLabel');
            const icon  = document.getElementById('followIcon');
            const stat  = document.getElementById('statFollower');

            btn.className = 'btn-follow ' + (isFollowing ? 'btn-follow--following' : 'btn-follow--seguire');
            label.textContent = isFollowing ? 'Seguito' : 'Segui';
            icon.innerHTML = isFollowing
                ? '<polyline points="20 6 9 17 4 12"/>'
                : '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>';
            if (stat) stat.textContent = followerCount;
        }).catch(() => {});
    }
</script>
<script><?php include_once '../js/dropDownMenu.js'; ?></script>
</body>
</html>