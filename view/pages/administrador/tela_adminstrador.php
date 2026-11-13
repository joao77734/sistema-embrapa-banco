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
                                <input type="text" id="nome" placeholder="Digite o nome completo" required>
                            </div>
                            <div class="campo">
                                <label for="matricula">Matrícula</label>
                                <input type="text" id="matricula" placeholder="Digite a matrícula" required>
                            </div>
                            <div class="campo">
                                <label for="email">E-mail</label>
                                <input type="email" id="email" placeholder="Digite o e-mail" required>
                            </div>
                            <div class="campo">
                                <label for="senha">Senha</label>
                                <input type="password" id="senha" placeholder="Digite a senha">
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
                        <button type="submit" class="btn salvar" style="margin-top: 15px;">Cadastrar Usuário</button>
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
                            <div class="campo" id="editarBlocoSetor" style="display: none;">
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
    <section class="popup-exclusao" role="alertdialog" aria-modal="true">
        <div class="popup-exclusao__aviso" aria-hidden="true">!</div>
        <h1 id="tituloModalDesativacao">Desativar usuário?</h1>
        <p id="textoModalDesativacao">Tem certeza que deseja desativar este usuário?</p>
        <div class="popup-exclusao__acoes">
            <button type="button" class="btn cancelar" id="cancelarDesativacao">Cancelar</button>
            <button type="button" class="btn popup-exclusao__confirmar" id="confirmarDesativacao">Confirmar desativação</button>
        </div>
    </section>
</div>

<div id="modalEdicaoSucesso" class="popup-fundo admin-popup" style="display: none;" hidden>
    <section class="popup-edicao" role="status">
        <div class="popup-edicao__aviso" aria-hidden="true">!</div>
        <h1>Usuário editado</h1>
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
    const editarBlocoSetor = document.getElementById('editarBlocoSetor');

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
        let resposta = await fetch(apiUsuarios, {
            ...opcoes,
            headers: { 'Content-Type': 'application/json', ...(opcoes.headers || {}) }
        });
        let dados = await resposta.json();
        if (!resposta.ok) throw new Error(dados.erro || 'Erro na operação.');
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

    function criarBotao(texto, classe, acao, estiloExtra = '') {
        const botao = document.createElement('button');
        botao.type = 'button';
        botao.className = `btn ${classe}`;
        botao.textContent = texto;
        if (estiloExtra) botao.style.cssText += estiloExtra;
        botao.addEventListener('click', acao);
        return botao;
    }

    function renderizarUsuarios(usuarios) {
        listaUsuarios.replaceChildren();
        if (!usuarios.length) {
            const linha = document.createElement('tr');
            linha.innerHTML = '<td colspan="6" style="text-align: center;">Nenhum usuário cadastrado.</td>';
            listaUsuarios.append(linha);
            return;
        }

        usuarios.forEach((usuario) => {
            const linha = document.createElement('tr');
            const ativo = Number(usuario.status) === 1;
            
            // Pega todas as funções cadastradas (ex: "Pesquisador, Validador, Comitê") e remove espaços/vírgulas sobrando nas pontas
            let funcoesExibicao = '';
            if (usuario.funcao && usuario.funcao.trim() !== '') {
                // Trata para garantir que se houver múltiplas funções, elas fiquem legíveis e sem vírgulas extras
                funcoesExibicao = usuario.funcao.split(',')
                    .map(f => f.trim())
                    .filter(f => f !== '')
                    .join(', ');
            } else {
                funcoesExibicao = usuario.tipo || 'Pesquisador';
            }

            // Insere os dados na linha da tabela (Nome, Matrícula, E-mail, Tipo/Função, Situação)
            [usuario.nome, usuario.matricula, usuario.email, funcoesExibicao, ativo ? 'Ativo' : 'Inativo'].forEach((valor) => {
                const celula = document.createElement('td');
                celula.textContent = valor;
                linha.append(celula);
            });

            // Coluna de Ações (Editar e Desativar/Ativar)
            const celulaAcoes = document.createElement('td');
            const acoes = document.createElement('div');
            acoes.className = 'acoes';
            acoes.append(criarBotao('Editar', 'editar', () => abrirEdicao(usuario)));
            
            const estiloBotaoStatus = !ativo ? 'background-color: #2E7D32; color: #fff;' : 'background-color: #C62828; color: #fff;';
            acoes.append(criarBotao(ativo ? 'Desativar' : 'Ativar', 'excluir', (e) => alterarStatus(usuario, e.currentTarget), estiloBotaoStatus));

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

        const ehValidador = usuario.tipo === 'Validador' || (usuario.funcao && usuario.funcao.includes('Validador'));

        if (ehValidador) {
            editarBlocoSetor.style.display = 'block';
            setorEdicao.required = true;
            preencherSetores(setorEdicao, usuario.id_setor);
        } else {
            editarBlocoSetor.style.display = 'none';
            setorEdicao.required = false;
            setorEdicao.value = '';
        }

        dadosOriginaisEdicao = {
            nome: usuario.nome.trim(),
            matricula: usuario.matricula.trim(),
            email: usuario.email.trim(),
            id_setor: ehValidador ? String(usuario.id_setor || '') : ''
        };

        areaEdicao.hidden = false;
        areaEdicao.open = true;
        areaEdicao.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function alterarStatus(usuario, botaoOrigem) {
        usuarioPendente = usuario;
        botaoOrigemModal = botaoOrigem;
        const ativo = Number(usuario.status) === 1;
        
        document.getElementById('tituloModalDesativacao').textContent = ativo ? 'Desativar usuário?' : 'Reativar usuário?';
        document.getElementById('textoModalDesativacao').textContent = `Tem certeza que deseja ${ativo ? 'desativar' : 'reativar'} ${usuario.nome}?`;
        botaoConfirmarDesativacao.textContent = ativo ? 'Confirmar desativação' : 'Confirmar reativação';
        botaoConfirmarDesativacao.style.backgroundColor = ativo ? '#C62828' : '#2E7D32';
        botaoConfirmarDesativacao.style.color = '#fff';

        modalDesativacao.hidden = false;
        modalDesativacao.style.display = 'grid';
    }

    document.getElementById('cancelarDesativacao').addEventListener('click', () => {
        modalDesativacao.hidden = true;
        modalDesativacao.style.display = 'none';
    });

    botaoConfirmarDesativacao.addEventListener('click', async () => {
        if (!usuarioPendente) return;
        const ativo = Number(usuarioPendente.status) === 1;
        try {
            await requisitarApi({
                method: 'PATCH',
                body: JSON.stringify({ id: usuarioPendente.id, tipo: usuarioPendente.tipo, status: ativo ? 0 : 1 })
            });
            modalDesativacao.hidden = true;
            modalDesativacao.style.display = 'none';
            await carregarUsuarios();
        } catch (erro) {
            alert(erro.message);
        }
    });

    document.getElementById('formCadastro').addEventListener('submit', async (e) => {
        e.preventDefault();
        const funcoes = Array.from(document.querySelectorAll('input[name="funcao"]:checked')).map(cb => cb.value);
        if (!funcoes.length) return alert('Selecione pelo menos uma função.');

        try {
            await requisitarApi({
                method: 'POST',
                body: JSON.stringify({
                    nome: document.getElementById('nome').value.trim(),
                    matricula: document.getElementById('matricula').value.trim(),
                    email: document.getElementById('email').value.trim(),
                    senha: document.getElementById('senha').value.trim(),
                    funcoes: funcoes,
                    id_setor: checkValidador.checked ? setorCadastro.value : null
                })
            });
            e.target.reset();
            blocoSetor.style.display = 'none';
            await carregarUsuarios();
            alert('Usuário cadastrado com sucesso.');
        } catch (erro) {
            alert(erro.message);
        }
    });

    document.getElementById('formEditar').addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            await requisitarApi({
                method: 'PUT',
                body: JSON.stringify({
                    id: document.getElementById('editarId').value,
                    tipo: document.getElementById('editarTipoOriginal').value,
                    nome: document.getElementById('editarNome').value.trim(),
                    matricula: document.getElementById('editarMatricula').value.trim(),
                    email: document.getElementById('editarEmail').value.trim(),
                    id_setor: editarBlocoSetor.style.display !== 'none' ? setorEdicao.value : ''
                })
            });
            areaEdicao.hidden = true;
            await carregarUsuarios();
            modalEdicaoSucesso.hidden = false;
            modalEdicaoSucesso.style.display = 'grid';
        } catch (erro) {
            alert(erro.message);
        }
    });

    document.getElementById('fecharModalEdicao').addEventListener('click', () => {
        modalEdicaoSucesso.hidden = true;
        modalEdicaoSucesso.style.display = 'none';
    });

    document.getElementById('cancelarEdicao').addEventListener('click', () => areaEdicao.hidden = true);

    carregarUsuarios();
</script>
</body>
</html>