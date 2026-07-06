<?php include("conexao.php");

$q = trim($_GET['q'] ?? '');

$sql = "SELECT pr.*, COUNT(p.id) AS total_produtos
        FROM produtores pr
        LEFT JOIN produtos p ON p.produtor_id = pr.id";
if ($q) {
    $sql .= " WHERE pr.nome LIKE ? OR pr.fazenda LIKE ? OR pr.regiao LIKE ? OR pr.estado LIKE ?";
}
$sql .= " GROUP BY pr.id ORDER BY pr.nome ASC";

$stmt = mysqli_prepare($conexao, $sql);
if ($q) {
    $like = "%$q%";
    mysqli_stmt_bind_param($stmt, 'ssss', $like, $like, $like, $like);
}
mysqli_stmt_execute($stmt);
$produtores = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
$total = count($produtores);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Nossos Produtores — Origem Brasil</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
        --soil: #1A1208; --green: #2C4A2E; --green2: #3D6B40;
        --clay: #C4531F; --cream: #F5F2ED; --white: #fff;
        --border: #E5DED4; --ink: #1C1208; --dust: #9E9080;
    }

    /* ── PAGE HEADER ── */
    .ph {
        background: var(--white);
        border-bottom: 1px solid var(--border);
        padding: 52px 40px 36px;
    }
    .ph-inner {
        max-width: 1060px; margin: 0 auto;
        display: flex; align-items: flex-end; justify-content: space-between; gap: 24px;
    }
    .ph-eyebrow {
        font-size: 11px; font-weight: 600; letter-spacing: .13em;
        text-transform: uppercase; color: var(--clay); margin-bottom: 10px;
    }
    .ph h1 {
        font-family: 'Playfair Display', serif;
        font-size: 38px; font-weight: 700; color: var(--ink); line-height: 1.1;
    }
    .ph h1 em { font-style: italic; color: var(--green); }
    .ph-sub {
        font-size: 14px; color: var(--dust); margin-top: 10px; max-width: 420px; line-height: 1.7;
    }
    .ph-count {
        flex-shrink: 0; text-align: right;
        font-family: 'Playfair Display', serif;
        font-size: 52px; font-weight: 700; color: var(--border); line-height: 1;
    }
    .ph-count span { display: block; font-family: 'Inter', sans-serif; font-size: 11px; font-weight: 600; color: var(--dust); letter-spacing: .08em; text-transform: uppercase; margin-top: 4px; }

    /* ── SEARCH BAR ── */
    .search-strip {
        background: var(--cream); border-bottom: 1px solid var(--border);
        padding: 16px 40px;
    }
    .search-strip form {
        max-width: 1060px; margin: 0 auto;
        display: flex; gap: 10px; align-items: center;
    }
    .search-strip input {
        flex: 1; max-width: 380px;
        padding: 10px 16px; border: 1.5px solid var(--border); border-radius: 8px;
        font-family: 'Inter', sans-serif; font-size: 13px; color: var(--ink); background: var(--white);
        outline: none; transition: border-color .15s;
    }
    .search-strip input:focus { border-color: var(--green); }
    .search-strip button {
        padding: 10px 20px; border: none; border-radius: 8px;
        background: var(--green); color: #fff;
        font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer;
        transition: background .15s;
    }
    .search-strip button:hover { background: var(--green2); }
    .search-strip .q-result {
        font-size: 12px; color: var(--dust); margin-left: 4px;
    }
    .search-strip a.clear-q {
        font-size: 12px; color: var(--clay); text-decoration: none; margin-left: 2px;
    }

    /* ── MAIN CONTENT ── */
    .pg-body { max-width: 1060px; margin: 0 auto; padding: 36px 40px 80px; }

    /* ── DIRECTORY LIST ── */
    .dir-list { display: flex; flex-direction: column; gap: 0; }

    .dir-item {
        display: flex; align-items: center; gap: 20px;
        padding: 22px 24px;
        background: var(--white);
        border: 1px solid var(--border);
        border-top: none;
        text-decoration: none; color: inherit;
        transition: background .15s, box-shadow .15s;
        position: relative;
    }
    .dir-list .dir-item:first-child { border-top: 1px solid var(--border); border-radius: 12px 12px 0 0; }
    .dir-list .dir-item:last-child  { border-radius: 0 0 12px 12px; }
    .dir-list .dir-item:only-child  { border-radius: 12px; border-top: 1px solid var(--border); }
    .dir-item:hover { background: #fdfcfa; box-shadow: inset 3px 0 0 var(--green); }

    .dir-n {
        font-family: 'Playfair Display', serif;
        font-size: 22px; font-weight: 700; color: var(--border);
        min-width: 36px; text-align: right; flex-shrink: 0;
        transition: color .15s;
    }
    .dir-item:hover .dir-n { color: var(--green); }

    .dir-avatar {
        width: 52px; height: 52px; border-radius: 50%; flex-shrink: 0;
        border: 2px solid var(--border); overflow: hidden;
        background: linear-gradient(135deg, #e8ddd4, #d4c8bc);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: var(--dust);
        transition: border-color .15s;
    }
    .dir-item:hover .dir-avatar { border-color: var(--green); }
    .dir-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .dir-main { flex: 1; min-width: 0; }
    .dir-name {
        font-family: 'Playfair Display', serif;
        font-size: 17px; font-weight: 600; color: var(--ink);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        margin-bottom: 3px;
    }
    .dir-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .dir-fazenda {
        font-size: 12px; color: var(--dust);
        display: flex; align-items: center; gap: 4px;
    }
    .dir-fazenda svg { width: 11px; height: 11px; stroke: var(--dust); fill: none; stroke-width: 2; }

    .dir-pills { display: flex; align-items: center; gap: 8px; margin-left: auto; flex-shrink: 0; }
    .chip {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 5px;
        font-size: 11px; font-weight: 600; white-space: nowrap;
    }
    .chip-region {
        background: var(--cream); color: var(--dust); border: 1px solid var(--border);
    }
    .chip-region svg { width: 10px; height: 10px; stroke: var(--dust); fill: none; stroke-width: 2; }
    .chip-state {
        background: #EBF5EC; color: var(--green);
        border: 1px solid rgba(44,74,46,.15);
        font-size: 10px; letter-spacing: .04em;
    }
    .chip-prods {
        background: var(--cream); color: var(--ink); border: 1px solid var(--border);
    }

    .dir-arrow {
        width: 32px; height: 32px; border-radius: 50%;
        border: 1.5px solid var(--border);
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        transition: all .15s;
    }
    .dir-arrow svg { width: 13px; height: 13px; stroke: var(--dust); fill: none; stroke-width: 2.5; }
    .dir-item:hover .dir-arrow { background: var(--green); border-color: var(--green); }
    .dir-item:hover .dir-arrow svg { stroke: #fff; }

    /* ── DIVIDER between items: thin terracotta accent on hover ── */
    .dir-item + .dir-item { border-top: 1px solid var(--border); }

    /* ── EMPTY ── */
    .dir-empty {
        text-align: center; padding: 72px 24px;
        background: var(--white); border: 1px solid var(--border); border-radius: 12px;
    }
    .dir-empty svg { width: 44px; height: 44px; stroke: var(--border); fill: none; stroke-width: 1.5; margin-bottom: 16px; }
    .dir-empty h3 { font-family: 'Playfair Display', serif; font-size: 20px; color: var(--ink); margin-bottom: 8px; }
    .dir-empty p { font-size: 13px; color: var(--dust); }

    /* ── CTA ── */
    .cta-block {
        margin-top: 40px;
        background: var(--soil);
        border-radius: 14px; padding: 36px 40px;
        display: flex; align-items: center; justify-content: space-between; gap: 24px;
    }
    .cta-block .cta-txt h3 {
        font-family: 'Playfair Display', serif; font-size: 20px; color: #fff; margin-bottom: 6px;
    }
    .cta-block .cta-txt p { font-size: 13px; color: rgba(255,255,255,.55); max-width: 380px; line-height: 1.7; }
    .cta-block a {
        flex-shrink: 0; display: inline-flex; align-items: center; gap: 8px;
        background: var(--clay); color: #fff;
        padding: 13px 26px; border-radius: 8px;
        font-size: 13px; font-weight: 700; text-decoration: none;
        transition: background .15s; white-space: nowrap;
    }
    .cta-block a:hover { background: #b04319; }
    .cta-block a svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 2.5; }

    @media(max-width: 700px) {
        .ph { padding: 36px 20px 28px; }
        .ph-inner { flex-direction: column; align-items: flex-start; gap: 8px; }
        .ph-count { display: none; }
        .ph h1 { font-size: 28px; }
        .search-strip { padding: 14px 20px; }
        .pg-body { padding: 24px 20px 60px; }
        .dir-item { padding: 16px; gap: 12px; }
        .dir-n { display: none; }
        .dir-pills { display: none; }
        .cta-block { flex-direction: column; padding: 28px 20px; }
    }
    </style>
</head>
<body>
<?php include("includes/header.php"); ?>

<!-- PAGE HEADER -->
<div class="ph">
    <div class="ph-inner">
        <div>
            <div class="ph-eyebrow">Do campo à sua mesa</div>
            <h1>Quem está por trás<br>de cada <em>produto</em></h1>
            <p class="ph-sub">Famílias, cooperativas e pequenos agricultores que cultivam com cuidado e entregam com orgulho.</p>
        </div>
        <div class="ph-count">
            <?php echo $total ?>
            <span>produtores parceiros</span>
        </div>
    </div>
</div>

<!-- SEARCH -->
<div class="search-strip">
    <form method="get" action="produtores.php">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Buscar por nome, fazenda, região ou estado…">
        <button type="submit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Buscar
        </button>
        <?php if ($q): ?>
        <span class="q-result">
            <?php echo $total ?> resultado<?php echo $total != 1 ? 's' : ''; ?> para "<strong><?php echo htmlspecialchars($q); ?></strong>"
            &nbsp;<a href="produtores.php" class="clear-q">✕ limpar</a>
        </span>
        <?php endif; ?>
    </form>
</div>

<!-- DIRECTORY -->
<div class="pg-body">
    <?php if (empty($produtores)): ?>
    <div class="dir-empty">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <h3>Nenhum produtor encontrado</h3>
        <p><?php echo $q ? 'Tente buscar por outro termo.' : 'Ainda não há produtores cadastrados.'; ?></p>
    </div>
    <?php else: ?>
    <div class="dir-list">
        <?php foreach ($produtores as $i => $p): ?>
        <a href="produtor.php?id=<?php echo $p['id']; ?>" class="dir-item">

            <!-- número -->
            <div class="dir-n"><?php echo str_pad($i+1, 2, '0', STR_PAD_LEFT); ?></div>

            <!-- avatar -->
            <div class="dir-avatar">
                <?php if (!empty($p['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($p['foto']); ?>"
                         alt="<?php echo htmlspecialchars($p['nome']); ?>"
                         onerror="this.style.display='none';this.parentElement.textContent='<?php echo mb_strtoupper(mb_substr($p['nome'],0,1)); ?>'">
                <?php else: ?>
                    <?php echo mb_strtoupper(mb_substr($p['nome'],0,1)); ?>
                <?php endif; ?>
            </div>

            <!-- nome + fazenda -->
            <div class="dir-main">
                <div class="dir-name"><?php echo htmlspecialchars($p['nome']); ?></div>
                <div class="dir-meta">
                    <?php if ($p['fazenda']): ?>
                    <span class="dir-fazenda">
                        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                        <?php echo htmlspecialchars($p['fazenda']); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- chips -->
            <div class="dir-pills">
                <?php if ($p['regiao']): ?>
                <span class="chip chip-region">
                    <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?php echo htmlspecialchars($p['regiao']); ?>
                </span>
                <?php endif; ?>
                <?php if ($p['estado']): ?>
                <span class="chip chip-state"><?php echo htmlspecialchars($p['estado']); ?></span>
                <?php endif; ?>
                <?php if ($p['total_produtos'] > 0): ?>
                <span class="chip chip-prods"><?php echo $p['total_produtos']; ?> produto<?php echo $p['total_produtos'] != 1 ? 's' : ''; ?></span>
                <?php endif; ?>
            </div>

            <!-- arrow -->
            <div class="dir-arrow">
                <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </div>

        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CTA -->
    <div class="cta-block">
        <div class="cta-txt">
            <h3>Você também produz com cuidado?</h3>
            <p>Junte-se à Origem Brasil e leve seus produtos artesanais para consumidores em todo o país.</p>
        </div>
        <a href="seja_produtor.php">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Seja um Produtor
        </a>
    </div>
</div>

<?php include("includes/footer.php"); ?>
</body>
</html>
