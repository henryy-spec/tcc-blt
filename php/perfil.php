<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

exigirLogin('../entrar.php');

$id = (int)$_SESSION['id_usuario'];
$stmt = $conn->prepare('SELECT id, nome, email, criado_em, ultimo_login, assinatura_ate, xp, nivel FROM usuarios WHERE id = ? LIMIT 1');
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
$username = '@' . strtolower(preg_replace('/[^a-z0-9]+/i', '', explode(' ', trim($usuario['nome']))[0] ?: 'jogador'));

$progresso = progressoNivel((int)$usuario['xp']);
$conquistas = obterConquistasUsuario($conn, $id);
$totalConquistas = count($conquistas);
$conquistasFeitas = count(array_filter($conquistas, fn($c) => $c['desbloqueada_em'] !== null));
$favoritos = obterFavoritos($conn, $id);
$atividades = obterAtividades($conn, $id, 6);

$raridadeLabel = ['comum' => 'Comum', 'raro' => 'Raro', 'epico' => 'Épico', 'lendario' => 'Lendário'];
$atividadeIcone = [
    'conta' => 'fa-solid fa-door-open', 'login' => 'fa-solid fa-right-to-bracket', 'jogo' => 'fa-solid fa-gamepad',
    'conquista' => 'fa-solid fa-trophy', 'nivel' => 'fa-solid fa-star', 'assinatura' => 'fa-solid fa-crown',
    'favorito' => 'fa-solid fa-heart', 'xp' => 'fa-solid fa-bolt',
];
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
      <button class="bl-theme-toggle" id="blThemeToggle" type="button" aria-label="Alternar tema"><i class="fa-solid fa-moon"></i></button>
      <a href="logout.php">Sair</a>
    </nav>
  </div>
</header>

<main class="container bl-dash-page">
  <?php if ($flash): ?><div class="bl-flash <?= e($flash['tipo']) ?>" role="status"><?= e($flash['mensagem']) ?></div><?php endif; ?>

  <div class="bl-gamercard bl-reveal">
    <div class="bl-gamercard-top">
      <div class="bl-avatar-lg bl-avatar-xl"><?= e($inicial) ?><span class="bl-level-chip">Nv. <?= (int)$progresso['nivel'] ?></span></div>
      <div>
        <h1><?= e($usuario['nome']) ?></h1>
        <p class="bl-username"><?= e($username) ?> <?php if ($ativa): ?><span class="bl-badge bl-badge-popular"><i class="fa-solid fa-crown"></i> Assinante</span><?php endif; ?></p>
      </div>
    </div>

    <div class="bl-xp-row">
      <span>Nível <?= (int)$progresso['nivel'] ?></span>
      <span><?= (int)$progresso['xp_no_nivel'] ?> / <?= (int)$progresso['xp_necessario'] ?> XP</span>
    </div>
    <div class="bl-progress-bar bl-xp-bar"><span style="width:<?= (int)$progresso['percentual'] ?>%"></span></div>
  </div>

  <div class="bl-info-grid bl-reveal">
    <div class="bl-card bl-info bl-stat">
      <i class="fa-solid fa-gamepad"></i>
      <strong>Jogos na biblioteca</strong>
      <div class="bl-info-val">1</div>
    </div>
    <div class="bl-card bl-info bl-stat">
      <i class="fa-solid fa-heart"></i>
      <strong>Favoritos</strong>
      <div class="bl-info-val"><?= count($favoritos) ?></div>
    </div>
    <div class="bl-card bl-info bl-stat">
      <i class="fa-solid fa-trophy"></i>
      <strong>Conquistas</strong>
      <div class="bl-info-val"><?= $conquistasFeitas ?>/<?= $totalConquistas ?></div>
    </div>
    <div class="bl-card bl-info bl-stat">
      <i class="fa-solid fa-crown"></i>
      <strong>Plano</strong>
      <div class="bl-info-val"><?= $ativa ? 'Ativo' : 'Sem plano' ?></div>
    </div>
  </div>

  <div class="bl-info-grid bl-reveal">
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
  </div>

  <div class="bl-card bl-activity-card bl-reveal">
    <h3><i class="fa-solid fa-clock-rotate-left"></i> Atividade recente</h3>
    <?php if (!$atividades): ?>
      <div class="bl-empty" style="padding:30px;"><div class="bl-empty-icon"><i class="fa-solid fa-inbox"></i></div><p style="margin:0;">Nenhuma atividade ainda.</p></div>
    <?php else: ?>
      <ul class="bl-activity-list">
        <?php foreach ($atividades as $a): ?>
        <li>
          <i class="<?= e($atividadeIcone[$a['tipo']] ?? 'fa-solid fa-circle') ?>"></i>
          <span><?= e($a['descricao']) ?></span>
          <time><?= e(date('d/m H:i', strtotime($a['criado_em']))) ?></time>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <div class="bl-acoes bl-reveal">
    <a class="bl-btn bl-btn-ghost" href="../Index.php">Início</a>
    <a class="bl-btn bl-btn-primary" href="../pagamento.php">Assinar / renovar</a>
    <a class="bl-btn bl-btn-outline" href="biblioteca.php">Minha biblioteca</a>
    <a class="bl-btn bl-btn-outline" href="conquistas.php">Conquistas</a>
    <a class="bl-btn bl-btn-ghost" href="logout.php">Sair</a>
  </div>
</main>

<div id="blToasts" class="bl-toasts" aria-live="polite"></div>
<div id="blLevelUpModal" class="bl-fx-modal" hidden></div>
<div id="blConquistaModal" class="bl-fx-modal" hidden></div>

<script src="../Js/app.js"></script>
<?php renderGamificacaoPayload(); ?>
</body></html>
