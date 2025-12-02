<?php
require_once 'verificar_login.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Unidades de Saúde</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.svg" type="image/png">
</head>

<body>
    <header class="topo">
        <div class="logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>

        <nav class="menu">
            <a href="index.php">INÍCIO</a>
            <span class="divisor">|</span>
            <a href="perfil.php">MEU PERFIL</a>
            <span class="divisor">|</span>
            <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['paciente', 'medico', 'enfermeiro'])): ?>
            <a href="dependentes.php">DEPENDENTES</a>
            <span class="divisor">|</span>
            <?php endif; ?>
            <a href="historico.php">HISTÓRICO</a>
            <span class="divisor">|</span>
            <a href="hospital.php" class="ativo">UNIDADES DE SAÚDE</a>
            <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['medico', 'enfermeiro'])): ?>
            <span class="divisor">|</span>
            <a href="inicio-med.php">ESCANEAR PULSEIRA</a>
            <?php endif; ?>
        </nav>

        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <main>
        <h2 class="titulo">HOSPITAIS PRÓXIMOS</h2>

        <!-- Filtro de Busca -->
        <div class="hospital-filter-container">
            <div class="hospital-filter-wrapper">
                <div class="hospital-filter">
                    <div class="filtro-input-wrapper">
                        <svg class="filtro-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <input type="text" id="filtro-busca" placeholder="Buscar por nome, endereço ou telefone..." class="filtro-input">
                        <button type="button" id="limpar-filtro" class="btn-limpar-filtro" style="display: none;" title="Limpar busca">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <select id="filtro-cidade" class="filtro-select">
                        <option value="">Todas as cidades</option>
                        <option value="Itapira">Itapira - SP</option>
                        <option value="Mogi Guaçu">Mogi Guaçu - SP</option>
                        <option value="Mogi Mirim">Mogi Mirim - SP</option>
                    </select>
                    <select id="ordenacao" class="filtro-select">
                        <option value="nome">Ordenar por: Nome</option>
                        <option value="cidade">Ordenar por: Cidade</option>
                    </select>
                </div>
                <div class="filtro-actions">
                    <div id="resultado-busca" class="resultado-busca"></div>
                    <button type="button" id="limpar-todos-filtros" class="btn-limpar-todos" style="display: none;">
                        Limpar todos os filtros
                    </button>
                </div>
            </div>
        </div>

        <div class="hospital-list-container">
            <ul class="hospital-list" id="lista-hospitais">
                
                <li class="hospital-card">
                    <img src="img/logo-santa-casa-itapira.png" alt="Logo Santa Casa de Itapira" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>SANTA CASA DE ITAPIRA</h3>
                            <span class="city-tag">Itapira - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong> 
                            <a href="https://www.google.com/maps/search/?api=1&query=Rua+Cel.+Jose+Pires,138,Itapira,SP" target="_blank">
                                R. Cel. José Píres, 138 - Centro, Itapira - SP, 13970-060
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3863-1122</p>
                    </div>
                </li>

                <li class="hospital-card">
                    <img src="img/logo-hospital-municipal-itapira.png" alt="Logo Hospital Municipal de Itapira" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>HOSPITAL MUNICIPAL DE ITAPIRA</h3>
                            <span class="city-tag">Itapira - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong>
                            <a href="https://www.google.com/maps/search/?api=1&query=Rua+Farmaceutico+Pires+de+Godoi,313,Itapira,SP" target="_blank">
                                R. Farm. Píres de Godói, 313 - Centro, Itapira - SP, 13970-190
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3843-1222</p>
                    </div>
                </li>
                
                <li class="hospital-card">
                    <img src="img/logo-hospital-sao-francisco.png" alt="Logo Hospital São Francisco" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>HOSPITAL SÃO FRANCISCO</h3>
                            <span class="city-tag">Mogi Guaçu - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong> 
                            <a href="https://www.google.com/maps/search/?api=1&query=Rua+Inacio+Franco+Alves,561,Mogi+Guacu,SP" target="_blank">
                                R. Inácio Franco Alves, 561 - Parque Cidade Nova, Mogi Guaçu - SP, 13845-420
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3851-8000</p>
                    </div>
                </li>

                <li class="hospital-card">
                    <img src="img/logo-santa-casa-mogiguacu.png" alt="Logo Hospital Santa Casa" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>HOSPITAL SANTA CASA</h3>
                            <span class="city-tag">Mogi Guaçu - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong>
                            <a href="https://www.google.com/maps/search/?api=1&query=Rua+Chico+de+Paula,608,Mogi+Guacu,SP" target="_blank">
                                R. Chico de Paula, 608 - Centro, Mogi Guaçu - SP, 13840-005
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3861-1313</p>
                    </div>
                </li>

                <li class="hospital-card">
                    <img src="img/logo-hospital-22-outubro.png" alt="Logo Hospital 22 de Outubro" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>HOSPITAL 22 DE OUTUBRO</h3>
                            <span class="city-tag">Mogi Mirim - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong>
                            <a href="https://www.google.com/maps/search/?api=1&query=Av+22+de+Outubro,733,Mogi+Mirim,SP" target="_blank">
                                Av. 22 de Outubro, 733 - Jardim Santa Helena, Mogi Mirim - SP, 13806-050
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3814-3400</p>
                    </div>
                </li>

            </ul>
        </div>
    </main>

    <script>
        // Elementos do filtro
        const filtroInput = document.getElementById('filtro-busca');
        const filtroCidade = document.getElementById('filtro-cidade');
        const ordenacao = document.getElementById('ordenacao');
        const limparBtn = document.getElementById('limpar-filtro');
        const limparTodosBtn = document.getElementById('limpar-todos-filtros');
        const listaHospitais = document.getElementById('lista-hospitais');
        const resultadoBusca = document.getElementById('resultado-busca');
        const cardsHospitais = Array.from(document.querySelectorAll('.hospital-card'));

        // Função para obter dados do card
        function obterDadosCard(card) {
            const nome = (card.querySelector('h3')?.textContent || '').trim();
            const cidadeTag = card.querySelector('.city-tag')?.textContent || '';
            const cidade = cidadeTag.split(' - ')[0] || '';
            const paragrafos = card.querySelectorAll('.hospital-info p');
            
            let endereco = '';
            let telefone = '';
            
            paragrafos.forEach(p => {
                const texto = p.textContent.toLowerCase();
                if (texto.includes('endereço') || texto.includes('rua') || texto.includes('av.')) {
                    endereco = p.textContent;
                }
                if (texto.includes('telefone')) {
                    telefone = p.textContent;
                }
            });

            return {
                nome: nome.toLowerCase(),
                cidade: cidade.toLowerCase(),
                cidadeCompleta: cidadeTag.toLowerCase(),
                endereco: endereco.toLowerCase(),
                telefone: telefone.toLowerCase(),
                textoCompleto: (nome + ' ' + cidadeTag + ' ' + endereco + ' ' + telefone).toLowerCase(),
                elemento: card
            };
        }

        // Função para filtrar hospitais
        function filtrarHospitais() {
            const termo = filtroInput.value.toLowerCase().trim();
            const cidadeFiltro = filtroCidade.value.toLowerCase();
            let totalEncontrados = 0;
            const cardsVisiveis = [];

            cardsHospitais.forEach(card => {
                const dados = obterDadosCard(card);
                let corresponde = true;

                // Filtro por texto
                if (termo !== '' && !dados.textoCompleto.includes(termo)) {
                    corresponde = false;
                }

                // Filtro por cidade
                if (cidadeFiltro !== '' && !dados.cidade.includes(cidadeFiltro)) {
                    corresponde = false;
                }

                if (corresponde) {
                    card.style.display = 'flex';
                    totalEncontrados++;
                    cardsVisiveis.push(dados);
                } else {
                    card.style.display = 'none';
                }
            });

            // Ordenar cards visíveis
            ordenarHospitais(cardsVisiveis);

            // Mostrar/ocultar botões limpar
            const inputWrapper = filtroInput.parentElement;
            if (termo !== '') {
                limparBtn.style.display = 'flex';
                inputWrapper.classList.add('has-clear-btn');
            } else {
                limparBtn.style.display = 'none';
                inputWrapper.classList.remove('has-clear-btn');
            }

            if (termo !== '' || cidadeFiltro !== '') {
                limparTodosBtn.style.display = 'inline-block';
            } else {
                limparTodosBtn.style.display = 'none';
            }

            // Mostrar resultado da busca
            atualizarResultadoBusca(totalEncontrados, termo, cidadeFiltro);
        }

        // Função para ordenar hospitais
        function ordenarHospitais(cardsVisiveis) {
            const tipoOrdenacao = ordenacao.value;
            const lista = listaHospitais;

            // Criar array com elementos e dados de ordenação
            const elementosOrdenados = cardsVisiveis.map(dados => ({
                elemento: dados.elemento,
                nome: dados.nome,
                cidade: dados.cidade
            }));

            // Ordenar
            elementosOrdenados.sort((a, b) => {
                if (tipoOrdenacao === 'cidade') {
                    return a.cidade.localeCompare(b.cidade) || a.nome.localeCompare(b.nome);
                } else {
                    return a.nome.localeCompare(b.nome);
                }
            });

            // Reordenar no DOM
            elementosOrdenados.forEach(item => {
                lista.appendChild(item.elemento);
            });
        }

        // Função para atualizar mensagem de resultado
        function atualizarResultadoBusca(total, termo, cidade) {
            if (termo === '' && cidade === '') {
                resultadoBusca.textContent = '';
                resultadoBusca.className = 'resultado-busca';
                return;
            }

            let mensagem = '';
            if (total === 0) {
                mensagem = 'Nenhum hospital encontrado';
                if (termo !== '') mensagem += ' com "' + termo + '"';
                if (cidade !== '') mensagem += ' em ' + filtroCidade.options[filtroCidade.selectedIndex].text;
                mensagem += '.';
                resultadoBusca.className = 'resultado-busca sem-resultado';
            } else {
                mensagem = total + ' hospital' + (total > 1 ? 'is' : '') + ' encontrado' + (total > 1 ? 's' : '');
                if (cidade !== '') {
                    mensagem += ' em ' + filtroCidade.options[filtroCidade.selectedIndex].text;
                }
                resultadoBusca.className = 'resultado-busca com-resultado';
            }
            resultadoBusca.textContent = mensagem;
        }

        // Event listeners
        filtroInput.addEventListener('input', filtrarHospitais);
        filtroCidade.addEventListener('change', filtrarHospitais);
        ordenacao.addEventListener('change', filtrarHospitais);

        filtroInput.addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                filtroInput.value = '';
                filtrarHospitais();
            }
        });

        limparBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            filtroInput.value = '';
            filtrarHospitais();
            filtroInput.focus();
        });

        limparTodosBtn.addEventListener('click', function() {
            filtroInput.value = '';
            filtroCidade.value = '';
            ordenacao.value = 'nome';
            filtrarHospitais();
            filtroInput.focus();
        });

        // Inicializar ordenação
        filtrarHospitais();
    </script>

    <!-- Rodapé -->
    <footer>
        <div class="footer-logo">
            <img src="img/logo-branco.png" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <p>&copy; 2025 Grupo SAMED. Todos os direitos reservados.</p>
        <div class="lojas">
            <img src="img/appstore.webp" alt="App Store">
             <img src="img/googleplay.webp" alt="App Store">
        </div>
    </footer>
</body>

</html>