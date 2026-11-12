<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redireciona para o painel correspondente
if (isset($_SESSION['usuario'])) {
    $tipo = $_SESSION['usuario']['tipo'] ?? '';
    if ($tipo === 'Administrador') {
        header('Location: administrador/tela_adminstrador.php');
        exit;
    } elseif ($tipo === 'Pesquisador') {
        header('Location: pesquisador/tela_pesquisador.php');
        exit;
    } elseif ($tipo === 'Validador') {
        header('Location: validador/tela_validador.php');
        exit;
    } elseif ($tipo === 'Comitê') {
        header('Location: comite/tela_comite.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Embrapa</title>
  <link rel="stylesheet" href="../../public/css/main.css">
  <link rel="stylesheet" href="../../modelos/base.css">
</head>
<body>

<header class="header">
  <div class="container">
    <a href="#" class="logo" aria-label="Página inicial da Embrapa">
      <span class="logo__nome">Embrapa</span>
    </a>
  </div>
</header>

<main class="login-main">
    
  <div class="login-card">
    <h2>Entre no Sistema</h2>
    <p>Insira suas informações para entrar</p>
    
    <form action="auth.php" method="POST" class="login-form" id="formLogin">
      <div class="input-group">
        <label for="usuario">E-mail</label>
        <input type="email" id="usuario" name="usuario" placeholder="Digite o seu email" autocomplete="username" required>
      </div>

      <div class="input-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required>
      </div>

      <p id="erroLogin" role="alert" aria-live="polite" style="color: #C62828; background: #FCE5E5; padding: 10px; border-radius: 6px; font-size: 14px; text-align: center; margin-bottom: 15px;" hidden></p>
      
      <button type="submit" class="btn-login">Entrar</button>
    </form>
  </div>

</main>

<footer class="footer">
  <div class="container">
    <span>© Embrapa – Trabalho do Mauricio</span>
    <a href="#">Política de privacidade</a>
  </div>
</footer>

<script>
  document.getElementById('formLogin').addEventListener('submit', async (evento) => {
    evento.preventDefault();
    const formulario = evento.currentTarget;
    const erro = document.getElementById('erroLogin');
    const botao = formulario.querySelector('button[type="submit"]');
    erro.hidden = true;
    botao.disabled = true;

    try {
      const resposta = await fetch('auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: document.getElementById('usuario').value.trim(),
          senha: document.getElementById('senha').value
        })
      });
      const dados = await resposta.json();
      
      if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível entrar.');
      
      window.location.href = dados.destino;
    } catch (falha) {
      erro.textContent = falha.message || 'Falha ao conectar com o servidor.';
      erro.hidden = false;
    } finally {
      botao.disabled = false;
    }
  });
</script>

</body>
</html>