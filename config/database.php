<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'nome_do_banco';

$conexao = mysqli_connect($host, $username, $password, $dbname);

if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}

echo "Conexão realizada com sucesso!";