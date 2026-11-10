<?php
require_once __DIR__ . '/../../../config/database.php';

session_start();
$usuario = $_SESSION['usuario'] ?? [];

if (($usuario['tipo'] ?? null) !== 'Pesquisador') {
    header('Location: ../tela_login.html', true, 303);
    exit;
}

$idPesquisador = filter_var($usuario['id'] ?? null, FILTER_VALIDATE_INT);

// Contas demonstrativas usam id 0; associe-as a um perfil existente pelo e-mail.
if (!$idPesquisador || $idPesquisador < 1) {
    $email = (string) ($usuario['email'] ?? '');
    $stmt = mysqli_prepare(
        $conexao,
        'SELECT id FROM Pesquisador WHERE email = ? AND COALESCE(status, 1) = 1 LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $perfil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $idPesquisador = $perfil ? (int) $perfil['id'] : 0;
}

if ($idPesquisador < 1) {
    http_response_code(403);
    exit('Esta conta não possui um cadastro ativo de Pesquisador vinculado.');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    return;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || ($_POST['acao'] ?? '') !== 'criar') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit('Método ou operação não permitida.');
}

$titulo = trim((string) ($_POST['titulo_acao'] ?? ''));
$descricao = trim((string) ($_POST['descricao_acao'] ?? ''));
$dataRealizacao = (string) ($_POST['data_realizacao'] ?? '');
$categorias = [
    'local' => 'Coordenação de eventos LOCAL',
    'nacional' => 'Coordenação de evento NACIONAL',
    'regional' => 'Coordenação de evento REGIONAL',
];
$categoria = $categorias[(string) ($_POST['tipo_acao'] ?? '')] ?? null;
$dataValida = DateTimeImmutable::createFromFormat('!Y-m-d', $dataRealizacao);
$errosData = DateTimeImmutable::getLastErrors();
$dataValida = $dataValida
    && $dataValida->format('Y-m-d') === $dataRealizacao
    && ($errosData === false || $errosData['warning_count'] === 0 && $errosData['error_count'] === 0);

if ($titulo === '' || mb_strlen($titulo) > 100
    || $descricao === '' || mb_strlen($descricao) > 300
    || $categoria === null || !$dataValida) {
    header('Location: tela_pesquisador.php?erro=campos', true, 303);
    exit;
}

try {
    $stmt = mysqli_prepare(
        $conexao,
        "INSERT INTO Acao_pesquisador
            (titulo, descricao, categoria_acao, data_realizacao, status, id_pesquisador)
         VALUES (?, ?, ?, ?, 'Pendente', ?)"
    );
    mysqli_stmt_bind_param($stmt, 'ssssi', $titulo, $descricao, $categoria, $dataRealizacao, $idPesquisador);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: tela_pesquisador.php?sucesso=1', true, 303);
    exit;
} catch (mysqli_sql_exception $erro) {
    error_log($erro->getMessage());
    header('Location: tela_pesquisador.php?erro=banco', true, 303);
    exit;
}