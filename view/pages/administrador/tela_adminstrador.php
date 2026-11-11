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
                                <label for="senha">Senha</label>
                                <input type="password" id="senha"
                                    placeholder="Digite a senha">
                            </div>

                            <div class="campo" style="grid-column: 1 / -1;">
                                <label>Funções / Cargos do Usuário</label>
                                <div style="display: flex; gap: 20px; margin-top: 8px;">
                                    <label><input type="checkbox" name="funcao" value="Pesquisador" id="checkPesquisador"> Pesquisador</label>
                                    <label><input type="checkbox" name="funcao" value="Validador" id="checkValidador"> Validador</label>
                                    <label><input type="checkbox" name="funcao" value="Comitê"> Comitê</label>
                                </div>
                            </div>

                            <div class="campo" id="blocoSetor" style="display: none;">
                                <label for="setor">Setor</label>
                                <select id="setor">
                                    <option value="">Selecione o setor</option>
                                </select>
                            </div>

                        </div>

                        <button type="submit" class="btn salvar" style="margin-top: 15px;">
                            Cadastrar Usuário
                        </button>

                    </form>

                </div>

            </details>

            <details class="funcao" id="areaEdicao" hidden>

                <summary>Editar Usuário</summary>

                <div class="conteudo-funcao">

                    <form id="formEditar">
                        <input type="hidden" id="editarId">
                        <input type="hidden" id="editarTipoOriginal">

                        <div class="campos">

                            <div class="campo">
                                <label for="editarNome">Nome completo</label>
                                <input type="text" id="editarNome" required>
                            </div>

                            <div class="campo">
                                <label for="editarMatricula">Matrícula</label>
                                <input type="text" id="editarMatricula" required>
                            </div>

                            <div class="campo">
                                <label for="editarEmail">E-mail</label>
                                <input type="email" id="editarEmail" required>
                            </div>

                            <div class="campo">
                                <label for="editarSetor">Setor</label>
                                <select id="editarSetor">
                                    <option value="">Selecione o setor</option>
                                </select>
                            </div>

                        </div>

                        <div class="acoes-formulario" style="display: flex; gap: 10px; margin-top: 15px;">
                            <button type="submit" class="btn salvar">Salvar Alterações</button>
                            <button type="button" class="btn cancelar" id="cancelarEdicao">Cancelar</button>
                        </div>

                    </form>

                </div>

            </details>

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
                                    <th>Tipo/Função</th>
                                    <th>Situação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody id="listaUsuarios">
                            </tbody>

                        </table>

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
    
    const checkValidador = document.getElementById('checkValidador');
    const blocoSetor = document.getElementById('blocoSetor');

    checkValidador.addEventListener('change', () => {
        if (checkValidador.checked) {
            blocoSetor.style.display = 'block';
            setorCadastro.required = true;
        } else {
            blocoSetor.style.display = 'none';
            setorCadastro.required = false;
            setorCadastro.value = '';
        }
    });

    let setores = [];
    let usuarioPendente = null;
    let botaoOrigemModal = null;
    let dadosOriginaisEdicao = {};

    async function requisitarApi(opcoes = {}) {
        let resposta;
        try {
            resposta = await fetch(apiUsuarios, {
                ...opcoes,
                headers: { 'Content-Type': 'application/json', ...(opcoes.headers || {}) }
            });
        } catch (erro) {
            throw new Error('Não foi possível acessar usuarios.php. Verifique se o Apache está rodando.');
        }

        let dados;
        try {
            dados = await resposta.json();
        } catch (erro) {
            throw new Error('O endpoint usuarios.php não retornou JSON.');
        }
        if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível concluir a operação.');
        return dados;
    }

    function preencherSetores(select, selecionado = '') {
        select.replaceChildren(new Option('Selecione o setor', ''));
        
        const setoresUnicos = [];
        const nomesVistos = new Set();

        setores.forEach((setor) => {
            const nomeFormatado = setor.nome.trim();
            const nomeChave = nomeFormatado.toUpperCase();
            
            if (!nomesVistos.has(nomeChave)) {
                nomesVistos.add(nomeChave);
                setoresUnicos.push({ ...setor, nome: nomeFormatado });
            }
        });

        setoresUnicos.forEach((setor) => {
            const opcao = new Option(setor.nome, setor.id);
            opcao.selected = String(setor.id) === String(selecionado);
            select.add(opcao);
        });
    }

    function criarBotao(texto, classe, acao, estiloExtra = '') {
        const botao = document.createElement('button');
        botao.type = 'button';
        botao.className = `btn ${classe}`;
        botao.textContent = texto;
        if (estiloExtra) {
            botao.style.cssText += estiloExtra;
        }
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
            [usuario.nome, usuario.matricula, usuario.email, usuario.tipo,
                ativo ? 'Ativo' : 'Inativo'].forEach((valor) => {
                const celula = document.createElement('td');
                celula.textContent = valor;
                linha.append(celula);
            });

            const celulaAcoes = document.createElement('td');
            const acoes = document.createElement('div');
            acoes.className = 'acoes';
            acoes.append(criarBotao('Editar', 'editar', () => abrirEdicao(usuario)));
            
            // Botão de ativar (verde) ou desativar (vermelho) na tabela
            const estiloBotaoStatus = !ativo 
                ? 'background-color: #2E7D32; color: #fff;' 
                : 'background-color: #C62828; color: #fff;';
            const textoBotaoStatus = ativo ? 'Desativar' : 'Ativar';
            
            acoes.append(criarBotao(textoBotaoStatus, 'excluir', (evento) => {
                alterarStatus(usuario, evento.currentTarget);
            }, estiloBotaoStatus));

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
        document.getElementById('editarTipoOriginal').value = usuario.tipo;
        preencherSetores(setorEdicao, usuario.id_setor);

        dadosOriginaisEdicao = {
            nome: usuario.nome.trim(),
            matricula: usuario.matricula.trim(),
            email: usuario.email.trim(),
            id_setor: String(usuario.id_setor || '')
        };

        areaEdicao.hidden = false;
        areaEdicao.open = true;
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

        // Vermelho (#C62828) para desativação e Verde (#2E7D32) para reativação
        if (ativo) {
            botaoConfirmarDesativacao.style.backgroundColor = '#C62828';
            botaoConfirmarDesativacao.style.color = '#fff';
        } else {
            botaoConfirmarDesativacao.style.backgroundColor = '#2E7D32';
            botaoConfirmarDesativacao.style.color = '#fff';
        }

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
        
        const checkboxes = document.querySelectorAll('input[name="funcao"]:checked');
        const funcoesSelecionadas = Array.from(checkboxes).map(cb => cb.value);

        if (funcoesSelecionadas.length === 0) {
            alert('Selecione pelo menos uma função/cargo.');
            return;
        }

        const corpo = {
            nome: document.getElementById('nome').value.trim(),
            matricula: document.getElementById('matricula').value.trim(),
            email: document.getElementById('email').value.trim(),
            senha: document.getElementById('senha').value.trim(),
            funcoes: funcoesSelecionadas,
            id_setor: checkValidador.checked ? setorCadastro.value : null
        };

        try {
            await requisitarApi({ method: 'POST', body: JSON.stringify(corpo) });
            formulario.reset();
            blocoSetor.style.display = 'none';
            await carregarUsuarios();
            alert('Usuário cadastrado com sucesso.');
        } catch (erro) {
            alert(erro.message);
        }
    });

    document.getElementById('formEditar').addEventListener('submit', async (evento) => {
        evento.preventDefault();
        const usuarioId = document.getElementById('editarId').value;
        const tipoOriginal = document.getElementById('editarTipoOriginal').value;
        
        const novosDados = {
            nome: document.getElementById('editarNome').value.trim(),
            matricula: document.getElementById('editarMatricula').value.trim(),
            email: document.getElementById('editarEmail').value.trim(),
            id_setor: String(setorEdicao.value || '')
        };

        const houveAlteracao = 
            novosDados.nome !== dadosOriginaisEdicao.nome ||
            novosDados.matricula !== dadosOriginaisEdicao.matricula ||
            novosDados.email !== dadosOriginaisEdicao.email ||
            novosDados.id_setor !== dadosOriginaisEdicao.id_setor;

        if (!houveAlteracao) {
            alert('Nenhuma alteração foi realizada.');
            return;
        }

        try {
            await requisitarApi({
                method: 'PUT',
                body: JSON.stringify({
                    id: usuarioId,
                    tipo: tipoOriginal,
                    ...novosDados
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