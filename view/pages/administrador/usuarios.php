<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/auth_helpers.php';

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

try {
    if (function_exists('garantirColunasDeSenha')) {
        garantirColunasDeSenha($conexao);
    }
    
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodo === 'GET') {
        $setoresResultado = mysqli_query($conexao, 'SELECT DISTINCT id, nome FROM Setor WHERE status = 1 ORDER BY nome');
        $setores = mysqli_fetch_all($setoresResultado, MYSQLI_ASSOC);
        mysqli_free_result($setoresResultado);

        // Seleciona explicitamente a coluna funcao de cada tabela
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
        $nome = trim($dados['nome'] ?? '');
        $matricula = trim($dados['matricula'] ?? '');
        $email = trim($dados['email'] ?? '');
        $senhaInput = trim($dados['senha'] ?? '');
        $funcoes = $dados['funcoes'] ?? [];
        $idSetor = filter_var($dados['id_setor'] ?? null, FILTER_VALIDATE_INT);

        if ($nome === '' || $matricula === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($funcoes)) {
            responder(422, ['erro' => 'Preencha todos os campos obrigatórios e selecione ao menos uma função.']);
        }

        $senhaFinal = $senhaInput !== '' ? $senhaInput : '1234';
        $senhaHash = password_hash($senhaFinal, PASSWORD_DEFAULT);
        // Junta as funções selecionadas com vírgula e espaço
        $funcaoTexto = implode(', ', $funcoes);

        $tabela = 'Pesquisador';
        $setorFinal = $idSetor ? $idSetor : 1;

        if (in_array('Validador', $funcoes, true)) {
            if (!$idSetor) {
                responder(422, ['erro' => 'O setor é obrigatório para validadores.']);
            }
            $tabela = 'Validador';
            $setorFinal = $idSetor;
        }

        $stmt = mysqli_prepare($conexao, "INSERT INTO {$tabela} (nome, email, matricula, funcao, senha_hash, id_setor) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssssi', $nome, $email, $matricula, $funcaoTexto, $senhaHash, $setorFinal);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        responder(201, ['mensagem' => 'Usuário cadastrado com sucesso.']);
    }

    if ($metodo === 'PUT') {
        $dados = entradaJson();
        $id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
        $tipo = $dados['tipo'] ?? 'Pesquisador';
        $nome = trim($dados['nome'] ?? '');
        $matricula = trim($dados['matricula'] ?? '');
        $email = trim($dados['email'] ?? '');
        $idSetor = filter_var($dados['id_setor'] ?? null, FILTER_VALIDATE_INT);

        if (!$id) responder(422, ['erro' => 'Identificador inválido.']);

        $tabela = ($tipo === 'Validador') ? 'Validador' : 'Pesquisador';
        
        if ($tabela === 'Validador') {
            if (!$idSetor) {
                responder(422, ['erro' => 'O setor é obrigatório para validadores.']);
            }
            $stmt = mysqli_prepare($conexao, "UPDATE Validador SET nome = ?, email = ?, matricula = ?, id_setor = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'sssii', $nome, $email, $matricula, $idSetor, $id);
        } else {
            $stmt = mysqli_prepare($conexao, "UPDATE Pesquisador SET nome = ?, email = ?, matricula = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'sssi', $nome, $email, $matricula, $id);
        }

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        responder(200, ['mensagem' => 'Cadastro atualizado com sucesso.']);
    }

    if ($metodo === 'PATCH') {
        $dados = entradajson();
        $tipo = $dados['tipo'] ?? 'Pesquisador';
        $id = filter_var($dados['id'] ?? null, FILTER_VALIDATE_INT);
        $status = filter_var($dados['status'] ?? null, FILTER_VALIDATE_INT);

        $tabela = ($tipo === 'Validador') ? 'Validador' : 'Pesquisador';
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
        responder(409, ['erro' => 'Já existe um usuário cadastrado com essa matrícula.']);
    }
    error_log($erro->getMessage());
    responder(500, ['erro' => 'Erro ao acessar o banco de dados.']);
}