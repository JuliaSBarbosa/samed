<?php
require_once 'verificar_login.php';
require_once 'config.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$editar = isset($_GET['editar']) && $_GET['editar'] == '1';
$perfil = null;
$contato_emergencia = null;

// Carregar dados existentes se estiver editando
$nome_usuario = $_SESSION['usuario_nome'] ?? '';
if ($editar && $pdo && $usuario_id) {
    try {
        // Buscar nome do usuário
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario_data = $stmt->fetch();
        if ($usuario_data) {
            $nome_usuario = $usuario_data['nome'];
        }
        
        // Buscar perfil médico (garantir que não é de dependente)
        $stmt = $pdo->prepare("SELECT * FROM perfis_medicos WHERE usuario_id = ? AND dependente_id IS NULL");
        $stmt->execute([$usuario_id]);
        $perfil = $stmt->fetch();
        
        // Buscar contato de emergência
        if ($perfil) {
            $stmt = $pdo->prepare("SELECT * FROM contatos_emergencia WHERE usuario_id = ?");
            $stmt->execute([$usuario_id]);
            $contato_emergencia = $stmt->fetch();
        }
    } catch (PDOException $e) {
        // Erro ao buscar dados
        error_log("Erro ao buscar dados do perfil para edição: " . $e->getMessage());
        $_SESSION['erro_perfil'] = "Não foi possível carregar seus dados para edição. Por favor, tente novamente.";
    }
}

// Se houver dados salvos na sessão (após erro), usar eles
if (isset($_SESSION['dados_form'])) {
    $dados_form = $_SESSION['dados_form'];
    unset($_SESSION['dados_form']);
} else {
    $dados_form = null;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Perfil</title>
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
            <a href="perfil.php" class="ativo">MEU PERFIL</a>
            <span class="divisor">|</span>
            <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['paciente', 'medico', 'enfermeiro'])): ?>
            <a href="dependentes.php">DEPENDENTES</a>
            <span class="divisor">|</span>
            <?php endif; ?>
            <a href="historico.php">HISTÓRICO</a>
            <span class="divisor">|</span>
            <a href="hospital.php">UNIDADES DE SAÚDE</a>
            <?php if ($_SESSION['usuario_tipo'] === 'paciente'): ?>
            <span class="divisor">|</span>
            <a href="buscar_paciente.php">BUSCAR PACIENTE</a>
            <?php endif; ?>
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
        
        <?php if ($editar && $perfil): ?>
            <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #6ec1e4;">
                <h3 style="margin: 0; color: #244357; font-size: 1.1rem;">✏️ Modo de Edição</h3>
                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">Você está editando seus dados cadastrados. Os campos abaixo estão preenchidos com suas informações atuais.</p>
            </div>
        <?php endif; ?>
        
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

        <form id="perfilForm" action="salvar_perfil.php" method="post" enctype="multipart/form-data">
            <!-- Etapa 1: Informações Básicas -->
            <div class="form-step active" id="step1" data-step="1">
                <h2>Informações básicas</h2>

                <label for="foto_perfil">Foto de Perfil</label>
                <div style="margin-bottom: 15px;">
                    <?php
                    $foto_atual = null;
                    if ($perfil && $perfil['foto_perfil']) {
                        $foto_atual = 'uploads/fotos/' . $perfil['foto_perfil'];
                    }
                    ?>
                    <?php if ($foto_atual && file_exists($foto_atual)): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= htmlspecialchars($foto_atual) ?>" alt="Foto atual"
                                style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #6ec1e4;">
                            <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">Foto atual</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="padding: 8px;">
                    <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">Formatos aceitos: JPG, PNG, GIF (máx.
                        5MB)</p>
                </div>

                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome" placeholder="Digite o nome completo"
                    value="<?= htmlspecialchars($dados_form['nome'] ?? $nome_usuario ?? '') ?>" required>

                <label for="nome_social">Nome social (opcional)</label>
                <input type="text" id="nome_social" name="nome_social" placeholder="Como você prefere ser chamado(a)?">

                <label for="data_nascimento">Data de nascimento</label>
                <?php 
                $data_nascimento_valor = '';
                if (isset($dados_form['data_nascimento'])) {
                    $data_nascimento_valor = $dados_form['data_nascimento'];
                } elseif ($perfil && $perfil['data_nascimento']) {
                    $data_nascimento_valor = date('Y-m-d', strtotime($perfil['data_nascimento']));
                }
                ?>
                <input type="date" id="data_nascimento" name="data_nascimento" value="<?= htmlspecialchars($data_nascimento_valor) ?>" required>

                <label for="sexo">Sexo</label>
                <?php 
                $sexo_selecionado = $dados_form['sexo'] ?? $perfil['sexo'] ?? '';
                ?>
                <select id="sexo" name="sexo" required>
                    <option value="">Selecione</option>
                    <option value="masculino" <?= $sexo_selecionado == 'masculino' ? 'selected' : '' ?>>Masculino</option>
                    <option value="feminino" <?= $sexo_selecionado == 'feminino' ? 'selected' : '' ?>>Feminino</option>
                    <option value="outro" <?= $sexo_selecionado == 'outro' ? 'selected' : '' ?>>Outro</option>
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
                <input type="text" id="cpf" name="cpf" placeholder="Digite o número do seu CPF" value="<?= htmlspecialchars($dados_form['cpf'] ?? $perfil['cpf'] ?? '') ?>" required>

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
                <input type="tel" id="telefone" name="telefone" placeholder="Digite o número do seu telefone" value="<?= htmlspecialchars($dados_form['telefone'] ?? $perfil['telefone'] ?? '') ?>" required>

                <label for="email">E-mail de contato</label>
                <?php 
                // Usar email do perfil se existir, senão usar email do login
                $email_valor = $dados_form['email'] ?? $perfil['email'] ?? $_SESSION['usuario_email'] ?? '';
                ?>
                <input type="email" id="email" name="email" placeholder="Digite o seu e-mail" value="<?= htmlspecialchars($email_valor) ?>" required>
                <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">
                    Email do login: <strong><?= htmlspecialchars($_SESSION['usuario_email'] ?? 'Não informado') ?></strong>
                </p>
            </div>

            <!-- Etapa 2: Contato de Emergência -->
            <div class="form-step" id="step2" data-step="2">
                <h2>CONTATO DE EMERGÊNCIA</h2> <br>

                <label for="contato_nome">Nome do contato</label>
                <input type="text" id="contato_nome" name="contato_nome" value="<?= htmlspecialchars($dados_form['contato_nome'] ?? $contato_emergencia['nome'] ?? '') ?>" required>

                <label for="parentesco">Parentesco</label>
                <?php 
                $parentesco_selecionado = $dados_form['parentesco'] ?? $contato_emergencia['parentesco'] ?? '';
                ?>
                <select id="parentesco" name="parentesco" required>
                    <option value="">Selecione</option>

                    <!-- Pais -->
                    <option value="Pai" <?= $parentesco_selecionado == 'Pai' ? 'selected' : '' ?>>Pai</option>
                    <option value="Mãe" <?= $parentesco_selecionado == 'Mãe' ? 'selected' : '' ?>>Mãe</option>
                    <option value="Padrasto" <?= $parentesco_selecionado == 'Padrasto' ? 'selected' : '' ?>>Padrasto</option>
                    <option value="Madrasta" <?= $parentesco_selecionado == 'Madrasta' ? 'selected' : '' ?>>Madrasta</option>

                    <!-- Filhos -->
                    <option value="Filho" <?= $parentesco_selecionado == 'Filho' ? 'selected' : '' ?>>Filho</option>
                    <option value="Filha" <?= $parentesco_selecionado == 'Filha' ? 'selected' : '' ?>>Filha</option>
                    <option value="Enteado" <?= $parentesco_selecionado == 'Enteado' ? 'selected' : '' ?>>Enteado</option>
                    <option value="Enteada" <?= $parentesco_selecionado == 'Enteada' ? 'selected' : '' ?>>Enteada</option>

                    <!-- Cônjuges / Parceiros -->
                    <option value="Esposo" <?= $parentesco_selecionado == 'Esposo' ? 'selected' : '' ?>>Esposo</option>
                    <option value="Esposa" <?= $parentesco_selecionado == 'Esposa' ? 'selected' : '' ?>>Esposa</option>
                    <option value="Companheiro" <?= $parentesco_selecionado == 'Companheiro' ? 'selected' : '' ?>>Companheiro</option>
                    <option value="Companheira" <?= $parentesco_selecionado == 'Companheira' ? 'selected' : '' ?>>Companheira</option>

                    <!-- Avós -->
                    <option value="Avô" <?= $parentesco_selecionado == 'Avô' ? 'selected' : '' ?>>Avô</option>
                    <option value="Avó" <?= $parentesco_selecionado == 'Avó' ? 'selected' : '' ?>>Avó</option>

                    <!-- Netos -->
                    <option value="Neto" <?= $parentesco_selecionado == 'Neto' ? 'selected' : '' ?>>Neto</option>
                    <option value="Neta" <?= $parentesco_selecionado == 'Neta' ? 'selected' : '' ?>>Neta</option>

                    <!-- Irmãos -->
                    <option value="Irmão" <?= $parentesco_selecionado == 'Irmão' ? 'selected' : '' ?>>Irmão</option>
                    <option value="Irmã" <?= $parentesco_selecionado == 'Irmã' ? 'selected' : '' ?>>Irmã</option>

                    <!-- Tios -->
                    <option value="Tio" <?= $parentesco_selecionado == 'Tio' ? 'selected' : '' ?>>Tio</option>
                    <option value="Tia" <?= $parentesco_selecionado == 'Tia' ? 'selected' : '' ?>>Tia</option>

                    <!-- Sobrinhos -->
                    <option value="Sobrinho" <?= $parentesco_selecionado == 'Sobrinho' ? 'selected' : '' ?>>Sobrinho</option>
                    <option value="Sobrinha" <?= $parentesco_selecionado == 'Sobrinha' ? 'selected' : '' ?>>Sobrinha</option>

                    <!-- Primos -->
                    <option value="Primo" <?= $parentesco_selecionado == 'Primo' ? 'selected' : '' ?>>Primo</option>
                    <option value="Prima" <?= $parentesco_selecionado == 'Prima' ? 'selected' : '' ?>>Prima</option>

                    <!-- Outros parentes -->
                    <option value="Cunhado" <?= $parentesco_selecionado == 'Cunhado' ? 'selected' : '' ?>>Cunhado</option>
                    <option value="Cunhada" <?= $parentesco_selecionado == 'Cunhada' ? 'selected' : '' ?>>Cunhada</option>
                    <option value="Genro" <?= $parentesco_selecionado == 'Genro' ? 'selected' : '' ?>>Genro</option>
                    <option value="Nora" <?= $parentesco_selecionado == 'Nora' ? 'selected' : '' ?>>Nora</option>
                    <option value="Sogro" <?= $parentesco_selecionado == 'Sogro' ? 'selected' : '' ?>>Sogro</option>
                    <option value="Sogra" <?= $parentesco_selecionado == 'Sogra' ? 'selected' : '' ?>>Sogra</option>

                    <!-- Geral -->
                    <option value="Tutor" <?= $parentesco_selecionado == 'Tutor' ? 'selected' : '' ?>>Tutor</option>
                    <option value="Responsável" <?= $parentesco_selecionado == 'Responsável' ? 'selected' : '' ?>>Responsável</option>
                    <option value="Amigo" <?= $parentesco_selecionado == 'Amigo' ? 'selected' : '' ?>>Amigo</option>
                    <option value="Outro" <?= $parentesco_selecionado == 'Outro' ? 'selected' : '' ?>>Outro</option>
                </select>

                <div id="campoOutroParentesco" style="display: none; margin-top: 10px;">
                    <label for="outroParentesco">Qual?</label>
                    <input type="text" id="outroParentesco" name="outro_parentesco" placeholder="Descreva o parentesco">
                </div>


                <label for="contato_telefone">Telefone</label>
                <input type="tel" id="contato_telefone" name="contato_telefone" value="<?= htmlspecialchars($dados_form['contato_telefone'] ?? $contato_emergencia['telefone'] ?? '') ?>" required>

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
                                    <option value="esteatose_hepatica_cronica">Esteatose hepática (gordura no fígado)
                                        crônica
                                    </option>
                                    <option value="doenca_renal_cronica">Doença renal crônica</option>
                                    <option value="insuficiencia_renal">Insuficiência renal</option>
                                </optgroup>

                                <optgroup label="Doenças Gastrointestinais">
                                    <option value="refluxo_gastroesofagico_cronico">Refluxo gastroesofágico crônico
                                        (GERD)</option>
                                    <option value="sindrome_do_intestino_irritavel">Síndrome do intestino irritável
                                        (SII)</option>
                                    <option value="gastrite_cronica">Gastrite crônica</option>
                                </optgroup>

                                <optgroup label="Outras Condições Crônicas">
                                    <option value="cancer">Câncer (em acompanhamento ou histórico)</option>
                                    <option value="hiv">HIV</option>
                                    <option value="doencas_hematologicas">Doenças hematológicas</option>
                                </optgroup>

                                <option value="outra_nao_listada">Outra doença não listada acima</option>
                            </select>
                            <button type="button" class="remover-doenca btn-small remove"
                                style="display: none;">Remover</button>
                        </div>
                        <div class="campo-outra-doenca" style="display: none; margin-top: 10px;">
                            <input type="text" name="outraDoenca[]" class="outra-doenca-input"
                                placeholder="Digite o nome da doença">
                        </div>
                    </div>
                </div>
                <button type="button" id="adicionar-doenca" class="btn-small add"
                    style="margin-top:8px; display: none;">Adicionar doença</button>

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
                            <button type="button" class="remover-alergia btn-small remove"
                                style="display: none;">Remover</button>
                        </div>
                        <div class="campo-descricao-alergia" style="display: none; margin-top: 10px;">
                            <input type="text" name="descricaoAlergia[]" class="descricao-alergia-input"
                                placeholder="Descreva a alergia">
                        </div>
                    </div>
                </div>
                <button type="button" id="adicionar-alergia" class="btn-small add"
                    style="margin-top:8px; display: none;">Adicionar alergia</button>

                <label for="tipo_sanguineo">Tipo sanguíneo</label>
                <?php 
                $tipo_sanguineo_selecionado = $dados_form['tipo_sanguineo'] ?? $perfil['tipo_sanguineo'] ?? '';
                ?>
                <select id="tipo_sanguineo" name="tipo_sanguineo" required>
                    <option value="">Selecione</option>
                    <?php 
                    $tipos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'RH-NULO'];
                    foreach ($tipos as $tipo): 
                    ?>
                        <option value="<?= $tipo ?>" <?= $tipo_sanguineo_selecionado == $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="medicacao">Medicação de uso contínuo</label>
                <div id="medicacoes-wrapper">
                    <div class="medicacao-item">
                        <input type="text" name="medicacao[]" class="medicacao-input" placeholder="Nome do medicamento">
                        <button type="button" class="remover-medicacao btn-small remove">Remover</button>
                    </div>
                </div>
                <button type="button" id="adicionar-medicacao" class="btn-small add"
                    style="margin-top:8px; display: none;">Adicionar
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
                                <option value="transtorno_estresse_pos_traumatico">Transtorno de Estresse Pós-Traumático
                                </option>
                                <option value="outra">Outra</option>
                            </select>
                            <button type="button" class="remover-doenca-mental btn-small remove"
                                style="display: none;">Remover</button>
                        </div>
                        <div class="campo-outra-doenca-mental" style="display: none; margin-top: 10px;">
                            <input type="text" name="outraDoencaMental[]" class="outra-doenca-mental-input"
                                placeholder="Digite o nome da doença">
                        </div>
                    </div>
                </div>
                <button type="button" id="adicionar-doenca-mental" class="btn-small add"
                    style="margin-top:8px; display: none;">Adicionar doença mental</button>

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
                            <button type="button" class="remover-dispositivo btn-small remove"
                                style="display: none;">Remover</button>
                        </div>
                        <div class="campo-outro-dispositivo" style="display: none; margin-top: 10px;">
                            <input type="text" name="outroDispositivo[]" class="outro-dispositivo-input"
                                placeholder="Digite o nome do dispositivo">
                        </div>
                    </div>
                </div>
                <button type="button" id="adicionar-dispositivo" class="btn-small add"
                    style="margin-top:8px; display: none;">Adicionar dispositivo</button>

                <label for="doador_orgaos">É doador(a) de órgãos?</label>
                <select id="doador_orgaos" name="doador_orgaos" required>
                    <option value="">Selecione</option>
                    <option value="sim">Sim</option>
                    <option value="nao">Não</option>
                    <option value="nao_informar">Prefiro não informar</option>
                </select>

                <label for="ressuscitacao">Você autoriza procedimentos de reanimação em caso de emergência?</label>
                <select id="ressuscitacao" name="ressuscitacao" required>
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
                <textarea id="info_relevantes" name="info_relevantes" rows="3"><?= htmlspecialchars($dados_form['info_relevantes'] ?? $perfil['info_relevantes'] ?? '') ?></textarea>


            </div>

            <!-- Etapa 4: Histórico Médico -->
            <div class="form-step" id="step4" data-step="4">
                <h2>HISTÓRICO MÉDICO</h2> <br>

                <label for="cirurgias">Cirurgias</label>
                <textarea id="cirurgias" name="cirurgias" rows="4"><?= htmlspecialchars($dados_form['cirurgias'] ?? $perfil['cirurgias'] ?? '') ?></textarea>

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

                <label for="autorizacao_usuario">Autoriza usuários comuns consultar informações básicas (nome, telefone,
                    contato de emergência)?</label>
                <select id="autorizacao_usuario" name="autorizacao_usuario" required>
                    <option value="">Selecione</option>
                    <option value="sim" <?= (($dados_form['autorizacao_usuario'] ?? $perfil['autorizacao_usuario'] ?? '') === 'sim') ? 'selected' : '' ?>>Sim, autorizo</option>
                    <option value="nao" <?= (($dados_form['autorizacao_usuario'] ?? $perfil['autorizacao_usuario'] ?? '') === 'nao') ? 'selected' : '' ?>>Não autorizo</option>
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
                <button type="submit" class="submit-btn">Salvar</button>
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
    <script src="js/toast.js"></script>
    <script src="js/validacoes.js"></script>
    
    <script>
        // Garantir que o formulário seja submetido quando clicar em Salvar
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('perfilForm');
            const submitBtn = document.querySelector('.submit-btn');
            
            if (submitBtn && form) {
                // Event listener para o botão submit - usar mousedown para capturar antes do multi-step
                submitBtn.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                }, true);
                
                submitBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    // Verificar se está no último step (step 6 - Termo)
                    const currentStep = document.querySelector('.form-step.active');
                    const stepNumber = currentStep ? parseInt(currentStep.getAttribute('data-step')) : 0;
                    
                    // Se não estiver no último step, navegar até ele primeiro
                    if (stepNumber < 6) {
                        e.preventDefault();
                        
                        // Navegar até o último step
                        const allSteps = document.querySelectorAll('.form-step');
                        const lastStep = allSteps[allSteps.length - 1];
                        if (lastStep) {
                            document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
                            lastStep.classList.add('active');
                            
                            // Atualizar barra de progresso
                            document.querySelectorAll('.progress-bar .step').forEach((s, i) => {
                                s.classList.remove('active');
                                if (i === 5) s.classList.add('active');
                            });
                            
                            // Mostrar botão salvar e esconder próximo
                            submitBtn.style.display = '';
                            document.querySelectorAll('.btn-proximo').forEach(btn => btn.style.display = 'none');
                        }
                        return false;
                    }
                    
                    // Validar checkbox do termo ANTES de submeter
                    const aceitarTermo = document.getElementById('aceitar_termo');
                    if (!aceitarTermo || !aceitarTermo.checked) {
                        e.preventDefault();
                        alert('Você deve aceitar o termo de consentimento para continuar.');
                        aceitarTermo.focus();
                        return false;
                    }
                    
                    // Validar todos os campos obrigatórios antes de submeter
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        form.reportValidity();
                        return false;
                    }
                    
                    // Se tudo estiver OK, submeter o formulário programaticamente
                    form.submit();
                }, true);
                
                // Validação no submit do formulário
                form.addEventListener('submit', function(e) {
                    const aceitarTermo = document.getElementById('aceitar_termo');
                    if (aceitarTermo && !aceitarTermo.checked) {
                        e.preventDefault();
                        alert('Você deve aceitar o termo de consentimento para continuar.');
                        aceitarTermo.focus();
                        return false;
                    }
                });
            }
        });
    </script>
    
    <?php if ($editar && $perfil): ?>
    <script>
        // Preencher campos de arrays quando estiver editando
        document.addEventListener('DOMContentLoaded', function() {
            <?php 
            // Processar doenças crônicas
            if (!empty($perfil['doencas_cronicas'])) {
                $doencas_array = explode(',', $perfil['doencas_cronicas']);
                $doencas_array = array_map('trim', $doencas_array);
                echo "preencherArraySelect('doencas', " . json_encode($doencas_array) . ");\n";
            }
            
            // Processar alergias
            if (!empty($perfil['alergias'])) {
                $alergias_array = explode(';', $perfil['alergias']);
                $alergias_array = array_map('trim', $alergias_array);
                echo "preencherArraySelect('alergias', " . json_encode($alergias_array) . ");\n";
            }
            
            // Processar medicações
            if (!empty($perfil['medicacao_continua'])) {
                $medicacoes_array = explode(',', $perfil['medicacao_continua']);
                $medicacoes_array = array_map('trim', $medicacoes_array);
                echo "preencherArrayInput('medicacao', " . json_encode($medicacoes_array) . ");\n";
            }
            
            // Processar doenças mentais
            if (!empty($perfil['doenca_mental'])) {
                $doencas_mentais_array = explode(',', $perfil['doenca_mental']);
                $doencas_mentais_array = array_map('trim', $doencas_mentais_array);
                echo "preencherArraySelect('doenca_mental', " . json_encode($doencas_mentais_array) . ");\n";
            }
            
            // Processar dispositivos
            if (!empty($perfil['dispositivo_implantado'])) {
                $dispositivos_array = explode(',', $perfil['dispositivo_implantado']);
                $dispositivos_array = array_map('trim', $dispositivos_array);
                echo "preencherArraySelect('dispositivo', " . json_encode($dispositivos_array) . ");\n";
            }
            ?>
        });
        
        function preencherArraySelect(nomeCampo, valores) {
            const wrapper = document.getElementById(nomeCampo + '-wrapper');
            if (!wrapper || valores.length === 0) return;
            
            const selects = wrapper.querySelectorAll('select[name="' + nomeCampo + '[]"]');
            valores.forEach((valor, index) => {
                if (index < selects.length) {
                    selects[index].value = valor.toLowerCase().replace(/\s+/g, '_');
                } else {
                    // Adicionar novo campo se necessário
                    if (index === selects.length && selects.length > 0) {
                        // Clonar o último item e adicionar
                        const ultimoItem = selects[selects.length - 1].closest('.doenca-item, .alergia-item, .doenca-mental-item, .dispositivo-item');
                        if (ultimoItem) {
                            const novoItem = ultimoItem.cloneNode(true);
                            const novoSelect = novoItem.querySelector('select');
                            novoSelect.value = valor.toLowerCase().replace(/\s+/g, '_');
                            wrapper.appendChild(novoItem);
                        }
                    }
                }
            });
        }
        
        function preencherArrayInput(nomeCampo, valores) {
            const wrapper = document.getElementById(nomeCampo + 's-wrapper');
            if (!wrapper || valores.length === 0) return;
            
            const inputs = wrapper.querySelectorAll('input[name="' + nomeCampo + '[]"]');
            valores.forEach((valor, index) => {
                if (index < inputs.length) {
                    inputs[index].value = valor;
                } else {
                    // Adicionar novo campo se necessário
                    if (index === inputs.length && inputs.length > 0) {
                        const ultimoItem = inputs[inputs.length - 1].closest('.medicacao-item');
                        if (ultimoItem) {
                            const novoItem = ultimoItem.cloneNode(true);
                            const novoInput = novoItem.querySelector('input');
                            novoInput.value = valor;
                            wrapper.appendChild(novoItem);
                        }
                    }
                }
            });
        }
    </script>
    <?php endif; ?>
</body>

</html>