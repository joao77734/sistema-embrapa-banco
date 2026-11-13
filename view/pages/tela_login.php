<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
  <style>
    .modal-perfil-fundo {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); display: none; place-items: center; z-index: 999;
    }
    .modal-perfil-conteudo {
        background: #fff; padding: 30px; border-radius: 12px; width: min(100%, 400px); text-align: center;
    }
    .modal-perfil-conteudo h3 { margin-bottom: 10px; color: #333; }
    .modal-perfil-conteudo p { margin-bottom: 20px; color: #666; font-size: 14px; }
    .botoes-perfis { display: flex; flex-direction: column; gap: 10px; }
    .btn-perfil { padding: 12px; background: #2E7D32; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
    .btn-perfil:hover { background: #1B5E20; }
  </style>
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

<!-- Modal de Seleção de Múltiplas Funções -->
<div id="modalSelecaoPerfil" class="modal-perfil-fundo">
    <div class="modal-perfil-conteudo">
        <h3>Selecione o perfil</h3>
        <p>Este e-mail possui mais de uma função cadastrada. Escolha com qual deseja entrar:</p>
        <div id="listaBotoesPerfis" class="botoes-perfis"></div>
    </div>
</div>

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
      
      if (dados.multiplas_funcoes) {
          exibirModalPerfis(dados.perfis);
      } else {
          window.location.href = dados.destino;
      }
    } catch (falha) {
      erro.textContent = falha.message || 'Falha ao conectar com o servidor.';
      erro.hidden = false;
    } finally {
      botao.disabled = false;
    }
  });

  function exibirModalPerfis(perfis) {
      const container = document.getElementById('listaBotoesPerfis');
      container.replaceChildren();

      perfis.forEach(perfil => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn-perfil';
          btn.textContent = `Entrar como ${perfil.tipo}`;
          btn.addEventListener('click', async () => {
              try {
                  const resp = await fetch('selecionar_perfil.php', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ tipo: perfil.tipo })
                  });
                  const resDados = await resp.json();
                  if (!resp.ok) throw new Error(resDados.erro || 'Erro ao selecionar perfil.');
                  window.location.href = resDados.destino;
              } catch (e) {
                  alert(e.message);
              }
          });
          container.append(btn);
      });

      document.getElementById('modalSelecaoPerfil').style.display = 'grid';
  }
</script>

</body>
</html>