<?php
declare(strict_types=1);
require_once __DIR__ . '/../php/conexao.php';
require_once __DIR__ . '/../php/funcoes.php';
require_once __DIR__ . '/../php/config_email.php';

exigirAdmin($conn, 'index.php');

$ok = null;
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigirCsrf($_POST['csrf_token'] ?? null, 'teste_smtp.php');
    $cfg = bltMailConfig();
    try {
        $mail = bltCriarMailer();
        $mail->addAddress($cfg['username']);
        $mail->Subject = 'Teste SMTP - Blue Light';
        $mail->isHTML(true);
        $mail->Body = '<h2>SMTP funcionando!</h2><p>Este é um teste do PHPMailer + Gmail SMTP da Blue Light.</p>';
        $mail->AltBody = 'SMTP funcionando! Este é um teste do PHPMailer + Gmail SMTP da Blue Light.';
        $mail->send();
        $ok = 'Teste enviado para ' . $cfg['username'] . '.';
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Teste SMTP | Blue Light</title><link rel="stylesheet" href="../Css/design-system.css"><link rel="stylesheet" href="dashboard.css"></head>
<body><main class="container bl-admin-page"><div class="bl-admin-title"><div><p class="bl-admin-kicker">Admin</p><h1>Teste SMTP</h1><p>Envia um único e-mail para a conta configurada no SMTP.</p></div></div>
<?php if ($ok): ?><div class="bl-admin-alert sucesso"><?= e($ok) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="bl-admin-alert erro"><strong>Falha no SMTP:</strong><br><?= e($erro) ?></div><?php endif; ?>
<section class="bl-admin-card" style="padding:24px;max-width:700px"><p>O teste envia somente para o endereço definido em <code>BLT_SMTP_USERNAME</code>. Nenhum usuário do banco será contatado.</p><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="bl-btn bl-btn-primary" type="submit">Enviar e-mail de teste</button> <a class="bl-btn" href="index.php">Voltar</a></form></section></main></body></html>
