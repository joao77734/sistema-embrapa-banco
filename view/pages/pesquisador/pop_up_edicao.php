<?php
require_once '../../../config/database.php';
$id = $_GET['id'] ?? '';

$stmt = mysqli_prepare($conexao, "SELECT * FROM Acao_pesquisador WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$acaoData = mysqli_fetch_assoc($resultado);

if (!$acaoData) {
    die("Ação não encontrada.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar ação</title>
    <link rel="stylesheet" href="../../../public/css/main.css">
</head>
<body class="pagina-popup">
    <main class="pesquisador-pop-up-fundo">
        <section class="pesquisador-pop-up-edicao" role="dialog" aria-modal="true" aria-labelledby="titulo-editar-acao">
            <h1 id="titulo-editar-acao">Editar Ação</h1>

            <form action="acoes_controller.php" method="POST">
                
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" value="<?= $acaoData['id'] ?>">

                <div class="pesquisador-pop-up-edicao__campos">
                    <div class="pesquisador-pop-up-edicao__campo">
                        <label for="editar-titulo">Título da ação</label>
                        <input type="text" id="editar-titulo" name="titulo" value="<?= htmlspecialchars($acaoData['titulo']) ?>" required>
                    </div>

                    <div class="pesquisador-pop-up-edicao__campo">
                        <label for="editar-tipo">Tipo da ação</label>
                        <select id="editar-tipo" name="categoria_acao" required>
                            <option <?= $acaoData['categoria_acao'] === 'Coordenação de eventos LOCAL' ? 'selected' : '' ?>>Coordenação de eventos LOCAL</option>
                            <option <?= $acaoData['categoria_acao'] === 'Coordenação de evento NACIONAL' ? 'selected' : '' ?>>Coordenação de evento NACIONAL</option>
                            <option <?= $acaoData['categoria_acao'] === 'Coordenação de evento REGIONAL' ? 'selected' : '' ?>>Coordenação de evento REGIONAL</option>
                        </select>
                    </div>

                    <div class="pesquisador-pop-up-edicao__campo">
                        <label for="editar-data">Data de realização</label>
                        <input type="date" id="editar-data" name="data_realizacao" value="<?= $acaoData['data_realizacao'] ?>" required>
                    </div>

                    <div class="pesquisador-pop-up-edicao__campo">
                        <label for="editar-descricao">Descrição</label>
                        <textarea id="editar-descricao" name="descricao" rows="4" required><?= htmlspecialchars($acaoData['descricao']) ?></textarea>
                    </div>
                </div>

                <div class="pesquisador-pop-up-edicao__acoes">
                    <button type="button" class="btn cancelar pesquisador-pop-up-btn" onclick="parent.fecharPopup()">Cancelar</button>
                    <button type="submit" class="btn pesquisador-pop-up-btn pesquisador-pop-up-edicao__confirmar">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>