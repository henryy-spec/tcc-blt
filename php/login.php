<?php
session_start();

if(isset($_SESSION["id_usuario"])){

    header("Location: perfil.php");

}

?>

<!DOCTYPE html>

<html lang="pt-br">

<head>

<meta charset="UTF-8">

<title>Login</title>

<link rel="stylesheet" href="css/login.css">

</head>

<body>

<div class="login">

<h1>Entrar</h1>

<form action="php/login.php" method="POST">

<input
type="email"
name="email"
placeholder="Email"
required>

<input
type="password"
name="senha"
placeholder="Senha"
required>

<button>

Entrar

</button>

</form>

<a href="cadastro.php">

Criar conta

</a>

<br>

<a href="recuperarSenha.php">

Esqueci minha senha

</a>

</div>

</body>

</html>
