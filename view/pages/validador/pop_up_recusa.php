<?php
$id = $_GET['id'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recusar ação</title>
    <link rel="stylesheet" href="../../../public/css/main.css">
    <style>
        html,
        body.pagina-popup {
            min-height: 100%;
            margin: 0;
            background: transparent !important;
        }

        body.pagina-popup {
            display: block;
        }

        .validador-pop-up-fundo {
            position: fixed;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 20px;
            background: transparent !important;
        }

        .validador-pop-up-recusa {
            background: #fff !important;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.24) !important;
        }
    </style>
</head>
<body class="pagina-popup">
    <main class="validador-pop-up-fundo">
        <section class="validador-pop-up-recusa" role="dialog" aria-modal="true"
            aria-labelledby="titulo-recusar-acao">
            <h1 id="titulo-recusar-acao">Recusar Ação</h1>

            <form action="avaliacoes.php" method="POST" target="_top">
                <input type="hidden" name="acao" value="recusar">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

                <div class="validador-pop-up-recusa__campos">
                    <div class="validador-pop-up-recusa__campo">
                        <label for="motivo-recusa">Motivo da recusa</label>
                        <textarea id="motivo-recusa" name="motivo" rows="5" maxlength="300"
                            placeholder="Explique o motivo pelo qual a ação está sendo recusada" required></textarea>
                    </div>
                </div>

                <div class="validador-pop-up-recusa__acoes">
                    <button type="button" class="btn cancelar validador-pop-up-btn" onclick="parent.fecharPopup()">Cancelar</button>
                    <button type="submit" class="btn validador-pop-up-btn validador-pop-up-recusa__confirmar">
                        Confirmar Recusa
                    </button>
                </div>
            </form>
        </section>
    </main>
</body>
</html>