<?php
require_once '../../../config/database.php';

$resultado = mysqli_query($conexao, "SELECT * FROM Acao_pesquisador ORDER BY data_realizacao DESC");
$acoesList = [];
if ($resultado) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $acoesList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Minhas Ações - Embrapa</title>
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
                <li><span>Pesquisador</span></li>
                <li><a href="../auth.php?acao=sair">Sair</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <section class="pesquisador" id="acoes">
            <h1>Minhas Ações</h1>
            <p class="descricao">
                Consulte e gerencie suas ações cadastradas no sistema.
            </p>

            <div class="pesquisador-nova-acao">
                <button type="button" class="btn salvar" id="btnCriarAcao">Criar Nova Ação</button>
            </div>

            <details class="funcao funcao--lista" open>
                <summary>Listar Minhas Ações</summary>
                <div class="conteudo-funcao conteudo-funcao--lista">
                    <div class="tabela-responsiva">
                        <table>
                            <thead>
                                <tr>
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
                                        <td colspan="5" style="text-align: center;">Nenhuma ação cadastrada.</td>
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
                                            <td><?= htmlspecialchars($row['titulo'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($row['categoria_acao'] ?? '') ?></td>
                                            <td><?= isset($row['data_realizacao']) ? date('d/m/Y', strtotime($row['data_realizacao'])) : '' ?></td>
                                            <td>
                                                <span class="status <?= $statusClass ?>">
                                                    <?= htmlspecialchars($statusText) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="acoes">
                                                    <a class="btn editar" onclick="abrirPopup('pop_up_edicao.php?id=<?= $row['id'] ?>')">Editar</a>
                                                    <a class="btn excluir" onclick="abrirPopup('pop_up_exclusao.php?id=<?= $row['id'] ?>')">Excluir</a>
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
    const btnCriar = document.getElementById('btnCriarAcao');

    btnCriar.addEventListener('click', () => {
        iframe.src = 'pop_up_criar_acao.php';
        modal.showModal();
    });

    window.fecharPopup = function() {
        modal.close();
        iframe.src = '';
        location.reload(); 
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