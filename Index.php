<?php
declare(strict_types=1);
require_once __DIR__ . '/php/conexao.php';
require_once __DIR__ . '/php/funcoes.php';

$logado = usuarioLogado();
$nome = $_SESSION['nome_usuario'] ?? '';
$flash = obterFlash();

$progresso = null;
$favoritos = [];
if ($logado) {
    $idAtual = (int)$_SESSION['id_usuario'];
    $stmt = $conn->prepare('SELECT xp FROM usuarios WHERE id = ? LIMIT 1');

if (!$stmt) {
    die('Erro na consulta SQL: ' . $conn->error);
}

$stmt->bind_param('i', $idAtual);
$stmt->execute();
    $linha = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $progresso = progressoNivel((int)($linha['xp'] ?? 0));
    $favoritos = obterFavoritos($conn, $idAtual);
}
$favChronocide = in_array('chronocide', $favoritos, true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Blue Light — plataforma de jogos premium. Jogue, assine e desbloqueie conquistas.">
<link rel="stylesheet" href="Css/design-system.css">
<link rel="stylesheet" href="Css/Index.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<title>BlueLight | Plataforma de Jogos</title>
</head>
<body>

<?php if ($flash): ?><div class="flash-site <?= e($flash['tipo']) ?>" role="status"><?= e($flash['mensagem']) ?></div><?php endif; ?>

<header class="bl-header">
  <div class="container">
    <a class="bl-logo" href="Index.php#inicio">
      <img src="img/Logo Blue Light com contorno.png" alt="Logo Blue Light" id="blt">
      <span>BLUE<b>LIGHT</b></span>
    </a>

    <nav class="bl-nav" aria-label="Navegação principal">
      <ul>
        <li><a href="#inicio">Início</a></li>
        <li><a href="#jogos">Jogos</a></li>
        <li><a href="<?= $logado ? 'php/biblioteca.php' : 'entrar.php' ?>">Biblioteca</a></li>
        <li><a href="#planos">Planos</a></li>
        <li><a href="<?= $logado ? 'php/conquistas.php' : 'entrar.php' ?>">Conquistas</a></li>
      </ul>

      <form class="bl-search" role="search" onsubmit="event.preventDefault(); pesquisar();">
        <label for="pesquisa" class="visually-hidden">Pesquisar jogos</label>
        <input type="text" id="pesquisa" placeholder="Pesquisar jogos..." onkeydown="if(event.key==='Enter'){event.preventDefault(); pesquisar();}">
        <button type="submit" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>

      <button class="bl-theme-toggle" id="blThemeToggle" type="button" aria-label="Alternar tema"><i class="fa-solid fa-moon"></i></button>
      <button class="bl-theme-toggle" id="blSomToggle" type="button" aria-label="Ativar/desativar som"><i class="fa-solid fa-volume-xmark"></i></button>
      <?php if ($logado && usuarioEhAdmin($conn)): ?>
        <a class="perfil-link bl-admin-nav-link" href="admin/"><i class="fa-solid fa-gauge-high"></i><span>Admin</span></a>
      <?php endif; ?>

      <?php if ($logado): ?>
        <a class="perfil-link" href="php/perfil.php">
          <span class="avatar"><?= e(mb_strtoupper(mb_substr($nome, 0, 1))) ?><span class="bl-level-chip" style="position:absolute;bottom:-6px;right:-8px;font-size:9px;">Nv<?= (int)$progresso['nivel'] ?></span></span>
          <span>
            <?= e($nome) ?>
            <small style="display:block;font-size:10.5px;color:var(--text-2);font-weight:500;"><?= (int)$progresso['xp_no_nivel'] ?>/<?= (int)$progresso['xp_necessario'] ?> XP</small>
          </span>
        </a>
      <?php else: ?>
        <button class="bl-btn bl-btn-primary" id="abrirModal" type="button">Entrar</button>
      <?php endif; ?>
    </nav>

    <button class="bl-burger" type="button" aria-label="Abrir menu" aria-expanded="false" id="abrirMenuMobile">
      <i class="fa-solid fa-bars"></i>
    </button>
  </div>
</header>

<nav class="bl-mobile-menu" id="menuMobile" aria-label="Navegação mobile">
  <a href="#inicio">Início</a>
  <a href="#jogos">Jogos</a>
  <a href="<?= $logado ? 'php/biblioteca.php' : 'entrar.php' ?>">Biblioteca</a>
  <a href="#planos">Planos</a>
  <a href="<?= $logado ? 'php/conquistas.php' : 'entrar.php' ?>">Conquistas</a>
  <?php if ($logado): ?>
    <a href="php/perfil.php">Meu perfil</a>
  <?php else: ?>
    <a href="entrar.php">Entrar</a>
  <?php endif; ?>
</nav>

<main>

<section class="bl-hero" id="inicio">
  <div class="container">
    <div>
      <span class="bl-hero-eyebrow"><i class="fa-solid fa-bolt"></i> Plataforma de jogos premium</span>
      <h1>JOGUE SEM <span>LIMITES</span>, DE ONDE ESTIVER</h1>
      <p>Crie sua conta, escolha um plano e tenha acesso instantâneo a jogos, conquistas e uma biblioteca que evolui com você.</p>

      <div class="bl-hero-ctas">
        <a class="bl-btn bl-btn-primary" href="#jogos"><i class="fa-solid fa-compass"></i> Explorar jogos</a>
        <a class="bl-btn bl-btn-ghost" href="<?= $logado ? '#planos' : 'entrar.php' ?>"><i class="fa-solid fa-play"></i> Começar a jogar</a>
      </div>

      <form class="bl-search" role="search" onsubmit="event.preventDefault(); pesquisar();">
        <label for="pesquisa-hero" class="visually-hidden">Pesquisar jogos</label>
        <input type="text" id="pesquisa-hero" placeholder="Pesquisar jogos..." onkeydown="if(event.key==='Enter'){event.preventDefault(); document.getElementById('pesquisa').value=this.value; pesquisar();}">
        <button type="submit" aria-label="Buscar" onclick="document.getElementById('pesquisa').value=document.getElementById('pesquisa-hero').value;"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>

      <div class="bl-hero-stats">
        <div><strong>1</strong><span>Jogo integrado</span></div>
        <div><strong>3</strong><span>Planos flexíveis</span></div>
        <div><strong>24/7</strong><span>Suporte Enterprise</span></div>
      </div>
    </div>

    <div class="bl-hero-visual bl-reveal">
      <img src="img/darkhouse.jpg" alt="Cena do jogo Chronocide">
      <span class="bl-tag"><i class="fa-solid fa-fire"></i> Em destaque agora</span>
    </div>
  </div>
</section>

<section class="bl-section" id="jogos">
  <div class="container">
    <div class="bl-section-head bl-reveal">
      <span class="bl-eyebrow">Catálogo</span>
      <h2 class="bl-section-title">Jogos Mais Jogados</h2>
      <p class="bl-section-sub">Explore o universo de Chronocide através de diferentes cenários.</p>
    </div>

    <div class="bl-filter-row bl-reveal">
      <button class="bl-filter-chip active" data-bl-cat="todos" type="button">Todos</button>
      <button class="bl-filter-chip" data-bl-cat="acao" type="button">Ação</button>
      <button class="bl-filter-chip" data-bl-cat="aventura" type="button">Aventura</button>
      <button class="bl-filter-chip" data-bl-cat="misterio" type="button">Mistério</button>
      <button class="bl-filter-chip" data-bl-cat="rpg" type="button">RPG</button>
      <button class="bl-filter-chip" data-bl-cat="estrategia" type="button">Estratégia</button>
      <button class="bl-filter-chip" data-bl-cat="premium" type="button">Premium</button>
    </div>

    <div class="bl-cards">
      <article class="bl-card bl-game-card jogo-card <?= $logado ? '' : 'is-locked' ?> bl-reveal" data-cat="aventura">
        <div class="bl-badges">
          <span class="bl-badge bl-badge-popular"><i class="fa-solid fa-fire"></i> Popular</span>
        </div>
        <?php if ($logado): ?>
        <button class="bl-fav-heart <?= $favChronocide ? 'is-active' : '' ?>" type="button" data-bl-fav="chronocide" aria-pressed="<?= $favChronocide ? 'true' : 'false' ?>" aria-label="Favoritar Chronocide">
          <i class="fa-<?= $favChronocide ? 'solid' : 'regular' ?> fa-heart"></i>
        </button>
        <?php endif; ?>
        <div class="bl-thumb"><img src="img/island.jpeg" alt="Chronocide — Ilha do tempo"></div>
        <div class="bl-body">
          <span class="bl-cat">Aventura</span>
          <h3>Chronocide</h3>
          <p>Viagem no tempo e aventura em cenários inexplorados.</p>
          <a class="bl-btn bl-btn-outline" href="<?= $logado ? 'jogar.php' : 'entrar.php' ?>">Jogar agora</a>
        </div>
        <?php if (!$logado): ?>
        <div class="bl-locked-overlay"><i class="fa-solid fa-lock"></i><span>Requer conta e assinatura</span><a class="bl-btn bl-btn-primary" href="pagamento.php" style="margin-top:8px;">Ver planos</a></div>
        <?php endif; ?>
      </article>

      <article class="bl-card bl-game-card jogo-card <?= $logado ? '' : 'is-locked' ?> bl-reveal" data-cat="acao">
        <div class="bl-badges">
          <span class="bl-badge bl-badge-new"><i class="fa-solid fa-star"></i> Novo</span>
        </div>
        <?php if ($logado): ?>
        <button class="bl-fav-heart <?= $favChronocide ? 'is-active' : '' ?>" type="button" data-bl-fav="chronocide" aria-pressed="<?= $favChronocide ? 'true' : 'false' ?>" aria-label="Favoritar Chronocide">
          <i class="fa-<?= $favChronocide ? 'solid' : 'regular' ?> fa-heart"></i>
        </button>
        <?php endif; ?>
        <div class="bl-thumb"><img src="img/war.jpeg" alt="Chronocide — Ação em guerra"></div>
        <div class="bl-body">
          <span class="bl-cat">Ação</span>
          <h3>Chronocide</h3>
          <p>Ação em diferentes épocas da história.</p>
          <a class="bl-btn bl-btn-outline" href="<?= $logado ? 'jogar.php' : 'entrar.php' ?>">Jogar agora</a>
        </div>
        <?php if (!$logado): ?>
        <div class="bl-locked-overlay"><i class="fa-solid fa-lock"></i><span>Requer conta e assinatura</span><a class="bl-btn bl-btn-primary" href="pagamento.php" style="margin-top:8px;">Ver planos</a></div>
        <?php endif; ?>
      </article>

      <article class="bl-card bl-game-card jogo-card <?= $logado ? '' : 'is-locked' ?> bl-reveal" data-cat="misterio premium">
        <div class="bl-badges">
          <span class="bl-badge bl-badge-ok"><i class="fa-solid fa-eye"></i> Mistério</span>
          <span class="bl-badge bl-badge-popular"><i class="fa-solid fa-crown"></i> Premium</span>
        </div>
        <?php if ($logado): ?>
        <button class="bl-fav-heart <?= $favChronocide ? 'is-active' : '' ?>" type="button" data-bl-fav="chronocide" aria-pressed="<?= $favChronocide ? 'true' : 'false' ?>" aria-label="Favoritar Chronocide">
          <i class="fa-<?= $favChronocide ? 'solid' : 'regular' ?> fa-heart"></i>
        </button>
        <?php endif; ?>
        <div class="bl-thumb"><img src="img/hidden.jpeg" alt="Chronocide — Mistérios do tempo"></div>
        <div class="bl-body">
          <span class="bl-cat">Mistério</span>
          <h3>Chronocide</h3>
          <p>Explore os mistérios escondidos entre as eras.</p>
          <a class="bl-btn bl-btn-outline" href="<?= $logado ? 'jogar.php' : 'entrar.php' ?>">Jogar agora</a>
        </div>
        <?php if (!$logado): ?>
        <div class="bl-locked-overlay"><i class="fa-solid fa-lock"></i><span>Requer conta e assinatura</span><a class="bl-btn bl-btn-primary" href="pagamento.php" style="margin-top:8px;">Ver planos</a></div>
        <?php endif; ?>
      </article>

      <div class="bl-empty" id="blSemResultado" hidden>
        <div class="bl-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        <p style="margin:0;">Nenhum jogo encontrado nessa categoria.</p>
      </div>
    </div>
  </div>
</section>

<section class="bl-section">
  <div class="container">
    <div class="bl-section-head bl-reveal">
      <span class="bl-eyebrow">Destaque da semana</span>
      <h2 class="bl-section-title">Não perca essa experiência</h2>
    </div>

    <div class="bl-banner bl-reveal" data-bl-carousel>
      <a class="bl-slide active" href="<?= $logado ? 'jogar.php' : 'entrar.php' ?>">
        <img src="img/darkhouse.jpg" alt="Chronocide — destaque">
        <div class="bl-slide-text">
          <h2>Chronocide</h2>
          <p>Uma jornada através do tempo à espera de ser desvendada.</p>
          <span class="bl-btn bl-btn-primary"><?= $logado ? 'Jogar agora' : 'Entrar para jogar' ?></span>
        </div>
      </a>
      <a class="bl-slide" href="<?= $logado ? 'jogar.php' : 'entrar.php' ?>">
        <img src="img/island.jpeg" alt="Chronocide — ilha">
        <div class="bl-slide-text">
          <h2>Chronocide</h2>
          <p>Explore ilhas perdidas em diferentes períodos da história.</p>
          <span class="bl-btn bl-btn-primary"><?= $logado ? 'Jogar agora' : 'Entrar para jogar' ?></span>
        </div>
      </a>
      <div class="bl-dots">
        <button class="bl-dot active" type="button" aria-label="Slide 1"></button>
        <button class="bl-dot" type="button" aria-label="Slide 2"></button>
      </div>
    </div>
  </div>
</section>

<section class="bl-section">
  <div class="container">
    <div class="bl-section-head bl-reveal">
      <span class="bl-eyebrow">Explore</span>
      <h2 class="bl-section-title">Categorias</h2>
    </div>
    <div class="bl-cat-grid bl-reveal">
      <div class="bl-card bl-cat-chip"><i class="fa-solid fa-hourglass-half"></i><span>Aventura</span></div>
      <div class="bl-card bl-cat-chip"><i class="fa-solid fa-explosion"></i><span>Ação</span></div>
      <div class="bl-card bl-cat-chip"><i class="fa-solid fa-magnifying-glass"></i><span>Mistério</span></div>
      <div class="bl-card bl-cat-chip"><i class="fa-solid fa-chess"></i><span>Estratégia</span></div>
    </div>
  </div>
</section>

<section class="bl-section" id="planos">
  <div class="container">
    <div class="bl-section-head bl-reveal">
      <span class="bl-eyebrow">Assinatura</span>
      <h2 class="bl-section-title">Nossos Planos</h2>
      <p class="bl-section-sub">Cancele quando quiser. Sem taxas escondidas.</p>
    </div>

    <div class="bl-plan-grid">
      <?php
      $planos = [
        'basico' => ['Básico','19,90','1 GB RAM','10 GB SSD','Suporte Básico', false],
        'premium' => ['Premium','49,90','4 GB RAM','50 GB SSD','Suporte Prioritário', true],
        'enterprise' => ['Enterprise','99,90','8 GB RAM','100 GB SSD','Suporte 24/7', false],
      ];
      foreach ($planos as $slug => $p): ?>
      <div class="bl-card bl-plan <?= $p[5] ? 'is-recommended' : '' ?> bl-reveal">
        <?php if ($p[5]): ?><span class="bl-plan-tag">Mais escolhido</span><?php endif; ?>
        <h3><?= e($p[0]) ?></h3>
        <div class="bl-price">R$<?= e($p[1]) ?><small>/mês</small></div>
        <ul>
          <li><i class="fa-solid fa-circle-check"></i> <?= e($p[2]) ?></li>
          <li><i class="fa-solid fa-circle-check"></i> <?= e($p[3]) ?></li>
          <li><i class="fa-solid fa-circle-check"></i> <?= e($p[4]) ?></li>
        </ul>
        <a class="neymar" href="<?= $logado ? 'pagamento.php?plano=' . e($slug) : 'entrar.php' ?>">
          <button class="bl-btn <?= $p[5] ? 'bl-btn-primary' : 'bl-btn-ghost' ?>" type="button">Contratar</button>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="bl-section">
  <div class="container">
    <div class="bl-section-head bl-reveal">
      <span class="bl-eyebrow">Vantagens</span>
      <h2 class="bl-section-title">Benefícios da Assinatura</h2>
    </div>
    <div class="bl-benefits bl-reveal">
      <div class="bl-card bl-benefit"><i class="fa-solid fa-gauge-high"></i><h3>Acesso instantâneo</h3><p>Jogue direto do navegador, sem downloads pesados.</p></div>
      <div class="bl-card bl-benefit"><i class="fa-solid fa-trophy"></i><h3>Conquistas exclusivas</h3><p>Desbloqueie badges à medida que avança na plataforma.</p></div>
      <div class="bl-card bl-benefit"><i class="fa-solid fa-shield-halved"></i><h3>Pagamento seguro</h3><p>Assinaturas registradas com confirmação protegida.</p></div>
      <div class="bl-card bl-benefit"><i class="fa-solid fa-headset"></i><h3>Suporte dedicado</h3><p>Prioridade de atendimento conforme seu plano.</p></div>
    </div>
  </div>
</section>

<section class="bl-section">
  <div class="container">
    <div class="bl-section-head bl-reveal">
      <span class="bl-eyebrow">Progresso</span>
      <h2 class="bl-section-title">Conquistas</h2>
      <p class="bl-section-sub">Acompanhe sua evolução dentro da plataforma.</p>
    </div>
    <div class="bl-cards bl-reveal">
      <div class="bl-card bl-benefit"><i class="fa-solid fa-medal"></i><h3>Primeira assinatura</h3><p>Assine seu primeiro plano e desbloqueie essa conquista.</p></div>
      <div class="bl-card bl-benefit"><i class="fa-solid fa-gamepad"></i><h3>Primeiro jogo</h3><p>Abra qualquer jogo da biblioteca pela primeira vez.</p></div>
      <div class="bl-card bl-benefit">
        <a class="bl-btn bl-btn-outline" href="<?= $logado ? 'php/conquistas.php' : 'entrar.php' ?>">Ver minhas conquistas</a>
      </div>
    </div>
  </div>
</section>

</main>

<footer class="bl-footer" id="contato">
  <div class="container">
    <div>© 2026 BlueLight. Todos os direitos reservados.</div>
    <div class="bl-footer-links">
      <a href="termos.html">Termos de Uso</a>
      <a href="<?= $logado ? 'php/perfil.php' : 'login.php' ?>"><?= $logado ? 'Minha conta' : 'Criar conta' ?></a>
    </div>
  </div>
</footer>

<?php if (!$logado): ?>
<dialog id="modalLogin">
  <div class="modal-content">
    <button class="fechar" id="fecharModal" type="button" aria-label="Fechar">✕</button>
    <h2>Entrar</h2>
    <form action="php/login.php" method="post" data-bl-loading>
      <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
      <div class="bl-field">
        <i class="fa-regular fa-envelope bl-icon"></i>
        <input type="email" name="email" placeholder="E-mail" required>
      </div>
      <div class="bl-field">
        <i class="fa-solid fa-lock bl-icon"></i>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="button" class="bl-toggle-pass" aria-label="Mostrar senha"><i class="fa-regular fa-eye"></i></button>
      </div>
      <button type="submit" class="bl-btn bl-btn-primary">
        <span class="bl-spinner"></span><span class="bl-btn-label">Entrar</span>
      </button>
      <a href="login.php" class="cadastro">Cadastre-se</a>
      <a href="php/recuperarSenha.php" class="cadastro">Esqueci minha senha</a>
    </form>
  </div>
</dialog>
<?php endif; ?>

<a href="https://wa.me/5511999999999?text=Ola%20gostaria%20de%20mais%20informa%C3%A7%C3%B5es" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Conversar no WhatsApp"><i class="fab fa-whatsapp"></i></a>

<div id="blToasts" class="bl-toasts" aria-live="polite"></div>
<div id="blLevelUpModal" class="bl-fx-modal" hidden></div>
<div id="blConquistaModal" class="bl-fx-modal" hidden></div>

<?php if ($logado): ?><script>window.BL_CSRF = <?= json_encode(csrfToken()) ?>;</script><?php endif; ?>
<script src="Js/app.js"></script>
<?php renderGamificacaoPayload(); ?>
<script>
<?php if (!$logado): ?>
const modal=document.getElementById('modalLogin');
document.getElementById('abrirModal').addEventListener('click',()=>modal.showModal());
document.getElementById('fecharModal').addEventListener('click',()=>modal.close());
modal.addEventListener('click',(event)=>{if(event.target===modal)modal.close();});
<?php endif; ?>
function pesquisar(){
  const termo=document.getElementById('pesquisa').value.toLowerCase().trim();
  let visiveis=0;
  document.querySelectorAll('.jogo-card').forEach(card=>{
    const mostra=(!termo||card.innerText.toLowerCase().includes(termo));
    card.style.display=mostra?'':'none';
    if(mostra) visiveis++;
  });
  const semResultado=document.getElementById('blSemResultado');
  if(semResultado) semResultado.hidden = visiveis>0;
  document.getElementById('jogos').scrollIntoView({behavior:'smooth'});
}
</script>
</body></html>
