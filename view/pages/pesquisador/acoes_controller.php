<?php
require_once '../../../config/database.php';

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
$id_pesquisador = 1;

if ($acao === 'criar') {
    $titulo = $_POST['titulo'] ?? '';
    $categoria_acao = $_POST['categoria_acao'] ?? '';
    $data_realizacao = $_POST['data_realizacao'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $status = 'Pendente';

    $stmt = mysqli_prepare($conexao, "INSERT INTO Acao_pesquisador (titulo, descricao, categoria_acao, data_realizacao, status, id_pesquisador) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssssi", $titulo, $descricao, $categoria_acao, $data_realizacao, $status, $id_pesquisador);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: tela_pesquisador.php");
    exit;
}

if ($acao === 'editar') {
    $id = $_POST['id'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $categoria_acao = $_POST['categoria_acao'] ?? '';
    $data_realizacao = $_POST['data_realizacao'] ?? '';
    $descricao = $_POST['descricao'] ?? '';

    $stmt = mysqli_prepare($conexao, "UPDATE Acao_pesquisador SET titulo = ?, descricao = ?, categoria_acao = ?, data_realizacao = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssssi", $titulo, $descricao, $categoria_acao, $data_realizacao, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: tela_pesquisador.php");
    exit;
}

if ($acao === 'excluir') {
    $id = $_GET['id'] ?? '';

    $stmt = mysqli_prepare($conexao, "DELETE FROM Acao_pesquisador WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: tela_pesquisador.php");
    exit;
}
?>