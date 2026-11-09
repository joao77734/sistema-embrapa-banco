<?php
require_once __DIR__ . '/../../../config/database.php';

$atividades = [];
$erroConsulta = false;

$sql = '
    SELECT
        log.data_hora,
        log.operacao_tabela,
        log.id_registro,
        COALESCE(pesquisador.nome, validador.nome, "Usuário não encontrado") AS usuario,
        COALESCE(pesquisador.funcao, validador.funcao, "Não informado") AS cargo
    FROM Log_auditor AS log
    LEFT JOIN Pesquisador AS pesquisador
        ON pesquisador.matricula = log.matricula_usuario
    LEFT JOIN Validador AS validador
        ON validador.matricula = log.matricula_usuario
    ORDER BY log.data_hora DESC, log.id_log DESC
';

try {
    $resultado = mysqli_query($conexao, $sql);
    $atividades = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_free_result($resultado);
} catch (mysqli_sql_exception $erro) {
    $erroConsulta = true;
}

function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
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
                <li><span>Comitê</span></li>
                <li><a href="#">Sair</a></li>
            </ul>
        </nav>

    </div>
</header>

<main>

    <div class="container">

        <section class="comite-conteudo">

            <h1 class="comite-titulo">Auditoria do Sistema</h1>

            <p class="comite-subtitulo">
                Registro das atividades realizadas pelos usuários
            </p>

            <details class="comite-funcao">

                <summary class="comite-funcao-titulo">Listar Atividades dos Usuários</summary>

                <div class="comite-funcao-conteudo">

                    <div class="comite-tabela-responsiva">

                        <table class="comite-tabela">

                            <thead>
                                <tr class="comite-tabela-linha">
                                    <th class="comite-tabela-cabecalho">Usuário</th>
                                    <th class="comite-tabela-cabecalho">Cargo</th>
                                    <th class="comite-tabela-cabecalho">Data e hora</th>
                                    <th class="comite-tabela-cabecalho">Descrição do que foi realizado</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if ($erroConsulta): ?>
                                    <tr class="comite-tabela-linha">
                                        <td class="comite-tabela-celula" colspan="4">
                                            Não foi possível carregar as atividades. Verifique a estrutura do banco de dados.
                                        </td>
                                    </tr>
                                <?php elseif (empty($atividades)): ?>
                                    <tr class="comite-tabela-linha">
                                        <td class="comite-tabela-celula" colspan="4">
                                            Nenhuma atividade foi registrada.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($atividades as $atividade): ?>
                                        <tr class="comite-tabela-linha">
                                            <td class="comite-tabela-celula"><?= escapar($atividade['usuario']) ?></td>
                                            <td class="comite-tabela-celula"><?= escapar($atividade['cargo']) ?></td>
                                            <td class="comite-tabela-celula comite-tabela-celula-data">
                                                <?= escapar(date('d/m/Y H:i', strtotime($atividade['data_hora']))) ?>
                                            </td>
                                            <td class="comite-tabela-celula">
                                                <?= escapar('Registro na tabela ' . $atividade['operacao_tabela'] . ' (ID ' . $atividade['id_registro'] . ')') ?>
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