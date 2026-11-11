<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tela de Login</title>
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

      <p id="erroLogin" role="alert" aria-live="polite" hidden></p>
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
