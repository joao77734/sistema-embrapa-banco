<?php
require_once '../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$id_pesquisador = $_SESSION['usuario']['id'] ?? 0;

$acaoData = null;
if ($id) {
    $stmt = mysqli_prepare($conexao, "SELECT * FROM Acao_pesquisador WHERE id = ? AND id_pesquisador = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $id_pesquisador);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $acaoData = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Ação</title>
    <link rel="stylesheet" href="../../../public/css/main.css">
</head>
<body class="pagina-popup">
    <main class="pesquisador-pop-up-fundo" style="position: relative; background: transparent !important;">
        <section class="pesquisador-pop-up-exclusao" role="alertdialog" aria-modal="true" style="margin: auto;">
            <div class="pesquisador-pop-up-exclusao__aviso" aria-hidden="true">!</div>
            <h1>Excluir ação?</h1>
            <p>Tem certeza de que deseja excluir esta ação?</p>
            
            <?php if ($acaoData): ?>
                <p class="pesquisador-pop-up-exclusao__nome">
                    <strong><?= htmlspecialchars($acaoData['titulo']) ?></strong>
                </p>
            <?php endif; ?>

            <div class="pesquisador-pop-up-exclusao__acoes">
                <button type="button" class="btn cancelar" onclick="parent.fecharPopup()">Cancelar</button>
                <?php if ($acaoData): ?>
                    <form method="POST" action="tela_pesquisador.php" style="display: inline;">
                        <input type="hidden" name="acao_tipo" value="excluir">
                        <input type="hidden" name="id_acao" value="<?= $acaoData['id'] ?>">
                        <button type="submit" class="btn pesquisador-pop-up-exclusao__confirmar">
                            Confirmar exclusão
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>