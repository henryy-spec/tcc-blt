<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

exigirLogin('../entrar.php');

$id = (int)$_SESSION['id_usuario'];
$stmt = $conn->prepare('SELECT id, nome, email, criado_em, ultimo_login, assinatura_ate, primeira_assinatura, primeiro_jogo FROM usuarios WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $_SESSION = [];
    redirecionar('../entrar.php');
}

$ativa = assinaturaAtiva($usuario['assinatura_ate']);
$flash = obterFlash();
$inicial = mb_strtoupper(mb_substr($usuario['nome'], 0, 1));

$conquistasFeitas = (int)!empty($usuario['primeira_assinatura']) + (int)!empty($usuario['primeiro_jogo']);
$progresso = (int) round(($conquistasFeitas / 2) * 100);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Meu Perfil | Blue Light</title>
<link rel="stylesheet" href="../Css/design-system.css">
<link rel="stylesheet" href="../Css/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<header class="bl-dash-header">
  <div class="container">
    <a class="bl-logo" href="../Index.php"><img src="../img/Logo Blue Light com contorno.png" alt="Logo Blue Light"><span>BLUE LIGHT</span></a>
    <nav>
      <a href="../Index.php">Início</a>
      <a class="active" href="perfil.php">Perfil</a>
      <a href="biblioteca.php">Biblioteca</a>
      <a href="conquistas.php">Conquistas</a>
      <a href="logout.php">Sair</a>
    </nav>
  </div>
</header>

<main class="container bl-dash-page">
  <?php if ($flash): ?><div class="bl-flash <?= e($flash['tipo']) ?>" role="status"><?= e($flash['mensagem']) ?></div><?php endif; ?>

  <div class="bl-dash-hero bl-reveal">
    <div class="bl-avatar-lg"><?= e($inicial) ?></div>
    <div>
      <h1>Olá, <?= e($usuario['nome']) ?> 👋</h1>
      <p>Gerencie sua conta e sua assinatura.</p>
    </div>
  </div>

  <div class="bl-info-grid bl-reveal">
    <div class="bl-card bl-info">
      <strong>Nome</strong>
      <div class="bl-info-val"><?= e($usuario['nome']) ?></div>
    </div>
    <div class="bl-card bl-info">
      <strong>E-mail</strong>
      <div class="bl-info-val"><?= e($usuario['email']) ?></div>
    </div>
    <div class="bl-card bl-info">
      <strong>Conta criada</strong>
      <div class="bl-info-val"><?= e(date('d/m/Y H:i', strtotime($usuario['criado_em']))) ?></div>
    </div>
    <div class="bl-card bl-info">
      <strong>Último login</strong>
      <div class="bl-info-val"><?= !empty($usuario['ultimo_login']) ? e(date('d/m/Y H:i', strtotime($usuario['ultimo_login']))) : 'Primeiro acesso' ?></div>
    </div>
    <div class="bl-card bl-info">
      <strong>Status da assinatura</strong>
      <div class="bl-info-val">
        <?php if ($ativa): ?>
          <span class="bl-status-pill on"><i class="fa-solid fa-circle-check"></i> Ativa até <?= e(date('d/m/Y', strtotime($usuario['assinatura_ate']))) ?></span>
        <?php else: ?>
          <span class="bl-status-pill off"><i class="fa-solid fa-circle-xmark"></i> Sem assinatura ativa</span>
        <?php endif; ?>
      </div>
    </div>
    <div class="bl-card bl-info">
      <strong>Progresso de conquistas</strong>
      <div class="bl-progress-bar"><span style="width:<?= $progresso ?>%"></span></div>
      <div class="bl-info-val" style="font-size:13px;color:var(--text-2);font-weight:500;"><?= $conquistasFeitas ?>/2 desbloqueadas</div>
    </div>
  </div>

  <div class="bl-acoes bl-reveal">
    <a class="bl-btn bl-btn-ghost" href="../Index.php">Início</a>
    <a class="bl-btn bl-btn-primary" href="../pagamento.php">Assinar / renovar</a>
    <a class="bl-btn bl-btn-outline" href="biblioteca.php">Minha biblioteca</a>
    <a class="bl-btn bl-btn-outline" href="conquistas.php">Conquistas</a>
    <a class="bl-btn bl-btn-ghost" href="logout.php">Sair</a>
  </div>
</main>

<script src="../Js/app.js"></script>
</body></html>
