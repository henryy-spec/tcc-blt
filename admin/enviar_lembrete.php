<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/conexao.php';
require_once __DIR__ . '/../php/funcoes.php';
require_once __DIR__ . '/../php/config_email.php';

exigirAdmin($conn, 'index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('index.php');
}

exigirCsrf($_POST['csrf_token'] ?? null, 'index.php');

$usuarioId = (int)($_POST['usuario_id'] ?? 0);
$stmt = $conn->prepare("
    SELECT u.nome, u.email, u.assinatura_ate,
           (SELECT a.plano FROM assinaturas a WHERE a.usuario_id=u.id ORDER BY a.id DESC LIMIT 1) AS plano
    FROM usuarios u
    WHERE u.id = ? AND u.ativo = 1
    LIMIT 1
");
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario || !filter_var($usuario['email'], FILTER_VALIDATE_EMAIL)) {
    flash('erro', 'Usuário ou e-mail inválido.');
    redirecionar('index.php');
}

if (enviarEmailLembreteRenovacao($usuario['email'], $usuario['nome'], $usuario['plano'] ?? null, $usuario['assinatura_ate'] ?? null)) {
    flash('sucesso', 'Lembrete enviado para ' . $usuario['email'] . '.');
} else {
    flash('erro', 'Não foi possível enviar o e-mail. Verifique o SMTP do Gmail no .env.');
}

redirecionar('index.php');
