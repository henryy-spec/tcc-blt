<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

exigirLogin('../entrar.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../pagamento.php');
}

exigirCsrf($_POST['csrf_token'] ?? null, '../pagamento.php');

$plano = strtolower(trim((string)($_POST['plano'] ?? '')));
$metodo = strtolower(trim((string)($_POST['metodo'] ?? '')));
$info = planoInfo($plano);

if (!$info || !in_array($metodo, ['pix', 'cartao'], true)) {
    flash('erro', 'Plano ou método de pagamento inválido.');
    redirecionar('../pagamento.php');
}

$id = (int)$_SESSION['id_usuario'];

// Esta é uma confirmação de pagamento SIMULADA para o projeto/TCC.
$conn->begin_transaction();

try {
    $stmt = $conn->prepare('SELECT assinatura_ate FROM usuarios WHERE id = ? FOR UPDATE');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $inicio = (!empty($usuario['assinatura_ate']) && strtotime($usuario['assinatura_ate']) > time())
        ? strtotime($usuario['assinatura_ate'])
        : time();
    $ate = date('Y-m-d H:i:s', strtotime('+' . $info['dias'] . ' days', $inicio));
    $valor = (float)$info['valor'];

    $stmt = $conn->prepare('INSERT INTO assinaturas (usuario_id, plano, valor, metodo, status) VALUES (?, ?, ?, ?, ?)');
    $status = 'pago';
    $stmt->bind_param('isdss', $id, $plano, $valor, $metodo, $status);
    if (!$stmt->execute()) {
        throw new RuntimeException('Falha ao registrar assinatura.');
    }
    $assinaturaId = (int)$stmt->insert_id;
    $stmt->close();

    // Mantém a tabela de pagamentos sincronizada para o dashboard administrativo.
    $stmt = $conn->prepare('INSERT INTO pagamentos (assinatura_id, valor, forma_pagamento, status) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('idss', $assinaturaId, $valor, $metodo, $status);
    if (!$stmt->execute()) {
        throw new RuntimeException('Falha ao registrar pagamento.');
    }
    $stmt->close();

    $stmt = $conn->prepare('UPDATE usuarios SET assinatura_ate = ?, primeira_assinatura = 1 WHERE id = ?');
    $stmt->bind_param('si', $ate, $id);
    if (!$stmt->execute()) {
        throw new RuntimeException('Falha ao atualizar assinatura.');
    }
    $stmt->close();

    $conn->commit();

    registrarAtividade($conn, $id, 'assinatura', 'Assinou o plano ' . $info['nome'] . '.');
    desbloquearConquista($conn, $id, 'primeira_assinatura');

    flash('sucesso', 'Pagamento simulado aprovado. Seu plano ' . $info['nome'] . ' está ativo até ' . date('d/m/Y', strtotime($ate)) . '.');
    redirecionar('../php/perfil.php');
} catch (Throwable $e) {
    $conn->rollback();
    flash('erro', 'Não foi possível registrar a assinatura.');
    redirecionar('../pagamento.php?plano=' . urlencode($plano));
}
