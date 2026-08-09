<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../entrar.php');
}

exigirCsrf($_POST['csrf_token'] ?? null, '../entrar.php');

$email = strtolower(trim((string)($_POST['email'] ?? '')));
$senha = (string)($_POST['senha'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
    flash('erro', 'Informe e-mail e senha.');
    redirecionar('../entrar.php');
}

$stmt = $conn->prepare('SELECT id, nome, email, senha FROM usuarios WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario || !password_verify($senha, $usuario['senha'])) {
    flash('erro', 'E-mail ou senha incorretos.');
    redirecionar('../entrar.php');
}

if (password_needs_rehash($usuario['senha'], PASSWORD_DEFAULT)) {
    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
    $id = (int)$usuario['id'];
    $stmt->bind_param('si', $novoHash, $id);
    $stmt->execute();
    $stmt->close();
}

session_regenerate_id(true);
$_SESSION['id_usuario'] = (int)$usuario['id'];
$_SESSION['nome_usuario'] = $usuario['nome'];

$id = (int)$usuario['id'];
$stmt = $conn->prepare('UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

redirecionar('../php/perfil.php');
