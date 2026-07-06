<?php
// adm/layout.php — layout compartilhado do painel admin
include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php?voltar=adm/dashboard.php"); exit;
}

$n_sol_prod   = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtores WHERE status='pendente'"))['n'];
$n_sol_prd    = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM solicitacoes_produtos  WHERE status='pendente'"))['n'];
$n_pedidos    = (int)mysqli_fetch_assoc(mysqli_query($conexao,"SELECT COUNT(*) n FROM pedidos WHERE status='pendente'"))['n'];
$total_alerts = $n_sol_prod + $n_sol_prd + $n_pedidos;
$active       = $active_menu ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($page_title ?? 'Admin'); ?> — Origem Brasil</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --soil:#1A1208;
    --soil-2:#261A0C;
    --stem:#2A5C2E;
    --stem-2:#3D7A42;
    --clay:#C4531F;
    --clay-light:#FBF0EB;
    --cream:#F6F3EE;
    --parchment:#FFFFFF;
    --ink:#1C1208;
    --ink-2:#5C5046;
    --dust:#A0948A;
    --border:#E8E2D9;
    --radius:10px;
    --shadow:0 1px 3px rgba(26,18,8,.06),0 6px 18px rgba(26,18,8,.07);
}
body{font-family:'Inter',sans-serif;background:var(--cream);color:var(--ink);min-height:100vh;display:flex;flex-direction:column;font-size:14px;}
a{text-decoration:none;color:inherit;}
button{font-family:'Inter',sans-serif;cursor:pointer;}

/* ── TOP NAV BAR ── */
.adminbar{
    background:var(--soil);
    display:flex;align-items:center;
    height:58px;padding:0 28px;gap:0;
    position:sticky;top:0;z-index:200;
    border-bottom:2px solid var(--soil-2);
}

.ab-brand{
    display:flex;align-items:baseline;gap:10px;
    margin-right:36px;flex-shrink:0;
}
.ab-brand .wordmark{
    font-family:'Playfair Display',serif;
    font-size:17px;font-weight:700;color:#fff;
    letter-spacing:.01em;
}
.ab-brand .tag{
    font-size:9px;font-weight:600;text-transform:uppercase;
    letter-spacing:.14em;color:var(--clay);
    border:1px solid rgba(196,83,31,.4);
    padding:2px 6px;border-radius:3px;
}

.ab-nav{display:flex;align-items:stretch;gap:0;flex:1;}
.ab-nav a{
    display:flex;align-items:center;gap:7px;
    padding:0 14px;height:58px;
    font-size:12px;font-weight:500;
    color:rgba(255,255,255,.55);
    position:relative;transition:color .15s;
    white-space:nowrap;
}
.ab-nav a:hover{color:rgba(255,255,255,.9);}
.ab-nav a.active{color:#fff;}
.ab-nav a.active::after{
    content:'';position:absolute;
    bottom:0;left:14px;right:14px;height:2px;
    background:var(--clay);border-radius:2px 2px 0 0;
}
.ab-nav a svg{width:14px;height:14px;flex-shrink:0;opacity:.7;}
.ab-nav a.active svg{opacity:1;}
.ab-nav .n-badge{
    background:var(--clay);color:#fff;
    font-size:9px;font-weight:700;
    padding:1px 5px;border-radius:10px;min-width:16px;text-align:center;
    line-height:16px;
}
.ab-nav .divider{width:1px;background:rgba(255,255,255,.08);margin:16px 4px;flex-shrink:0;}

/* dropdown */
.ab-dropdown{position:relative;}
.ab-dropdown > a::before{
    content:'';position:absolute;bottom:-2px;
    left:0;right:0;height:8px;/* hover bridge */
}
.ab-dropdown-menu{
    display:none;position:absolute;top:calc(100% + 2px);left:0;
    background:var(--soil-2);border:1px solid rgba(255,255,255,.08);
    border-radius:8px;padding:4px;min-width:200px;z-index:300;
    box-shadow:0 8px 24px rgba(0,0,0,.35);
}
.ab-dropdown:hover .ab-dropdown-menu{display:block;}
.ab-dropdown-menu a{
    display:flex;align-items:center;gap:9px;
    padding:9px 12px;border-radius:6px;
    font-size:12px;font-weight:500;color:rgba(255,255,255,.65);
    height:auto;transition:background .12s,color .12s;
}
.ab-dropdown-menu a:hover{background:rgba(255,255,255,.07);color:#fff;}
.ab-dropdown-menu a::after{display:none;}

.ab-right{display:flex;align-items:center;gap:14px;margin-left:auto;flex-shrink:0;}
.ab-store-link{
    font-size:11px;font-weight:500;color:rgba(255,255,255,.4);
    display:flex;align-items:center;gap:5px;
    transition:color .15s;
}
.ab-store-link:hover{color:rgba(255,255,255,.8);}
.ab-store-link svg{width:12px;height:12px;}
.ab-user-pill{
    display:flex;align-items:center;gap:9px;
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.1);
    border-radius:24px;padding:5px 12px 5px 6px;
    cursor:default;
}
.ab-user-pill .av{
    width:26px;height:26px;border-radius:50%;
    background:linear-gradient(135deg,var(--stem),var(--stem-2));
    font-size:11px;font-weight:700;color:#fff;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.ab-user-pill span{font-size:12px;font-weight:500;color:rgba(255,255,255,.75);}
.ab-logout{
    font-size:11px;color:rgba(239,68,68,.6);
    display:flex;align-items:center;gap:5px;
    transition:color .15s;padding:6px 2px;
}
.ab-logout:hover{color:rgba(239,68,68,.9);}
.ab-logout svg{width:13px;height:13px;}

/* ── PAGE HEADER ── */
.page-header{
    background:var(--parchment);
    border-bottom:1px solid var(--border);
    padding:18px 28px 16px;
    display:flex;align-items:flex-end;justify-content:space-between;
    gap:16px;
}
.page-header .ph-left{}
.page-header .ph-eyebrow{
    font-size:10px;font-weight:600;text-transform:uppercase;
    letter-spacing:.12em;color:var(--dust);margin-bottom:4px;
}
.page-header h1{
    font-family:'Playfair Display',serif;
    font-size:24px;font-weight:600;color:var(--ink);line-height:1.1;
}

/* ── MAIN CONTENT ── */
.adm-content{flex:1;padding:26px 28px;max-width:1600px;width:100%;}

/* ── METRIC CARDS ── */
.metrics{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px;}
.metric{
    background:var(--parchment);border:1px solid var(--border);
    border-radius:var(--radius);padding:18px 20px;
    position:relative;overflow:hidden;
}
.metric::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
    background:var(--border);
}
.metric.green::before{background:var(--stem);}
.metric.orange::before{background:var(--clay);}
.metric.red::before{background:#EF4444;}
.metric.blue::before{background:#3B82F6;}
.metric .m-label{font-size:10px;font-weight:600;color:var(--dust);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;}
.metric .m-val{font-size:26px;font-weight:700;color:var(--ink);line-height:1;}
.metric.green .m-val{color:var(--stem);}
.metric.orange .m-val{color:var(--clay);}
.metric.red .m-val{color:#EF4444;}
.metric.blue .m-val{color:#3B82F6;}
.metric .m-sub{font-size:11px;color:var(--dust);margin-top:6px;}
.metric .m-icon{
    position:absolute;right:16px;top:16px;
    width:34px;height:34px;border-radius:8px;
    background:var(--cream);
    display:flex;align-items:center;justify-content:center;
}
.metric .m-icon svg{width:16px;height:16px;stroke:var(--ink-2);}
.metric.green .m-icon{background:#EBF5EC;} .metric.green .m-icon svg{stroke:var(--stem);}
.metric.orange .m-icon{background:var(--clay-light);} .metric.orange .m-icon svg{stroke:var(--clay);}
.metric.red .m-icon{background:#FEF2F2;} .metric.red .m-icon svg{stroke:#EF4444;}
.metric.blue .m-icon{background:#EFF6FF;} .metric.blue .m-icon svg{stroke:#3B82F6;}

/* ── CARD ── */
.card{background:var(--parchment);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:20px;box-shadow:var(--shadow);}
.card-header{
    padding:14px 20px;border-bottom:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;
}
.card-header h3{font-size:13px;font-weight:700;color:var(--ink);}
.card-header .sub{font-size:12px;color:var(--dust);}

/* ── TABLE ── */
table.adm-table{width:100%;border-collapse:collapse;}
.adm-table th{
    font-size:10px;text-transform:uppercase;letter-spacing:.09em;
    color:var(--dust);font-weight:600;padding:10px 16px;
    background:var(--cream);border-bottom:1px solid var(--border);text-align:left;
}
.adm-table td{font-size:13px;padding:12px 16px;border-bottom:1px solid var(--cream);vertical-align:middle;color:var(--ink);}
.adm-table tr:last-child td{border-bottom:none;}
.adm-table tbody tr:hover td{background:#FDFBF8;}

/* ── BADGES ── */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;white-space:nowrap;}
.badge-pendente{background:#FEF3C7;color:#92400E;}
.badge-pago{background:#DCFCE7;color:#166534;}
.badge-enviado{background:#DBEAFE;color:#1E40AF;}
.badge-entregue{background:#F0FDF4;color:#166534;}
.badge-cancelado{background:#FEE2E2;color:#991B1B;}
.badge-aprovado{background:#DCFCE7;color:#166534;}
.badge-rejeitado{background:#FEE2E2;color:#991B1B;}
.badge-admin{background:#EDE9FE;color:#5B21B6;}
.badge-cliente{background:var(--cream);color:var(--ink-2);}
.badge-ativo{background:#DCFCE7;color:#166534;}
.badge-inativo{background:var(--cream);color:var(--ink-2);}
.badge-pill{background:var(--clay);color:#fff;font-size:9px;font-weight:700;padding:1px 5px;border-radius:10px;min-width:16px;text-align:center;}
.badge-pill.orange{background:var(--clay);}

/* ── BUTTONS ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border:none;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s;}
.btn-green{background:var(--stem);color:#fff;} .btn-green:hover{background:var(--stem-2);}
.btn-red{background:#FEE2E2;color:#991B1B;} .btn-red:hover{background:#FECACA;}
.btn-blue{background:#DBEAFE;color:#1E40AF;} .btn-blue:hover{background:#BFDBFE;}
.btn-gray{background:var(--cream);color:var(--ink-2);border:1px solid var(--border);} .btn-gray:hover{background:var(--border);}
.btn-orange{background:var(--clay-light);color:var(--clay);border:1px solid rgba(196,83,31,.2);} .btn-orange:hover{background:#F5DDD5;}
.btn-lg{padding:11px 24px;font-size:14px;border-radius:9px;}
.btn svg{width:14px;height:14px;}

/* topbar compat (alguns .php usam topbar-btn) */
.topbar-btn{
    padding:7px 14px;border:1px solid var(--border);background:var(--parchment);
    border-radius:8px;font-size:12px;font-weight:600;color:var(--ink-2);
    display:inline-flex;align-items:center;gap:6px;cursor:pointer;transition:all .15s;
}
.topbar-btn:hover{background:var(--cream);}
.topbar-btn.primary{background:var(--stem);color:#fff;border-color:var(--stem);}
.topbar-btn.primary:hover{background:var(--stem-2);}

/* ── FORMS ── */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.form-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;}
.form-full{grid-column:1/-1;}
.field{display:flex;flex-direction:column;gap:5px;}
.field label{font-size:10px;font-weight:700;color:var(--dust);text-transform:uppercase;letter-spacing:.08em;}
.field input,.field select,.field textarea{
    padding:9px 13px;border:1px solid var(--border);border-radius:8px;
    font-family:'Inter',sans-serif;font-size:13px;outline:none;
    background:var(--cream);transition:border-color .15s,background .15s;color:var(--ink);
}
.field input:focus,.field select:focus,.field textarea:focus{border-color:var(--stem);background:#fff;}
.field textarea{resize:vertical;min-height:80px;}
.field .hint{font-size:11px;color:var(--dust);}

/* ── ALERTS ── */
.alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:20px;display:flex;align-items:flex-start;gap:10px;}
.alert-ok{background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;}
.alert-err{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;}
.alert-warn{background:#FFFBEB;border:1px solid #FDE68A;color:#92400E;}
.alert-info{background:#EFF6FF;border:1px solid #BFDBFE;color:#1E40AF;}

/* ── MISC ── */
.img-thumb{width:46px;height:46px;object-fit:cover;border-radius:7px;background:var(--cream);border:1px solid var(--border);}
.avatar-circle{
    width:32px;height:32px;border-radius:50%;
    background:linear-gradient(135deg,var(--stem),var(--stem-2));
    display:flex;align-items:center;justify-content:center;
    font-size:12px;font-weight:700;color:#fff;flex-shrink:0;
}
.empty-state{text-align:center;padding:52px 20px;color:var(--dust);}
.empty-state p{font-size:14px;}

/* ── TABS ── */
.tabs{display:flex;gap:2px;background:var(--cream);border-radius:9px;padding:3px;margin-bottom:20px;border:1px solid var(--border);}
.tab-btn{padding:7px 16px;border:none;background:none;border-radius:7px;font-size:13px;font-weight:500;color:var(--ink-2);cursor:pointer;transition:all .15s;}
.tab-btn.active{background:var(--parchment);color:var(--ink);font-weight:600;box-shadow:0 1px 3px rgba(0,0,0,.07);}

/* ── RESPONSIVE ── */
@media(max-width:1100px){.metrics{grid-template-columns:repeat(3,1fr);}}
@media(max-width:768px){.ab-nav{display:none;}.metrics{grid-template-columns:repeat(2,1fr);}.adm-content{padding:16px;}}
</style>
</head>
<body>

<!-- TOP NAV -->
<header class="adminbar">

    <div class="ab-brand">
        <span class="wordmark">Origem Brasil</span>
        <span class="tag">Admin</span>
    </div>

    <nav class="ab-nav">
        <!-- Dashboard -->
        <a href="dashboard.php" class="<?php echo $active==='dashboard'?'active':''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
            <?php if($total_alerts>0):?><span class="n-badge"><?php echo $total_alerts;?></span><?php endif;?>
        </a>

        <div class="divider"></div>

        <!-- Catálogo -->
        <a href="produtos.php" class="<?php echo $active==='produtos'?'active':''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Produtos
        </a>
        <a href="produtores.php" class="<?php echo $active==='produtores'?'active':''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Produtores
        </a>
        <a href="categorias.php" class="<?php echo $active==='categorias'?'active':''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Categorias
        </a>

        <div class="divider"></div>

        <!-- Vendas / Usuários -->
        <a href="pedidos.php" class="<?php echo $active==='pedidos'?'active':''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Pedidos
            <?php if($n_pedidos>0):?><span class="n-badge"><?php echo $n_pedidos;?></span><?php endif;?>
        </a>
        <a href="clientes.php" class="<?php echo $active==='clientes'?'active':''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Clientes
        </a>
        <a href="admins.php" class="<?php echo $active==='admins'?'active':''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Admins
        </a>

        <div class="divider"></div>

        <!-- Solicitações (dropdown) -->
        <div class="ab-dropdown">
            <a href="#" class="<?php echo in_array($active,['sol_produtores','sol_produtos'])?'active':''; ?>"
               onclick="return false;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                Solicitações
                <?php if($n_sol_prod+$n_sol_prd>0):?><span class="n-badge"><?php echo $n_sol_prod+$n_sol_prd;?></span><?php endif;?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:10px;height:10px;opacity:.4;"><polyline points="6 9 12 15 18 9"/></svg>
            </a>
            <div class="ab-dropdown-menu">
                <a href="sol_produtores.php" class="<?php echo $active==='sol_produtores'?'active':''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                    Novos Produtores
                    <?php if($n_sol_prod>0):?><span class="n-badge" style="margin-left:auto"><?php echo $n_sol_prod;?></span><?php endif;?>
                </a>
                <a href="sol_produtos.php" class="<?php echo $active==='sol_produtos'?'active':''; ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    Novos Produtos
                    <?php if($n_sol_prd>0):?><span class="n-badge" style="margin-left:auto"><?php echo $n_sol_prd;?></span><?php endif;?>
                </a>
            </div>
        </div>

        <!-- Config -->
        <a href="configuracoes.php" class="<?php echo $active==='configuracoes'?'active':''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Config
        </a>
    </nav>

    <div class="ab-right">
        <a href="../index.php" target="_blank" class="ab-store-link">
            Ver Loja
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
        <div class="ab-user-pill">
            <div class="av"><?php echo mb_strtoupper(mb_substr($_SESSION['nome'],0,1)); ?></div>
            <span><?php echo htmlspecialchars(explode(' ',$_SESSION['nome'])[0]); ?></span>
        </div>
        <a href="../logout.php" class="ab-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Sair
        </a>
    </div>

</header>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="ph-left">
        <div class="ph-eyebrow">Painel Administrativo</div>
        <h1><?php echo htmlspecialchars($page_title ?? 'Admin'); ?></h1>
    </div>
    <?php if (!empty($topbar_action)): ?>
    <div><?php echo $topbar_action; ?></div>
    <?php endif; ?>
</div>

<!-- CONTENT -->
<div class="adm-content">

<?php if (!empty($msg)): ?>
<div class="alert alert-<?php echo $msg_type ?? 'ok'; ?>">
    <?php if(($msg_type??'ok')==='ok'): ?>
    <svg style="width:15px;height:15px;flex-shrink:0;stroke:#16a34a;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
    <?php elseif($msg_type==='err'): ?>
    <svg style="width:15px;height:15px;flex-shrink:0;stroke:#dc2626;fill:none;stroke-width:2.5;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    <?php else: ?>
    <svg style="width:15px;height:15px;flex-shrink:0;stroke:#d97706;fill:none;stroke-width:2;" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <?php endif; ?>
    <?php echo htmlspecialchars($msg); ?>
</div>
<?php endif; ?>

<!-- ── KEYBOARD SHORTCUTS ── -->
<div id="ks-overlay" style="display:none;position:fixed;inset:0;background:rgba(26,18,8,.6);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);" onclick="if(event.target===this)closeKS()">
  <div style="background:#fff;border-radius:16px;padding:32px;max-width:500px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.3);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:20px;font-weight:700;color:#1C1208;">Atalhos de Teclado</div>
        <div style="font-size:12px;color:#9E9080;margin-top:2px;">Pressione as teclas em sequência</div>
      </div>
      <button onclick="closeKS()" style="border:none;background:var(--cream);border-radius:8px;padding:6px 10px;cursor:pointer;font-size:13px;color:var(--ink2);">✕ Fechar</button>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
      <?php
      $ks = [
        ['g d','Dashboard'],['g p','Produtos'],['g o','Pedidos'],
        ['g r','Produtores'],['g c','Clientes'],['g s','Solicitações'],
        ['n p','Novo Produto'],['n c','Nova Categoria'],['?','Esta tela'],['esc','Fechar modal'],
      ];
      foreach ($ks as [$k,$l]): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 12px;background:var(--cream);border-radius:8px;">
        <span style="font-size:13px;color:var(--ink);"><?php echo $l; ?></span>
        <span style="display:flex;gap:4px;">
          <?php foreach(explode(' ',$k) as $key): ?>
          <kbd style="background:#fff;border:1.5px solid var(--border2);border-radius:5px;padding:2px 8px;font-size:11px;font-weight:700;color:var(--green);font-family:monospace;"><?php echo $key; ?></kbd>
          <?php endforeach; ?>
        </span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- botão flutuante -->
<button onclick="document.getElementById('ks-overlay').style.display='flex'" title="Atalhos (pressione ?)"
  style="position:fixed;bottom:24px;right:24px;width:38px;height:38px;border-radius:50%;background:var(--soil);color:rgba(255,255,255,.7);border:none;font-size:16px;font-weight:700;cursor:pointer;z-index:999;box-shadow:0 4px 12px rgba(0,0,0,.3);transition:all .15s;"
  onmouseover="this.style.background='var(--green)';this.style.color='#fff'"
  onmouseout="this.style.background='var(--soil)';this.style.color='rgba(255,255,255,.7)'">?</button>

<script>
function closeKS(){document.getElementById('ks-overlay').style.display='none'; ksSeq='';}
let ksSeq='', ksT;
const ksMap={
  'gd':'dashboard.php','gp':'produtos.php','go':'pedidos.php',
  'gr':'produtores.php','gc':'clientes.php','gs':'sol_produtores.php',
  'np':'produtos.php?acao=novo','nc':'categorias.php'
};
document.addEventListener('keydown',e=>{
  if(['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName)) return;
  if(e.key==='Escape'){closeKS();return;}
  if(e.key==='?'){document.getElementById('ks-overlay').style.display='flex';return;}
  ksSeq+=e.key.toLowerCase(); clearTimeout(ksT);
  ksT=setTimeout(()=>ksSeq='',1200);
  if(ksMap[ksSeq]){location.href=ksMap[ksSeq]; ksSeq='';}
});
</script>
