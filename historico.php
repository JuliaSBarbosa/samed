<?php
require_once 'verificar_login.php';

// --- SIMULAÇÃO DE DADOS (Substituir pela busca REAL no banco de dados) ---
$historico_acessos = [
    ['nome' => 'Dr. João Silva', 'registro' => 'CRM-SP 123456', 'data_hora' => '2025-10-25 09:30:00', 'tipo' => 'Médico', 'paciente_consultado' => htmlspecialchars($_SESSION['usuario_nome'])],
    ['nome' => 'Enf. Maria Oliveira', 'registro' => 'COREN-SP 98765', 'data_hora' => '2025-10-25 14:00:00', 'tipo' => 'Enfermeiro(a)', 'paciente_consultado' => 'Pedro Oliveira (Dependente)'],
    ['nome' => 'Téc. Paulo Santos', 'registro' => 'COREN-SP 32165', 'data_hora' => '2025-10-26 11:45:00', 'tipo' => 'Técnico de Enfermagem', 'paciente_consultado' => htmlspecialchars($_SESSION['usuario_nome'])],
    ['nome' => 'Dr. Ana Costa', 'registro' => 'CRM-SP 654321', 'data_hora' => '2025-11-01 19:15:00', 'tipo' => 'Médico', 'paciente_consultado' => 'Sofia Pereira (Dependente)'],
    ['nome' => 'Soc. Anderson Barbosa', 'registro' => 'COREN-SP 12345', 'data_hora' => '2025-11-02 08:00:00', 'tipo' => 'Acesso de Emergência', 'paciente_consultado' => htmlspecialchars($_SESSION['usuario_nome'])],
    ['nome' => 'Dr. Lucas Pereira', 'registro' => 'CRM-SP 778899', 'data_hora' => '2025-11-03 10:20:00', 'tipo' => 'Médico', 'paciente_consultado' => 'Pedro Oliveira (Dependente)'],
];

// --- LÓGICA DE FILTRAGEM ---
$data_inicio = $_GET['data_inicio'] ?? null;
$data_fim = $_GET['data_fim'] ?? null;
$paciente = $_GET['paciente'] ?? null;
$historico_filtrado = $historico_acessos;

if ($data_inicio || $data_fim || $paciente) {
    $historico_filtrado = array_filter($historico_acessos, function($acesso) use ($data_inicio, $data_fim, $paciente) {

        $data_acesso = date('Y-m-d', strtotime($acesso['data_hora']));
        $ok_inicio = true;
        $ok_fim = true;
        $ok_paciente = true;

        if ($data_inicio) {
            $ok_inicio = ($data_acesso >= $data_inicio);
        }

        if ($data_fim) {
            $ok_fim = ($data_acesso <= $data_fim);
        }

        if ($paciente) {
            $ok_paciente = ($acesso['paciente_consultado'] === $paciente);
        }

        return $ok_inicio && $ok_fim && $ok_paciente;
    });
}


// Para manter os dados de filtro no formulário
$valor_inicio = htmlspecialchars($data_inicio ?? '');
$valor_fim = htmlspecialchars($data_fim ?? '');

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Histórico de Acessos</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.svg" type="image/png">
    <style>


    </style>
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
            <a href="dependentes.php">DEPENDENTES</a>
            <span class="divisor">|</span>
            <a href="dependentes.php" class="ativo">HISTÓRICO</a>
            <span class="divisor">|</span>
            <a href="hospital.php">UNIDADES DE SAÚDE</a>
        </nav>

        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <!-- Conteúdo principal -->
    <main>

        <section class="secao-historico">

            <h2>VISUALIZAÇÃO DE CONSULTAS AOS DADOS MÉDICOS</h2>
            <hr>

            <!-- Formulário de Filtro -->
            <form method="GET" class="filtro-historico">

                <div class="campo-filtro">
                    <label for="data_inicio">Data Início:</label>
                    <input type="date" id="data_inicio" name="data_inicio" value="<?= $valor_inicio ?>">
                </div>

                <div class="campo-filtro">
                    <label for="data_fim">Data Fim:</label>
                    <input type="date" id="data_fim" name="data_fim" value="<?= $valor_fim ?>">
                </div>

                <div class="campo-filtro">
                    <label for="paciente">Paciente:</label>
                    <select name="paciente" id="paciente">
                        <option value="">Todos</option>

                        <?php
                        // Gera lista única de pacientes dinamicamente
                        $pacientes = array_unique(array_column($historico_acessos, 'paciente_consultado'));
                        foreach ($pacientes as $p):
                            $p_esc = htmlspecialchars($p);
                            $selecionado = (isset($_GET['paciente']) && $_GET['paciente'] === $p) ? 'selected' : '';
                            ?>
                            <option value="<?= $p_esc ?>" <?= $selecionado ?>><?= $p_esc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-filtrar">FILTRAR</button>
            </form>

            <!-- Tabela de Histórico -->
            <?php if (count($historico_filtrado) > 0): ?>
                <table class="tabela-historico">
                    <thead>
                        <tr>
                            <th>Paciente Consultado</th>
                            <th>Data e Hora</th>
                            <th>Profissional</th>
                            <th>Registro Profissional</th>
                            <th>Tipo de Acesso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico_filtrado as $acesso): ?>
                            <tr>
                                <td><?= htmlspecialchars($acesso['paciente_consultado']); ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($acesso['data_hora'])); ?></td>
                                <td><?= htmlspecialchars($acesso['nome']); ?></td>
                                <td><?= htmlspecialchars($acesso['registro']); ?></td>
                                <td><?= htmlspecialchars($acesso['tipo']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="mensagem-vazio">
                    Nenhum registro de acesso encontrado para o período selecionado.
                </div>
            <?php endif; ?>

        </section>


    </main>

    <!-- Rodapé -->
    <footer>
        <div class="footer-logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <p>DESENVOLVIDO POR GRUPO AINDA SEM NOME.</p>
        <div class="lojas">
            <img src="img/appstore.png" alt="App Store">
            <img src="img/googleplay.png" alt="Google Play">
        </div>
    </footer>
</body>

</html>