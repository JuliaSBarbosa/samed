<?php
require_once 'verificar_login.php';
require_once 'config.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$historico_acessos = [];

// Buscar histórico de acessos do banco de dados
if ($pdo && $usuario_id) {
    try {
        // Buscar acessos ao próprio paciente e seus dependentes
        // Inclui tanto profissionais quanto pacientes comuns que visualizaram
        // IMPORTANTE: Busca tanto acessos diretos ao paciente quanto aos seus dependentes
        $stmt = $pdo->prepare("
            SELECT 
                ha.*,
                u_prof.nome as nome_profissional,
                u_prof.tipo as tipo_profissional,
                u_prof.crm,
                u_prof.coren,
                u_pac.nome as nome_paciente,
                d.nome as nome_dependente,
                d.paciente_id as dependente_paciente_id,
                ha.registro_profissional,
                ha.tipo_acesso
            FROM historico_acessos ha
            INNER JOIN usuarios u_prof ON ha.profissional_id = u_prof.id
            LEFT JOIN usuarios u_pac ON ha.paciente_id = u_pac.id
            LEFT JOIN dependentes d ON ha.dependente_id = d.id
            WHERE (ha.paciente_id = ? OR (ha.dependente_id IS NOT NULL AND d.paciente_id = ?))
            ORDER BY ha.data_hora DESC
        ");
        $stmt->execute([$usuario_id, $usuario_id]);
        $acessos = $stmt->fetchAll();
        
        foreach ($acessos as $acesso) {
            $tipo_prof = '';
            $registro = '';
            
            // Determinar tipo baseado no tipo de usuário e tipo de acesso
            if ($acesso['tipo_profissional'] === 'medico') {
                $tipo_prof = 'Médico';
                $registro = $acesso['crm'] ?? $acesso['registro_profissional'] ?? '';
            } elseif ($acesso['tipo_profissional'] === 'enfermeiro') {
                $tipo_prof = 'Enfermeiro(a)';
                $registro = $acesso['coren'] ?? $acesso['registro_profissional'] ?? '';
            } elseif ($acesso['tipo_profissional'] === 'paciente') {
                $tipo_prof = 'Usuário Comum';
                $registro = '';
            } else {
                $tipo_prof = 'Profissional de Saúde';
                $registro = $acesso['registro_profissional'] ?? '';
            }
            
            // Usar tipo_acesso se disponível
            if (!empty($acesso['tipo_acesso']) && $acesso['tipo_acesso'] !== 'Consulta') {
                $tipo_prof = $acesso['tipo_acesso'];
            }
            
            $paciente_nome = '';
            if ($acesso['dependente_id']) {
                $paciente_nome = $acesso['nome_dependente'] . ' (Dependente)';
            } else {
                $paciente_nome = $acesso['nome_paciente'];
            }
            
            $historico_acessos[] = [
                'nome' => $acesso['nome_profissional'],
                'registro' => $registro,
                'data_hora' => $acesso['data_hora'],
                'tipo' => $tipo_prof,
                'paciente_consultado' => $paciente_nome
            ];
        }
    } catch(PDOException $e) {
        // Erro ao buscar dados
        error_log("Erro ao buscar histórico: " . $e->getMessage());
    }
}

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
            <?php if (in_array($_SESSION['usuario_tipo'] ?? '', ['paciente', 'medico', 'enfermeiro'])): ?>
            <a href="dependentes.php">DEPENDENTES</a>
            <span class="divisor">|</span>
            <?php endif; ?>
            <a href="historico.php" class="ativo">HISTÓRICO</a>
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
    <main>

        <section class="secao-historico">

            <h2>VISUALIZAÇÃO DE CONSULTAS AOS DADOS MÉDICOS</h2>
            <hr>

            <!-- Formulário de Filtro -->
            <form method="GET" class="filtro-historico">
                <div class="filtro-header">
                    <h3>Filtros de Busca</h3>
                    <?php if ($data_inicio || $data_fim || $paciente): ?>
                        <a href="historico.php" class="btn-limpar-filtros">Limpar Filtros</a>
                    <?php endif; ?>
                </div>

                <div class="filtro-grid">
                    <div class="campo-filtro">
                        <label for="data_inicio">
                            <span class="filtro-icon">📅</span>
                            Data Início
                        </label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?= $valor_inicio ?>" class="filtro-input">
                    </div>

                    <div class="campo-filtro">
                        <label for="data_fim">
                            <span class="filtro-icon">📅</span>
                            Data Fim
                        </label>
                        <input type="date" id="data_fim" name="data_fim" value="<?= $valor_fim ?>" class="filtro-input">
                    </div>

                    <div class="campo-filtro">
                        <label for="paciente">
                            <span class="filtro-icon">👤</span>
                            Paciente
                        </label>
                        <select name="paciente" id="paciente" class="filtro-select">
                            <option value="">Todos os pacientes</option>
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
                </div>

                <div class="filtro-actions-historico">
                    <button type="submit" class="btn-filtrar">
                        <span>🔍</span>
                        Filtrar
                    </button>
                </div>
            </form>

            <!-- Contador de Resultados -->
            <?php if (count($historico_filtrado) > 0): ?>
                <div class="contador-resultados">
                    <strong><?= count($historico_filtrado) ?></strong> 
                    <?= count($historico_filtrado) == 1 ? 'registro encontrado' : 'registros encontrados' ?>
                </div>
            <?php endif; ?>

            <!-- Tabela de Histórico -->
            <?php if (count($historico_filtrado) > 0): ?>
                <div class="tabela-wrapper">
                    <table class="tabela-historico">
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Data e Hora</th>
                                <th>Profissional</th>
                                <th>Registro</th>
                                <th>Tipo de Acesso</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico_filtrado as $acesso): 
                                // Determina a classe do badge baseado no tipo
                                $tipo_class = '';
                                $tipo_icon = '';
                                if (strpos($acesso['tipo'], 'Médico') !== false) {
                                    $tipo_class = 'badge-medico';
                                    $tipo_icon = '👨‍⚕️';
                                } elseif (strpos($acesso['tipo'], 'Enfermeiro') !== false) {
                                    $tipo_class = 'badge-enfermeiro';
                                    $tipo_icon = '👩‍⚕️';
                                } elseif (strpos($acesso['tipo'], 'Técnico') !== false) {
                                    $tipo_class = 'badge-tecnico';
                                    $tipo_icon = '💉';
                                } elseif (strpos($acesso['tipo'], 'Emergência') !== false) {
                                    $tipo_class = 'badge-emergencia';
                                    $tipo_icon = '🚨';
                                } else {
                                    $tipo_class = 'badge-outro';
                                    $tipo_icon = '📋';
                                }
                                
                                // Verifica se é dependente
                                $is_dependente = strpos($acesso['paciente_consultado'], '(Dependente)') !== false;
                            ?>
                                <tr>
                                    <td>
                                        <div class="paciente-info">
                                            <?php if ($is_dependente): ?>
                                                <span class="badge-dependente">👶 Dependente</span>
                                            <?php else: ?>
                                                <span class="badge-titular">👤 Titular</span>
                                            <?php endif; ?>
                                            <span class="paciente-nome"><?= htmlspecialchars($acesso['paciente_consultado']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="data-hora">
                                            <span class="data"><?= date('d/m/Y', strtotime($acesso['data_hora'])); ?></span>
                                            <span class="hora"><?= date('H:i:s', strtotime($acesso['data_hora'])); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="profissional-info">
                                            <strong><?= htmlspecialchars($acesso['nome']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="registro"><?= htmlspecialchars($acesso['registro']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge-tipo <?= $tipo_class ?>">
                                            <?= $tipo_icon ?> <?= htmlspecialchars($acesso['tipo']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="mensagem-vazio">
                    <div class="mensagem-icon">📋</div>
                    <h3>Nenhum registro encontrado</h3>
                    <p>Não há registros de acesso para o período ou filtros selecionados.</p>
                    <?php if ($data_inicio || $data_fim || $paciente): ?>
                        <a href="historico.php" class="btn-limpar-filtros-inline">Limpar filtros e ver todos</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

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
</body>

</html>