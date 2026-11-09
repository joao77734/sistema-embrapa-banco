<?php
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

function responderAvaliacao(int $codigo, array $dados): void
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SESSION['usuario']['tipo'] ?? null) !== 'Validador') {
    responderAvaliacao(401, ['erro' => 'Entre com uma conta de Validador para avaliar ações.']);
}

try {
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodo === 'GET') {
        $sql = '
            SELECT
                acao.id,
                acao.titulo,
                acao.descricao,
                acao.categoria_acao,
                acao.data_realizacao,
                acao.status,
                pesquisador.nome AS pesquisador
            FROM Acao_pesquisador AS acao
            INNER JOIN Pesquisador AS pesquisador ON pesquisador.id = acao.id_pesquisador
            ORDER BY CASE WHEN acao.status = "Pendente" THEN 0 ELSE 1 END,
                acao.data_realizacao DESC, acao.id DESC
        ';
        $resultado = mysqli_query($conexao, $sql);
        $acoes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_free_result($resultado);
        responderAvaliacao(200, ['acoes' => $acoes]);
    }

    if ($metodo !== 'PATCH') {
        header('Allow: GET, PATCH');
        responderAvaliacao(405, ['erro' => 'Método não permitido.']);
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        responderAvaliacao(400, ['erro' => 'Requisição inválida.']);
    }

    $id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
    $status = $dados['status'] ?? '';

    if (!$id || !in_array($status, ['Aprovado', 'Recusado'], true)) {
        responderAvaliacao(422, ['erro' => 'Ação ou avaliação inválida.']);
    }

    $stmt = mysqli_prepare(
        $conexao,
        "UPDATE Acao_pesquisador SET status = ? WHERE id = ? AND status = 'Pendente'"
    );
    mysqli_stmt_bind_param($stmt, 'si', $status, $id);
    mysqli_stmt_execute($stmt);
    $alteradas = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($alteradas !== 1) {
        responderAvaliacao(409, ['erro' => 'A ação não existe ou já foi avaliada. Atualize a lista e tente novamente.']);
    }

    responderAvaliacao(200, ['mensagem' => 'Avaliação salva com sucesso.']);
} catch (mysqli_sql_exception $erro) {
    error_log($erro->getMessage());
    responderAvaliacao(500, ['erro' => 'Erro ao acessar as ações no banco de dados.']);
}