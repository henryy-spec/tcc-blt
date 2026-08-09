<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function usuarioLogado(): bool
{
    return isset($_SESSION['id_usuario']) && (int)$_SESSION['id_usuario'] > 0;
}

function exigirLogin(string $destino = '../entrar.php'): void
{
    if (!usuarioLogado()) {
        flash('erro', 'Faça login para continuar.');
        redirecionar($destino);
    }
}

function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function obterFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validarCsrf(?string $token): bool
{
    return is_string($token) && $token !== '' && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function exigirCsrf(?string $token, string $destino): void
{
    if (!validarCsrf($token)) {
        flash('erro', 'A sessão do formulário expirou. Tente novamente.');
        redirecionar($destino);
    }
}

function planoInfo(string $slug): ?array
{
    $planos = [
        'basico' => ['nome' => 'Básico', 'valor' => 19.90, 'dias' => 30, 'ram' => '1 GB RAM', 'ssd' => '10 GB SSD', 'suporte' => 'Suporte Básico'],
        'premium' => ['nome' => 'Premium', 'valor' => 49.90, 'dias' => 30, 'ram' => '4 GB RAM', 'ssd' => '50 GB SSD', 'suporte' => 'Suporte Prioritário'],
        'enterprise' => ['nome' => 'Enterprise', 'valor' => 99.90, 'dias' => 30, 'ram' => '8 GB RAM', 'ssd' => '100 GB SSD', 'suporte' => 'Suporte 24/7'],
    ];
    return $planos[$slug] ?? null;
}

function assinaturaAtiva(?string $assinaturaAte): bool
{
    return !empty($assinaturaAte) && strtotime($assinaturaAte) !== false && strtotime($assinaturaAte) >= time();
}

function formatarMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}
