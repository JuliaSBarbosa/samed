<?php
require_once 'verificar_login.php';
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
            <a href="dependentes.php">DEPENDENTES</a>
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
            </div>

            <form id="perfilForm" action="salvar_perfil.php" method="post" >
                <!-- Etapa 1: Informações Básicas -->
                <div class="form-step active" id="step1" data-step="1">
                    <h2>Informações básicas</h2>
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" required>

                    <label for="data_nascimento">Data de nascimento</label>
                    <input type="date" id="data_nascimento" name="data_nascimento" required>

                    <label for="sexo">Sexo</label>
                    <select id="sexo" name="sexo" required>
                        <option value="">Selecione</option>
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                        <option value="outro">Outro</option>
                    </select>

                    <label for="cpf">CPF</label>
                    <input type="text" id="cpf" name="cpf">

                    <label for="telefone">Telefone</label>
                    <input type="tel" id="telefone" name="telefone">

                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email">
                </div>

                <!-- Etapa 2: Contato de Emergência -->
                <div class="form-step" id="step2" data-step="2">
                        <h2>CONTATO DE EMERGÊNCIA</h2>

                        <label for="contato_nome">Nome do contato</label>
                        <input type="text" id="contato_nome" name="contato_nome" required>

                        <label for="parentesco">Parentesco</label>
                        <input type="text" id="parentesco" name="parentesco">

                        <label for="contato_telefone">Telefone</label>
                        <input type="tel" id="contato_telefone" name="contato_telefone" required>
                    
                </div>

                <!-- Etapa 3: Informações Médicas -->
                <div class="form-step" id="step3" data-step="3">
                        <h2>INFORMAÇÕES MÉDICAS</h2>

                        <label for="doencas">Doenças crônicas</label>
                        <textarea id="doencas" name="doencas" rows="3"></textarea>

                        <label for="alergias">Alergias</label>
                        <textarea id="alergias" name="alergias" rows="2"></textarea>

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
                        </select>

                        <label for="medicacao">Medicação de uso contínuo</label>
                        <textarea id="medicacao" name="medicacao" rows="2"></textarea>

                        <label for="doenca_mental">Doença mental</label>
                        <textarea id="doenca_mental" name="doenca_mental" rows="2"></textarea>

                        <label for="dispositivo">Dispositivo implantado</label>
                        <textarea id="dispositivo" name="dispositivo" rows="2"></textarea>

                        <label for="info_relevantes">Informações relevantes</label>
                        <textarea id="info_relevantes" name="info_relevantes" rows="3"></textarea>
                
                </div>

                <!-- Etapa 4: Histórico Médico -->
                <div class="form-step" id="step4" data-step="4">
                        <h2>HISTÓRICO MÉDICO</h2>

                        <label for="cirurgias">Cirurgias</label>
                        <textarea id="cirurgias" name="cirurgias" rows="4"></textarea>
                  
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
</body>

</html>
