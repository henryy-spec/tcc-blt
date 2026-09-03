<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/conexao.php';
require_once __DIR__ . '/../php/funcoes.php';

exigirAdmin($conn, '../entrar.php');

$flash = obterFlash();
$agora = date('Y-m-d H:i:s');
$limite7 = date('Y-m-d H:i:s', strtotime('+7 days'));

function adminQuery(mysqli $conn, string $sql): array
{
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

$totais = [
    'usuarios' => (int)($conn->query("SELECT COUNT(*) c FROM usuarios WHERE ativo=1")->fetch_assoc()['c'] ?? 0),
    'ativos' => (int)($conn->query("SELECT COUNT(*) c FROM usuarios WHERE ativo=1 AND assinatura_ate >= NOW()")->fetch_assoc()['c'] ?? 0),
    'expirando' => (int)($conn->query("SELECT COUNT(*) c FROM usuarios WHERE ativo=1 AND assinatura_ate >= NOW() AND assinatura_ate <= '$limite7'")->fetch_assoc()['c'] ?? 0),
    'receita' => (float)($conn->query("SELECT COALESCE(SUM(valor),0) total FROM pagamentos WHERE status='pago'")->fetch_assoc()['total'] ?? 0),
];

$ultimosPagamentos = adminQuery($conn, "
    SELECT u.nome, u.email, a.plano, a.metodo, a.valor, a.status, a.criado_em
    FROM assinaturas a
    INNER JOIN usuarios u ON u.id = a.usuario_id
    ORDER BY a.criado_em DESC
    LIMIT 10
");

$usuarios = adminQuery($conn, "
    SELECT
        u.id, u.nome, u.email, u.ativo, u.criado_em, u.ultimo_login, u.assinatura_ate,
        COALESCE(a.plano, '-') AS ultimo_plano,
        COALESCE(a.metodo, '-') AS metodo_pagamento,
        COALESCE(a.valor, 0) AS ultimo_valor
    FROM usuarios u
    LEFT JOIN (
        SELECT a1.*
        FROM assinaturas a1
        INNER JOIN (
            SELECT usuario_id, MAX(id) id
            FROM assinaturas
            GROUP BY usuario_id
        ) ult ON ult.id = a1.id
    ) a ON a.usuario_id = u.id
    ORDER BY u.criado_em DESC
    LIMIT 50
");

function statusAssinaturaAdmin(?string $data): array
{
    if (!$data) return ['Sem plano', 'off'];
    $ts = strtotime($data);
    if ($ts === false || $ts < time()) return ['Expirada', 'off'];
    if ($ts <= strtotime('+7 days')) return ['Expira em breve', 'soon'];
    return ['Ativa', 'on'];
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin | Blue Light</title>
<link rel="stylesheet" href="../Css/design-system.css">
<link rel="stylesheet" href="dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<header class="bl-admin-header">
  <div class="bl-admin-inner">
    <a href="../Index.php" class="bl-admin-brand"><img src="../img/Logo Blue Light com contorno.png" alt=""> Blue Light <span>Admin</span></a>
    <nav>
      <a href="../Index.php"><i class="fa-solid fa-gamepad"></i> Site</a>
      <button id="blThemeToggle" class="bl-admin-icon" type="button" aria-label="Alternar tema"><i class="fa-solid fa-moon"></i></button>
      <button id="blSomToggle" class="bl-admin-icon" type="button" aria-label="Desativar som"><i class="fa-solid fa-volume-high"></i></button>
    </nav>
  </div>
</header>

<main class="container bl-admin-page">
  <div class="bl-admin-title">
    <div>
      <p class="bl-admin-kicker">Painel de controle</p>
      <h1>Dashboard</h1>
      <p>Visão rápida da plataforma, assinaturas e usuários.</p>
    </div>
  </div>
  <p><a class="bl-btn" href="teste_smtp.php">Testar SMTP</a></p>

  <?php if ($flash): ?>
    <div class="bl-admin-alert <?= e($flash['tipo']) ?>"><?= e($flash['mensagem']) ?></div>
  <?php endif; ?>

  <section class="bl-admin-stats">
    <article class="bl-admin-stat"><span>Usuários</span><strong><?= $totais['usuarios'] ?></strong><small>contas ativas</small></article>
    <article class="bl-admin-stat"><span>Assinaturas ativas</span><strong><?= $totais['ativos'] ?></strong><small>no momento</small></article>
    <article class="bl-admin-stat"><span>Renovações</span><strong><?= $totais['expirando'] ?></strong><small>nos próximos 7 dias</small></article>
    <article class="bl-admin-stat"><span>Receita registrada</span><strong><?= e(formatarMoeda($totais['receita'])) ?></strong><small>pagamentos aprovados</small></article>
  </section>

  <section class="bl-admin-grid">
    <div class="bl-admin-card">
      <div class="bl-admin-card-head">
        <div><h2>Usuários</h2><p>Plano e último método de pagamento.</p></div>
      </div>
      <div class="bl-table-wrap">
        <table>
          <thead><tr><th>Usuário</th><th>Plano</th><th>Pagamento</th><th>Assinatura</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($usuarios as $u): [$statusTxt,$statusClass] = statusAssinaturaAdmin($u['assinatura_ate']); ?>
            <tr>
              <td><strong><?= e($u['nome']) ?></strong><small><?= e($u['email']) ?></small></td>
              <td><?= e(ucfirst($u['ultimo_plano'])) ?></td>
              <td><?= e(strtoupper($u['metodo_pagamento'])) ?></td>
              <td><span class="bl-admin-status <?= $statusClass ?>"><?= e($statusTxt) ?></span><small><?= $u['assinatura_ate'] ? e(date('d/m/Y', strtotime($u['assinatura_ate']))) : '—' ?></small></td>
              <td>
                <?php if ($u['email']): ?>
                <form action="enviar_lembrete.php" method="post" class="bl-reminder-form">
                  <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                  <input type="hidden" name="usuario_id" value="<?= (int)$u['id'] ?>">
                  <button type="submit" title="Enviar lembrete de renovação"><i class="fa-regular fa-envelope"></i></button>
                </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="bl-admin-card">
      <div class="bl-admin-card-head">
        <div><h2>Pagamentos recentes</h2><p>Últimas assinaturas registradas.</p></div>
      </div>
      <div class="bl-payment-list">
        <?php if (!$ultimosPagamentos): ?><div class="bl-admin-empty">Nenhum pagamento registrado ainda.</div><?php endif; ?>
        <?php foreach ($ultimosPagamentos as $p): ?>
          <div class="bl-payment-item">
            <div class="bl-payment-main">
              <strong><?= e($p['nome']) ?></strong>
              <small><?= e($p['email']) ?> · <?= e(ucfirst($p['plano'])) ?></small>
            </div>
            <div class="bl-payment-side">
              <strong><?= e(formatarMoeda((float)$p['valor'])) ?></strong>
              <small><?= e(strtoupper($p['metodo'])) ?> · <?= e(date('d/m/Y H:i', strtotime($p['criado_em']))) ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<script src="../Js/app.js"></script>
<script>
document.querySelectorAll('.bl-reminder-form').forEach(function(form){
  form.addEventListener('submit', function(){
    var button = form.querySelector('button');
    if(button){ button.disabled = true; button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>'; }
  });
});
</script>
</body>
</html>
