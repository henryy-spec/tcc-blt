<?php
declare(strict_types=1);
require_once __DIR__ . '/php/conexao.php';
require_once __DIR__ . '/php/funcoes.php';

exigirLogin('entrar.php');

$id = (int)$_SESSION['id_usuario'];
$stmt = $conn->prepare('SELECT assinatura_ate FROM usuarios WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!assinaturaAtiva($usuario['assinatura_ate'] ?? null)) {
    flash('erro', 'Você precisa de uma assinatura ativa para jogar.');
    redirecionar('pagamento.php');
}

$stmt = $conn->prepare('UPDATE usuarios SET primeiro_jogo = 1 WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

registrarAtividade($conn, $id, 'jogo', 'Jogou Chronocide.');
desbloquearConquista($conn, $id, 'primeiro_jogo');
adicionarXp($conn, $id, 25, 'Sessão de jogo');
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Chronocide | Blue Light</title>
<link rel="stylesheet" href="Css/design-system.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
html,body{ margin:0; width:100%; height:100%; background:var(--bg-0); overflow:hidden; }
.bl-game-shell{ position:relative; width:100%; height:100%; }
.bl-game-topbar{
  position:fixed; top:0; left:0; right:0; z-index:20; display:flex; align-items:center; justify-content:space-between;
  padding:14px 20px; background:linear-gradient(to bottom, rgba(5,8,16,.85), transparent);
  transition: opacity .3s var(--ease);
}
.bl-game-topbar a{
  display:inline-flex; align-items:center; gap:8px; background:rgba(5,8,16,.75); color:#fff; text-decoration:none;
  padding:9px 14px; border-radius:var(--r-pill); font-size:14px; font-weight:600; border:1px solid var(--surface-border);
  transition: background var(--t-fast);
}
.bl-game-topbar a:hover{ background:rgba(59,130,246,.35); }
.bl-game-title{ color:#fff; font-weight:700; font-size:14px; background:rgba(5,8,16,.75); border:1px solid var(--surface-border); padding:9px 16px; border-radius:var(--r-pill); }
.bl-game-frame-wrap{ position:absolute; inset:0; }
iframe{ border:0; width:100%; height:100%; display:block; background:#000; }
.bl-game-loading{
  position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:14px;
  background: var(--bg-radial); z-index:10; transition: opacity .4s var(--ease);
}
.bl-game-loading.is-hidden{ opacity:0; pointer-events:none; }
.bl-game-loading .bl-spinner{ width:34px; height:34px; border-width:3px; }
.bl-game-loading span{ color:var(--text-2); font-size:14px; }
</style>
</head>
<body>
<div class="bl-game-shell">
  <div class="bl-game-topbar">
    <a href="php/biblioteca.php"><i class="fa-solid fa-arrow-left"></i> Biblioteca</a>
    <span class="bl-game-title"><i class="fa-solid fa-gamepad"></i> Chronocide</span>
  </div>

  <div class="bl-game-loading" id="blGameLoading">
    <span class="bl-spinner"></span>
    <span>Carregando Chronocide...</span>
  </div>

  <div class="bl-game-frame-wrap">
    <iframe src="Jogo/Chronoside.html" title="Chronocide" onload="document.getElementById('blGameLoading').classList.add('is-hidden')"></iframe>
  </div>
</div>

<div id="blToasts" class="bl-toasts" aria-live="polite"></div>
<div id="blLevelUpModal" class="bl-fx-modal" hidden></div>
<div id="blConquistaModal" class="bl-fx-modal" hidden></div>

<script src="Js/app.js"></script>
<?php renderGamificacaoPayload(); ?>
</body></html>
