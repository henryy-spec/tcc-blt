<?php
declare(strict_types=1);
require_once __DIR__ . '/php/funcoes.php';

exigirLogin('entrar.php');

$plano = strtolower(trim((string)($_GET['plano'] ?? $_POST['plano'] ?? 'premium')));
if (!planoInfo($plano)) $plano = 'premium';
$info = planoInfo($plano);
$flash = obterFlash();

$todosPlanos = [
    'basico' => planoInfo('basico'),
    'premium' => planoInfo('premium'),
    'enterprise' => planoInfo('enterprise'),
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="Css/design-system.css">
<link rel="stylesheet" href="Css/pagamento.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<title>Pagamento | Blue Light</title>
</head>
<body>
<main class="container bl-checkout-page">
  <div class="bl-checkout-head">
    <h1>Assine a Blue Light</h1>
    <p>Escolha o plano ideal e confirme o pagamento (simulado).</p>
  </div>

  <?php if ($flash): ?><div class="bl-flash <?= e($flash['tipo']) ?>" role="status"><?= e($flash['mensagem']) ?></div><?php endif; ?>

  <div class="bl-checkout-grid">
    <div class="bl-card bl-plan-compare">
      <h3>Comparar planos</h3>
      <?php foreach ($todosPlanos as $slug => $p): ?>
        <a href="pagamento.php?plano=<?= e($slug) ?>" style="text-decoration:none;color:inherit;display:block;">
          <div class="bl-compare-row <?= $slug === $plano ? 'is-active' : '' ?>">
            <div>
              <div class="bl-compare-name"><?= e($p['nome']) ?></div>
              <div class="bl-compare-specs"><?= e($p['ram']) ?> · <?= e($p['ssd']) ?> · <?= e($p['suporte']) ?></div>
            </div>
            <div class="bl-compare-price">R$<?= e(number_format($p['valor'], 2, ',', '.')) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="bl-card bl-checkout-card">
      <div class="bl-resumo">
        <h3><?= e($info['nome']) ?></h3>
        <div class="bl-resumo-preco"><?= e(formatarMoeda((float)$info['valor'])) ?>/mês</div>
        <div class="bl-resumo-specs"><?= e($info['ram']) ?> · <?= e($info['ssd']) ?> · <?= e($info['suporte']) ?></div>
      </div>

      <form action="php/assinatura.php" method="post" data-bl-loading>
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="plano" value="<?= e($plano) ?>">

        <h3 style="margin-bottom:10px;font-size:15px;color:var(--text-2);text-transform:uppercase;letter-spacing:.5px;">Método de pagamento</h3>
        <div class="bl-metodo-grid">
          <input type="radio" id="pix" name="metodo" value="pix" required>
          <label for="pix"><i class="fa-solid fa-qrcode"></i> PIX</label>
          <input type="radio" id="cartao" name="metodo" value="cartao">
          <label for="cartao"><i class="fa-regular fa-credit-card"></i> Cartão</label>
        </div>

        <div id="pixInfo" class="bl-metodo-info">
          <div class="bl-pix-icon"><i class="fa-solid fa-qrcode"></i></div>
          <p>PIX demonstrativo: nenhum dinheiro real será cobrado. Clique em confirmar para simular o pagamento.</p>
        </div>
        <div id="cartaoInfo" class="bl-metodo-info">
          <p>Cartão demonstrativo: não informe um cartão real. Nenhum dado de cartão é armazenado.</p>
        </div>

        <div class="bl-demo-note"><i class="fa-solid fa-circle-info"></i> Modo TCC/demonstração: a confirmação registra a assinatura no banco, sem cobrança real.</div>

        <button class="bl-btn bl-btn-primary bl-checkout-submit" type="submit">
          <span class="bl-spinner"></span><span class="bl-btn-label">Confirmar pagamento</span>
        </button>
      </form>

      <a class="bl-voltar" href="Index.php"><i class="fa-solid fa-arrow-left"></i> Voltar para o site</a>
    </div>
  </div>
</main>

<script src="Js/app.js"></script>
<script>
document.querySelectorAll('input[name="metodo"]').forEach(r=>r.addEventListener('change',()=>{
  document.getElementById('pixInfo').classList.toggle('is-visible', document.getElementById('pix').checked);
  document.getElementById('cartaoInfo').classList.toggle('is-visible', document.getElementById('cartao').checked);
}));
</script>
</body></html>
