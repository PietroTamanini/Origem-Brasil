<?php
include("conexao.php");
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit; }

$stmt = mysqli_prepare($conexao,"SELECT * FROM produtores WHERE id=?");
mysqli_stmt_bind_param($stmt,'i',$id);
mysqli_stmt_execute($stmt);
$prod = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$prod) { header("Location: index.php"); exit; }

$sp = mysqli_prepare($conexao,
    "SELECT p.*, COALESCE(ROUND(AVG(a.nota),1),0) AS media_nota
     FROM produtos p LEFT JOIN avaliacoes a ON a.produto_id=p.id
     WHERE p.produtor_id=? GROUP BY p.id ORDER BY p.destaque DESC, p.id ASC"
);
mysqli_stmt_bind_param($sp,'i',$id);
mysqli_stmt_execute($sp);
$produtos = mysqli_fetch_all(mysqli_stmt_get_result($sp), MYSQLI_ASSOC);

$total_estoque = array_sum(array_column($produtos,'estoque'));
$em_destaque   = count(array_filter($produtos, fn($p)=>$p['destaque']));
$categorias    = array_unique(array_column($produtos,'categoria'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($prod['nome']); ?> — Origem Brasil</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
*,*::before,*::after{box-sizing:border-box}
:root{
  --green:#2C4A2E;--green2:#3D6B40;--clay:#C4531F;
  --cream:#F5F2ED;--white:#fff;--border:#E5DED4;
  --ink:#1C1208;--dust:#9E9080;--r:14px;
}

/* ── HERO ── */
.hero{
  background:
    linear-gradient(160deg,rgba(8,24,10,.88),rgba(28,51,32,.82) 50%,rgba(44,74,46,.82) 100%),
    url('imagens/bg-campo1.png') center/cover no-repeat;
  padding:56px 40px 72px;
  color:#fff;
  position:relative;
}
.hero-inner{max-width:1060px;margin:0 auto;display:flex;align-items:flex-start;gap:36px;flex-wrap:wrap;position:relative;z-index:1;}

.hero-avatar{
  width:110px;height:110px;border-radius:50%;flex-shrink:0;
  border:3px solid rgba(255,255,255,.25);overflow:hidden;
  background:linear-gradient(135deg,#C4531F,#e8834e);
  display:flex;align-items:center;justify-content:center;
  box-shadow:0 8px 32px rgba(0,0,0,.45);
}
.hero-avatar img{width:100%;height:100%;object-fit:cover;}
.hero-avatar .ini{font-family:'Playfair Display',serif;font-size:42px;font-weight:700;color:#fff;}

.hero-info{flex:1;min-width:220px;}
.hero-eyebrow{
  display:inline-flex;align-items:center;gap:6px;
  font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.13em;
  color:#a3d9ab;background:rgba(163,217,171,.1);border:1px solid rgba(163,217,171,.2);
  padding:4px 12px;border-radius:20px;margin-bottom:12px;
}
.hero-info h1{
  font-family:'Playfair Display',serif;
  font-size:32px;font-weight:700;color:#fff;line-height:1.15;margin-bottom:14px;
}
.hero-chips{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
.hchip{
  display:inline-flex;align-items:center;gap:6px;
  background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);
  color:rgba(255,255,255,.85);font-size:12px;padding:5px 13px;border-radius:20px;
}
.hchip svg{width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;}
.hero-stats{
  display:inline-flex;background:rgba(255,255,255,.07);
  border:1px solid rgba(255,255,255,.12);border-radius:12px;overflow:hidden;
}
.hstat{padding:13px 22px;text-align:center;border-right:1px solid rgba(255,255,255,.08);}
.hstat:last-child{border-right:none;}
.hstat strong{display:block;font-size:21px;font-weight:800;color:#a3d9ab;line-height:1;}
.hstat span{font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.05em;margin-top:3px;display:block;}

/* ── TRANSITION: sem wave, só sombra interna ── */
.hero::after{
  content:'';
  position:absolute;bottom:0;left:0;right:0;height:48px;
  background:linear-gradient(to bottom,transparent,rgba(8,24,10,.28));
  pointer-events:none;
}

/* ── BODY ── */
.pg-wrap{background:var(--cream);min-height:100vh;}
.pg-inner{
  max-width:1060px;margin:0 auto;
  padding:40px 40px 80px;
  display:grid;grid-template-columns:1fr 310px;gap:40px;align-items:start;
}

/* ── CARDS ── */
.pc{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:20px;}
.pc-header{
  background:#FDFAF5;border-bottom:1px solid var(--border);
  padding:15px 22px;display:flex;align-items:center;gap:10px;
  font-size:14px;font-weight:700;color:var(--ink);
}
.pc-header svg{width:15px;height:15px;stroke:var(--green);fill:none;stroke-width:2;}
.pc-body{padding:22px;}

/* ── HISTÓRIA ── */
.historia-text{font-size:14px;line-height:1.95;color:#3A3028;}

/* ── PRODUCTS GRID ── */
.prods-header{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:20px;padding-bottom:14px;border-bottom:2px solid var(--border);
}
.prods-header h3{
  font-family:'Playfair Display',serif;font-size:19px;font-weight:600;color:var(--ink);
}
.prods-header .pg-info{font-size:12px;color:var(--dust);}

.pg-grid{
  display:grid;grid-template-columns:repeat(3,1fr);gap:16px;
  min-height:320px;
}

/* product card (local override) */
.pg-grid .product-card{margin:0;}

/* ── PAGINATION ── */
.pagination{
  display:flex;align-items:center;justify-content:center;gap:10px;
  margin-top:24px;
}
.pg-btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 18px;border:1.5px solid var(--border);background:var(--white);
  border-radius:9px;font-size:12px;font-weight:600;color:var(--ink);
  cursor:pointer;transition:all .15s;
}
.pg-btn:hover:not(:disabled){border-color:var(--green);color:var(--green);background:#f0f7f0;}
.pg-btn:disabled{opacity:.35;cursor:default;}
.pg-btn svg{width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.5;}
.pg-dots{
  display:flex;gap:4px;align-items:center;
}
.pg-dot{
  width:8px;height:8px;border-radius:50%;background:var(--border);cursor:pointer;transition:all .15s;
}
.pg-dot.on{background:var(--green);width:22px;border-radius:4px;}

/* ── SIDEBAR ── */
.side-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:18px;}
.side-header{
  background:#FDFAF5;border-bottom:1px solid var(--border);
  padding:13px 18px;font-size:12px;font-weight:700;color:var(--ink);
  display:flex;align-items:center;gap:7px;
}
.side-header svg{width:13px;height:13px;stroke:var(--green);fill:none;stroke-width:2;}
.info-row{
  display:flex;justify-content:space-between;align-items:flex-start;gap:8px;
  padding:10px 18px;border-bottom:1px solid #FAF6F0;font-size:13px;
}
.info-row:last-child{border-bottom:none;}
.info-row .lbl{color:var(--dust);flex-shrink:0;}
.info-row .val{font-weight:600;color:var(--ink);text-align:right;}

.mini-prod{
  display:flex;align-items:center;gap:11px;padding:11px 16px;
  border-bottom:1px solid #FAF6F0;text-decoration:none;transition:background .15s;
}
.mini-prod:hover{background:#FDFAF5;}
.mini-prod:last-child{border-bottom:none;}
.mini-prod img{width:38px;height:38px;border-radius:8px;object-fit:cover;border:1px solid var(--border);flex-shrink:0;}
.mini-prod .mn{font-size:12px;font-weight:600;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mini-prod .mp{font-size:11px;color:var(--green);font-weight:700;margin-top:2px;}

.btn-maps{
  display:flex;align-items:center;justify-content:center;gap:7px;
  margin:14px 18px 16px;padding:10px;
  background:var(--green);color:#fff;border-radius:9px;
  font-size:12px;font-weight:700;text-decoration:none;transition:background .15s;
}
.btn-maps:hover{background:var(--green2);color:#fff;}
.btn-maps svg{width:12px;height:12px;stroke:#fff;fill:none;stroke-width:2;}

.breadcrumb{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--dust);margin-bottom:24px;}
.breadcrumb a{color:var(--dust);text-decoration:none;}
.breadcrumb a:hover{color:var(--green);}

@media(max-width:860px){
  .hero{padding:36px 20px 56px;}
  .hero-info h1{font-size:24px;}
  .pg-inner{grid-template-columns:1fr;padding:24px 18px 60px;}
  .pg-grid{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:480px){.pg-grid{grid-template-columns:1fr;}}
</style>
</head>
<body data-logado="<?php echo isset($_SESSION['id'])?'1':'0'; ?>">
<?php include("includes/header.php"); ?>

<!-- HERO -->
<div class="hero">
  <div class="hero-inner">
    <div class="hero-avatar">
      <?php if (!empty($prod['foto'])): ?>
        <img src="<?php echo htmlspecialchars($prod['foto']); ?>"
             alt="<?php echo htmlspecialchars($prod['nome']); ?>"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="ini" style="display:none"><?php echo mb_strtoupper(mb_substr($prod['nome'],0,1)); ?></div>
      <?php else: ?>
        <div class="ini"><?php echo mb_strtoupper(mb_substr($prod['nome'],0,1)); ?></div>
      <?php endif; ?>
    </div>

    <div class="hero-info">
      <div class="hero-eyebrow">
        <svg viewBox="0 0 24 24" style="width:11px;height:11px;stroke:#a3d9ab;fill:none;stroke-width:2.5;"><polyline points="20 6 9 17 4 12"/></svg>
        Produtor Verificado
      </div>
      <h1><?php echo htmlspecialchars($prod['nome']); ?></h1>
      <div class="hero-chips">
        <?php if ($prod['fazenda']): ?>
        <span class="hchip">
          <svg viewBox="0 0 24 24"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <?php echo htmlspecialchars($prod['fazenda']); ?>
        </span>
        <?php endif; ?>
        <?php if ($prod['regiao']): ?>
        <span class="hchip">
          <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <?php echo htmlspecialchars($prod['regiao']); ?>
        </span>
        <?php endif; ?>
        <?php if ($prod['estado']): ?>
        <span class="hchip">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          <?php echo htmlspecialchars($prod['estado']); ?>
        </span>
        <?php endif; ?>
      </div>
      <div class="hero-stats">
        <div class="hstat"><strong><?php echo count($produtos); ?></strong><span>produto<?php echo count($produtos)!=1?'s':''; ?></span></div>
        <?php if ($em_destaque > 0): ?>
        <div class="hstat"><strong><?php echo $em_destaque; ?></strong><span>destaque<?php echo $em_destaque!=1?'s':''; ?></span></div>
        <?php endif; ?>
        <?php if (count($categorias) > 0): ?>
        <div class="hstat"><strong><?php echo count($categorias); ?></strong><span>categoria<?php echo count($categorias)!=1?'s':''; ?></span></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- BODY -->
<div class="pg-wrap">
<div class="pg-inner">

  <!-- COLUNA PRINCIPAL -->
  <div>
    <div class="breadcrumb">
      <a href="index.php">Home</a> ›
      <a href="produtores.php">Produtores</a> ›
      <span><?php echo htmlspecialchars($prod['nome']); ?></span>
    </div>

    <!-- História -->
    <div class="pc">
      <div class="pc-header">
        <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        História do Produtor
      </div>
      <div class="pc-body">
        <p class="historia-text"><?php echo nl2br(htmlspecialchars($prod['historia'] ?? 'Em breve.')); ?></p>
      </div>
    </div>

    <!-- Produtos com paginação -->
    <?php if (!empty($produtos)): ?>
    <div>
      <div class="prods-header">
        <h3>Produtos de <?php echo htmlspecialchars(explode(' ',$prod['nome'])[0]); ?></h3>
        <span class="pg-info" id="pg-info"></span>
      </div>

      <!-- Grid paginado -->
      <div class="pg-grid" id="pg-grid">
        <?php foreach ($produtos as $p): ?>
        <div class="product-card" data-produto-id="<?php echo $p['id']; ?>">
          <div class="img-wrap">
            <a href="produto.php?id=<?php echo $p['id']; ?>">
              <img src="<?php echo htmlspecialchars($p['imagem']); ?>"
                   alt="<?php echo htmlspecialchars($p['nome']); ?>"
                   loading="lazy" onerror="this.src='imagens/cafe1.jpg'">
            </a>
            <?php if ($p['destaque']): ?><span class="badge-destaque">Destaque</span><?php endif; ?>
          </div>
          <div class="product-info">
            <div class="product-name"><a href="produto.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></a></div>
            <div class="product-desc"><?php echo htmlspecialchars($p['descricao']); ?></div>
            <div class="product-footer">
              <div class="product-price">R$ <?php echo number_format($p['preco'],2,',','.'); ?></div>
              <div style="display:flex;gap:6px;align-items:center;">
                <button class="fav-btn" data-id="<?php echo $p['id']; ?>" onclick="toggleFav(<?php echo $p['id']; ?>,this)">&#9825;</button>
                <?php if ($p['estoque']>0): ?>
                <button class="add-btn" onclick="addToCart(<?php echo $p['id']; ?>,'<?php echo addslashes($p['nome']); ?>',<?php echo $p['preco']; ?>,'<?php echo addslashes($p['imagem']); ?>')">Adicionar</button>
                <?php else: ?><button class="add-btn" disabled>Esgotado</button><?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Controles de paginação -->
      <div class="pagination" id="pagination">
        <button class="pg-btn" id="pg-prev" onclick="changePage(-1)">
          <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
          Anterior
        </button>
        <div class="pg-dots" id="pg-dots"></div>
        <button class="pg-btn" id="pg-next" onclick="changePage(1)">
          Próxima
          <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- SIDEBAR -->
  <aside>
    <!-- Localização -->
    <div class="side-card">
      <div class="side-header">
        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        Localização
      </div>
      <?php if ($prod['fazenda']): ?><div class="info-row"><span class="lbl">Fazenda</span><span class="val"><?php echo htmlspecialchars($prod['fazenda']); ?></span></div><?php endif; ?>
      <?php if ($prod['regiao']): ?><div class="info-row"><span class="lbl">Região</span><span class="val"><?php echo htmlspecialchars($prod['regiao']); ?></span></div><?php endif; ?>
      <?php if ($prod['estado']): ?><div class="info-row"><span class="lbl">Estado</span><span class="val"><?php echo htmlspecialchars($prod['estado']); ?></span></div><?php endif; ?>
      <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode(($prod['fazenda']??'').' '.($prod['regiao']??'').' '.($prod['estado']??'')); ?>"
         target="_blank" class="btn-maps">
        <svg viewBox="0 0 24 24"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
        Abrir no Google Maps
      </a>
    </div>

    <!-- Mini-lista produtos -->
    <?php if (!empty($produtos)): ?>
    <div class="side-card">
      <div class="side-header">
        <svg viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
        Produtos (<?php echo count($produtos); ?>)
      </div>
      <?php foreach (array_slice($produtos,0,5) as $p): ?>
      <a href="produto.php?id=<?php echo $p['id']; ?>" class="mini-prod">
        <img src="<?php echo htmlspecialchars($p['imagem']); ?>" onerror="this.src='imagens/cafe1.jpg'">
        <div style="flex:1;min-width:0;">
          <div class="mn"><?php echo htmlspecialchars($p['nome']); ?></div>
          <div class="mp">R$ <?php echo number_format($p['preco'],2,',','.'); ?></div>
        </div>
        <?php if ($p['destaque']): ?>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="#D4622A" stroke="#D4622A" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
      <?php if (count($produtos) > 5): ?>
      <div style="padding:10px 16px;font-size:11px;color:var(--dust);text-align:center;">
        +<?php echo count($produtos)-5; ?> produto(s) no grid abaixo
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </aside>

</div>
</div>

<?php include("includes/footer.php"); ?>
<?php include("includes/carrinho_sidebar.php"); ?>
<script src="js/carrinho.js"></script>
<script>
// ── PAGINAÇÃO ──
const PER_PAGE = 6;
const cards    = Array.from(document.querySelectorAll('#pg-grid .product-card'));
const total    = cards.length;
const pages    = Math.ceil(total / PER_PAGE);
let   curPage  = 0;

function renderPage(p) {
    curPage = Math.max(0, Math.min(p, pages - 1));
    const start = curPage * PER_PAGE;
    cards.forEach((c, i) => {
        c.style.display = (i >= start && i < start + PER_PAGE) ? '' : 'none';
    });
    document.getElementById('pg-prev').disabled = curPage === 0;
    document.getElementById('pg-next').disabled = curPage >= pages - 1;
    document.getElementById('pg-info').textContent =
        `${start + 1}–${Math.min(start + PER_PAGE, total)} de ${total} produtos`;
    // dots
    const dots = document.getElementById('pg-dots');
    dots.innerHTML = '';
    if (pages > 1) {
        for (let i = 0; i < pages; i++) {
            const d = document.createElement('div');
            d.className = 'pg-dot' + (i === curPage ? ' on' : '');
            d.onclick = () => renderPage(i);
            dots.appendChild(d);
        }
    }
    // esconde paginação se só tem 1 página
    document.getElementById('pagination').style.display = pages <= 1 ? 'none' : '';
}

function changePage(dir) { renderPage(curPage + dir); }

renderPage(0);
</script>
</body>
</html>
