<?php
require_once __DIR__ . '/../../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $dados): void
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function entradaJson(): array
{
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        responder(400, ['erro' => 'Corpo da requisição inválido.']);
    }
    return $dados;
}

function validarTipo($tipo): string
{
    if (!in_array($tipo, ['Pesquisador', 'Validador'], true)) {
        responder(422, ['erro' => 'Selecione Pesquisador ou Validador.']);
    }
    return $tipo;
}

function validarUsuario(array $dados): array
{
    $usuario = [
        'nome' => trim((string) ($dados['nome'] ?? '')),
        'matricula' => trim((string) ($dados['matricula'] ?? '')),
        'email' => trim((string) ($dados['email'] ?? '')),
        'funcao' => trim((string) ($dados['funcao'] ?? '')),
        'id_setor' => filter_var($dados['id_setor'] ?? null, FILTER_VALIDATE_INT),
    ];
    $tamanhoCargo = function_exists('mb_strlen')
        ? mb_strlen($usuario['funcao'], 'UTF-8')
        : strlen($usuario['funcao']);

    if ($usuario['nome'] === '' || $usuario['matricula'] === '' ||
        !filter_var($usuario['email'], FILTER_VALIDATE_EMAIL) ||
        $usuario['funcao'] === '' || $tamanhoCargo > 13 || !$usuario['id_setor']) {
        responder(422, ['erro' => 'Confira os dados. Cargo deve ter até 13 caracteres e o setor é obrigatório.']);
    }
    return $usuario;
}

function tabelaUsuario(string $tipo): string
{
    return $tipo === 'Pesquisador' ? 'Pesquisador' : 'Validador';
}

try {
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodo === 'GET') {
        foreach (['SIPT', 'NCO', 'SPIT'] as $nomeSetor) {
            $stmtSetor = mysqli_prepare(
                $conexao,
                'INSERT INTO Setor (nome) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM Setor WHERE nome = ? AND status = 1)'
            );
            mysqli_stmt_bind_param($stmtSetor, 'ss', $nomeSetor, $nomeSetor);
            mysqli_stmt_execute($stmtSetor);
            mysqli_stmt_close($stmtSetor);
        }

        $setoresResultado = mysqli_query($conexao, 'SELECT id, nome FROM Setor WHERE status = 1 ORDER BY nome');
        $setores = mysqli_fetch_all($setoresResultado, MYSQLI_ASSOC);
        mysqli_free_result($setoresResultado);

        $sql = "
            SELECT id, nome, email, matricula, funcao, status, id_setor, 'Pesquisador' AS tipo
            FROM Pesquisador
            UNION ALL
            SELECT id, nome, email, matricula, funcao, status, id_setor, 'Validador' AS tipo
            FROM Validador
            ORDER BY nome
        ";
        $resultado = mysqli_query($conexao, $sql);
        $usuarios = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_free_result($resultado);
        responder(200, ['usuarios' => $usuarios, 'setores' => $setores]);
    }

    if ($metodo === 'POST') {
        $dados = entradaJson();
        $tipo = validarTipo($dados['tipo'] ?? null);
        $usuario = validarUsuario($dados);
        $tabela = tabelaUsuario($tipo);
        $stmt = mysqli_prepare($conexao, "INSERT INTO {$tabela} (nome, email, matricula, funcao, id_setor) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssi', $usuario['nome'], $usuario['email'], $usuario['matricula'], $usuario['funcao'], $usuario['id_setor']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        responder(201, ['mensagem' => 'Usuário cadastrado.']);
    }

    if ($metodo === 'PUT') {
        $dados = entradaJson();
        $tipo = validarTipo($dados['tipo'] ?? null);
        $usuario = validarUsuario($dados);
        $id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) responder(422, ['erro' => 'Identificador de usuário inválido.']);

        $tabela = tabelaUsuario($tipo);
        $stmt = mysqli_prepare($conexao, "UPDATE {$tabela} SET nome = ?, email = ?, matricula = ?, funcao = ?, id_setor = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ssssii', $usuario['nome'], $usuario['email'], $usuario['matricula'], $usuario['funcao'], $usuario['id_setor'], $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        responder(200, ['mensagem' => 'Cadastro atualizado.']);
    }

    if ($metodo === 'PATCH') {
        $dados = entradaJson();
        $tipo = validarTipo($dados['tipo'] ?? null);
        $id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
        $status = filter_var($dados['status'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || !in_array($status, [0, 1], true)) {
            responder(422, ['erro' => 'Identificador ou situação inválida.']);
        }

        $tabela = tabelaUsuario($tipo);
        $stmt = mysqli_prepare($conexao, "UPDATE {$tabela} SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $status, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        responder(200, ['mensagem' => 'Situação atualizada.']);
    }

    header('Allow: GET, POST, PUT, PATCH');
    responder(405, ['erro' => 'Método não permitido.']);
} catch (mysqli_sql_exception $erro) {
    if ($erro->getCode() === 1062) {
        responder(409, ['erro' => 'Já existe um usuário com essa matrícula.']);
    }
    if ($erro->getCode() === 1452) {
        responder(422, ['erro' => 'O setor selecionado não existe ou está inativo.']);
    }
    error_log($erro->getMessage());
    responder(500, ['erro' => 'Erro ao acessar o banco de dados. Verifique a conexão e as tabelas.']);
}