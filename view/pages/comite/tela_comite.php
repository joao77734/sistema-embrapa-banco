<?php
require_once __DIR__ . '/../../../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['tipo'] !== 'Comitê' && $_SESSION['usuario']['tipo'] !== 'Administrador')) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['erro' => 'Acesso não autorizado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    header('Location: ../tela_login.php');
    exit;
}

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json' || isset($_GET['ajax']);

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    $atividades = [];
    
    $sql = "
        SELECT 
            p.nome AS usuario, 
            p.matricula AS matricula,
            'Pesquisador' AS cargo, 
            a.data_realizacao AS data_hora, 
            CONCAT('Ação cadastrada: ', a.titulo) AS descricao,
            'Acao_pesquisador' AS operacao_tabela,
            a.id AS id_registro
        FROM Acao_pesquisador a
        INNER JOIN Pesquisador p ON p.id = a.id_pesquisador
        ORDER BY a.data_realizacao DESC
        LIMIT 50
    ";
    
    $resultado = mysqli_query($conexao, $sql);
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $atividades[] = $row;
        }
        mysqli_free_result($resultado);
    }

    echo json_encode(['atividades' => $atividades], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comitê - Embrapa</title>
    <link rel="stylesheet" href="../../../public/css/main.css">
    <link rel="stylesheet" href="../../../modelos/base.css">
</head>

<body>
    <header class="header">
        <div class="container">
            <a href="#" class="logo">
                <span class="logo__nome">Embrapa</span>
            </a>
            <nav aria-label="Principal">
                <ul class="abas">
                    <li><span>Comitê (<?= htmlspecialchars($_SESSION['usuario']['nome'] ?? '') ?>)</span></li>
                    <li><a href="../auth.php?acao=sair">Sair</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <section class="comite-conteudo">
                <h1 class="comite-titulo">Auditoria do Sistema</h1>
                <p class="comite-subtitulo">Registro das atividades realizadas pelos usuários</p>

                <details class="comite-funcao" open>
                    <summary class="comite-funcao-titulo">Listar Atividades dos Usuários</summary>
                    <div class="comite-funcao-conteudo">
                        <div class="comite-tabela-responsiva">
                            <table class="comite-tabela">
                                <thead>
                                    <tr class="comite-tabela-linha">
                                        <th class="comite-tabela-cabecalho">Usuário</th>
                                        <th class="comite-tabela-cabecalho">Matrícula</th>
                                        <th class="comite-tabela-cabecalho">Cargo</th>
                                        <th class="comite-tabela-cabecalho">Data</th>
                                        <th class="comite-tabela-cabecalho">Descrição do que foi realizado</th>
                                    </tr>
                                </thead>
                                <tbody id="listaAtividades">
                                    <tr class="comite-tabela-linha">
                                        <td class="comite-tabela-celula" colspan="5">Carregando atividades...</td>
                                    </tr>
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

    <script>
        const listaAtividades = document.getElementById('listaAtividades');

        function exibirMensagem(mensagem) {
            const linha = document.createElement('tr');
            linha.className = 'comite-tabela-linha';
            const celula = document.createElement('td');
            celula.className = 'comite-tabela-celula';
            celula.colSpan = 5;
            celula.textContent = mensagem;
            linha.append(celula);
            listaAtividades.replaceChildren(linha);
        }

        async function carregarAtividades() {
            try {
                const resposta = await fetch('tela_comite.php?ajax=1', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const dados = await resposta.json();
                if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível carregar as atividades.');

                if (!dados.atividades || !dados.atividades.length) {
                    exibirMensagem('Nenhuma atividade foi registrada.');
                    return;
                }

                listaAtividades.replaceChildren();
                dados.atividades.forEach((atividade) => {
                    const linha = document.createElement('tr');
                    linha.className = 'comite-tabela-linha';
                    const data = new Date(String(atividade.data_hora).replace(' ', 'T'));
                    
                    const dataFormatada = Number.isNaN(data.getTime())
                        ? atividade.data_hora
                        : new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short' }).format(data);
                    
                    const valores = [
                        atividade.usuario,
                        atividade.matricula,
                        atividade.cargo,
                        dataFormatada,
                        atividade.descricao || `Registro na tabela ${atividade.operacao_tabela} (ID ${atividade.id_registro})`
                    ];

                    valores.forEach((valor, indice) => {
                        const celula = document.createElement('td');
                        celula.className = indice === 3
                            ? 'comite-tabela-celula comite-tabela-celula-data'
                            : 'comite-tabela-celula';
                        celula.textContent = valor;
                        linha.append(celula);
                    });
                    listaAtividades.append(linha);
                });
            } catch (erro) {
                exibirMensagem(erro.message || 'Erro ao carregar as atividades.');
            }
        }

        carregarAtividades();
    </script>
</body>
</html>