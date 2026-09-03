<?php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/config_email.php';

$mensagem = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirCsrf($_POST['csrf_token'] ?? null, '../entrar.php');

    $email = strtolower(trim((string)($_POST['email'] ?? '')));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } else {
        $stmt = $conn->prepare('SELECT id, nome, ativo FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($usuario && (int)$usuario['ativo'] === 1) {
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $id = (int)$usuario['id'];

            $stmt = $conn->prepare('UPDATE tokens_senha SET usado = 1 WHERE usuario_id = ? AND usado = 0');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare(
                'INSERT INTO tokens_senha (usuario_id, token, expira_em) VALUES (?, ?, ?)'
            );
            $stmt->bind_param('iss', $id, $token, $expira);

            if ($stmt->execute()) {
                $stmt->close();

                $cfg = bltMailConfig();
                $link = rtrim($cfg['url_base'], '/') . '/php/redefinirSenha.php?token=' . urlencode($token);

                if (!enviarEmailRecuperacao($email, (string)$usuario['nome'], $link)) {
                    $stmt = $conn->prepare('UPDATE tokens_senha SET usado = 1 WHERE token = ?');
                    $stmt->bind_param('s', $token);
                    $stmt->execute();
                    $stmt->close();

                    $erro = 'Não foi possível enviar o e-mail. Verifique a configuração de e-mail do servidor.';
                } else {
                    $mensagem = 'Se o e-mail estiver cadastrado, você receberá as instruções para redefinir sua senha.';
                }
            } else {
                $stmt->close();
                $erro = 'Não foi possível gerar a recuperação.';
            }
        } else {
            // Resposta genérica para não revelar quais e-mails estão cadastrados.
            $mensagem = 'Se o e-mail estiver cadastrado, você receberá as instruções para redefinir sua senha.';
        }
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
</head>
<body class="bl-auth-body">
<main class="container">
  <div class="bl-auth-logo">
    <img src="../img/Logo Blue Light com contorno.png" alt="Logo Blue Light">
    <span>BLUE <b>LIGHT</b></span>
  </div>

  <h1>Esqueci a senha</h1>
  <p class="bl-auth-sub">Digite o e-mail da sua conta. Enviaremos um link para criar uma nova senha.</p>

  <?php if ($mensagem): ?>
    <div class="bl-flash sucesso msg" role="status"><?= e($mensagem) ?></div>
  <?php endif; ?>

  <?php if ($erro): ?>
    <div class="bl-flash erro msg" role="alert"><?= e($erro) ?></div>
  <?php endif; ?>

  <form method="post" data-bl-loading novalidate>
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <div class="bl-field">
      <i class="fa-regular fa-envelope bl-icon"></i>
      <input type="email" name="email" placeholder="Seu e-mail" required aria-label="E-mail">
    </div>

    <button type="submit" class="bl-btn bl-btn-primary bl-auth-submit">
      <span class="bl-spinner"></span>
      <span class="bl-btn-label">Enviar confirmação</span>
    </button>

    <a href="../entrar.php" class="cadastro">Voltar ao login</a>
  </form>
</main>
<script src="../Js/app.js"></script>
</body>
</html>
