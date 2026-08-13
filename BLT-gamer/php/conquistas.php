<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

exigirLogin('../entrar.php');

$id = (int)$_SESSION['id_usuario'];
$stmt = $conn->prepare('SELECT nome FROM usuarios WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$conquistas = obterConquistasUsuario($conn, $id);
$total = count($conquistas);
$feitas = count(array_filter($conquistas, fn($c) => $c['desbloqueada_em'] !== null));
$progresso = $total > 0 ? (int) round(($feitas / $total) * 100) : 0;

// Marca a conquista "explorador" se o usuário já visitou biblioteca + planos + conquistas nesta sessão.
$_SESSION['visitou_conquistas'] = true;
if (!empty($_SESSION['visitou_biblioteca']) && !empty($_SESSION['visitou_planos']) && !empty($_SESSION['visitou_conquistas'])) {
    desbloquearConquista($conn, $id, 'explorador');
}

$categorias = [];
foreach ($conquistas as $c) {
    $categorias[$c['categoria']][] = $c;
}
$labelsCategoria = ['geral' => 'Geral', 'jogos' => 'Jogos', 'assinatura' => 'Assinatura', 'exploracao' => 'Exploração', 'especial' => 'Especial'];
$raridadeLabel = ['comum' => 'Comum', 'raro' => 'Raro', 'epico' => 'Épico', 'lendario' => 'Lendário'];
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
      <button class="bl-theme-toggle" id="blThemeToggle" type="button" aria-label="Alternar tema"><i class="fa-solid fa-moon"></i></button>
      <a href="logout.php">Sair</a>
    </nav>
  </div>
</header>

<main class="container bl-dash-page">
  <div class="bl-dash-hero bl-reveal">
    <div>
      <h1>Conquistas de <?= e($usuario['nome'] ?? '') ?> 🏆</h1>
      <p><?= $feitas ?>/<?= $total ?> desbloqueadas</p>
    </div>
  </div>

  <div class="bl-card" style="padding:20px;margin-bottom:var(--sp-4);">
    <div class="bl-progress-bar"><span style="width:<?= $progresso ?>%"></span></div>
    <span style="font-size:13px;color:var(--text-2);"><?= $progresso ?>% do progresso geral concluído</span>
  </div>

  <?php foreach ($categorias as $slug => $itens): ?>
  <h3 class="bl-cat-heading bl-reveal"><?= e($labelsCategoria[$slug] ?? ucfirst($slug)) ?></h3>
  <div class="bl-badge-list bl-reveal">
    <?php foreach ($itens as $c): $desbloqueada = $c['desbloqueada_em'] !== null; ?>
    <div class="bl-card bl-achievement <?= $desbloqueada ? 'unlocked' : '' ?>">
      <div class="bl-ach-icon"><i class="<?= $desbloqueada ? e($c['icone']) : 'fa-solid fa-lock' ?>"></i></div>
      <div style="flex:1;">
        <strong><?= e($c['nome']) ?></strong>
        <small><?= e($c['descricao']) ?></small>
      </div>
      <span class="bl-badge bl-badge-<?= $c['raridade'] === 'comum' ? 'locked' : 'popular' ?>"><?= e($raridadeLabel[$c['raridade']] ?? $c['raridade']) ?></span>
      <span class="bl-badge <?= $desbloqueada ? 'bl-badge-ok' : 'bl-badge-locked' ?>">
        <?= $desbloqueada ? 'Desbloqueada' : '+' . (int)$c['xp'] . ' XP' ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <div class="bl-acoes bl-reveal">
    <a class="bl-btn bl-btn-ghost" href="perfil.php"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
  </div>
</main>

<div id="blToasts" class="bl-toasts" aria-live="polite"></div>
<div id="blLevelUpModal" class="bl-fx-modal" hidden></div>
<div id="blConquistaModal" class="bl-fx-modal" hidden></div>

<script src="../Js/app.js"></script>
<?php renderGamificacaoPayload(); ?>
</body></html>
