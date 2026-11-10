
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Administrador - Embrapa</title>

    <link rel="stylesheet" href="../../../public/css/main.css">
</head>

<body>

<header class="header">
    <div class="container">

        <a href="#" class="logo">
            <span class="logo__nome">Embrapa</span>
        </a>

        <nav aria-label="Principal">
            <ul class="abas">
                <li><span>Administrador</span></li>
                <li><a href="../auth.php?acao=sair">Sair</a></li>
            </ul>
        </nav>

    </div>
</header>

<main>

    <div class="container">

        <section class="administrador">

            <h1>Painel do Administrador</h1>

            <p class="descricao">
                Gerencie os usuários cadastrados no sistema. A senha inicial dos novos usuários é 1234.
            </p>

            <!-- CADASTRAR NOVO USUÁRIO -->
            <details class="funcao">

                <summary>Cadastrar Novo Usuário</summary>

                <div class="conteudo-funcao">

                    <form id="formCadastro">

                        <div class="campos">

                            <div class="campo">
                                <label for="nome">Nome completo</label>
                                <input type="text" id="nome"
                                    placeholder="Digite o nome completo"
                                    required>
                            </div>

                            <div class="campo">
                                <label for="matricula">Matrícula</label>
                                <input type="text" id="matricula"
                                    placeholder="Digite a matrícula"
                                    required>
                            </div>

                            <div class="campo">
                                <label for="email">E-mail</label>
                                <input type="email" id="email"
                                    placeholder="Digite o e-mail"
                                    required>
                            </div>

                            <div class="campo">
                                <label for="cargo">Cargo</label>
                                <input type="text" id="cargo"
                                    placeholder="Até 13 caracteres"
                                    required>
                            </div>

                            <div class="campo">
                                <label for="tipo">Tipo de usuário</label>
                                <select id="tipo" required>
                                    <option value="">Selecione</option>
                                    <option value="Pesquisador">Pesquisador</option>
                                    <option value="Validador">Validador</option>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="setor">Setor</label>
                                <select id="setor" required>
                                    <option value="">Carregando setores...</option>
                                </select>
                            </div>

                        </div>

                        <button type="submit" class="btn salvar">
                            Cadastrar Usuário
                        </button>

                    </form>

                </div>

            </details>

            <!-- LISTAR E GERENCIAR USUÁRIOS -->
            <details class="funcao" open>

                <summary>Listar Todos os Usuários</summary>

                <div class="conteudo-funcao">

                    <div class="tabela-responsiva">

                        <table>

                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Matrícula</th>
                                    <th>E-mail</th>
                                    <th>Cargo</th>
                                    <th>Situação</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody id="listaUsuarios">
                            </tbody>

                        </table>

                    </div>



                        </form>

                    </div>

                </div>

            </details>

        </section>

    </div>

</main>

<footer class="footer">

    <div class="container">
        <span>© Embrapa – Trabalho do Mauricio</span>
        <a href="#">Política de privacidade</a>
    </div>

</footer>

<div id="modalDesativacao" class="popup-fundo admin-popup" style="display: none;" hidden>
    <section class="popup-exclusao" role="alertdialog" aria-modal="true"
        aria-labelledby="tituloModalDesativacao" aria-describedby="textoModalDesativacao">
        <div class="popup-exclusao__aviso" aria-hidden="true">!</div>
        <h1 id="tituloModalDesativacao">Desativar usuário?</h1>
        <p id="textoModalDesativacao">Tem certeza que deseja desativar este usuário?</p>
        <div class="popup-exclusao__acoes">
            <button type="button" class="btn cancelar" id="cancelarDesativacao">Cancelar</button>
            <button type="button" class="btn popup-exclusao__confirmar" id="confirmarDesativacao">
                Confirmar desativação
            </button>
        </div>
    </section>
</div>

<div id="modalEdicaoSucesso" class="popup-fundo admin-popup" style="display: none;" hidden>
    <section class="popup-edicao" role="status" aria-labelledby="tituloModalEdicao">
        <div class="popup-edicao__aviso" aria-hidden="true">!</div>
        <h1 id="tituloModalEdicao">Usuário editado</h1>
        <p>As informações do usuário foram atualizadas com sucesso.</p>
        <div class="popup-edicao__acoes">
            <button type="button" class="btn popup-edicao__fechar" id="fecharModalEdicao">Fechar</button>
        </div>
    </section>
</div>

<script>
    const apiUsuarios = 'usuarios.php';
    const listaUsuarios = document.getElementById('listaUsuarios');
    const setorCadastro = document.getElementById('setor');
    const setorEdicao = document.getElementById('editarSetor');
    const areaEdicao = document.getElementById('areaEdicao');
    const modalDesativacao = document.getElementById('modalDesativacao');
    const modalEdicaoSucesso = document.getElementById('modalEdicaoSucesso');
    const botaoConfirmarDesativacao = document.getElementById('confirmarDesativacao');
    let setores = [];
    let usuarioPendente = null;
    let botaoOrigemModal = null;

    async function requisitarApi(opcoes = {}) {
        let resposta;
        try {
            resposta = await fetch(apiUsuarios, {
                ...opcoes,
                headers: { 'Content-Type': 'application/json', ...(opcoes.headers || {}) }
            });
        } catch (erro) {
            const orientacao = window.location.protocol === 'file:'
                ? 'A tela foi aberta como arquivo. Abra pelo Apache usando http://localhost/sistema-embrapa-banco/view/pages/administrador/tela_adminstrador.html.'
                : 'Confira se o Apache do XAMPP está iniciado e se a tela e o endpoint estão no mesmo servidor.';
            throw new Error(`Não foi possível acessar usuarios.php. ${orientacao}`);
        }

        let dados;
        try {
            dados = await resposta.json();
        } catch (erro) {
            throw new Error('O endpoint usuarios.php não retornou JSON. Verifique se o Apache está executando PHP e se o caminho está correto.');
        }
        if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível concluir a operação.');
        return dados;
    }

    function preencherSetores(select, selecionado = '') {
        select.replaceChildren(new Option('Selecione o setor', ''));
        setores.forEach((setor) => {
            const opcao = new Option(setor.nome, setor.id);
            opcao.selected = String(setor.id) === String(selecionado);
            select.add(opcao);
        });
    }

    function criarBotao(texto, classe, acao) {
        const botao = document.createElement('button');
        botao.type = 'button';
        botao.className = `btn ${classe}`;
        botao.textContent = texto;
        botao.addEventListener('click', acao);
        return botao;
    }

    function renderizarUsuarios(usuarios) {
        listaUsuarios.replaceChildren();
        if (!usuarios.length) {
            const linha = document.createElement('tr');
            const celula = document.createElement('td');
            celula.colSpan = 6;
            celula.textContent = 'Nenhum usuário cadastrado.';
            linha.append(celula);
            listaUsuarios.append(linha);
            return;
        }

        usuarios.forEach((usuario) => {
            const linha = document.createElement('tr');
            linha.dataset.id = usuario.id;
            linha.dataset.tipo = usuario.tipo;
            const ativo = Number(usuario.status) === 1;
            [usuario.nome, usuario.matricula, usuario.email, usuario.funcao,
                ativo ? 'Ativo' : 'Inativo'].forEach((valor) => {
                const celula = document.createElement('td');
                celula.textContent = valor;
                linha.append(celula);
            });

            const celulaAcoes = document.createElement('td');
            const acoes = document.createElement('div');
            acoes.className = 'acoes';
            acoes.append(criarBotao('Editar', 'editar', () => abrirEdicao(usuario)));
            acoes.append(criarBotao(ativo ? 'Desativar' : 'Ativar', 'excluir', (evento) => {
                alterarStatus(usuario, evento.currentTarget);
            }));
            celulaAcoes.append(acoes);
            linha.append(celulaAcoes);
            listaUsuarios.append(linha);
        });
    }

    async function carregarUsuarios() {
        try {
            const dados = await requisitarApi();
            setores = dados.setores;
            preencherSetores(setorCadastro);
            preencherSetores(setorEdicao);
            renderizarUsuarios(dados.usuarios);
        } catch (erro) {
            alert(erro.message);
        }
    }

    function abrirEdicao(usuario) {
        document.getElementById('editarId').value = usuario.id;
        document.getElementById('editarNome').value = usuario.nome;
        document.getElementById('editarMatricula').value = usuario.matricula;
        document.getElementById('editarEmail').value = usuario.email;
        document.getElementById('editarCargo').value = usuario.funcao;
        preencherSetores(setorEdicao, usuario.id_setor);
        areaEdicao.hidden = false;
        areaEdicao.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function alterarStatus(usuario, botaoOrigem) {
        const ativo = Number(usuario.status) === 1;
        usuarioPendente = usuario;
        botaoOrigemModal = botaoOrigem;
        document.getElementById('tituloModalDesativacao').textContent = ativo
            ? 'Desativar usuário?'
            : 'Reativar usuário?';
        document.getElementById('textoModalDesativacao').textContent = ativo
            ? `Tem certeza que deseja desativar ${usuario.nome}?`
            : `Tem certeza que deseja reativar ${usuario.nome}?`;
        botaoConfirmarDesativacao.textContent = ativo
            ? 'Confirmar desativação'
            : 'Confirmar reativação';
        modalDesativacao.hidden = false;
        modalDesativacao.style.display = 'grid';
        botaoConfirmarDesativacao.focus();
    }

    function fecharModalDesativacao() {
        modalDesativacao.hidden = true;
        modalDesativacao.style.display = 'none';
        usuarioPendente = null;
        if (botaoOrigemModal?.isConnected) botaoOrigemModal.focus();
        botaoOrigemModal = null;
    }

    document.getElementById('cancelarDesativacao').addEventListener('click', fecharModalDesativacao);

    botaoConfirmarDesativacao.addEventListener('click', async () => {
        if (!usuarioPendente) return;
        const usuario = usuarioPendente;
        const ativo = Number(usuario.status) === 1;
        botaoConfirmarDesativacao.disabled = true;
        try {
            await requisitarApi({
                method: 'PATCH',
                body: JSON.stringify({ id: usuario.id, tipo: usuario.tipo, status: ativo ? 0 : 1 })
            });
            fecharModalDesativacao();
            await carregarUsuarios();
        } catch (erro) {
            alert(erro.message);
        } finally {
            botaoConfirmarDesativacao.disabled = false;
        }
    });

    document.getElementById('formCadastro').addEventListener('submit', async (evento) => {
        evento.preventDefault();
        const formulario = evento.currentTarget;
        const corpo = {
            nome: document.getElementById('nome').value.trim(),
            matricula: document.getElementById('matricula').value.trim(),
            email: document.getElementById('email').value.trim(),
            funcao: document.getElementById('cargo').value.trim(),
            tipo: document.getElementById('tipo').value,
            id_setor: setorCadastro.value
        };
        try {
            await requisitarApi({ method: 'POST', body: JSON.stringify(corpo) });
            formulario.reset();
            await carregarUsuarios();
            alert('Usuário cadastrado com sucesso.');
        } catch (erro) {
            alert(erro.message);
        }
    });

    document.getElementById('formEditar').addEventListener('submit', async (evento) => {
        evento.preventDefault();
        const usuarioId = document.getElementById('editarId').value;
        const linhaUsuario = [...listaUsuarios.rows].find((linha) => linha.dataset.id === usuarioId);
        if (!linhaUsuario) return;
        try {
            await requisitarApi({
                method: 'PUT',
                body: JSON.stringify({
                    id: usuarioId,
                    tipo: linhaUsuario.dataset.tipo,
                    nome: document.getElementById('editarNome').value.trim(),
                    matricula: document.getElementById('editarMatricula').value.trim(),
                    email: document.getElementById('editarEmail').value.trim(),
                    funcao: document.getElementById('editarCargo').value.trim(),
                    id_setor: setorEdicao.value
                })
            });
            areaEdicao.hidden = true;
            await carregarUsuarios();
            modalEdicaoSucesso.hidden = false;
            modalEdicaoSucesso.style.display = 'grid';
            document.getElementById('fecharModalEdicao').focus();
        } catch (erro) {
            alert(erro.message);
        }
    });

    document.getElementById('fecharModalEdicao').addEventListener('click', () => {
        modalEdicaoSucesso.hidden = true;
        modalEdicaoSucesso.style.display = 'none';
    });

    document.getElementById('cancelarEdicao').addEventListener('click', () => {
        areaEdicao.hidden = true;
    });

    carregarUsuarios();
</script>

</body>
</html>
