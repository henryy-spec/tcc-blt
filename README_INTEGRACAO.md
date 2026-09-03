# Blue Light Team — PHP integrado

Esta versão mantém o site original e completa o backend PHP.

## Funcionalidades
- Cadastro com validação e senha com `password_hash`.
- Login/logout com sessão.
- Proteção de páginas privadas.
- Perfil do usuário.
- Assinatura com três planos e registro no MySQL.
- Biblioteca protegida por assinatura.
- Conquista de primeira assinatura e primeiro jogo.
- Recuperação/redefinição de senha com token.
- Proteção CSRF nos formulários POST.
- Jogo protegido por assinatura através de `jogar.php`.
- Busca de jogos no site.

## Instalação no XAMPP
1. Coloque a pasta `BLT-gamer` diretamente em `C:\xampp\htdocs\`.
2. Inicie **Apache** e **MySQL** no XAMPP.
3. Abra `http://localhost/phpmyadmin`.
4. Importe `blt.sql`.
5. Abra `http://localhost/BlueLight/Index.php`.
6. Crie uma conta e teste login, assinatura, biblioteca e jogo.

## Importante sobre pagamento
O pagamento é **simulado**, adequado para demonstração/TCC. Nenhum cartão real é processado ou armazenado. Para cobrança real, o arquivo `php/assinatura.php` deve ser substituído por uma integração com um gateway e confirmação via webhook.

## Recuperação de senha
Em ambiente local, depois de informar o e-mail cadastrado, o sistema mostra o link de recuperação na tela para facilitar o teste. Em produção, esse link deve ser enviado por e-mail e não exibido ao usuário.
