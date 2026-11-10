<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar nova ação</title>
    <link rel="stylesheet" href="../../../public/css/main.css">
</head>
<body class="pagina-popup">
    <main class="pesquisador-pop-up-fundo">
        <section class="pesquisador-pop-up-criar-acao" role="dialog" aria-modal="true" aria-labelledby="titulo-criar-acao">
            <h1 id="titulo-criar-acao">Criar Nova Ação</h1>

            <form action="acoes_controller.php" method="POST">
                <input type="hidden" name="acao" value="criar">
                <div class="pesquisador-pop-up-criar-acao__campos">
                    <div class="pesquisador-pop-up-criar-acao__campo">
                        <label for="novo-titulo">Título da ação</label>
                        <input type="text" id="novo-titulo" name="titulo" placeholder="Digite o título da ação" required>
                    </div>

                    <div class="pesquisador-pop-up-criar-acao__campo">
                        <label for="novo-tipo">Tipo da ação</label>
                        <select id="novo-tipo" name="categoria_acao" required>
                            <option value="">Selecione o tipo</option>
                            <option value="Coordenação de eventos LOCAL">Coordenação de eventos LOCAL</option>
                            <option value="Coordenação de evento NACIONAL">Coordenação de evento NACIONAL</option>
                            <option value="Coordenação de evento REGIONAL">Coordenação de evento REGIONAL</option>
                        </select>
                    </div>

                    <div class="pesquisador-pop-up-criar-acao__campo">
                        <label for="nova-data">Data de realização</label>
                        <input type="date" id="nova-data" name="data_realizacao" required>
                    </div>

                    <div class="pesquisador-pop-up-criar-acao__campo">
                        <label for="nova-descricao">Descrição</label>
                        <textarea id="nova-descricao" name="descricao" rows="4" placeholder="Descreva a ação" required></textarea>
                    </div>
                </div>

                <div class="pesquisador-pop-up-criar-acao__acoes">
                    <button type="button" class="btn cancelar pesquisador-pop-up-btn" onclick="parent.fecharPopup()">Cancelar</button>
                    <button type="submit" class="btn pesquisador-pop-up-btn pesquisador-pop-up-criar-acao__confirmar">
                        Criar Ação
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>