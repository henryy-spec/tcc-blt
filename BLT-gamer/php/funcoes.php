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

/* ============================================================
   GAMIFICAÇÃO — XP, níveis, conquistas, atividades, favoritos.
   Tudo validado e persistido no servidor/MySQL. O JavaScript
   apenas EXIBE o que o PHP calculou; nunca decide XP/nível.
   ============================================================ */

/** XP necessário para completar o nível informado (curva progressiva simples). */
function xpParaNivel(int $nivel): int
{
    return 100 * $nivel * $nivel;
}

/** Calcula o nível atual a partir do XP total acumulado. */
function calcularNivel(int $xpTotal): int
{
    $nivel = 1;
    while ($xpTotal >= xpParaNivel($nivel)) {
        $nivel++;
    }
    return $nivel;
}

/** Retorna [nivel, xpAtualNoNivel, xpNecessarioNoNivel, percentual]. */
function progressoNivel(int $xpTotal): array
{
    $nivel = calcularNivel($xpTotal);
    $xpInicioNivel = $nivel > 1 ? xpParaNivel($nivel - 1) : 0;
    $xpFimNivel = xpParaNivel($nivel);
    $xpNoNivel = $xpTotal - $xpInicioNivel;
    $xpNecessario = max(1, $xpFimNivel - $xpInicioNivel);
    $percentual = (int) round(min(100, max(0, ($xpNoNivel / $xpNecessario) * 100)));

    return [
        'nivel' => $nivel,
        'xp_total' => $xpTotal,
        'xp_no_nivel' => $xpNoNivel,
        'xp_necessario' => $xpNecessario,
        'percentual' => $percentual,
    ];
}

/**
 * Adiciona XP a um usuário de forma segura (transação simples) e
 * detecta level up. Retorna dados para exibir feedback ao usuário.
 */
function adicionarXp(mysqli $conn, int $usuarioId, int $quantidade, string $motivo = ''): array
{
    if ($quantidade <= 0) {
        return ['subiu_nivel' => false];
    }

    $stmt = $conn->prepare('SELECT xp, nivel FROM usuarios WHERE id = ? FOR UPDATE');
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $atual = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$atual) {
        return ['subiu_nivel' => false];
    }

    $nivelAntes = (int)$atual['nivel'];
    $novoXp = (int)$atual['xp'] + $quantidade;
    $novoNivel = calcularNivel($novoXp);

    $stmt = $conn->prepare('UPDATE usuarios SET xp = ?, nivel = ? WHERE id = ?');
    $stmt->bind_param('iii', $novoXp, $novoNivel, $usuarioId);
    $stmt->execute();
    $stmt->close();

    $subiuNivel = $novoNivel > $nivelAntes;

    if ($motivo !== '') {
        registrarAtividade($conn, $usuarioId, 'xp', $motivo . ' (+' . $quantidade . ' XP)');
    }

    if ($subiuNivel) {
        registrarAtividade($conn, $usuarioId, 'nivel', 'Alcançou o nível ' . $novoNivel . '.');
        if ($novoNivel >= 5) {
            desbloquearConquista($conn, $usuarioId, 'nivel_5');
        }
        $_SESSION['level_up'] = ['nivel_anterior' => $nivelAntes, 'nivel_novo' => $novoNivel];
    }

    return [
        'subiu_nivel' => $subiuNivel,
        'nivel_anterior' => $nivelAntes,
        'nivel_novo' => $novoNivel,
        'xp_ganho' => $quantidade,
    ];
}

/** Registra uma atividade no feed do usuário (usado no perfil / dashboard). */
function registrarAtividade(mysqli $conn, int $usuarioId, string $tipo, string $descricao): void
{
    $stmt = $conn->prepare('INSERT INTO atividades (usuario_id, tipo, descricao) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $usuarioId, $tipo, $descricao);
    $stmt->execute();
    $stmt->close();
}

/** Busca as atividades mais recentes do usuário. */
function obterAtividades(mysqli $conn, int $usuarioId, int $limite = 6): array
{
    $stmt = $conn->prepare('SELECT tipo, descricao, criado_em FROM atividades WHERE usuario_id = ? ORDER BY criado_em DESC LIMIT ?');
    $stmt->bind_param('ii', $usuarioId, $limite);
    $stmt->execute();
    $linhas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $linhas;
}

/**
 * Desbloqueia uma conquista pelo slug, se ainda não desbloqueada.
 * Concede o XP da conquista automaticamente. Retorna os dados da
 * conquista desbloqueada agora (ou null se já tinha ou não existe).
 */
function desbloquearConquista(mysqli $conn, int $usuarioId, string $slug): ?array
{
    $stmt = $conn->prepare('SELECT id, nome, descricao, icone, raridade, xp FROM conquistas WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $conquista = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$conquista) {
        return null;
    }

    $conquistaId = (int)$conquista['id'];
    $stmt = $conn->prepare('INSERT IGNORE INTO usuario_conquistas (usuario_id, conquista_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $usuarioId, $conquistaId);
    $stmt->execute();
    $novo = $stmt->affected_rows > 0;
    $stmt->close();

    if (!$novo) {
        return null;
    }

    registrarAtividade($conn, $usuarioId, 'conquista', 'Desbloqueou a conquista "' . $conquista['nome'] . '".');
    adicionarXp($conn, $usuarioId, (int)$conquista['xp']);

    // Guarda na sessão para exibir o modal de conquista na próxima página carregada.
    $_SESSION['conquista_desbloqueada'][] = [
        'nome' => $conquista['nome'],
        'descricao' => $conquista['descricao'],
        'icone' => $conquista['icone'],
        'raridade' => $conquista['raridade'],
        'xp' => (int)$conquista['xp'],
    ];

    return $conquista;
}

/** Retorna todas as conquistas do catálogo com o status (desbloqueada ou não) do usuário. */
function obterConquistasUsuario(mysqli $conn, int $usuarioId): array
{
    $sql = 'SELECT c.id, c.slug, c.nome, c.descricao, c.icone, c.categoria, c.raridade, c.xp,
                   uc.desbloqueada_em
            FROM conquistas c
            LEFT JOIN usuario_conquistas uc ON uc.conquista_id = c.id AND uc.usuario_id = ?
            ORDER BY (uc.desbloqueada_em IS NULL) ASC, c.xp ASC';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $linhas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $linhas;
}

/** Consome (lê e limpa) as conquistas desbloqueadas nesta sessão, para exibir o modal uma vez. */
function consumirConquistasDesbloqueadas(): array
{
    $lista = $_SESSION['conquista_desbloqueada'] ?? [];
    unset($_SESSION['conquista_desbloqueada']);
    return is_array($lista) ? $lista : [];
}

/** Consome (lê e limpa) a notificação de level-up desta sessão, para exibir uma única vez. */
function consumirLevelUp(): ?array
{
    $levelUp = $_SESSION['level_up'] ?? null;
    unset($_SESSION['level_up']);
    return is_array($levelUp) ? $levelUp : null;
}

/**
 * Imprime um <script> com as conquistas desbloqueadas e o level-up
 * pendentes desta sessão, para o app.js exibir os modais/toasts.
 * Chamar uma vez, perto do fim do <body>, em páginas logadas.
 */
function renderGamificacaoPayload(): void
{
    $conquistas = consumirConquistasDesbloqueadas();
    $levelUp = consumirLevelUp();
    if (!$conquistas && !$levelUp) {
        return;
    }
    echo '<script id="bl-gamificacao-payload" type="application/json">'
        . json_encode(['conquistas' => $conquistas, 'levelUp' => $levelUp], JSON_UNESCAPED_UNICODE)
        . '</script>';
}

/** Alterna favorito (adiciona/remove) e retorna o novo estado (true = favoritado). */
function alternarFavorito(mysqli $conn, int $usuarioId, string $jogoSlug): bool
{
    $stmt = $conn->prepare('SELECT 1 FROM favoritos WHERE usuario_id = ? AND jogo_slug = ? LIMIT 1');
    $stmt->bind_param('is', $usuarioId, $jogoSlug);
    $stmt->execute();
    $existe = (bool)$stmt->get_result()->fetch_row();
    $stmt->close();

    if ($existe) {
        $stmt = $conn->prepare('DELETE FROM favoritos WHERE usuario_id = ? AND jogo_slug = ?');
        $stmt->bind_param('is', $usuarioId, $jogoSlug);
        $stmt->execute();
        $stmt->close();
        return false;
    }

    $stmt = $conn->prepare('INSERT INTO favoritos (usuario_id, jogo_slug) VALUES (?, ?)');
    $stmt->bind_param('is', $usuarioId, $jogoSlug);
    $stmt->execute();
    $stmt->close();

    registrarAtividade($conn, $usuarioId, 'favorito', 'Favoritou um jogo.');
    desbloquearConquista($conn, $usuarioId, 'primeiro_favorito');
    return true;
}

/** Retorna o conjunto (slugs) de jogos favoritados pelo usuário. */
function obterFavoritos(mysqli $conn, int $usuarioId): array
{
    $stmt = $conn->prepare('SELECT jogo_slug FROM favoritos WHERE usuario_id = ?');
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $linhas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return array_column($linhas, 'jogo_slug');
}

/**
 * Concede XP de "login diário" no máximo uma vez por dia (evita farm via F5).
 * Controlado inteiramente pelo servidor através da coluna ultimo_xp_login.
 */
function xpLoginDiario(mysqli $conn, int $usuarioId): void
{
    $hoje = date('Y-m-d');
    $stmt = $conn->prepare('SELECT ultimo_xp_login FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (($row['ultimo_xp_login'] ?? null) === $hoje) {
        return;
    }

    $stmt = $conn->prepare('UPDATE usuarios SET ultimo_xp_login = ? WHERE id = ?');
    $stmt->bind_param('si', $hoje, $usuarioId);
    $stmt->execute();
    $stmt->close();

    adicionarXp($conn, $usuarioId, 15, 'Login diário');
}
