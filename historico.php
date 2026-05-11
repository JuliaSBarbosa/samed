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
        $tem_tabela_denuncias = false;
        try {
            $check = $pdo->query("SHOW TABLES LIKE 'denuncias_acessos'");
            $tem_tabela_denuncias = $check && $check->fetch() !== false;
        } catch (PDOException $e) {
            $tem_tabela_denuncias = false;
        }

        if ($tem_tabela_denuncias) {
            $stmt = $pdo->prepare("
                SELECT 
                    ha.id AS historico_id,
                    ha.profissional_id,
                    ha.paciente_id,
                    ha.dependente_id,
                    ha.data_hora,
                    ha.registro_profissional,
                    ha.tipo_acesso,
                    u_prof.nome as nome_profissional,
                    u_prof.tipo as tipo_profissional,
                    u_prof.crm,
                    u_prof.coren,
                    u_pac.nome as nome_paciente,
                    d.nome as nome_dependente,
                    d.paciente_id as dependente_paciente_id,
                    da.id AS denuncia_id,
                    da.status AS denuncia_status,
                    da.data_denuncia AS denuncia_data
                FROM historico_acessos ha
                INNER JOIN usuarios u_prof ON ha.profissional_id = u_prof.id
                LEFT JOIN usuarios u_pac ON ha.paciente_id = u_pac.id
                LEFT JOIN dependentes d ON ha.dependente_id = d.id
                LEFT JOIN denuncias_acessos da
                    ON da.historico_acesso_id = ha.id AND da.denunciante_id = ?
                WHERE (ha.paciente_id = ? OR (ha.dependente_id IS NOT NULL AND d.paciente_id = ?))
                ORDER BY ha.data_hora DESC
            ");
            $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        } else {
            $stmt = $pdo->prepare("
                SELECT 
                    ha.id AS historico_id,
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
        }
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
                'historico_id' => $acesso['historico_id'] ?? null,
                'nome' => $acesso['nome_profissional'],
                'registro' => $registro,
                'data_hora' => $acesso['data_hora'],
                'tipo' => $tipo_prof,
                'paciente_consultado' => $paciente_nome,
                'denuncia_id' => $acesso['denuncia_id'] ?? null,
                'denuncia_status' => $acesso['denuncia_status'] ?? null,
                'denuncia_data' => $acesso['denuncia_data'] ?? null
            ];
        }
    } catch(PDOException $e) {
        // Erro ao buscar dados
        error_log("Erro ao buscar histórico: " . $e->getMessage());
        $_SESSION['erro'] = "Não foi possível carregar o histórico de acessos. Por favor, tente novamente.";
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
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="mensagem-erro"><?= htmlspecialchars($_SESSION['erro']) ?></div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['sucesso'])): ?>
            <div class="mensagem-sucesso"><?= htmlspecialchars($_SESSION['sucesso']) ?></div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>

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
                                <th>Ação</th>
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
                                    <td class="td-acao-denuncia">
                                        <?php if (!empty($acesso['denuncia_id'])): ?>
                                            <span class="badge-denunciado" title="Denunciado em <?= date('d/m/Y H:i', strtotime($acesso['denuncia_data'])) ?> — status: <?= htmlspecialchars($acesso['denuncia_status'] ?? 'pendente') ?>">
                                                🚩 Denunciado
                                            </span>
                                        <?php elseif (!empty($acesso['historico_id'])): ?>
                                            <button type="button" class="btn-denunciar"
                                                data-historico-id="<?= (int) $acesso['historico_id'] ?>"
                                                data-paciente="<?= htmlspecialchars($acesso['paciente_consultado']) ?>"
                                                data-data="<?= date('d/m/Y H:i', strtotime($acesso['data_hora'])) ?>"
                                                data-profissional="<?= htmlspecialchars($acesso['nome']) ?>">
                                                🚩 Denunciar
                                            </button>
                                        <?php else: ?>
                                            <span class="acao-indisponivel">—</span>
                                        <?php endif; ?>
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

        <!-- Modal de Denúncia -->
        <div class="modal-denuncia" id="modalDenuncia" aria-hidden="true">
            <div class="modal-denuncia-overlay" data-fechar-modal></div>
            <div class="modal-denuncia-card" role="dialog" aria-modal="true" aria-labelledby="modalDenunciaTitulo">
                <div class="modal-denuncia-header">
                    <h3 id="modalDenunciaTitulo">🚩 Denunciar consulta</h3>
                    <button type="button" class="modal-denuncia-fechar" data-fechar-modal aria-label="Fechar">×</button>
                </div>
                <form method="POST" action="denunciar_acesso.php" class="modal-denuncia-form" id="formDenuncia">
                    <input type="hidden" name="historico_acesso_id" id="denunciaHistoricoId">

                    <div class="modal-denuncia-info">
                        <p><strong>Paciente:</strong> <span id="denunciaPaciente">—</span></p>
                        <p><strong>Data:</strong> <span id="denunciaData">—</span></p>
                        <p><strong>Profissional:</strong> <span id="denunciaProfissional">—</span></p>
                    </div>

                    <label for="denunciaMotivo">Motivo da denúncia *</label>
                    <select name="motivo" id="denunciaMotivo" required>
                        <option value="">Selecione um motivo</option>
                        <option value="acesso_indevido">Acesso indevido / sem autorização</option>
                        <option value="dados_incorretos">Dados incorretos no histórico</option>
                        <option value="profissional_desconhecido">Não reconheço este profissional</option>
                        <option value="outro">Outro</option>
                    </select>

                    <label for="denunciaDescricao">Descrição *</label>
                    <textarea name="descricao" id="denunciaDescricao" rows="4" required minlength="10"
                              placeholder="Descreva o que aconteceu (mínimo 10 caracteres)"></textarea>
                    <small class="modal-denuncia-hint">Sua denúncia será analisada pela equipe SAMED. Não compartilharemos seu nome com o profissional.</small>

                    <div class="modal-denuncia-acoes">
                        <button type="button" class="btn-denuncia-cancelar" data-fechar-modal>Cancelar</button>
                        <button type="submit" class="btn-denuncia-enviar">Enviar denúncia</button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <!-- Rodapé -->
    <footer>
        <div class="footer-logo">
            <img src="img/logo-branco.png" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <p>&copy; 2025 Grupo SAMED. Todos os direitos reservados.</p>
    </footer>

    <script>
        (function () {
            var modal = document.getElementById('modalDenuncia');
            if (!modal) return;
            var inputId = document.getElementById('denunciaHistoricoId');
            var elPac = document.getElementById('denunciaPaciente');
            var elData = document.getElementById('denunciaData');
            var elProf = document.getElementById('denunciaProfissional');
            var form = document.getElementById('formDenuncia');

            function abrir(btn) {
                inputId.value = btn.getAttribute('data-historico-id') || '';
                elPac.textContent = btn.getAttribute('data-paciente') || '—';
                elData.textContent = btn.getAttribute('data-data') || '—';
                elProf.textContent = btn.getAttribute('data-profissional') || '—';
                if (form) form.reset();
                inputId.value = btn.getAttribute('data-historico-id') || '';
                modal.classList.add('aberto');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function fechar() {
                modal.classList.remove('aberto');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            document.querySelectorAll('.btn-denunciar').forEach(function (b) {
                b.addEventListener('click', function () { abrir(b); });
            });

            modal.querySelectorAll('[data-fechar-modal]').forEach(function (el) {
                el.addEventListener('click', fechar);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal.classList.contains('aberto')) fechar();
            });
        })();
    </script>
</body>

</html>