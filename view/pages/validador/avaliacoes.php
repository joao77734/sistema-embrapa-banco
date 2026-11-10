<?php
require_once __DIR__ . '/../../../config/database.php';
session_start();

if (($_SESSION['usuario']['tipo'] ?? null) !== 'Validador') {
    header("Location: ../auth.php");
    exit;
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';
$id = $_POST['id'] ?? $_GET['id'] ?? '';

if ($acao === 'aprovar' && !empty($id)) {
    $stmt = mysqli_prepare($conexao, "UPDATE Acao_pesquisador SET status = 'Aprovado' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: tela_validador.php");
    exit;
}

if ($acao === 'recusar' && !empty($id)) {
    $motivo = $_POST['motivo'] ?? '';
    
    $coluna = mysqli_query($conexao, "SHOW COLUMNS FROM Acao_pesquisador LIKE 'motivo_recusa'");
    if (mysqli_num_rows($coluna) == 0) {
        mysqli_query($conexao, "ALTER TABLE Acao_pesquisador ADD COLUMN motivo_recusa VARCHAR(300) NULL");
    }
    mysqli_free_result($coluna);

    $stmt = mysqli_prepare($conexao, "UPDATE Acao_pesquisador SET status = 'Recusado', motivo_recusa = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $motivo, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: tela_validador.php");
    exit;
}

header("Location: tela_validador.php");
exit;
?>