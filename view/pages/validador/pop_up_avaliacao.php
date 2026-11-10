<?php
require_once __DIR__ . '/../../../config/database.php';
$id = $_GET['id'] ?? '';

$stmt = mysqli_prepare($conexao, "SELECT acao.*, pesquisador.nome AS pesquisador FROM Acao_pesquisador AS acao INNER JOIN Pesquisador AS pesquisador ON pesquisador.id = acao.id_pesquisador WHERE acao.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$acaoData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$acaoData) {
    die("Ação não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar ação</title>
    <link rel="stylesheet" href="../../../public/css/main.css">
</head>
<body class="pagina-popup">
    <main class="validador-pop-up-fundo">
        <section class="validador-pop-up-avaliacao" role="dialog" aria-modal="true"
            aria-labelledby="titulo-avaliar-acao" aria-describedby="aviso-avaliar-acao">
            <div class="validador-pop-up-avaliacao__icone" aria-hidden="true">?</div>
            <h1 id="titulo-avaliar-acao">Avaliar ação</h1>
            <p id="aviso-avaliar-acao">Deseja aprovar ou recusar esta ação?</p>
            <p class="validador-pop-up-avaliacao__nome"><strong><?= htmlspecialchars($acaoData['titulo']) ?></strong></p>
            <p><?= htmlspecialchars($acaoData['pesquisador']) ?> · <?= htmlspecialchars($acaoData['categoria_acao']) ?> · <?= date('d/m/Y', strtotime($acaoData['data_realizacao'])) ?></p>
            
            <div class="validador-pop-up-avaliacao__acoes">
                <button type="button" class="btn cancelar validador-pop-up-btn" onclick="parent.fecharPopup()">Cancelar</button>
                <button type="button" class="btn validador-pop-up-btn validador-pop-up-avaliacao__recusar" onclick="parent.abrirRecusa(<?= $acaoData['id'] ?>)">
                    Recusar Ação
                </button>
                <form action="avaliacoes.php" method="POST" style="display:inline;">
                    <input type="hidden" name="acao" value="aprovar">
                    <input type="hidden" name="id" value="<?= $acaoData['id'] ?>">
                    <button type="submit" class="btn validador-pop-up-btn validador-pop-up-avaliacao__aprovar">
                        Aprovar
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>