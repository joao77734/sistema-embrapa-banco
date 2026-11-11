<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar exclusão</title>
    <link rel="stylesheet" href="../../../public/css/main.css">
</head>
<body class="pagina-popup">
    <main class="popup-fundo">
        <section class="popup-exclusao" role="alertdialog" aria-modal="true"
            aria-labelledby="titulo-exclusao" aria-describedby="aviso-exclusao">
            <div class="popup-exclusao__aviso" aria-hidden="true">!</div>
            <h1 id="titulo-exclusao">Excluir usuário?</h1>
            <p id="aviso-exclusao">
               Tem certeza que deseja desativar este empregado?
            </p>
            <div class="popup-exclusao__acoes">
                <button type="button" class="btn cancelar">Cancelar</button>
                <button type="button" class="btn popup-exclusao__confirmar">
                    Confirmar desativação
                </button>
            </div>
        </section>
    </main>
</body>
</html>