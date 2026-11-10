<?php
require_once '../../../config/database.php';
$id = $_GET['id'] ?? '';

$stmt = mysqli_prepare($conexao, "SELECT titulo FROM Acao_pesquisador WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$acaoData = mysqli_fetch_assoc($resultado);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar exclusão da ação</title>
    <link rel="stylesheet" href="../../../public/css/main.css">
</head>
<body class="pagina-popup">
    <main class="pesquisador-pop-up-fundo">
        <section class="pesquisador-pop-up-exclusao" role="alertdialog" aria-modal="true"
            aria-labelledby="titulo-excluir-acao" aria-describedby="aviso-excluir-acao">
            <div class="pesquisador-pop-up-exclusao__aviso" aria-hidden="true">!</div>
            <h1 id="titulo-excluir-acao">Excluir ação?</h1>
            <p id="aviso-excluir-acao">
                Tem certeza de que deseja excluir esta ação?
            </p>
            <p class="pesquisador-pop-up-exclusao__nome">
                <strong><?= htmlspecialchars($acaoData['titulo'] ?? 'Ação') ?></strong>
            </p>
            <div class="pesquisador-pop-up-exclusao__acoes">
                <button type="button" class="btn cancelar pesquisador-pop-up-btn" onclick="parent.fecharPopup()">Cancelar</button>
                <a href="acoes_controller.php?acao=excluir&id=<?= $id ?>" class="btn pesquisador-pop-up-btn pesquisador-pop-up-exclusao__confirmar" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                    Confirmar exclusão
                </a>
            </div>
        </section>
    </main>
</body>
</html>