<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);
$tipoEscolhido = $dados['tipo'] ?? '';

if (!isset($_SESSION['perfis_pendentes']) || !is_array($_SESSION['perfis_pendentes'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'Nenhuma sessão pendente de seleção.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$perfilSelecionado = null;
foreach ($_SESSION['perfis_pendentes'] as $perfil) {
    if ($perfil['tipo'] === $tipoEscolhido) {
        $perfilSelecionado = $perfil;
        break;
    }
}

if ($perfilSelecionado) {
    $_SESSION['usuario'] = [
        'id' => $perfilSelecionado['id'],
        'nome' => $perfilSelecionado['nome'],
        'email' => $perfilSelecionado['email'],
        'tipo' => $perfilSelecionado['tipo'],
    ];
    unset($_SESSION['perfis_pendentes']);
    echo json_encode(['destino' => $perfilSelecionado['destino']], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(400);
    echo json_encode(['erro' => 'Função inválida selecionada.'], JSON_UNESCAPED_UNICODE);
}
