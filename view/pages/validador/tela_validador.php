<?php
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

function responder(int $codigo, array $dados): void
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function garantirColunaMotivoRecusa(mysqli $conexao): void
{
    $coluna = mysqli_query($conexao, "SHOW COLUMNS FROM Acao_pesquisador LIKE 'motivo_recusa'");
    $existe = mysqli_num_rows($coluna) > 0;
    mysqli_free_result($coluna);

    if (!$existe) {
        mysqli_query($conexao, 'ALTER TABLE Acao_pesquisador ADD COLUMN motivo_recusa VARCHAR(300) NULL');
    }
}

if (($_SESSION['usuario']['tipo'] ?? null) !== 'Validador') {
    responder(401, ['erro' => 'Entre com uma conta de Validador para avaliar ações.']);
}

try {
    garantirColunaMotivoRecusa($conexao);
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodo === 'GET') {
        $setor = null;
        $idValidador = (int) ($_SESSION['usuario']['id'] ?? 0);

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
            $setor = $linhaSetor['nome'] ?? null;
        }

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
        $acoes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_free_result($resultado);
        responder(200, ['acoes' => $acoes, 'setor' => $setor]);
    }

    if ($metodo !== 'PATCH') {
        header('Allow: GET, PATCH');
        responder(405, ['erro' => 'Método não permitido.']);
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        responder(400, ['erro' => 'Requisição inválida.']);
    }

    $id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
    $status = $dados['status'] ?? '';
    $motivo = trim((string) ($dados['motivo'] ?? ''));

    if (!$id || !in_array($status, ['Aprovado', 'Recusado'], true)) {
        responder(422, ['erro' => 'Ação ou avaliação inválida.']);
    }

    if ($status === 'Recusado') {
        $tamanhoMotivo = function_exists('mb_strlen') ? mb_strlen($motivo, 'UTF-8') : strlen($motivo);
        if ($motivo === '' || $tamanhoMotivo > 300) {
            responder(422, ['erro' => 'Explique o motivo da recusa em até 300 caracteres.']);
        }
    } else {
        $motivo = null;
    }

    $stmt = mysqli_prepare(
        $conexao,
        "UPDATE Acao_pesquisador SET status = ?, motivo_recusa = ? WHERE id = ? AND status = 'Pendente'"
    );
    mysqli_stmt_bind_param($stmt, 'ssi', $status, $motivo, $id);
    mysqli_stmt_execute($stmt);
    $alteradas = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    if ($alteradas !== 1) {
        responder(409, ['erro' => 'A ação não existe ou já foi avaliada. Atualize a lista e tente novamente.']);
    }

    responder(200, ['mensagem' => 'Avaliação salva com sucesso.']);
} catch (mysqli_sql_exception $erro) {
    error_log($erro->getMessage());
    responder(500, ['erro' => 'Erro ao acessar as ações no banco de dados.']);
}