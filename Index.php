<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="Css/Index.css">
<link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<title>Blue Light Team</title>

</head>
<body>

<header>
    <div class="logo">
    <img src="img/Logo Blue Light com contorno.png" alt="Logo" id="blt">
    <span>BLUE LIGHT</span>
</div>

    <nav>
    <ul>
        <li><a href="#inicio">Início</a></li>
        <li><a href="#jogos">Jogos</a></li>
        <li><a href="#planos">Planos</a></li>
        <li><a href="#contato">Contato</a></li>
    </ul>
    </nav>

    <button class="login-btn" id="abrirModal">
        Entrar
    </button>

</header>

<section class="hero" id="inicio">

    <div class="hero-text">

        <h1>
            HOSPEDE SEU
            SERVIDOR
            <span>SEM COMPLICAÇÕES</span>
        </h1>

        <p>
            Crie, gerencie e compartilhe seus jogos
            em uma plataforma rápida e segura.
        </p>

        <div class="search">

            <input
            type="text"
            placeholder="Pesquisar jogos...">

            <button>Buscar</button>

        </div>

    </div>

    <div class="server">
        🖥️
    </div>

</section>

<section class="games" id="jogos">

    <h2 class="section-title">
        Jogos Mais Jogados
    </h2>

    <div class="cards">

        <div class="card">
            <img src="https://picsum.photos/500/300?1">
            <div class="card-info">
                <h3>Minecraft</h3>
                <p>Sandbox e sobrevivência.</p>
            </div>
        </div>

        <div class="card">
            <img src="https://picsum.photos/500/300?2">
            <div class="card-info">
                <h3>CS2</h3>
                <p>FPS competitivo.</p>
            </div>
        </div>

        <div class="card">
            <img src="https://picsum.photos/500/300?3">
            <div class="card-info">
                <h3>Valorant</h3>
                <p>Tático 5x5.</p>
            </div>
        </div>

    </div>

</section>

<section class="plans" id="planos">

    <h2 class="section-title">
        Destaque da Semana
    </h2>

    <div class="banner">
     <a href="Jogo/Chronoside.html" class="banner-link">
         <img src="https://picsum.photos/1200/500">
         <div class="banner-text">
            <h2>Chronocide</h2>
        </div>
     </a>

    </div>

</section>

<section class="plans">

    <h2 class="section-title">
        Nossos Planos
    </h2>

    <div class="plan-grid">

        <div class="plan">
            <h3>Básico</h3>
            <div class="price">R$19,90</div>
            <ul>
                <li>1 GB RAM</li>
                <li>10 GB SSD</li>
                <li>Suporte Básico</li>
            </ul>
            <a class = "neymar" href="pagamento.html">
            <button>Contratar</button>
            </a>
        </div>

        <div class="plan">
            <h3>Premium</h3>
            <div class="price">R$49,90</div>
            <ul>
                <li>4 GB RAM</li>
                <li>50 GB SSD</li>
                <li>Suporte Prioritário</li>
            </ul>
            <a  class = "neymar" href="pagamento.html">
            <button>Contratar</button>
            </a>
        </div>

        <div class="plan">
            <h3>Enterprise</h3>
            <div class="price">R$99,90</div>
            <ul>
                <li>8 GB RAM</li>
                <li>100 GB SSD</li>
                <li>Suporte 24/7</li>
            </ul>
            <a class ="neymar" href="pagamento.html">
            <button>Contratar</button>
            </a>
        </div>

    </div>

</section>

<footer id="contato">
    © 2026 Blue Light Team. Todos os direitos reservados.  [Termos de Uso] e [Política de Privacidade]
</footer>

<dialog id="modalLogin">

    <div class="modal-content">

        <button class="fechar" id="fecharModal">
            ✕
        </button>

        <h2>Entrar</h2>

        <form>

            <input
            type="email"
            placeholder="E-mail"
            required>

            <input
            type="password"
            placeholder="Senha"
            required>
            

            <button type="submit">
                Entrar
            </button>
            <a href="login.html" class="cadastro" role="button">
                            Cadastre-se
                 </a>


        </form>
        
    </div>

</dialog>

<a href="https://wa.me/5511999999999?text=Ola%20gostaria%20de%20mais%20informa%C3%A7%C3%B5es"
    class="whatsapp-float"
    target="_blank"
    aria-label="Conversar no WhatsApp">
        <i class="fab fa-whatsapp"></i>
</a>

<script>

const modal =
document.getElementById("modalLogin");

document
.getElementById("abrirModal")
.addEventListener("click", () => {

    modal.showModal();

});

document
.getElementById("fecharModal")
.addEventListener("click", () => {

    modal.close();


});

</script>

</body>
</html>