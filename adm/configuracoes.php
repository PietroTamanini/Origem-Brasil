<?php
$page_title  = 'Configurações';
$active_menu = 'configuracoes';
$msg = ''; $msg_type = 'ok';
include("../conexao.php");
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') { header("Location: ../login.php"); exit; }

// Garante tabela configuracoes
mysqli_query($conexao,"CREATE TABLE IF NOT EXISTS configuracoes (
    chave VARCHAR(80) PRIMARY KEY,
    valor TEXT NOT NULL DEFAULT ''
) ENGINE=InnoDB");

function cfg_get($con, $k, $default='') {
    $r = mysqli_fetch_assoc(mysqli_query($con,"SELECT valor FROM configuracoes WHERE chave='".mysqli_real_escape_string($con,$k)."'"));
    return $r ? $r['valor'] : $default;
}
function cfg_set($con, $k, $v) {
    $k = mysqli_real_escape_string($con,$k);
    $v = mysqli_real_escape_string($con,$v);
    mysqli_query($con,"INSERT INTO configuracoes (chave,valor) VALUES ('$k','$v') ON DUPLICATE KEY UPDATE valor='$v'");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pa = $_POST['acao'] ?? '';

    if ($pa === 'loja') {
        foreach (['loja_nome','loja_cnpj','loja_telefone','loja_email','loja_endereco','loja_instagram','loja_whatsapp'] as $campo) {
            cfg_set($conexao, $campo, trim($_POST[$campo] ?? ''));
        }
        $msg = 'Dados da loja atualizados!';
    }
    if ($pa === 'minha_conta') {
        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $uid   = (int)$_SESSION['id'];
        $chk = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT id FROM usuarios WHERE email='".mysqli_real_escape_string($conexao,$email)."' AND id!=$uid"));
        if ($chk) { $msg = 'E-mail já em uso.'; $msg_type = 'err'; }
        else {
            $s = mysqli_prepare($conexao,"UPDATE usuarios SET nome=?,email=? WHERE id=?");
            mysqli_stmt_bind_param($s,'ssi',$nome,$email,$uid);
            mysqli_stmt_execute($s);
            $_SESSION['nome'] = $nome;
            $msg = 'Dados atualizados!';
        }
    }
    if ($pa === 'senha') {
        $uid   = (int)$_SESSION['id'];
        $atual = $_POST['senha_atual'] ?? '';
        $nova  = $_POST['nova_senha']  ?? '';
        $conf  = $_POST['confirmar']   ?? '';
        $user  = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT senha FROM usuarios WHERE id=$uid"));
        if (!password_verify($atual, $user['senha'])) { $msg = 'Senha atual incorreta.'; $msg_type = 'err'; }
        elseif ($nova !== $conf)   { $msg = 'As senhas não conferem.'; $msg_type = 'err'; }
        elseif (strlen($nova) < 6) { $msg = 'Senha deve ter pelo menos 6 caracteres.'; $msg_type = 'err'; }
        else {
            $hash = password_hash($nova, PASSWORD_DEFAULT);
            $s = mysqli_prepare($conexao,"UPDATE usuarios SET senha=? WHERE id=?");
            mysqli_stmt_bind_param($s,'si',$hash,$uid);
            mysqli_stmt_execute($s);
            $msg = 'Senha alterada com sucesso!';
        }
    }
}

$me = mysqli_fetch_assoc(mysqli_query($conexao,"SELECT * FROM usuarios WHERE id=".(int)$_SESSION['id']));
include('layout.php');

$F = fn($k,$d='') => htmlspecialchars(cfg_get($conexao,$k,$d));
?>

<style>
.cfg-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; max-width:960px; }
.cfg-sec  { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--dust);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border); }
.cfg-field { display:flex;flex-direction:column;gap:5px;margin-bottom:14px; }
.cfg-field label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--dust); }
.cfg-field input,.cfg-field textarea {
    padding:10px 13px;border:1.5px solid var(--border);border-radius:9px;
    font-family:'Inter',sans-serif;font-size:13px;background:var(--cream);
    outline:none;transition:border-color .15s,background .15s;color:var(--ink);
}
.cfg-field input:focus,.cfg-field textarea:focus { border-color:var(--green);background:#fff; }
.cfg-field textarea { min-height:70px;resize:vertical; }
.cfg-hint { font-size:11px;color:var(--dust); }
</style>

<div class="cfg-grid">

    <!-- DADOS DA LOJA -->
    <div class="card" style="padding:24px;grid-column:1/-1;">
        <div class="cfg-sec">🏪 Dados da Loja</div>
        <form method="POST">
            <input type="hidden" name="acao" value="loja">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="cfg-field">
                    <label>Nome da Loja</label>
                    <input type="text" name="loja_nome" value="<?php echo $F('loja_nome','Origem Brasil'); ?>" required>
                </div>
                <div class="cfg-field">
                    <label>CNPJ</label>
                    <input type="text" name="loja_cnpj" value="<?php echo $F('loja_cnpj'); ?>" placeholder="00.000.000/0001-00">
                </div>
                <div class="cfg-field">
                    <label>Telefone de Suporte</label>
                    <input type="text" name="loja_telefone" value="<?php echo $F('loja_telefone'); ?>" placeholder="(00) 00000-0000">
                </div>
                <div class="cfg-field">
                    <label>E-mail de Suporte</label>
                    <input type="email" name="loja_email" value="<?php echo $F('loja_email','contato@origembrasil.com.br'); ?>">
                </div>
                <div class="cfg-field">
                    <label>WhatsApp</label>
                    <input type="text" name="loja_whatsapp" value="<?php echo $F('loja_whatsapp'); ?>" placeholder="5500900000000">
                    <span class="cfg-hint">Número completo com DDI: 55 + DDD + número</span>
                </div>
                <div class="cfg-field">
                    <label>Instagram</label>
                    <input type="text" name="loja_instagram" value="<?php echo $F('loja_instagram'); ?>" placeholder="@origembrasil">
                </div>
                <div class="cfg-field" style="grid-column:1/-1;">
                    <label>Endereço da Loja</label>
                    <input type="text" name="loja_endereco" value="<?php echo $F('loja_endereco'); ?>" placeholder="Rua, número — Cidade/UF">
                </div>
            </div>
            <button type="submit" class="btn btn-green" style="margin-top:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                Salvar Dados da Loja
            </button>
        </form>
    </div>

    <!-- MINHA CONTA -->
    <div class="card" style="padding:24px;">
        <div class="cfg-sec">👤 Minha Conta Admin</div>
        <form method="POST">
            <input type="hidden" name="acao" value="minha_conta">
            <div class="cfg-field"><label>Nome</label><input type="text" name="nome" value="<?php echo htmlspecialchars($me['nome']); ?>" required></div>
            <div class="cfg-field" style="margin-bottom:20px;"><label>E-mail</label><input type="email" name="email" value="<?php echo htmlspecialchars($me['email']); ?>" required></div>
            <button type="submit" class="btn btn-green">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                Salvar
            </button>
        </form>
    </div>

    <!-- ALTERAR SENHA -->
    <div class="card" style="padding:24px;">
        <div class="cfg-sec">🔑 Alterar Senha</div>
        <form method="POST">
            <input type="hidden" name="acao" value="senha">
            <div class="cfg-field"><label>Senha atual</label><input type="password" name="senha_atual" required></div>
            <div class="cfg-field"><label>Nova senha</label><input type="password" name="nova_senha" required></div>
            <div class="cfg-field" style="margin-bottom:20px;"><label>Confirmar nova senha</label><input type="password" name="confirmar" required></div>
            <button type="submit" class="btn btn-green">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                Alterar Senha
            </button>
        </form>
    </div>

    <!-- INFO DO SISTEMA -->
    <div class="card" style="padding:24px;grid-column:1/-1;">
        <div class="cfg-sec">ℹ️ Informações do Sistema</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
            <?php
            $info = [
                'Versão PHP'      => PHP_VERSION,
                'Banco de Dados'  => 'MySQL / MariaDB',
                'Servidor'        => $_SERVER['SERVER_SOFTWARE'] ?? 'XAMPP',
                'Charset'         => 'utf8mb4',
            ];
            foreach ($info as $k=>$v): ?>
            <div style="background:var(--cream);border:1px solid var(--border);border-radius:10px;padding:14px 16px;">
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--dust);margin-bottom:6px;"><?php echo $k; ?></div>
                <div style="font-size:13px;font-weight:600;color:var(--ink);"><?php echo htmlspecialchars($v); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

</div></main></body></html>
