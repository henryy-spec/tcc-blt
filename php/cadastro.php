<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('../login.php');
}

exigirCsrf($_POST['csrf_token'] ?? null, '../login.php');

$nome = trim((string)($_POST['nome'] ?? ''));
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$senha = (string)($_POST['senha'] ?? '');
$confirmar = (string)($_POST['confirmar_senha'] ?? '');
$termos = isset($_POST['termos']);

if ($nome === '' || mb_strlen($nome) < 2 || mb_strlen($nome) > 120) {
    flash('erro', 'Informe um nome válido entre 2 e 120 caracteres.');
    redirecionar('../login.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    flash('erro', 'Informe um e-mail válido.');
    redirecionar('../login.php');
}

if (strlen($senha) < 8) {
    flash('erro', 'A senha precisa ter pelo menos 8 caracteres.');
    redirecionar('../login.php');
}

if ($senha !== $confirmar) {
    flash('erro', 'As senhas não coincidem.');
    redirecionar('../login.php');
}

if (!$termos) {
    flash('erro', 'Você precisa aceitar os Termos de Uso.');
    redirecionar('../login.php');
}

$stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$existe = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existe) {
    flash('erro', 'Este e-mail já está cadastrado.');
    redirecionar('../login.php');
}

$hash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $nome, $email, $hash);

if (!$stmt->execute()) {
    $stmt->close();
    flash('erro', 'Não foi possível criar a conta.');
    redirecionar('../login.php');
}

$id = (int)$stmt->insert_id;
$stmt->close();

session_regenerate_id(true);
$_SESSION['id_usuario'] = $id;
$_SESSION['nome_usuario'] = $nome;

registrarAtividade($conn, $id, 'conta', 'Criou a conta na Blue Light.');
desbloquearConquista($conn, $id, 'bem_vindo');

flash('sucesso', 'Conta criada com sucesso!');
redirecionar('../php/perfil.php');
