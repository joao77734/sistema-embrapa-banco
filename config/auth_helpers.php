<?php
function garantirColunaSenha(mysqli $conexao, string $tabela): void
{
    $coluna = mysqli_query($conexao, "SHOW COLUMNS FROM `{$tabela}` LIKE 'senha_hash'");
    $existe = mysqli_num_rows($coluna) > 0;
    mysqli_free_result($coluna);

    if (!$existe) {
        mysqli_query($conexao, "ALTER TABLE `{$tabela}` ADD COLUMN senha_hash VARCHAR(255) NULL");
    }

    $senhaPadraoHash = password_hash('1234', PASSWORD_DEFAULT);
    $stmt = mysqli_prepare(
        $conexao,
        "UPDATE `{$tabela}` SET senha_hash = ? WHERE senha_hash IS NULL OR senha_hash = ''"
    );
    mysqli_stmt_bind_param($stmt, 's', $senhaPadraoHash);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function garantirColunasDeSenha(mysqli $conexao): void
{
    foreach (['Pesquisador', 'Validador', 'Administrador'] as $tabela) {
        garantirColunaSenha($conexao, $tabela);
    }
}

function garantirContasDemonstracao(mysqli $conexao): void
{
    $tabelasLegadas = mysqli_query(
        $conexao,
        "SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'Conta_demo'"
    );
    $existeLegada = (int) mysqli_fetch_assoc($tabelasLegadas)['total'] > 0;
    mysqli_free_result($tabelasLegadas);

    $tabelasNovas = mysqli_query(
        $conexao,
        "SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'Usuarios_conta'"
    );
    $existeNova = (int) mysqli_fetch_assoc($tabelasNovas)['total'] > 0;
    mysqli_free_result($tabelasNovas);

    if ($existeLegada && !$existeNova) {
        mysqli_query($conexao, 'RENAME TABLE Conta_demo TO Usuarios_conta');
    }

    mysqli_query(
        $conexao,
        "CREATE TABLE IF NOT EXISTS Usuarios_conta (
            email VARCHAR(100) PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            tipo VARCHAR(20) NOT NULL,
            senha_hash VARCHAR(255) NOT NULL,
            status BOOLEAN NOT NULL DEFAULT TRUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $contas = [
        ['pesquisador@embrapa.com.br', 'Conta Pesquisador', 'Pesquisador'],
        ['administrador@embrapa.com.br', 'Conta Administrador', 'Administrador'],
        ['comite@embrapa.com.br', 'Conta Comitê', 'Comitê'],
        ['validador@embrapa.com.br', 'Conta Validador', 'Validador'],
    ];
    $senhaHash = password_hash('1234', PASSWORD_DEFAULT);

    foreach ($contas as [$email, $nome, $tipo]) {
        $stmt = mysqli_prepare(
            $conexao,
            'INSERT IGNORE INTO Usuarios_conta (email, nome, tipo, senha_hash) VALUES (?, ?, ?, ?)'
        );
        mysqli_stmt_bind_param($stmt, 'ssss', $email, $nome, $tipo, $senhaHash);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
