<?php
$host = '127.0.0.1'; 
$username = 'root';
$password = ''; 
$dbname = 'sistema_embrapa_pi';

$conexao = mysqli_connect($host, $username, $password, $dbname);

if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, 'utf8mb4');