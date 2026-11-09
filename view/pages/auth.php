<?php
if (($_GET['acao'] ?? '') === 'sair') {
    session_start();
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
    header('Location: tela_login.html', true, 303);
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
$email = trim((string) ($dados['email'] ?? ''));
$senha = (string) ($dados['senha'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
    responderLogin(422, ['erro' => 'Informe um e-mail válido e a senha.']);
}

try {
    garantirColunasDeSenha($conexao);
    garantirContasDemonstracao($conexao);

    $destinos = [
        'Administrador' => 'administrador/tela_adminstrador.html',
        'Pesquisador' => 'pesquisador/tela_pesquisador.html',
        'Validador' => 'validador/tela_validador.html',
        'Comitê' => 'comite/tela_comite.html',
    ];
    $usuarioAutenticado = null;

    $stmtDemo = mysqli_prepare(
        $conexao,
        'SELECT email, nome, tipo, senha_hash FROM Usuarios_conta WHERE email = ? AND status = 1 LIMIT 1'
    );
    mysqli_stmt_bind_param($stmtDemo, 's', $email);
    mysqli_stmt_execute($stmtDemo);
    $resultadoDemo = mysqli_stmt_get_result($stmtDemo);
    $contaDemo = mysqli_fetch_assoc($resultadoDemo);
    mysqli_stmt_close($stmtDemo);

    if ($contaDemo && password_verify($senha, $contaDemo['senha_hash'])) {
        $usuarioAutenticado = [
            'id' => 0,
            'nome' => $contaDemo['nome'],
            'email' => $contaDemo['email'],
            'tipo' => $contaDemo['tipo'],
            'destino' => $destinos[$contaDemo['tipo']],
        ];
    }

    foreach (['Administrador', 'Pesquisador', 'Validador'] as $tipo) {
        if ($usuarioAutenticado) break;
        $stmt = mysqli_prepare(
            $conexao,
            "SELECT id, nome, email, senha_hash FROM `{$tipo}` WHERE email = ? AND COALESCE(status, 1) = 1 LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
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

    if (!$usuarioAutenticado) {
        responderLogin(401, ['erro' => 'E-mail ou senha incorretos, ou usuário desativado.']);
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
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
