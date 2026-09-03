<?php
declare(strict_types=1);
require_once __DIR__ . '/php/funcoes.php';

if (usuarioLogado()) {
    redirecionar('php/perfil.php');
}

$flash = obterFlash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="Css/design-system.css">
<link rel="stylesheet" href="Css/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<title>Entrar | Blue Light</title>
</head>
<body class="bl-auth-body">
<main class="container">
  <div class="bl-auth-logo">
    <img src="img/Logo Blue Light com contorno.png" alt="Logo Blue Light">
    <span>BLUE<b>LIGHT</b></span>
  </div>
  <h1>Bem-vindo de volta</h1>
  <p class="bl-auth-sub">Entre para acessar sua biblioteca de jogos.</p>

  <?php if ($flash): ?><div class="bl-flash <?= e($flash['tipo']) ?> msg" role="status"><?= e($flash['mensagem']) ?></div><?php endif; ?>

  <form action="php/login.php" method="POST" data-bl-loading novalidate>
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="bl-field">
      <i class="fa-regular fa-envelope bl-icon"></i>
      <input placeholder="Informe seu e-mail" type="email" name="email" required aria-label="E-mail">
    </div>

    <div class="bl-field">
      <i class="fa-solid fa-lock bl-icon"></i>
      <input placeholder="Sua senha" type="password" name="senha" required aria-label="Senha">
      <button type="button" class="bl-toggle-pass" aria-label="Mostrar senha"><i class="fa-regular fa-eye"></i></button>
    </div>

    <button type="submit" class="bl-btn bl-btn-primary bl-auth-submit">
      <span class="bl-spinner"></span><span class="bl-btn-label">Entrar</span>
    </button>

    <a href="login.php" class="cadastro">Criar conta</a>
    <a href="php/recuperarSenha.php" class="cadastro">Esqueci minha senha</a>
  </form>
</main>
<script src="Js/app.js"></script>
</body></html>
