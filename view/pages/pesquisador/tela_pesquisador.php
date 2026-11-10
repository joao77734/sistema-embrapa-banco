<?php
require_once __DIR__ . '/acoes_controller.php';

$mensagens = [
    'sucesso' => 'Ação cadastrada com sucesso e enviada para análise.',
    'campos' => 'Confira os dados informados e tente novamente.',
    'banco' => 'Não foi possível cadastrar a ação. Tente novamente.',
];
$tipoMensagem = isset($_GET['sucesso']) ? 'sucesso' : (string) ($_GET['erro'] ?? '');
$mensagem = $mensagens[$tipoMensagem] ?? '';

$stmt = mysqli_prepare(
    $conexao,
    'SELECT titulo, descricao, categoria_acao, data_realizacao, status
     FROM Acao_pesquisador
     WHERE id_pesquisador = ?
     ORDER BY data_realizacao DESC, id DESC'
);
mysqli_stmt_bind_param($stmt, 'i', $idPesquisador);
mysqli_stmt_execute($stmt);
$acoesList = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
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
                Consulte suas ações e cadastre novas ações para análise.
            </p>

            <?php if ($mensagem !== ''): ?>
                <p role="status" style="color: <?= isset($_GET['sucesso']) ? 'green' : '#b00020' ?>; margin-bottom: 15px;">
                    <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <details class="funcao funcao--criar">
                <summary>Criar Nova Ação</summary>
                <div class="conteudo-funcao">
                    <form class="pesquisador-formulario" method="POST" action="acoes_controller.php">
                        <input type="hidden" name="acao" value="criar">
                        <div class="campos">
                            <div class="campo">
                                <label for="titulo_acao">Título da ação</label>
                                <input
                                    type="text"
                                    id="titulo_acao"
                                    name="titulo_acao"
                                    placeholder="Digite o título da ação"
                                    maxlength="100"
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
                                    maxlength="300"
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($acoesList)): ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center;">Nenhuma ação cadastrada.</td>
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

</main>

<footer class="footer">
    <div class="container">
        <span>© Embrapa – Trabalho do Mauricio</span>
        <a href="#">Política de privacidade</a>
    </div>
</footer>

</body>
</html>