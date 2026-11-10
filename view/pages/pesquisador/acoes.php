<?php
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
session_start();

function responderAcoes(int $codigo, array $dados): void
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SESSION['usuario']['tipo'] ?? null) !== 'Pesquisador') {
    responderAcoes(401, ['erro' => 'Entre com uma conta de Pesquisador para gerenciar ações.']);
}

try {
    $usuario = $_SESSION['usuario'];
    $idPesquisador = filter_var($usuario['id'] ?? null, FILTER_VALIDATE_INT);

    // Contas demonstrativas usam id 0; associe-as somente a um perfil existente pelo e-mail.
    if (!$idPesquisador || $idPesquisador < 1) {
        $email = (string) ($usuario['email'] ?? '');
        $stmtPesquisador = mysqli_prepare(
            $conexao,
            'SELECT id FROM Pesquisador WHERE email = ? AND COALESCE(status, 1) = 1 LIMIT 1'
        );
        mysqli_stmt_bind_param($stmtPesquisador, 's', $email);
        mysqli_stmt_execute($stmtPesquisador);
        $resultadoPesquisador = mysqli_stmt_get_result($stmtPesquisador);
        $perfil = mysqli_fetch_assoc($resultadoPesquisador);
        mysqli_stmt_close($stmtPesquisador);
        $idPesquisador = $perfil ? (int) $perfil['id'] : 0;
    }

    if ($idPesquisador < 1) {
        responderAcoes(403, ['erro' => 'Esta conta não possui um cadastro de Pesquisador vinculado.']);
    }

    $categorias = [
        'local' => 'Coordenação de eventos LOCAL',
        'nacional' => 'Coordenação de evento NACIONAL',
        'regional' => 'Coordenação de evento REGIONAL',
    ];
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodo === 'GET') {
        $stmt = mysqli_prepare(
            $conexao,
            'SELECT id, titulo, descricao, categoria_acao, data_realizacao, status
             FROM Acao_pesquisador
             WHERE id_pesquisador = ?
             ORDER BY data_realizacao DESC, id DESC'
        );
        mysqli_stmt_bind_param($stmt, 'i', $idPesquisador);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $acoes = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        responderAcoes(200, ['acoes' => $acoes]);
    }

    if ($metodo !== 'POST') {
        header('Allow: GET, POST');
        responderAcoes(405, ['erro' => 'Método não permitido.']);
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        responderAcoes(400, ['erro' => 'Requisição inválida.']);
    }

    $titulo = trim((string) ($dados['titulo_acao'] ?? ''));
    $descricao = trim((string) ($dados['descricao_acao'] ?? ''));
    $categoria = $categorias[(string) ($dados['tipo_acao'] ?? '')] ?? null;
    $data = (string) ($dados['data_realizacao'] ?? '');
    $dataValida = DateTimeImmutable::createFromFormat('!Y-m-d', $data);
    $errosData = DateTimeImmutable::getLastErrors();
    $dataValida = $dataValida
        && $dataValida->format('Y-m-d') === $data
        && ($errosData === false || $errosData['warning_count'] === 0 && $errosData['error_count'] === 0);

    if ($titulo === '' || mb_strlen($titulo) > 100
        || $descricao === '' || mb_strlen($descricao) > 300
        || $categoria === null || !$dataValida) {
        responderAcoes(422, ['erro' => 'Confira os campos: título (até 100 caracteres), descrição (até 300), tipo e data válida.']);
    }

    $stmt = mysqli_prepare(
        $conexao,
        "INSERT INTO Acao_pesquisador (titulo, descricao, categoria_acao, data_realizacao, status, id_pesquisador)
         VALUES (?, ?, ?, ?, 'Pendente', ?)"
    );
    mysqli_stmt_bind_param($stmt, 'ssssi', $titulo, $descricao, $categoria, $data, $idPesquisador);
    mysqli_stmt_execute($stmt);
    $novoId = mysqli_stmt_insert_id($stmt);
    mysqli_stmt_close($stmt);
    responderAcoes(201, ['mensagem' => 'Ação cadastrada com sucesso e enviada para análise.', 'id' => $novoId]);
} catch (mysqli_sql_exception $erro) {
    error_log($erro->getMessage());
    responderAcoes(500, ['erro' => 'Erro ao acessar as ações no banco de dados.']);
}
