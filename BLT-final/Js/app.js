// Blue Light — comportamentos compartilhados de UI (não interfere na lógica PHP).
(function(){
  "use strict";

  // Header com sombra ao rolar
  var header = document.querySelector('.bl-header');
  if (header) {
    var onScroll = function(){ header.classList.toggle('is-scrolled', window.scrollY > 8); };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // Menu mobile
  var burger = document.querySelector('.bl-burger');
  var mobileMenu = document.querySelector('.bl-mobile-menu');
  if (burger && mobileMenu) {
    burger.addEventListener('click', function(){
      var open = mobileMenu.classList.toggle('is-open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    mobileMenu.querySelectorAll('a').forEach(function(a){
      a.addEventListener('click', function(){ mobileMenu.classList.remove('is-open'); });
    });
  }

  // Mostrar/ocultar senha
  document.querySelectorAll('.bl-toggle-pass').forEach(function(btn){
    btn.addEventListener('click', function(){
      var input = btn.parentElement.querySelector('input');
      if (!input) return;
      var showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      btn.innerHTML = showing ? '<i class="fa-regular fa-eye"></i>' : '<i class="fa-regular fa-eye-slash"></i>';
      btn.setAttribute('aria-label', showing ? 'Mostrar senha' : 'Ocultar senha');
    });
  });

  // Loading no botão ao enviar formulário (a submissão real do PHP não é alterada)
  document.querySelectorAll('form[data-bl-loading]').forEach(function(form){
    form.addEventListener('submit', function(){
      var btn = form.querySelector('button[type="submit"]');
      if (btn && !btn.disabled) {
        btn.classList.add('is-loading');
        btn.disabled = true;
      }
    });
  });

  // Reveal on scroll
  var revealEls = document.querySelectorAll('.bl-reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach(function(el){ io.observe(el); });
  } else {
    revealEls.forEach(function(el){ el.classList.add('in-view'); });
  }

  // Carrossel simples de destaques (usa [data-bl-carousel])
  document.querySelectorAll('[data-bl-carousel]').forEach(function(root){
    var slides = root.querySelectorAll('.bl-slide');
    var dots = root.querySelectorAll('.bl-dot');
    if (!slides.length) return;
    var i = 0;
    function show(n){
      slides.forEach(function(s, idx){ s.classList.toggle('active', idx === n); });
      dots.forEach(function(d, idx){ d.classList.toggle('active', idx === n); });
      i = n;
    }
    dots.forEach(function(d, idx){ d.addEventListener('click', function(){ show(idx); }); });
    if (slides.length > 1) {
      setInterval(function(){ show((i + 1) % slides.length); }, 5500);
    }
  });
})();
