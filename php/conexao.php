<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "blt";

// Criar conexão
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verificar conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Definir charset
$conn->set_charset("utf8mb4");
?>