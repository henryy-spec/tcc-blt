<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

$mensagem = null;
$link = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirCsrf($_POST['csrf_token'] ?? null, '../entrar.php');
    $email = strtolower(trim((string)($_POST['email'] ?? '')));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Informe um e-mail válido.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($usuario) {
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $id = (int)$usuario['id'];
            $stmt = $conn->prepare('UPDATE tokens_senha SET usado = 1 WHERE usuario_id = ? AND usado = 0');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare('INSERT INTO tokens_senha (usuario_id, token, expira_em) VALUES (?, ?, ?)');
            $stmt->bind_param('iss', $id, $token, $expira);
            $stmt->execute();
            $stmt->close();

            // Para o TCC em localhost, mostramos o link. Em produção, envie por e-mail.
            $link = 'redefinirSenha.php?token=' . urlencode($token);
        }
        $mensagem = 'Se o e-mail estiver cadastrado, um link de recuperação foi gerado.';
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../Css/design-system.css">
<link rel="stylesheet" href="../Css/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<title>Recuperar senha | Blue Light</title>
<style>.link-box{padding:14px;border:1px solid var(--surface-border);border-radius:var(--r-md);margin:14px 0;word-break:break-all;background:var(--surface)}.link-box a{color:var(--accent-2)}</style>
</head>
<body class="bl-auth-body">
<main class="container">
  <div class="bl-auth-logo">
    <img src="../img/Logo Blue Light com contorno.png" alt="Logo Blue Light">
    <span>BLUE <b>LIGHT</b></span>
  </div>
  <h1>Recuperar senha</h1>
  <p class="bl-auth-sub">Informe o e-mail cadastrado para gerar um link de redefinição.</p>

  <form method="post" data-bl-loading novalidate>
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <?php if ($mensagem): ?><div class="bl-flash sucesso msg" role="status"><?= e($mensagem) ?></div><?php endif; ?>
    <?php if ($link): ?>
      <div class="link-box"><strong>Link de teste local:</strong><br><a href="<?= e($link) ?>">Abrir redefinição de senha</a></div>
    <?php endif; ?>

    <div class="bl-field">
      <i class="fa-regular fa-envelope bl-icon"></i>
      <input type="email" name="email" placeholder="Seu e-mail" required aria-label="E-mail">
    </div>

    <button type="submit" class="bl-btn bl-btn-primary bl-auth-submit">
      <span class="bl-spinner"></span><span class="bl-btn-label">Gerar recuperação</span>
    </button>
    <a href="../entrar.php" class="cadastro">Voltar ao login</a>
  </form>
</main>
<script src="../Js/app.js"></script>
</body></html>
