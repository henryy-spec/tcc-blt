<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

exigirLogin('../entrar.php');

$id = (int)$_SESSION['id_usuario'];
$stmt = $conn->prepare('SELECT nome, primeira_assinatura, primeiro_jogo FROM usuarios WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$assinou = !empty($usuario['primeira_assinatura']);
$jogou = !empty($usuario['primeiro_jogo']);
$total = (int)$assinou + (int)$jogou;
$progresso = (int) round(($total / 2) * 100);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Conquistas | Blue Light</title>
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
      <a href="perfil.php">Perfil</a>
      <a href="biblioteca.php">Biblioteca</a>
      <a class="active" href="conquistas.php">Conquistas</a>
      <a href="logout.php">Sair</a>
    </nav>
  </div>
</header>

<main class="container bl-dash-page">
  <div class="bl-dash-hero bl-reveal">
    <div>
      <h1>Conquistas de <?= e($usuario['nome'] ?? '') ?> 🏆</h1>
      <p><?= $total ?>/2 desbloqueadas</p>
    </div>
  </div>

  <div class="bl-card" style="padding:20px;margin-bottom:var(--sp-4);" >
    <div class="bl-progress-bar"><span style="width:<?= $progresso ?>%"></span></div>
    <span style="font-size:13px;color:var(--text-2);"><?= $progresso ?>% do progresso geral concluído</span>
  </div>

  <div class="bl-badge-list bl-reveal">
    <div class="bl-card bl-achievement <?= $assinou ? 'unlocked' : '' ?>">
      <div class="bl-ach-icon"><i class="fa-solid <?= $assinou ? 'fa-trophy' : 'fa-lock' ?>"></i></div>
      <div>
        <strong>Primeira assinatura</strong>
        <small>Assine seu primeiro plano.</small>
      </div>
      <?php if ($assinou): ?><span class="bl-badge bl-badge-ok" style="margin-left:auto;">Desbloqueada</span><?php endif; ?>
    </div>

    <div class="bl-card bl-achievement <?= $jogou ? 'unlocked' : '' ?>">
      <div class="bl-ach-icon"><i class="fa-solid <?= $jogou ? 'fa-gamepad' : 'fa-lock' ?>"></i></div>
      <div>
        <strong>Primeiro jogo</strong>
        <small>Abra seu primeiro jogo.</small>
      </div>
      <?php if ($jogou): ?><span class="bl-badge bl-badge-ok" style="margin-left:auto;">Desbloqueada</span><?php endif; ?>
    </div>
  </div>

  <div class="bl-acoes bl-reveal">
    <a class="bl-btn bl-btn-ghost" href="perfil.php"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>
</main>

<script src="../Js/app.js"></script>
</body></html>
