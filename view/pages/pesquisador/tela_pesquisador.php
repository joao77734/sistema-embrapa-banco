<?php
require_once '../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'Pesquisador') {
    header('Location: ../tela_login.html');
    exit;
}

$id_pesquisador = $_SESSION['usuario']['id'];

$mensagemErro = '';
$mensagemSucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao_tipo = $_POST['acao_tipo'] ?? '';

    if ($acao_tipo === 'cadastrar') {
        $titulo = mysqli_real_escape_string($conexao, $_POST['titulo_acao'] ?? '');
        $tipo_acao = mysqli_real_escape_string($conexao, $_POST['tipo_acao'] ?? '');
        $data_realizacao = mysqli_real_escape_string($conexao, $_POST['data_realizacao'] ?? '');
        $descricao = mysqli_real_escape_string($conexao, $_POST['descricao_acao'] ?? '');
        $status = 'Pendente';

        if (!empty($titulo) && !empty($tipo_acao) && !empty($data_realizacao)) {
            $stmt = mysqli_prepare($conexao, "INSERT INTO Acao_pesquisador (titulo, categoria_acao, data_realizacao, descricao, status, id_pesquisador) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssssi", $titulo, $tipo_acao, $data_realizacao, $descricao, $status, $id_pesquisador);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensagemSucesso = "Ação cadastrada com sucesso!";
            } else {
                $mensagemErro = "Erro ao cadastrar: " . mysqli_error($conexao);
            }
            mysqli_stmt_close($stmt);
        } else {
            $mensagemErro = "Preencha todos os campos obrigatórios.";
        }
    } elseif ($acao_tipo === 'editar') {
        $id_acao = filter_var($_POST['id_acao'] ?? null, FILTER_VALIDATE_INT);
        $titulo = mysqli_real_escape_string($conexao, $_POST['titulo_acao'] ?? '');
        $tipo_acao = mysqli_real_escape_string($conexao, $_POST['tipo_acao'] ?? '');
        $data_realizacao = mysqli_real_escape_string($conexao, $_POST['data_realizacao'] ?? '');
        $descricao = mysqli_real_escape_string($conexao, $_POST['descricao_acao'] ?? '');

        if ($id_acao && !empty($titulo) && !empty($tipo_acao) && !empty($data_realizacao)) {
            $stmt = mysqli_prepare($conexao, "UPDATE Acao_pesquisador SET titulo = ?, categoria_acao = ?, data_realizacao = ?, descricao = ? WHERE id = ? AND id_pesquisador = ?");
            mysqli_stmt_bind_param($stmt, "ssssii", $titulo, $tipo_acao, $data_realizacao, $descricao, $id_acao, $id_pesquisador);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensagemSucesso = "Ação atualizada com sucesso!";
            } else {
                $mensagemErro = "Erro ao atualizar: " . mysqli_error($conexao);
            }
            mysqli_stmt_close($stmt);
        } else {
            $mensagemErro = "Preencha todos os campos para atualizar.";
        }
    } elseif ($acao_tipo === 'excluir') {
        $id_acao = filter_var($_POST['id_acao'] ?? null, FILTER_VALIDATE_INT);

        if ($id_acao) {
            $stmt = mysqli_prepare($conexao, "DELETE FROM Acao_pesquisador WHERE id = ? AND id_pesquisador = ?");
            mysqli_stmt_bind_param($stmt, "ii", $id_acao, $id_pesquisador);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensagemSucesso = "Ação excluída com sucesso!";
            } else {
                $mensagemErro = "Erro ao excluir: " . mysqli_error($conexao);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$stmtConsulta = mysqli_prepare($conexao, "SELECT * FROM Acao_pesquisador WHERE id_pesquisador = ? ORDER BY data_realizacao DESC");
mysqli_stmt_bind_param($stmtConsulta, "i", $id_pesquisador);
mysqli_stmt_execute($stmtConsulta);
$resultado = mysqli_stmt_get_result($stmtConsulta);

$acoesList = [];
if ($resultado) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $acoesList[] = $row;
    }
}
mysqli_stmt_close($stmtConsulta);
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
                <li><span>Pesquisador (<?= htmlspecialchars($_SESSION['usuario']['nome']) ?>)</span></li>
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
                <div class="alert alert-success" style="color: green; margin-bottom: 15px; text-align: center; font-weight: bold;"><?= $mensagemSucesso ?></div>
            <?php endif; ?>
            <?php if (!empty($mensagemErro)): ?>
                <div class="alert alert-danger" style="color: red; margin-bottom: 15px; text-align: center; font-weight: bold;"><?= $mensagemErro ?></div>
            <?php endif; ?>

            <!-- DROPDOWN: CRIAR NOVA AÇÃO -->
            <details class="funcao funcao--criar">
                <summary>Criar Nova Ação</summary>
                <div class="conteudo-funcao">
                    <form class="pesquisador-formulario" method="POST" action="">
                        <input type="hidden" name="acao_tipo" value="cadastrar">
                        <div class="campos">
                            <div class="campo">
                                <label for="titulo_acao">Título da ação</label>
                                <input type="text" id="titulo_acao" name="titulo_acao" placeholder="Digite o título da ação" required>
                            </div>

                            <div class="campo">
                                <label for="tipo_acao">Tipo da ação</label>
                                <select id="tipo_acao" name="tipo_acao" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="Coordenação de eventos LOCAL">Coordenação de eventos LOCAL</option>
                                    <option value="Coordenação de evento NACIONAL">Coordenação de evento NACIONAL</option>
                                    <option value="Coordenação de evento REGIONAL">Coordenação de evento REGIONAL</option>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="data_realizacao">Data de realização</label>
                                <input type="date" id="data_realizacao" name="data_realizacao" required>
                            </div>

                            <div class="campo">
                                <label for="descricao_acao">Descrição da ação</label>
                                <textarea id="descricao_acao" name="descricao_acao" placeholder="Descreva os detalhes da ação" required></textarea>
                            </div>
                        </div>

                        <div class="pesquisador-formulario__acoes">
                            <button type="submit" class="btn salvar">Cadastrar Ação</button>
                        </div>
                    </form>
                </div>
            </details>

            <!-- DROPDOWN: EDITAR AÇÃO -->
            <details class="funcao" id="secaoEditar" hidden>
                <summary>Editar Ação</summary>
                <div class="conteudo-funcao">
                    <form class="pesquisador-formulario" id="formEditar" method="POST" action="">
                        <input type="hidden" name="acao_tipo" value="editar">
                        <input type="hidden" id="edit_id_acao" name="id_acao">
                        <div class="campos">
                            <div class="campo">
                                <label for="edit_titulo_acao">Título da ação</label>
                                <input type="text" id="edit_titulo_acao" name="titulo_acao" required>
                            </div>

                            <div class="campo">
                                <label for="edit_tipo_acao">Tipo da ação</label>
                                <select id="edit_tipo_acao" name="tipo_acao" required>
                                    <option value="Coordenação de eventos LOCAL">Coordenação de eventos LOCAL</option>
                                    <option value="Coordenação de evento NACIONAL">Coordenação de evento NACIONAL</option>
                                    <option value="Coordenação de evento REGIONAL">Coordenação de evento REGIONAL</option>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="edit_data_realizacao">Data de realização</label>
                                <input type="date" id="edit_data_realizacao" name="data_realizacao" required>
                            </div>

                            <div class="campo">
                                <label for="edit_descricao_acao">Descrição da ação</label>
                                <textarea id="edit_descricao_acao" name="descricao_acao" required></textarea>
                            </div>
                        </div>

                        <div class="pesquisador-formulario__acoes" style="display: flex; gap: 10px;">
                            <button type="submit" class="btn salvar">Salvar Alterações</button>
                            <button type="button" class="btn cancelar" onclick="fecharEdicao()">Cancelar</button>
                        </div>
                    </form>
                </div>
            </details>

            <!-- DROPDOWN: LISTAR MINHAS AÇÕES -->
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
                                                    <button type="button" class="btn editar" onclick='preencherEdicao(<?= json_encode($row) ?>)'>Editar</button>
                                                    <button type="button" class="btn excluir" onclick='abrirModalExclusao(<?= $row['id'] ?>, "<?= htmlspecialchars($row['titulo'], ENT_QUOTES) ?>")'>Excluir</button>
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

    <!-- POP-UP DE EXCLUSÃO INTERNO -->
    <div id="modalExclusao" class="popup-fundo" style="display: none;" hidden>
        <section class="pesquisador-pop-up-exclusao" role="alertdialog" aria-modal="true" style="background: #fff; padding: 30px; border-radius: 12px; width: min(100%, 440px); text-align: center;">
            <div class="pesquisador-pop-up-exclusao__aviso" aria-hidden="true" style="display: grid; width: 58px; height: 58px; margin: 0 auto 16px; place-items: center; border-radius: 50%; font-size: 34px; font-weight: 700; background: #FCE5E5; color: #C62828;">!</div>
            <h1 style="margin: 0 0 10px; color: #C62828; font-size: 23px;">Excluir ação?</h1>
            <p style="margin: 0; color: #555; line-height: 1.6;">Tem certeza de que deseja excluir esta ação?</p>
            <p class="pesquisador-pop-up-exclusao__nome" style="margin-top: 12px;">
                <strong id="nomeAcaoExcluir" style="color: #333;"></strong>
            </p>
            <form method="POST" action="" style="margin-top: 24px;">
                <input type="hidden" name="acao_tipo" value="excluir">
                <input type="hidden" id="inputExcluirId" name="id_acao" value="">
                <div class="pesquisador-pop-up-exclusao__acoes" style="display: flex; justify-content: center; gap: 10px;">
                    <button type="button" class="btn cancelar" onclick="fecharModalExclusao()">Cancelar</button>
                    <button type="submit" class="btn pesquisador-pop-up-exclusao__confirmar" style="background: #C62828; color: #fff;">Confirmar exclusão</button>
                </div>
            </form>
        </section>
    </div>

<script>
    let dadosOriginaisEdicao = {};

    function preencherEdicao(acao) {
        const secaoEditar = document.getElementById('secaoEditar');
        secaoEditar.hidden = false;
        secaoEditar.open = true;

        document.getElementById('edit_id_acao').value = acao.id;
        document.getElementById('edit_titulo_acao').value = acao.titulo;
        document.getElementById('edit_tipo_acao').value = acao.categoria_acao;
        document.getElementById('edit_data_realizacao').value = acao.data_realizacao;
        document.getElementById('edit_descricao_acao').value = acao.descricao;

        // Guarda os dados originais no momento da abertura para validação posterior
        dadosOriginaisEdicao = {
            titulo: acao.titulo.trim(),
            categoria_acao: acao.categoria_acao.trim(),
            data_realizacao: acao.data_realizacao.trim(),
            descricao: acao.descricao.trim()
        };

        secaoEditar.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function fecharEdicao() {
        const secaoEditar = document.getElementById('secaoEditar');
        secaoEditar.open = false;
        secaoEditar.hidden = true;
    }

    // Valida se houve alteração antes de submeter o formulário de edição
    document.getElementById('formEditar').addEventListener('submit', function(evento) {
        const novosDados = {
            titulo: document.getElementById('edit_titulo_acao').value.trim(),
            categoria_acao: document.getElementById('edit_tipo_acao').value.trim(),
            data_realizacao: document.getElementById('edit_data_realizacao').value.trim(),
            descricao: document.getElementById('edit_descricao_acao').value.trim()
        };

        const houveAlteracao = 
            novosDados.titulo !== dadosOriginaisEdicao.titulo ||
            novosDados.categoria_acao !== dadosOriginaisEdicao.categoria_acao ||
            novosDados.data_realizacao !== dadosOriginaisEdicao.data_realizacao ||
            novosDados.descricao !== dadosOriginaisEdicao.descricao;

        if (!houveAlteracao) {
            evento.preventDefault();
            alert('Nenhuma alteração foi realizada.');
        }
    });

    const modalExclusao = document.getElementById('modalExclusao');

    function abrirModalExclusao(id, titulo) {
        document.getElementById('inputExcluirId').value = id;
        document.getElementById('nomeAcaoExcluir').innerText = titulo;
        modalExclusao.hidden = false;
        modalExclusao.style.display = 'grid';
    }

    function fecharModalExclusao() {
        modalExclusao.hidden = true;
        modalExclusao.style.display = 'none';
    }

    modalExclusao.addEventListener('click', (event) => {
        if (event.target === modalExclusao) {
            fecharModalExclusao();
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