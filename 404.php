<?php include("conexao.php"); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Página não encontrada — Origem Brasil</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:#F5F2ED;min-height:100vh;display:flex;flex-direction:column;}
.wrap{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 24px;}
.box{text-align:center;max-width:500px;}
.big{font-family:'Playfair Display',serif;font-size:120px;font-weight:700;color:#E5DED4;line-height:1;margin-bottom:8px;}
.title{font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#1C1208;margin-bottom:12px;}
.sub{font-size:14px;color:#9E9080;line-height:1.7;margin-bottom:32px;}
.actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;}
.btn-p{display:inline-flex;align-items:center;gap:7px;padding:12px 24px;background:#2C4A2E;color:#fff;border-radius:9px;font-size:13px;font-weight:700;text-decoration:none;transition:background .15s;}
.btn-p:hover{background:#3D6B40;}
.btn-s{display:inline-flex;align-items:center;gap:7px;padding:12px 24px;background:#fff;color:#5C5046;border:1.5px solid #E5DED4;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;transition:all .15s;}
.btn-s:hover{background:#F5F2ED;}
</style>
</head>
<body>
<?php include("includes/header.php"); ?>
<div class="wrap">
  <div class="box">
    <div class="big">404</div>
    <h1 class="title">Página não encontrada</h1>
    <p class="sub">O endereço que você acessou não existe ou foi movido.<br>Mas tem muita coisa boa esperando por você aqui.</p>
    <div class="actions">
      <a href="index.php" class="btn-p">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        Voltar ao Início
      </a>
      <a href="produtos.php" class="btn-s">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        Ver Produtos
      </a>
    </div>
  </div>
</div>
<?php include("includes/footer.php"); ?>
</body>
</html>
