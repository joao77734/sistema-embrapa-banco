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
        'Pesquisador'   => 'pesquisador/tela_pesquisador.php',
        'Validador'     => 'validador/tela_validador.php',
        'Comitê'        => 'comite/tela_comite.php',
    ];
    
    $perfisDisponiveis = [];
    $usuarioEncontrado = null;

    $adicionarPerfil = function (int $id, string $nome, string $emailUser, string $tipoPerfil) use (&$perfisDisponiveis, $destinos) {
        foreach ($perfisDisponiveis as $p) {
            if ($p['tipo'] === $tipoPerfil) {
                return;
            }
        }
        if (isset($destinos[$tipoPerfil])) {
            $perfisDisponiveis[] = [
                'id'      => $id,
                'nome'    => $nome,
                'email'   => $emailUser,
                'tipo'    => $tipoPerfil,
                'destino' => $destinos[$tipoPerfil],
            ];
        }
    };

    $stmtDemo = mysqli_prepare(
        $conexao,
        'SELECT email, nome, tipo, senha_hash, status FROM Usuarios_conta WHERE email = ?'
    );
    mysqli_stmt_bind_param($stmtDemo, 's', $email);
    mysqli_stmt_execute($stmtDemo);
    $resultadoDemo = mysqli_stmt_get_result($stmtDemo);
    
    while ($contaDemo = mysqli_fetch_assoc($resultadoDemo)) {
        if (isset($contaDemo['status']) && (int)$contaDemo['status'] === 0) {
            continue;
        }
        if (password_verify($senha, $contaDemo['senha_hash'])) {
            $usuarioEncontrado = ['id' => 0, 'nome' => $contaDemo['nome'], 'email' => $contaDemo['email']];
            $tiposConta = array_map('trim', explode(',', $contaDemo['tipo']));
            foreach ($tiposConta as $t) {
                $adicionarPerfil(0, $contaDemo['nome'], $contaDemo['email'], $t);
            }
        }
    }
    mysqli_stmt_close($stmtDemo);

    foreach (['Administrador', 'Pesquisador', 'Validador'] as $tabela) {
        $stmt = mysqli_prepare(
            $conexao,
            "SELECT id, nome, email, funcao, senha_hash, status FROM `{$tabela}` WHERE email = ?"
        );
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        while ($usuario = mysqli_fetch_assoc($resultado)) {
            if (isset($usuario['status']) && (int)$usuario['status'] === 0) {
                continue;
            }

            if (password_verify($senha, $usuario['senha_hash'])) {
                $usuarioEncontrado = ['id' => (int)$usuario['id'], 'nome' => $usuario['nome'], 'email' => $usuario['email']];
                $adicionarPerfil((int) $usuario['id'], $usuario['nome'], $usuario['email'], $tabela);

                $funcao = mb_strtolower(trim((string) ($usuario['funcao'] ?? '')), 'UTF-8');
                if (str_contains($funcao, 'comitê') || str_contains($funcao, 'comite')) {
                    $adicionarPerfil((int) $usuario['id'], $usuario['nome'], $usuario['email'], 'Comitê');
                }
                if (str_contains($funcao, 'validador')) {
                    $adicionarPerfil((int) $usuario['id'], $usuario['nome'], $usuario['email'], 'Validador');
                }
            }
        }
        mysqli_stmt_close($stmt);
    }

    if (empty($perfisDisponiveis) || !$usuarioEncontrado) {
        responderLogin(401, ['erro' => 'E-mail ou senha incorretos.']);
    }

    $adicionarPerfil($usuarioEncontrado['id'], $usuarioEncontrado['nome'], $usuarioEncontrado['email'], 'Pesquisador');
    $adicionarPerfil($usuarioEncontrado['id'], $usuarioEncontrado['nome'], $usuarioEncontrado['email'], 'Validador');
    $adicionarPerfil($usuarioEncontrado['id'], $usuarioEncontrado['nome'], $usuarioEncontrado['email'], 'Comitê');

    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path'     => '/',
    ]);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);

    if (count($perfisDisponiveis) > 1) {
        $_SESSION['perfis_pendentes'] = $perfisDisponiveis;
        responderLogin(200, ['multiplas_funcoes' => true, 'perfis' => $perfisDisponiveis]);
    } else {
        $_SESSION['usuario'] = [
            'id'    => $perfisDisponiveis[0]['id'],
            'nome'  => $perfisDisponiveis[0]['nome'],
            'email' => $perfisDisponiveis[0]['email'],
            'tipo'  => $perfisDisponiveis[0]['tipo'],
        ];
        responderLogin(200, ['destino' => $perfisDisponiveis[0]['destino']]);
    }
} catch (mysqli_sql_exception $erro) {
    error_log($erro->getMessage());
    responderLogin(500, ['erro' => 'Não foi possível acessar as contas. Verifique a estrutura do banco de dados.']);
}