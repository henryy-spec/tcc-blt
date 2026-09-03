// Blue Light — comportamentos de UI. Nada aqui decide XP, nível,
// conquistas ou acesso: isso é sempre validado e persistido pelo PHP/MySQL.
// Este arquivo só EXIBE o que o servidor já calculou.
(function () {
  "use strict";

  var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ============================================================
     TEMA (dark/light) — preferência visual persistida no navegador.
     ============================================================ */
  (function tema() {
    var root = document.documentElement;
    var KEY = 'bl-tema';

    function aplicarTema(tema) {
      if (tema === 'light') {
        root.setAttribute('data-theme', 'light');
      } else {
        root.removeAttribute('data-theme');
        tema = 'dark';
      }

      localStorage.setItem(KEY, tema);

      document.querySelectorAll('#blThemeToggle, .bl-theme-toggle').forEach(function (btn) {
        var claro = root.getAttribute('data-theme') === 'light';
        btn.innerHTML = '<i class="fa-solid fa-' + (claro ? 'sun' : 'moon') + '"></i>';
        btn.setAttribute('aria-label', claro ? 'Mudar para tema escuro' : 'Mudar para tema claro');
        btn.setAttribute('title', claro ? 'Tema escuro' : 'Tema claro');
      });
    }

    var salvo = localStorage.getItem(KEY);
    aplicarTema(salvo === 'light' ? 'light' : 'dark');

    // Delegação de evento: continua funcionando mesmo se o header for recriado.
    document.addEventListener('click', function (event) {
      var btn = event.target.closest && event.target.closest('#blThemeToggle, .bl-theme-toggle');
      if (!btn) return;

      event.preventDefault();
      var claro = root.getAttribute('data-theme') === 'light';
      aplicarTema(claro ? 'dark' : 'light');
    });
  })();

  /* ============================================================
     SOM — controla os efeitos da plataforma e, no jogo, envia o
     comando de mute/unmute para o iframe do Chronocide.
     ============================================================ */
  var somAtivo = localStorage.getItem('bl-som') !== 'off';
  var audioCtx = null;

  function atualizarBotoesSom() {
    document.querySelectorAll('#blSomToggle, .bl-som-toggle').forEach(function (btn) {
      btn.innerHTML = '<i class="fa-solid fa-volume-' + (somAtivo ? 'high' : 'xmark') + '"></i>';
      btn.setAttribute('aria-label', somAtivo ? 'Desativar som' : 'Ativar som');
      btn.setAttribute('title', somAtivo ? 'Desativar som' : 'Ativar som');
      btn.setAttribute('aria-pressed', somAtivo ? 'true' : 'false');
    });
  }

  function tocarBip(freq, duracao) {
    if (!somAtivo) return;
    try {
      var AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (!AudioContextClass) return;
      audioCtx = audioCtx || new AudioContextClass();
      if (audioCtx.state === 'suspended') audioCtx.resume().catch(function(){});
      var osc = audioCtx.createOscillator();
      var gain = audioCtx.createGain();
      osc.type = 'sine';
      osc.frequency.value = freq;
      gain.gain.value = 0.06;
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start();
      gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duracao);
      osc.stop(audioCtx.currentTime + duracao);
    } catch (e) {}
  }

  function enviarComandoSomAoJogo() {
    document.querySelectorAll('iframe[src*="Chronoside"]').forEach(function (frame) {
      try {
        frame.contentWindow.postMessage({
          type: 'BL_AUDIO_TOGGLE',
          muted: !somAtivo
        }, '*');
      } catch (e) {}
    });
  }

  atualizarBotoesSom();

  document.addEventListener('click', function (event) {
    var btn = event.target.closest && event.target.closest('#blSomToggle, .bl-som-toggle');
    if (!btn) return;

    event.preventDefault();
    somAtivo = !somAtivo;
    localStorage.setItem('bl-som', somAtivo ? 'on' : 'off');
    atualizarBotoesSom();

    if (somAtivo) tocarBip(660, 0.12);
    enviarComandoSomAoJogo();
  });

  window.addEventListener('load', function () {
    atualizarBotoesSom();
    enviarComandoSomAoJogo();
  });

  /* ============================================================
     TOASTS
     ============================================================ */
  var toastsBox = document.getElementById('blToasts');
  function toast(tipo, titulo, texto) {
    if (!toastsBox) return;
    var el = document.createElement('div');
    el.className = 'bl-toast ' + tipo;
    var icones = { sucesso: 'fa-circle-check', erro: 'fa-circle-xmark', info: 'fa-circle-info' };
    el.innerHTML =
      '<i class="fa-solid ' + (icones[tipo] || 'fa-bell') + ' bl-toast-icon"></i>' +
      '<div><strong></strong><p></p></div>' +
      '<button class="bl-toast-close" aria-label="Fechar">✕</button>' +
      '<span class="bl-toast-bar"></span>';
    el.querySelector('strong').textContent = titulo;
    el.querySelector('p').textContent = texto || '';
    toastsBox.appendChild(el);

    function remover() {
      el.classList.add('is-leaving');
      setTimeout(function () { el.remove(); }, 320);
    }
    el.querySelector('.bl-toast-close').addEventListener('click', remover);
    setTimeout(remover, 4200);
    if (tipo === 'sucesso') tocarBip(880, 0.1);
  }
  window.blToast = toast;

  // Flash messages já renderizadas pelo PHP (.flash-site, .bl-flash) também aparecem como toast leve.
  document.querySelectorAll('.flash-site').forEach(function (el) {
    var tipo = el.classList.contains('sucesso') ? 'sucesso' : 'erro';
    toast(tipo, tipo === 'sucesso' ? 'Sucesso' : 'Atenção', el.textContent.trim());
  });

  /* ============================================================
     PAYLOAD DE GAMIFICAÇÃO (conquistas + level up vindos do PHP)
     ============================================================ */
  (function gamificacao() {
    var script = document.getElementById('bl-gamificacao-payload');
    if (!script) return;
    var dados;
    try { dados = JSON.parse(script.textContent); } catch (e) { return; }

    var fila = [];
    if (dados.levelUp) fila.push({ tipo: 'levelup', dados: dados.levelUp });
    (dados.conquistas || []).forEach(function (c) { fila.push({ tipo: 'conquista', dados: c }); });

    function proximo() {
      if (!fila.length) return;
      var item = fila.shift();
      if (item.tipo === 'levelup') mostrarLevelUp(item.dados, proximo);
      else mostrarConquista(item.dados, proximo);
    }
    if (fila.length) setTimeout(proximo, 500);
  })();

  function criarParticulas(container) {
    if (prefersReducedMotion || !container) return;
    for (var i = 0; i < 16; i++) {
      var p = document.createElement('span');
      p.className = 'bl-fx-particle';
      p.style.left = (Math.random() * 100) + '%';
      p.style.top = '-10px';
      p.style.background = i % 2 ? 'var(--primary-2)' : 'var(--accent)';
      p.style.animationDelay = (Math.random() * 0.4) + 's';
      container.appendChild(p);
    }
  }

  function mostrarLevelUp(info, aoFechar) {
    var modal = document.getElementById('blLevelUpModal');
    if (!modal) { aoFechar && aoFechar(); return; }
    modal.innerHTML =
      '<div class="bl-fx-card">' +
        '<div class="bl-fx-particles"></div>' +
        '<div class="bl-fx-icon"><i class="fa-solid fa-star"></i></div>' +
        '<h2>🎉 LEVEL UP!</h2>' +
        '<p>Você alcançou o nível <strong>' + info.nivel_novo + '</strong>.</p>' +
        '<button class="bl-btn bl-btn-primary" id="blFxClose">Continuar</button>' +
      '</div>';
    modal.hidden = false;
    criarParticulas(modal.querySelector('.bl-fx-particles'));
    tocarBip(990, 0.18);
    function fechar() { modal.hidden = true; modal.innerHTML = ''; document.removeEventListener('keydown', onEsc); aoFechar && aoFechar(); }
    function onEsc(e) { if (e.key === 'Escape') fechar(); }
    modal.querySelector('#blFxClose').addEventListener('click', fechar);
    modal.addEventListener('click', function (e) { if (e.target === modal) fechar(); }, { once: true });
    document.addEventListener('keydown', onEsc);
  }

  function mostrarConquista(c, aoFechar) {
    var modal = document.getElementById('blConquistaModal');
    if (!modal) { aoFechar && aoFechar(); return; }
    modal.innerHTML =
      '<div class="bl-fx-card">' +
        '<div class="bl-fx-particles"></div>' +
        '<div class="bl-fx-icon"><i class="' + (c.icone || 'fa-solid fa-trophy') + '"></i></div>' +
        '<h2>🏆 Conquista desbloqueada!</h2>' +
        '<p><strong>' + escapeHtml(c.nome) + '</strong></p>' +
        '<p>' + escapeHtml(c.descricao) + '</p>' +
        '<span class="bl-fx-xp">+' + c.xp + ' XP</span><br>' +
        '<button class="bl-btn bl-btn-primary" id="blFxClose2">Continuar</button>' +
      '</div>';
    modal.hidden = false;
    criarParticulas(modal.querySelector('.bl-fx-particles'));
    tocarBip(740, 0.15);
    function fechar() { modal.hidden = true; modal.innerHTML = ''; document.removeEventListener('keydown', onEsc); aoFechar && aoFechar(); }
    function onEsc(e) { if (e.key === 'Escape') fechar(); }
    modal.querySelector('#blFxClose2').addEventListener('click', fechar);
    modal.addEventListener('click', function (e) { if (e.target === modal) fechar(); }, { once: true });
    document.addEventListener('keydown', onEsc);
  }

  function escapeHtml(s) {
    var d = document.createElement('div');
    d.textContent = s || '';
    return d.innerHTML;
  }

  /* ============================================================
     FAVORITOS (persistidos no MySQL via php/favoritar.php)
     ============================================================ */
  function caminhoFavoritar() {
    // Funciona tanto na raiz (Index.php) quanto em /php/*.php
    return location.pathname.includes('/php/') ? 'favoritar.php' : 'php/favoritar.php';
  }
  document.querySelectorAll('[data-bl-fav]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var jogo = btn.getAttribute('data-bl-fav');
      var csrf = window.BL_CSRF;
      if (!csrf) { toast('erro', 'Faça login', 'Entre na sua conta para favoritar jogos.'); return; }
      btn.disabled = true;
      fetch(caminhoFavoritar(), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ jogo: jogo, csrf_token: csrf })
      }).then(function (r) { return r.json(); }).then(function (data) {
        btn.disabled = false;
        if (data.erro) { toast('erro', 'Ops', data.erro); return; }
        var ativo = data.favoritado;
        btn.classList.toggle('is-active', ativo);
        btn.setAttribute('aria-pressed', ativo ? 'true' : 'false');
        btn.classList.add('is-animating');
        setTimeout(function () { btn.classList.remove('is-animating'); }, 500);
        if (btn.classList.contains('bl-fav-btn')) {
          btn.innerHTML = '<i class="fa-' + (ativo ? 'solid' : 'regular') + ' fa-heart"></i> ' + (ativo ? 'Favoritado' : 'Favoritar');
        } else {
          var icone = btn.querySelector('i');
          if (icone) icone.className = (ativo ? 'fa-solid' : 'fa-regular') + ' fa-heart';
        }
        toast(ativo ? 'sucesso' : 'info', ativo ? '❤️ Adicionado aos favoritos' : '♡ Removido dos favoritos', '');
        atualizarTabs();
      }).catch(function () {
        btn.disabled = false;
        toast('erro', 'Falha de conexão', 'Não foi possível salvar o favorito.');
      });
    });
  });

  /* ============================================================
     TABS / FILTROS (biblioteca)
     ============================================================ */
  function atualizarTabs() {
    var tabs = document.querySelectorAll('[data-bl-filtro]');
    if (!tabs.length) return;
    var ativa = document.querySelector('[data-bl-filtro].active');
    var filtro = ativa ? ativa.getAttribute('data-bl-filtro') : 'todos';
    var itens = document.querySelectorAll('[data-bl-item]');
    var algumVisivelFavoritos = false;

    itens.forEach(function (item) {
      var status = (item.getAttribute('data-status') || '').split(/\s+/);
      var mostrar = status.indexOf(filtro) !== -1;
      item.hidden = !mostrar;
      if (filtro === 'favoritos' && mostrar) algumVisivelFavoritos = true;
    });

    var vazio = document.getElementById('blFavEmptyMsg');
    if (vazio) vazio.hidden = !(filtro === 'favoritos' && !algumVisivelFavoritos);
  }
  document.querySelectorAll('[data-bl-filtro]').forEach(function (tab) {
    tab.addEventListener('click', function () {
      document.querySelectorAll('[data-bl-filtro]').forEach(function (t) { t.classList.remove('active'); });
      tab.classList.add('active');
      atualizarTabs();
    });
  });
  atualizarTabs();

  /* ============================================================
     FILTRO DE CATEGORIAS (catálogo de jogos na home)
     ============================================================ */
  document.querySelectorAll('[data-bl-cat]').forEach(function (chip) {
    chip.addEventListener('click', function () {
      document.querySelectorAll('[data-bl-cat]').forEach(function (c) { c.classList.remove('active'); });
      chip.classList.add('active');
      var cat = chip.getAttribute('data-bl-cat');
      document.querySelectorAll('.jogo-card').forEach(function (card) {
        var cats = (card.getAttribute('data-cat') || '').split(/\s+/);
        card.style.display = (cat === 'todos' || cats.indexOf(cat) !== -1) ? '' : 'none';
      });
    });
  });

  /* ============================================================
     HEADER — encolhe/blur ao rolar
     ============================================================ */
  var header = document.querySelector('.bl-header');
  if (header) {
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ============================================================
     MENU MOBILE
     ============================================================ */
  var burger = document.querySelector('.bl-burger');
  var mobileMenu = document.querySelector('.bl-mobile-menu');
  if (burger && mobileMenu) {
    burger.addEventListener('click', function () {
      var open = mobileMenu.classList.toggle('is-open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    mobileMenu.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { mobileMenu.classList.remove('is-open'); });
    });
  }

  /* ============================================================
     MOSTRAR/OCULTAR SENHA
     ============================================================ */
  document.querySelectorAll('.bl-toggle-pass').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = btn.parentElement.querySelector('input');
      if (!input) return;
      var showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      btn.innerHTML = showing ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
      btn.setAttribute('aria-label', showing ? 'Mostrar senha' : 'Ocultar senha');
    });
  });

  /* ============================================================
     LOADING NO BOTÃO AO ENVIAR FORMULÁRIO
     ============================================================ */
  document.querySelectorAll('form[data-bl-loading]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"]');
      if (btn && !btn.disabled) {
        btn.classList.add('is-loading');
        btn.disabled = true;
      }
    });
  });

  /* ============================================================
     REVEAL ON SCROLL
     ============================================================ */
  var revealEls = document.querySelectorAll('.bl-reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(function (el) { io.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('in-view'); });
  }

  /* ============================================================
     CARROSSEL — autoplay, pause no hover, arraste/swipe
     ============================================================ */
  document.querySelectorAll('[data-bl-carousel]').forEach(function (root) {
    var slides = root.querySelectorAll('.bl-slide');
    var dots = root.querySelectorAll('.bl-dot');
    if (!slides.length) return;
    var i = 0, timer = null, pausado = false;

    function show(n) {
      slides.forEach(function (s, idx) { s.classList.toggle('active', idx === n); });
      dots.forEach(function (d, idx) { d.classList.toggle('active', idx === n); });
      i = n;
    }
    function proximo() { show((i + 1) % slides.length); }
    function anterior() { show((i - 1 + slides.length) % slides.length); }
    function iniciarAuto() {
      if (timer || slides.length < 2 || prefersReducedMotion) return;
      timer = setInterval(function () { if (!pausado) proximo(); }, 5500);
    }
    dots.forEach(function (d, idx) { d.addEventListener('click', function () { show(idx); }); });
    root.addEventListener('mouseenter', function () { pausado = true; });
    root.addEventListener('mouseleave', function () { pausado = false; });

    if (slides.length > 1) {
      var setaEsq = document.createElement('button');
      var setaDir = document.createElement('button');
      setaEsq.className = 'bl-arrow bl-arrow-left'; setaEsq.type = 'button'; setaEsq.setAttribute('aria-label', 'Anterior');
      setaDir.className = 'bl-arrow bl-arrow-right'; setaDir.type = 'button'; setaDir.setAttribute('aria-label', 'Próximo');
      setaEsq.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
      setaDir.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
      setaEsq.addEventListener('click', anterior);
      setaDir.addEventListener('click', proximo);
      root.appendChild(setaEsq); root.appendChild(setaDir);
    }

    var startX = null;
    root.addEventListener('pointerdown', function (e) { startX = e.clientX; });
    root.addEventListener('pointerup', function (e) {
      if (startX === null) return;
      var dx = e.clientX - startX;
      if (Math.abs(dx) > 50) { dx < 0 ? proximo() : anterior(); }
      startX = null;
    });

    iniciarAuto();
  });

  /* ============================================================
     PARALLAX NO HERO (desktop) — respeita prefers-reduced-motion
     ============================================================ */
  var hero = document.querySelector('.bl-hero');
  if (hero && !prefersReducedMotion && window.matchMedia('(pointer:fine)').matches) {
    var visual = hero.querySelector('.bl-hero-visual');
    hero.addEventListener('mousemove', function (e) {
      var rect = hero.getBoundingClientRect();
      var relX = (e.clientX - rect.left) / rect.width - 0.5;
      var relY = (e.clientY - rect.top) / rect.height - 0.5;
      if (visual) visual.style.transform = 'translate(' + (relX * -14) + 'px,' + (relY * -14) + 'px)';
    });
    hero.addEventListener('mouseleave', function () {
      if (visual) visual.style.transform = '';
    });
  }

  /* O cursor nativo permanece ativo em todo o site, inclusive em modais. */

  /* ============================================================
     PESQUISA — expande e mostra sugestões instantâneas
     ============================================================ */
  document.querySelectorAll('.bl-search input').forEach(function (input) {
    var wrap = input.closest('.bl-search');
    if (!wrap) return;
    input.addEventListener('focus', function () { wrap.classList.add('is-expanded'); });
    input.addEventListener('blur', function () { setTimeout(function () { wrap.classList.remove('is-expanded'); }, 150); });
  });

  /* ============================================================
     EASTER EGG — clique 5x no logo em menos de 2s
     ============================================================ */
  document.querySelectorAll('.bl-logo').forEach(function (logo) {
    var cliques = 0, ultimo = 0;
    logo.addEventListener('click', function (e) {
      var agora = Date.now();
      cliques = (agora - ultimo < 2000) ? cliques + 1 : 1;
      ultimo = agora;
      if (cliques >= 5) {
        cliques = 0;
        e.preventDefault();
        toast('info', '✨ Modo secreto ativado', 'Você encontrou um easter egg da Blue Light!');
        document.body.style.transition = 'filter .6s';
        document.body.style.filter = 'hue-rotate(180deg)';
        setTimeout(function () { document.body.style.filter = ''; }, 1800);
      }
    });
  });
})();
