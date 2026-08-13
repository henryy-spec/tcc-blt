<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

exigirLogin('../entrar.php');

$id = (int)$_SESSION['id_usuario'];
$stmt = $conn->prepare('SELECT assinatura_ate FROM usuarios WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!assinaturaAtiva($usuario['assinatura_ate'] ?? null)) {
    flash('erro', 'Você precisa de uma assinatura ativa para jogar.');
    redirecionar('../pagamento.php');
}

$stmt = $conn->prepare('UPDATE usuarios SET primeiro_jogo = 1 WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

redirecionar('../Jogo/Chronoside.html');
