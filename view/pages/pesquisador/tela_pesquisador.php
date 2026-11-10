<?php
require_once '../../../config/database.php';

$mensagemErro = '';
$mensagemSucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_acao'])) {
    $titulo = mysqli_real_escape_string($conexao, $_POST['titulo_acao'] ?? '');
    $tipo_acao = mysqli_real_escape_string($conexao, $_POST['tipo_acao'] ?? '');
    $data_realizacao = mysqli_real_escape_string($conexao, $_POST['data_realizacao'] ?? '');
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao_acao'] ?? '');
    
    // Status inicial padrão
    $status = 'Pendente';

    if (!empty($titulo) && !empty($tipo_acao) && !empty($data_realizacao)) {
        // Ajuste o nome da coluna no banco se necessário (ex: categoria_acao ou tipo_acao)
        $sql = "INSERT INTO Acao_pesquisador (titulo, categoria_acao, data_realizacao, descricao, status) 
                VALUES ('$titulo', '$tipo_acao', '$data_realizacao', '$descricao', '$status')";
        
        if (mysqli_query($conexao, $sql)) {
            $mensagemSucesso = "Ação cadastrada com sucesso!";
        } else {
            $mensagemErro = "Erro ao cadastrar: " . mysqli_error($conexao);
        }
    } else {
        $mensagemErro = "Preencha todos os campos obrigatórios.";
    }
}

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

            <?php if (!empty($mensagemSucesso)): ?>
                <div class="alert alert-success" style="color: green; margin-bottom: 15px;"><?= $mensagemSucesso ?></div>
            <?php endif; ?>
            <?php if (!empty($mensagemErro)): ?>
                <div class="alert alert-danger" style="color: red; margin-bottom: 15px;"><?= $mensagemErro ?></div>
            <?php endif; ?>


            <details class="funcao funcao--criar">
                <summary>Criar Nova Ação</summary>
                <div class="conteudo-funcao">
                    <form class="pesquisador-formulario" method="POST" action="">
                        <input type="hidden" name="cadastrar_acao" value="1">
                        <div class="campos">
                            <div class="campo">
                                <label for="titulo_acao">Título da ação</label>
                                <input
                                    type="text"
                                    id="titulo_acao"
                                    name="titulo_acao"
                                    placeholder="Digite o título da ação"
                                    required
                                >
                            </div>

                            <div class="campo">
                                <label for="tipo_acao">Tipo da ação</label>
                                <select
                                    id="tipo_acao"
                                    name="tipo_acao"
                                    required
                                >
                                    <option value="">Selecione o tipo</option>
                                    <option value="local">Coordenação de eventos LOCAL</option>
                                    <option value="nacional">Coordenação de evento NACIONAL</option>
                                    <option value="regional">Coordenação de evento REGIONAL</option>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="data_realizacao">Data de realização</label>
                                <input
                                    type="date"
                                    id="data_realizacao"
                                    name="data_realizacao"
                                    required
                                >
                            </div>

                            <div class="campo">
                                <label for="descricao_acao">Descrição da ação</label>
                                <textarea
                                    id="descricao_acao"
                                    name="descricao_acao"
                                    placeholder="Descreva os detalhes da ação"
                                    required
                                ></textarea>
                            </div>
                        </div>

                        <div class="pesquisador-formulario__acoes">
                            <button type="submit" class="btn salvar">
                                Cadastrar Ação
                            </button>
                        </div>
                    </form>
                </div>
            </details>

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