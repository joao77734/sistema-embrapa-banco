<?php
require_once '../../../config/database.php';
session_start();

if (($_SESSION['usuario']['tipo'] ?? null) !== 'Validador') {
    header("Location: ../auth.php");
    exit;
}

$idValidador = (int) ($_SESSION['usuario']['id'] ?? 0);
$emailUsuario = $_SESSION['usuario']['email'] ?? '';
$nomeSetor = 'Não informado';

if ($idValidador > 0) {
    $stmtSetor = mysqli_prepare(
        $conexao,
        'SELECT setor.nome FROM Validador AS validador
         INNER JOIN Setor AS setor ON setor.id = validador.id_setor
         WHERE validador.id = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtSetor, 'i', $idValidador);
    mysqli_stmt_execute($stmtSetor);
    $linhaSetor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSetor));
    mysqli_stmt_close($stmtSetor);
    if (!empty($linhaSetor['nome'])) {
        $nomeSetor = $linhaSetor['nome'];
    }
} elseif (!empty($emailUsuario)) {
    $stmtSetor = mysqli_prepare(
        $conexao,
        'SELECT setor.nome FROM Validador AS validador
         INNER JOIN Setor AS setor ON setor.id = validador.id_setor
         WHERE validador.email = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtSetor, 's', $emailUsuario);
    mysqli_stmt_execute($stmtSetor);
    $linhaSetor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtSetor));
    mysqli_stmt_close($stmtSetor);
    if (!empty($linhaSetor['nome'])) {
        $nomeSetor = $linhaSetor['nome'];
    }
}

$colunaMotivo = mysqli_query(
    $conexao,
    "SHOW COLUMNS FROM Acao_pesquisador LIKE 'motivo_recusa'"
);
if (mysqli_num_rows($colunaMotivo) === 0) {
    mysqli_query(
        $conexao,
        'ALTER TABLE Acao_pesquisador ADD COLUMN motivo_recusa VARCHAR(300) NULL'
    );
}
mysqli_free_result($colunaMotivo);

$sql = '
    SELECT
        acao.id,
        acao.titulo,
        acao.descricao,
        acao.categoria_acao,
        acao.data_realizacao,
        acao.status,
        acao.motivo_recusa,
        pesquisador.nome AS pesquisador
    FROM Acao_pesquisador AS acao
    INNER JOIN Pesquisador AS pesquisador ON pesquisador.id = acao.id_pesquisador
    ORDER BY CASE WHEN acao.status = "Pendente" THEN 0 ELSE 1 END,
        acao.data_realizacao DESC, acao.id DESC
';
$resultado = mysqli_query($conexao, $sql);
$acoesList = [];
if ($resultado) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $acoesList[] = $row;
    }
    mysqli_free_result($resultado);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validador - Embrapa</title>
    <link rel="stylesheet" href="../../../public/css/main.css">
</head>

<body>

<header class="header">
    <div class="container">
        <a href="#" class="logo">
            <span class="logo__nome">Embrapa</span>
        </a>
        <nav aria-label="Principal">
            <ul class="abas">
                <li><span>Validador</span></li>
                <li><span>Setor: <?= htmlspecialchars($nomeSetor) ?></span></li>
                <li><a href="../auth.php?acao=sair">Sair</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <section class="pesquisador" id="acoes">
            <h1>Painel do Validador</h1>
            <p class="descricao">
                Consulte e avalie as ações cadastradas pelos pesquisadores.
            </p>

            <details class="funcao funcao--lista" open>
                <summary>Listar Ações dos Pesquisadores</summary>
                <div class="conteudo-funcao conteudo-funcao--lista">
                    <div class="tabela-responsiva">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pesquisador</th>
                                    <th>Título da ação</th>
                                    <th>Tipo da ação</th>
                                    <th>Data de realização</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($acoesList)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">Nenhuma ação cadastrada.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($acoesList as $row): ?>
                                        <?php 
                                            $statusClass = 'analise';
                                            $statusText = $row['status'] ?? 'Pendente';
                                            if ($statusText === 'Aprovado') $statusClass = 'aprovado';
                                            if ($statusText === 'Recusado' || $statusText === 'Rejeitado') $statusClass = 'recusado';
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['pesquisador'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['titulo'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['categoria_acao'] ?? '') ?></td>
                                            <td><?= isset($row['data_realizacao']) ? date('d/m/Y', strtotime($row['data_realizacao'])) : '' ?></td>
                                            <td>
                                                <span class="status <?= $statusClass ?>" <?= ($statusText === 'Recusado' && !empty($row['motivo_recusa'])) ? 'title="Motivo: ' . htmlspecialchars($row['motivo_recusa']) . '"' : '' ?>>
                                                    <?= htmlspecialchars($statusText) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="acoes">
                                                    <?php if ($statusText === 'Pendente'): ?>
                                                        <a class="btn editar" onclick="abrirPopup('pop_up_avaliacao.php?id=<?= $row['id'] ?>')">Avaliar</a>
                                                    <?php else: ?>
                                                        <span>Avaliada</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </details>
        </section>
    </div>

    <dialog id="modalPopup" style="border: none; background: transparent; padding: 0; width: 100%; height: 100%; max-width: none; max-height: none;">
        <iframe id="iframePopup" src="" style="width: 100%; height: 100%; border: none;"></iframe>
    </dialog>

<script>
    const modal = document.getElementById('modalPopup');
    const iframe = document.getElementById('iframePopup');

    window.fecharPopup = function() {
        modal.close();
        iframe.src = '';
        location.reload(); 
    };

    window.abrirRecusa = function(id) {
        iframe.src = 'pop_up_recusa.php?id=' + id;
    };

    function abrirPopup(pagina) {
        iframe.src = pagina;
        modal.showModal();
    }

    modal.addEventListener('click', (event) => {
        const rect = modal.getBoundingClientRect();
        const isInDialog = (
            rect.top <= event.clientY && event.clientY <= rect.bottom &&
            rect.left <= event.clientX && event.clientX <= rect.right
        );
        if (!isInDialog) {
            window.fecharPopup();
        }
    });
</script>
</main>

<footer class="footer">
    <div class="container">
        <span>© Embrapa – Trabalho do Mauricio</span>
        <a href="#">Política de privacidade</a>
    </div>
</footer>

</body>
</html>