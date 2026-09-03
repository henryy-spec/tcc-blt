<?php
declare(strict_types=1);
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/funcoes.php';

header('Content-Type: application/json; charset=utf-8');

if (!usuarioLogado()) {
    http_response_code(401);
    echo json_encode(['erro' => 'É preciso estar logado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);
$csrf = is_array($dados) ? ($dados['csrf_token'] ?? null) : ($_POST['csrf_token'] ?? null);
$jogoSlug = is_array($dados) ? ($dados['jogo'] ?? '') : ($_POST['jogo'] ?? '');
$jogoSlug = strtolower(trim((string)$jogoSlug));

if (!validarCsrf($csrf)) {
    http_response_code(403);
    echo json_encode(['erro' => 'Token inválido. Recarregue a página.']);
    exit;
}

if ($jogoSlug === '' || !preg_match('/^[a-z0-9\-]{1,60}$/', $jogoSlug)) {
    http_response_code(422);
    echo json_encode(['erro' => 'Jogo inválido.']);
    exit;
}

$id = (int)$_SESSION['id_usuario'];
$favoritado = alternarFavorito($conn, $id, $jogoSlug);

echo json_encode(['ok' => true, 'favoritado' => $favoritado, 'jogo' => $jogoSlug]);
