<?php
if (($_GET['acao'] ?? '') === 'sair') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $parametros['path'],
            $parametros['domain'],
            $parametros['secure'],
            $parametros['httponly']
        );
    }

    session_destroy();
    header('Location: tela_login.php', true, 303);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth_helpers.php';

header('Content-Type: application/json; charset=utf-8');

function responderLogin(int $codigo, array $dados): void
{
    http_response_code($codigo);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    responderLogin(405, ['erro' => 'Método não permitido.']);
}

$dados = json_decode(file_get_contents('php://input'), true);
$email = trim((string) ($dados['email'] ?? $_POST['usuario'] ?? ''));
$senha = (string) ($dados['senha'] ?? $_POST['senha'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
    responderLogin(422, ['erro' => 'Informe um e-mail válido e a senha.']);
}

try {
    garantirColunasDeSenha($conexao);
    garantirContasDemonstracao($conexao);

    $destinos = [
        'Administrador' => 'administrador/tela_adminstrador.php',
        'Pesquisador' => 'pesquisador/tela_pesquisador.php',
        'Validador' => 'validador/tela_validador.php',
        'Comitê' => 'comite/tela_comite.php',
    ];
    $usuarioAutenticado = null;

    // 1. Verificação nas contas de demonstração
    $stmtDemo = mysqli_prepare(
        $conexao,
        'SELECT email, nome, tipo, senha_hash, status FROM Usuarios_conta WHERE email = ? LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtDemo, 's', $email);
    mysqli_stmt_execute($stmtDemo);
    $resultadoDemo = mysqli_stmt_get_result($stmtDemo);
    $contaDemo = mysqli_fetch_assoc($resultadoDemo);
    mysqli_stmt_close($stmtDemo);

    if ($contaDemo) {
        if (isset($contaDemo['status']) && (int)$contaDemo['status'] === 0) {
            responderLogin(403, ['erro' => 'Sua conta está desativada. Entre em contato com o administrador.']);
        }
        if (password_verify($senha, $contaDemo['senha_hash'])) {
            $usuarioAutenticado = [
                'id' => 0,
                'nome' => $contaDemo['nome'],
                'email' => $contaDemo['email'],
                'tipo' => $contaDemo['tipo'],
                'destino' => $destinos[$contaDemo['tipo']],
            ];
        }
    }

    // 2. Verificação nas tabelas normais de Administrador, Pesquisador e Validador
    foreach (['Administrador', 'Pesquisador', 'Validador'] as $tipo) {
        if ($usuarioAutenticado) break;
        
        $stmt = mysqli_prepare(
            $conexao,
            "SELECT id, nome, email, senha_hash, status FROM `{$tipo}` WHERE email = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);

        if ($usuario) {
            // Valida se o usuário está desativado (status = 0)
            if (isset($usuario['status']) && (int)$usuario['status'] === 0) {
                responderLogin(403, ['erro' => 'Sua conta está desativada. Entre em contato com o administrador.']);
            }

            if (password_verify($senha, $usuario['senha_hash'])) {
                $usuarioAutenticado = [
                    'id' => (int) $usuario['id'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email'],
                    'tipo' => $tipo,
                    'destino' => $destinos[$tipo],
                ];
                break;
            }
        }
    }

    if (!$usuarioAutenticado) {
        responderLogin(401, ['erro' => 'E-mail ou senha incorretos.']);
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id' => $usuarioAutenticado['id'],
        'nome' => $usuarioAutenticado['nome'],
        'email' => $usuarioAutenticado['email'],
        'tipo' => $usuarioAutenticado['tipo'],
    ];

    responderLogin(200, ['destino' => $usuarioAutenticado['destino']]);
} catch (mysqli_sql_exception $erro) {
    error_log($erro->getMessage());
    responderLogin(500, ['erro' => 'Não foi possível acessar as contas. Verifique a estrutura do banco de dados.']);
}