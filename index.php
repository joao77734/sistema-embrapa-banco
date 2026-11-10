<?php
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($base === '.') {
    $base = '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema Embrapa - Início</title>
    <style>
        :root { color-scheme: light; font-family: Arial, sans-serif; color: #18332b; background: #f3f7f4; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; box-sizing: border-box; }
        main { width: min(100%, 680px); padding: 36px; box-sizing: border-box; background: #fff; border-radius: 16px; box-shadow: 0 12px 35px #173c2b18; }
        h1 { margin-top: 0; color: #176b43; }
        p { line-height: 1.6; }
        nav { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; margin: 24px 0; }
        nav a { padding: 15px 18px; border-radius: 9px; background: #176b43; color: white; text-decoration: none; font-weight: 700; }
        nav a:hover, nav a:focus { background: #105335; }
        .nota { padding: 14px 16px; border-left: 4px solid #e4a72b; background: #fff8e8; }
        small { color: #53665c; }
    </style>
</head>
<body>
    <main>
        <h1>Sistema Embrapa</h1>
        <p>O PHP está sendo servido pelo Apache. Escolha uma tela para abrir no navegador:</p>
        <nav aria-label="Telas do sistema">
            <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/view/pages/tela_login.html">Tela de login</a>
            <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/view/pages/administrador/tela_adminstrador.html">Painel do administrador</a>
            <a href="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>/view/pages/comite/tela_comite.php">Painel do comitê</a>
        </nav>
        <p class="nota"><strong>Para usar o painel do administrador:</strong> inicie Apache e MySQL no XAMPP e importe banco_embrapa.sql pelo phpMyAdmin.</p>
        <small>Ambiente local de desenvolvimento. A tela de login ainda não valida credenciais.</small>
    </main>
</body>
</html>