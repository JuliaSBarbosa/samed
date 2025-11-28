<?php
require_once 'verificar_login.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Novo Dependente</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.svg" type="image/png">
</head>

<body>
    <!-- Cabeçalho -->
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
            <a href="dependentes.php" class="ativo">DEPENDENTES</a>
            <span class="divisor">|</span>
            <a href="historico.php">HISTÓRICO</a>
            <span class="divisor">|</span>
            <a href="hospital.php">UNIDADES DE SAÚDE</a>
        </nav>

        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>
    <!-- Conteúdo principal -->
    <div class="form-container">
        <?php
        // Exibir mensagens de erro
        if (isset($_SESSION['erros']) && !empty($_SESSION['erros'])) {
            echo '<div class="mensagem-erro">';
            foreach ($_SESSION['erros'] as $erro) {
                echo '<p>' . htmlspecialchars($erro) . '</p>';
            }
            echo '</div>';
            unset($_SESSION['erros']);
        }

        // Exibir mensagem de sucesso
        if (isset($_SESSION['sucesso'])) {
            echo '<div class="mensagem-sucesso">' . htmlspecialchars($_SESSION['sucesso']) . '</div>';
            unset($_SESSION['sucesso']);
        }
        ?>
        <!-- Barra de progresso -->
        <div class="progress-bar">
            <div class="step active" data-step="1">
                <span>1</span>
                <span class="step-title">Básicas</span>
            </div>
            <div class="step" data-step="2">
                <span>2</span>
                <span class="step-title">Emergência</span>
            </div>
            <div class="step" data-step="3">
                <span>3</span>
                <span class="step-title">Médicas</span>
            </div>
            <div class="step" data-step="4">
                <span>4</span>
                <span class="step-title">Histórico</span>
            </div>
            <div class="step" data-step="5">
                <span>5</span>
                <span class="step-title">Privacidade</span>
            </div>
            <div class="step" data-step="6">
                <span>6</span>
                <span class="step-title">Termo</span>
            </div>
        </div>

        <form id="dependenteForm" action="registrar_dependente.php" method="post">
            <!-- Etapa 1: Informações Básicas -->
            <div class="form-step active" id="step1" data-step="1">
                <h2>Informações básicas</h2> <br>
                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o seu nome completo" required>

                <label for="nome_social">Nome social (opcional)</label>
                <input type="text" id="nome_social" name="nome_social" placeholder="Como você prefere ser chamado(a)?">

                <label for="data_nascimento">Data de nascimento</label>
                <input type="date" id="data_nascimento" name="data_nascimento" required>

                <label for="sexo">Sexo</label>
                <select id="sexo" name="sexo" required>
                    <option value="">Selecione</option>
                    <option value="masculino">Masculino</option>
                    <option value="feminino">Feminino</option>
                    <option value="intersexo">Intersexo</option>
                </select>

                <label for="genero">Identidade de gênero (opcional)</label>
                <select id="genero" name="genero">
                    <option value="">Selecione</option>
                    <option value="mulher_cis">Mulher (cisgênero)</option>
                    <option value="homem_cis">Homem (cisgênero)</option>
                    <option value="mulher_trans">Mulher trans</option>
                    <option value="homem_trans">Homem trans</option>
                    <option value="nao_binario">Pessoa não-binária</option>
                    <option value="nao_informar">Prefiro não informar</option>
                </select>

                <label for="cpf">CPF</label>
                <input type="text" id="cpf" name="cpf" placeholder="Digite o número do seu CPF">

                <label for="sus">Cartão do SUS</label>
                <input type="text" id="sus" name="sus" placeholder="Digite o número do seu cartão do sus">

                <label for="plano_saude">Possui plano de saúde?</label>
                <select id="plano_saude" name="plano_saude" required onchange="mostrarCampoPlano()">
                    <option value="">Selecione</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                </select>

                <div id="campoPlano" style="display:none; margin-top:10px;">
                    <label for="nome_plano">Qual plano?</label>
                    <input type="text" id="nome_plano" name="nome_plano" list="lista_planos"
                        placeholder="Ex: Unimed, Amil...">
                    <datalist id="lista_planos">
                        <option value="Unimed">
                        <option value="Amil">
                        <option value="SulAmérica">
                        <option value="Bradesco Saúde">
                        <option value="Itaú Saúde"></option>
                        <option value="Porto Seguro Saúde">
                        <option value="Bupa">
                        <option value="Hapvida">
                        <option value="Caixa Seguro Saúde">
                        <option value="Viva Saúde">
                        <option value="Intermédica">
                        <option value="Golden Cross">
                    </datalist>
                </div>

                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" placeholder="Digite o número do seu telefone">

                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Digite o seu e-mail">
            </div>

            <!-- Etapa 2: Contato de Emergência -->
            <div class="form-step" id="step2" data-step="2">
                <h2>CONTATO DE EMERGÊNCIA</h2> <br>

                <label for="contato_nome">Nome do contato</label>
                <input type="text" id="contato_nome" name="contato_nome" required>

                <label for="parentesco">Parentesco</label>
                <select id="parentesco" name="parentesco">
                    <option value="">Selecione</option>

                    <!-- Pais -->
                    <option>Pai</option>
                    <option>Mãe</option>
                    <option>Padrasto</option>
                    <option>Madrasta</option>

                    <!-- Filhos -->
                    <option>Filho</option>
                    <option>Filha</option>
                    <option>Enteado</option>
                    <option>Enteada</option>

                    <!-- Cônjuges / Parceiros -->
                    <option>Esposo</option>
                    <option>Esposa</option>
                    <option>Companheiro</option>
                    <option>Companheira</option>

                    <!-- Avós -->
                    <option>Avô</option>
                    <option>Avó</option>

                    <!-- Netos -->
                    <option>Neto</option>
                    <option>Neta</option>

                    <!-- Irmãos -->
                    <option>Irmão</option>
                    <option>Irmã</option>

                    <!-- Tios -->
                    <option>Tio</option>
                    <option>Tia</option>

                    <!-- Sobrinhos -->
                    <option>Sobrinho</option>
                    <option>Sobrinha</option>

                    <!-- Primos -->
                    <option>Primo</option>
                    <option>Prima</option>

                    <!-- Outros parentes -->
                    <option>Cunhado</option>
                    <option>Cunhada</option>
                    <option>Genro</option>
                    <option>Nora</option>
                    <option>Sogro</option>
                    <option>Sogra</option>

                    <!-- Geral -->
                    <option>Tutor</option>
                    <option>Responsável</option>
                    <option>Amigo</option>
                    <option>Outro</option>
                </select>

                <div id="campoOutroParentesco" style="display: none; margin-top: 10px;">
                    <label for="outroParentesco">Qual?</label>
                    <input type="text" id="outroParentesco" name="outro_parentesco" placeholder="Descreva o parentesco">
                </div>


                <label for="contato_telefone">Telefone</label>
                <input type="tel" id="contato_telefone" name="contato_telefone" required>

            </div>

            <!-- Etapa 3: Informações Médicas -->
            <div class="form-step" id="step3" data-step="3">
                <h2>INFORMAÇÕES MÉDICAS</h2> <br>

                <label for="doencas">Doenças crônicas</label>
                <div id="doencas-wrapper">
                    <div class="doenca-item">
                        <div class="doenca-select-wrapper">
                            <select name="doencas[]" class="doenca-select">
                                <option value="">Nenhuma</option>

                                <optgroup label="Doenças Cardiovasculares">
                                    <option value="hipertensao">Hipertensão arterial</option>
                                    <option value="insuficiencia_cardiaca">Insuficiência cardíaca</option>
                                    <option value="arritmias_cronicas">Arritmias crônicas</option>
                                    <option value="doenca_arterial_coronariana">Doença arterial coronariana</option>
                                    <option value="aterosclerose">Aterosclerose</option>
                                    <option value="doenca_vascular_periferica">Doença vascular periférica</option>
                                </optgroup>

                                <optgroup label="Doenças Endócrinas e Metabólicas">
                                    <option value="diabetes_tipo1">Diabetes tipo 1</option>
                                    <option value="diabetes_tipo2">Diabetes tipo 2</option>
                                    <option value="hipotireoidismo">Hipotireoidismo</option>
                                    <option value="hipertireoidismo">Hipertireoidismo</option>
                                    <option value="obesidade_cronica">Obesidade crônica</option>
                                    <option value="sindrome_metabolica">Síndrome metabólica</option>
                                </optgroup>

                                <optgroup label="Doenças Respiratórias Crônicas">
                                    <option value="asma">Asma</option>
                                    <option value="dpoc">DPOC (Doença Pulmonar Obstrutiva Crônica)</option>
                                    <option value="bronquite_cronica">Bronquite crônica</option>
                                    <option value="enfisema">Enfisema</option>
                                    <option value="fibrose_pulmonar">Fibrose pulmonar</option>
                                </optgroup>

                                <optgroup label="Doenças Autoimunes">
                                    <option value="artrite_reumatoide">Artrite reumatoide</option>
                                    <option value="lupus">Lúpus (LES)</option>
                                    <option value="psoriase">Psoríase</option>
                                    <option value="doenca_celiaca">Doença celíaca</option>
                                    <option value="tireoidite_hashimoto">Tireoidite de Hashimoto</option>
                                    <option value="doenca_de_crohn">Doença de Crohn</option>
                                    <option value="retocolite_ulcerativa">Retocolite ulcerativa</option>
                                </optgroup>

                                <optgroup label="Doenças Neurológicas">
                                    <option value="epilepsia">Epilepsia</option>
                                    <option value="enxaqueca_cronica">Enxaqueca crônica</option>
                                    <option value="doenca_de_parkinson">Doença de Parkinson</option>
                                    <option value="esclerose_multipla">Esclerose múltipla</option>
                                    <option value="neuropatias_perifericas">Neuropatias periféricas</option>
                                </optgroup>

                                <optgroup label="Doenças Musculoesqueléticas">
                                    <option value="artrose_osteoartrite">Artrose / Osteoartrite</option>
                                    <option value="fibromialgia">Fibromialgia</option>
                                    <option value="lombalgia_cronica">Lombalgia crônica</option>
                                    <option value="osteoporose">Osteoporose</option>
                                </optgroup>

                                <optgroup label="Doenças Hepáticas e Renais">
                                    <option value="hepatite_cronica">Hepatite crônica</option>
                                    <option value="cirrose">Cirrose</option>
                                    <option value="esteatose_hepatica_cronica">Esteatose hepática (gordura no fígado) crônica
                                    </option>
                                    <option value="doenca_renal_cronica">Doença renal crônica</option>
                                    <option value="insuficiencia_renal">Insuficiência renal</option>
                                </optgroup>

                                <optgroup label="Doenças Gastrointestinais">
                                    <option value="refluxo_gastroesofagico_cronico">Refluxo gastroesofágico crônico (GERD)</option>
                                    <option value="sindrome_do_intestino_irritavel">Síndrome do intestino irritável (SII)</option>
                                    <option value="gastrite_cronica">Gastrite crônica</option>
                                </optgroup>

                                <optgroup label="Outras Condições Crônicas">
                                    <option value="cancer">Câncer (em acompanhamento ou histórico)</option>
                                    <option value="hiv">HIV</option>
                                    <option value="doencas_hematologicas">Doenças hematológicas</option>
                                </optgroup>

                                <option value="outra_nao_listada">Outra doença não listada acima</option>
                            </select>
                            <button type="button" class="remover-doenca btn-small remove" style="display: none;">Remover</button>
                        </div>
                        <div class="campo-outra-doenca" style="display: none; margin-top: 10px;">
                            <input type="text" name="outraDoenca[]" class="outra-doenca-input" placeholder="Digite o nome da doença">
                        </div>
                    </div>
                </div>
                <button type="button" id="adicionar-doenca" class="btn-small add" style="margin-top:8px; display: none;">Adicionar doença</button>
               
                <label for="alergias">Tipo de alergia</label>
                <div id="alergias-wrapper">
                    <div class="alergia-item">
                        <div class="alergia-select-wrapper">
                            <select name="alergias[]" class="alergia-select">
                                <option value="">Nenhuma</option>
                                <option value="alimentar">Alergia alimentar</option>
                                <option value="medicamentos">Alergia medicamentosa</option>
                                <option value="respiratoria">Alergia respiratória</option>
                                <option value="dermatologica">Alergia dermatológica</option>
                                <option value="inseto">Alergia a picada de inseto</option>
                                <option value="quimica">Alergia química</option>
                                <option value="fisica">Alergia física</option>
                                <option value="outra">Outra</option>
                            </select>
                            <button type="button" class="remover-alergia btn-small remove" style="display: none;">Remover</button>
                        </div>
                        <div class="campo-descricao-alergia" style="display: none; margin-top: 10px;">
                            <input type="text" name="descricaoAlergia[]" class="descricao-alergia-input" placeholder="Descreva a alergia">
                        </div>
                    </div>
                </div>
                <button type="button" id="adicionar-alergia" class="btn-small add" style="margin-top:8px; display: none;">Adicionar alergia</button>
           
                <label for="tipo_sanguineo">Tipo sanguíneo</label>
                <select id="tipo_sanguineo" name="tipo_sanguineo">
                    <option value="">Selecione</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                    <option value="RH-NULO">RH NULO</option>

                </select>

                <label for="medicacao">Medicação de uso contínuo</label>
                <div id="medicacoes-wrapper">
                    <div class="medicacao-item">
                        <input type="text" name="medicacao[]" class="medicacao-input" placeholder="Nome do medicamento">
                        <button type="button" class="remover-medicacao btn-small remove">Remover</button>
                    </div>
                </div>
                <button type="button" id="adicionar-medicacao" class="btn-small add" style="margin-top:8px; display: none;">Adicionar
                    medicação</button>
             
                <label for="doenca_mental">Doença mental</label>
                <div id="doencas-mentais-wrapper">
                    <div class="doenca-mental-item">
                        <div class="doenca-mental-select-wrapper">
                            <select name="doenca_mental[]" class="doenca-mental-select">
                                <option value="">Nenhuma</option>
                                <option value="depressao">Depressão</option>
                                <option value="ansiedade">Transtorno de Ansiedade</option>
                                <option value="bipolaridade">Transtorno Bipolar</option>
                                <option value="esquizofrenia">Esquizofrenia</option>
                                <option value="tdah">TDAH (Transtorno do Déficit de Atenção e Hiperatividade)</option>
                                <option value="toc">TOC (Transtorno Obsessivo-Compulsivo)</option>
                                <option value="transtorno_estresse_pos_traumatico">Transtorno de Estresse Pós-Traumático</option>
                                <option value="outra">Outra</option>
                            </select>
                            <button type="button" class="remover-doenca-mental btn-small remove" style="display: none;">Remover</button>
                        </div>
                        <div class="campo-outra-doenca-mental" style="display: none; margin-top: 10px;">
                            <input type="text" name="outraDoencaMental[]" class="outra-doenca-mental-input"
                                placeholder="Digite o nome da doença">
                        </div>
                    </div>
                </div>
                <button type="button" id="adicionar-doenca-mental" class="btn-small add" style="margin-top:8px; display: none;">Adicionar doença mental</button>
                
                <label for="dispositivo">Dispositivo implantado</label>
                <div id="dispositivos-wrapper">
                    <div class="dispositivo-item">
                        <div class="dispositivo-select-wrapper">
                            <select name="dispositivo[]" class="dispositivo-select">
                                <option value="">Nenhum</option>
                                <option value="marca_passo">Marca-passo</option>
                                <option value="stent_cardiaco">Stent cardíaco</option>
                                <option value="valvula_cardiaca">Prótese de válvula cardíaca</option>
                                <option value="derivacao_cerebral">Derivação ventricular (shunt)</option>
                                <option value="implante_cochlear">Implante coclear</option>
                                <option value="proteses_ortopedicas">Próteses ortopédicas</option>
                                <option value="dispositivo_contraceptivo">Dispositivo contraceptivo</option>
                                <option value="outro">Outro</option>
                            </select>
                            <button type="button" class="remover-dispositivo btn-small remove" style="display: none;">Remover</button>
                        </div>
                        <div class="campo-outro-dispositivo" style="display: none; margin-top: 10px;">
                            <input type="text" name="outroDispositivo[]" class="outro-dispositivo-input"
                                placeholder="Digite o nome do dispositivo">
                        </div>
                    </div>
                </div>
                <button type="button" id="adicionar-dispositivo" class="btn-small add" style="margin-top:8px; display: none;">Adicionar dispositivo</button>
           
                <label for="doador_orgaos">É doador(a) de órgãos?</label>
                <select id="doador_orgaos" name="doador_orgaos" required>
                    <option value="">Selecione</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                    <option value="nao_informar">Prefiro não informar</option>
                </select>

                <label for="ressuscitacao">Você autoriza procedimentos de reanimação em caso de emergência?</label>
                <select id="ressuscitacao" name="ressuscitacao">
                    <option value="">Selecione</option>
                    <option value="sim">Sim, autorizo</option>
                    <option value="nao">Não autorizo</option>
                    <option value="nao_informar">Prefiro não informar</option>
                </select>

                <label for="transfusao">Em caso de necessidade, autoriza receber transfusão de sangue?</label>
                <select id="transfusao" name="transfusao" required>
                    <option value="">Selecione</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                    <option value="nao_informar">Prefiro não informar</option>
                </select>

                <label for="info_relevantes">Informações relevantes</label>
                <textarea id="info_relevantes" name="info_relevantes" rows="3"></textarea>


            </div>

            <!-- Etapa 4: Histórico Médico -->
            <div class="form-step" id="step4" data-step="4">
                <h2>HISTÓRICO MÉDICO</h2> <br>

                <label for="cirurgias">Cirurgias</label>
                <textarea id="cirurgias" name="cirurgias" rows="4"></textarea>

                <label for="emergencia">Histórico de emergências</label>
                <textarea id="emergencia" name="emergencia" rows="4"></textarea>

                <label for="habitos">Hábitos importantes</label>
                <textarea id="habitos" name="habitos" rows="4"></textarea>

            </div>

            <!-- Etapa 5: Configurações de privacidade -->
            <div class="form-step" id="step5" data-step="5">
                <h2>CONFIGURAÇÕES DE PRIVACIDADE</h2> <br>

                <label for="compartilhar_localizacao">Autoriza o compartilhamento da localização?</label>
                <select id="compartilhar_localizacao" name="compartilhar_localizacao" required>
                    <option value="">Selecione</option>
                    <option value="sim">Sim, autorizo</option>
                    <option value="nao">Não autorizo</option>
                </select>

                <label for="autorizacao_usuario">Autoriza usuários comuns consultar informações básicas (nome, telefone, contato de emergência)?</label>
                <select id="autorizacao_usuario" name="autorizacao_usuario" required>
                 <option value="">Selecione</option>
                    <option value="sim">Sim, autorizo</option>
                    <option value="nao">Não autorizo</option>
                </select>

            </div>

            <!-- Etapa 6: Termo de Consentimento -->
            <div class="form-step" id="step6" data-step="6">
                <h2>TERMO DE CONSENTIMENTO</h2> <br>

                <div>
                    <p>Eu, abaixo assinado, declaro que:</p>
                        <li>Tenho conhecimento e autorizo o uso dos meus dados pessoais e de saúde no sistema SAMED.
                        </li>
                        <li>Autorizo o compartilhamento dessas informações com profissionais de saúde quando necessário
                            para meu atendimento.</li>
                        <li>Compreendo que essas informações são armazenadas de forma segura e protegidas por lei.</li>
                        <li>Tenho o direito de solicitar acesso, retificação ou exclusão de meus dados a qualquer
                            momento.</li>
                        <li>Fui informado(a) sobre meus direitos e responsabilidades conforme a Lei de Proteção de Dados
                            (LGPD).</li>
                        <li>Declaro que todas as informações fornecidas são verdadeiras e de minha responsabilidade.
                        </li>
                    </ul>
                </div>
                <div style="margin-top: 20px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px;">
                        <input type="checkbox" id="aceitar_termo" name="aceitar_termo" required>
                        <span style="font-size: 14px;">Eu li e concordo com os termos acima e autorizo o armazenamento e
                            uso de meus dados de saúde no SAMED.</span>
                    </label>
                </div>
            </div>
            <!-- Botões de navegação -->

            <div class="button-group">
                <button type="button" class="btn-anterior">Anterior</button>
                <div style="flex:1"></div>
                <button type="button" class="btn-proximo">Próximo</button>
                <button type="submit" class="submit-btn">Salvar Dependente</button>
            </div>
        </form>
    </div>
    </section>
    </main>

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
    <script src="js/dependentes.js"></script>
</body>

</html>