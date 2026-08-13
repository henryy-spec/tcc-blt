<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

exigirLogin('../entrar.php');

$id = (int)$_SESSION['id_usuario'];
$stmt = $conn->prepare('SELECT nome, assinatura_ate FROM usuarios WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$ativa = assinaturaAtiva($usuario['assinatura_ate'] ?? null);
$favoritos = obterFavoritos($conn, $id);
$favChronocide = in_array('chronocide', $favoritos, true);

$_SESSION['visitou_biblioteca'] = true;
if (!empty($_SESSION['visitou_biblioteca']) && !empty($_SESSION['visitou_planos']) && !empty($_SESSION['visitou_conquistas'])) {
    desbloquearConquista($conn, $id, 'explorador');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Biblioteca | Blue Light</title>
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
      <a class="active" href="biblioteca.php">Biblioteca</a>
      <a href="conquistas.php">Conquistas</a>
      <button class="bl-theme-toggle" id="blThemeToggle" type="button" aria-label="Alternar tema"><i class="fa-solid fa-moon"></i></button>
      <a href="logout.php">Sair</a>
    </nav>
  </div>
</header>

<main class="container bl-dash-page">
  <div class="bl-dash-hero bl-reveal">
    <div>
      <h1>Minha Biblioteca 🎮</h1>
      <p>Olá, <?= e($usuario['nome'] ?? '') ?>. Aqui estão seus jogos.</p>
    </div>
  </div>

  <div class="bl-tabs bl-reveal" role="tablist">
    <button class="bl-tab active" data-bl-filtro="todos" type="button">Todos</button>
    <button class="bl-tab" data-bl-filtro="favoritos" type="button">Favoritos</button>
    <?php if (!$ativa): ?><button class="bl-tab" data-bl-filtro="bloqueados" type="button">Bloqueados</button><?php endif; ?>
  </div>

  <?php if (!$ativa): ?>
    <div class="bl-empty bl-reveal" data-bl-item data-status="todos bloqueados">
      <div class="bl-empty-icon"><i class="fa-solid fa-lock"></i></div>
      <h2 style="margin:0 0 8px;">Assinatura necessária</h2>
      <p style="margin:0 0 20px;">Ative um plano para liberar os jogos da sua biblioteca.</p>
      <a class="bl-btn bl-btn-primary" href="../pagamento.php"><i class="fa-solid fa-crown"></i> Ver planos</a>
    </div>
  <?php else: ?>
    <div class="bl-card bl-game-showcase bl-reveal" data-bl-item data-status="todos <?= $favChronocide ? 'favoritos' : '' ?>">
      <img src="../img/darkhouse.jpg" alt="Chronocide">
      <div style="flex:1;">
        <span class="bl-badge bl-badge-ok" style="margin-bottom:10px;display:inline-flex;"><i class="fa-solid fa-circle-check"></i> Disponível</span>
        <h2>Chronocide</h2>
        <p>Aventura e mistério em diferentes épocas.</p>
        <div class="bl-acoes" style="margin-top:0;">
          <a class="bl-btn bl-btn-primary" href="../jogar.php"><i class="fa-solid fa-play"></i> Jogar agora</a>
          <button class="bl-btn bl-btn-ghost bl-fav-btn <?= $favChronocide ? 'is-active' : '' ?>" type="button" data-bl-fav="chronocide" aria-pressed="<?= $favChronocide ? 'true' : 'false' ?>">
            <i class="fa-<?= $favChronocide ? 'solid' : 'regular' ?> fa-heart"></i> <?= $favChronocide ? 'Favoritado' : 'Favoritar' ?>
          </button>
        </div>
      </div>
    </div>

    <div class="bl-empty bl-reveal" id="blFavEmptyMsg" hidden>
      <div class="bl-empty-icon"><i class="fa-regular fa-heart"></i></div>
      <p style="margin:0;">Você ainda não favoritou nenhum jogo.</p>
    </div>
  <?php endif; ?>

  <div class="bl-acoes bl-reveal">
    <a class="bl-btn bl-btn-ghost" href="perfil.php"><i class="fa-solid fa-arrow-left"></i> Voltar ao perfil</a>
  </div>
</main>

<div id="blToasts" class="bl-toasts" aria-live="polite"></div>
<div id="blLevelUpModal" class="bl-fx-modal" hidden></div>
<div id="blConquistaModal" class="bl-fx-modal" hidden></div>

<script>window.BL_CSRF = <?= json_encode(csrfToken()) ?>;</script>
<script src="../Js/app.js"></script>
<?php renderGamificacaoPayload(); ?>
</body></html>
