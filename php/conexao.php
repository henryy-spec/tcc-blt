<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_OFF);

$host = getenv('DB_HOST') ?: 'localhost';
$usuario = getenv('DB_USER') ?: 'root';
$senha = getenv('DB_PASS') ?: '';
$banco = getenv('DB_NAME') ?: 'blt';

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_errno) {
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados. Verifique se o MySQL do XAMPP está ligado e se o banco "blt" foi importado.');
}

$conn->set_charset('utf8mb4');
