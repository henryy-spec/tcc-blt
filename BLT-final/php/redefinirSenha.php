<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$erro = '';

if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    $erro = 'Link de recuperação inválido.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $erro === '') {
    if (!validarCsrf($_POST['csrf_token'] ?? null)) {
        $erro = 'A sessão do formulário expirou. Abra o link novamente.';
    } else {
        $senha = (string)($_POST['senha'] ?? '');
        $confirmar = (string)($_POST['confirmar_senha'] ?? '');

        if (strlen($senha) < 8) {
            $erro = 'A senha precisa ter pelo menos 8 caracteres.';
        } elseif ($senha !== $confirmar) {
            $erro = 'As senhas não coincidem.';
        } else {
            $stmt = $conn->prepare('SELECT usuario_id FROM tokens_senha WHERE token = ? AND usado = 0 AND expira_em >= NOW() LIMIT 1');
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row) {
                $erro = 'Link inválido, já utilizado ou expirado.';
            } else {
                $id = (int)$row['usuario_id'];
                $hash = password_hash($senha, PASSWORD_DEFAULT);

                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
                    $stmt->bind_param('si', $hash, $id);
                    if (!$stmt->execute()) throw new RuntimeException();
                    $stmt->close();

                    $stmt = $conn->prepare('UPDATE tokens_senha SET usado = 1 WHERE token = ?');
                    $stmt->bind_param('s', $token);
                    if (!$stmt->execute()) throw new RuntimeException();
                    $stmt->close();

                    $conn->commit();
                    flash('sucesso', 'Senha alterada com sucesso. Faça login novamente.');
                    redirecionar('../entrar.php');
                } catch (Throwable $e) {
                    $conn->rollback();
                    $erro = 'Não foi possível alterar a senha.';
                }
            }
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
<title>Nova senha | Blue Light</title>
</head>
<body class="bl-auth-body">
<main class="container">
  <div class="bl-auth-logo">
    <img src="../img/Logo Blue Light com contorno.png" alt="Logo Blue Light">
    <span>BLUE <b>LIGHT</b></span>
  </div>
  <h1>Nova senha</h1>
  <p class="bl-auth-sub">Escolha uma nova senha para sua conta.</p>

  <form method="post" data-bl-loading novalidate>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">

    <?php if ($erro): ?><div class="bl-flash erro msg" role="alert"><?= e($erro) ?></div><?php endif; ?>

    <div class="bl-field">
      <i class="fa-solid fa-lock bl-icon"></i>
      <input type="password" name="senha" placeholder="Nova senha (mín. 8 caracteres)" minlength="8" required aria-label="Nova senha">
      <button type="button" class="bl-toggle-pass" aria-label="Mostrar senha"><i class="fa-regular fa-eye"></i></button>
    </div>

    <div class="bl-field">
      <i class="fa-solid fa-lock bl-icon"></i>
      <input type="password" name="confirmar_senha" placeholder="Confirme a nova senha" minlength="8" required aria-label="Confirmar nova senha">
      <button type="button" class="bl-toggle-pass" aria-label="Mostrar senha"><i class="fa-regular fa-eye"></i></button>
    </div>

    <button type="submit" class="bl-btn bl-btn-primary bl-auth-submit">
      <span class="bl-spinner"></span><span class="bl-btn-label">Alterar senha</span>
    </button>
    <a href="../entrar.php" class="cadastro">Voltar ao login</a>
  </form>
</main>
<script src="../Js/app.js"></script>
</body></html>
