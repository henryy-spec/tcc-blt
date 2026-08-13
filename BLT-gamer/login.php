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
<title>Cadastro | Blue Light</title>
</head>
<body class="bl-auth-body">
<main class="container">
  <div class="bl-auth-logo">
    <img src="img/Logo Blue Light com contorno.png" alt="Logo Blue Light">
    <span>BLUE <b>LIGHT</b></span>
  </div>
  <h1>Criar conta</h1>
  <p class="bl-auth-sub">Comece a jogar em poucos segundos.</p>

  <?php if ($flash): ?><div class="bl-flash <?= e($flash['tipo']) ?> msg" role="status"><?= e($flash['mensagem']) ?></div><?php endif; ?>

  <form action="php/cadastro.php" method="POST" data-bl-loading novalidate>
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="bl-field">
      <i class="fa-regular fa-user bl-icon"></i>
      <input placeholder="Seu nome" type="text" name="nome" minlength="2" maxlength="120" required aria-label="Seu nome">
    </div>

    <div class="bl-field">
      <i class="fa-regular fa-envelope bl-icon"></i>
      <input placeholder="Informe seu e-mail" type="email" name="email" required aria-label="E-mail">
    </div>

    <div class="bl-field">
      <i class="fa-solid fa-lock bl-icon"></i>
      <input placeholder="Crie sua senha (mín. 8 caracteres)" type="password" name="senha" minlength="8" required aria-label="Senha">
      <button type="button" class="bl-toggle-pass" aria-label="Mostrar senha"><i class="fa-regular fa-eye"></i></button>
    </div>

    <div class="bl-field">
      <i class="fa-solid fa-lock bl-icon"></i>
      <input placeholder="Confirme sua senha" type="password" name="confirmar_senha" minlength="8" required aria-label="Confirmar senha">
      <button type="button" class="bl-toggle-pass" aria-label="Mostrar senha"><i class="fa-regular fa-eye"></i></button>
    </div>

    <label class="termos">
      <input type="checkbox" name="termos" required>
      <span>Li e aceito os <a href="termos.html" target="_blank">Termos de Uso</a>.</span>
    </label>

    <button type="submit" class="bl-btn bl-btn-primary bl-auth-submit">
      <span class="bl-spinner"></span><span class="bl-btn-label">Criar conta</span>
    </button>

    <a href="entrar.php" class="cadastro">Já tenho uma conta</a>
  </form>
</main>
<script src="Js/app.js"></script>
</body></html>
