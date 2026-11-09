<?php
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
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

    $resultado = mysqli_query($conexao, $sql);
    $atividades = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_free_result($resultado);

    echo json_encode(['atividades' => $atividades], JSON_UNESCAPED_UNICODE);
} catch (mysqli_sql_exception $erro) {
    error_log($erro->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Não foi possível carregar as atividades. Verifique a conexão e a estrutura do banco de dados.'], JSON_UNESCAPED_UNICODE);
}
?>
