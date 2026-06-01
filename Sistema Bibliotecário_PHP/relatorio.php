<?php
// relatorio.php
require_once 'config.php';

// 1. VALIDAÇÃO DE SEGURANÇA E SESSÃO ADMIN (Nível 3)
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 3) {
    header("Location: login.php");
    exit;
}

// 2. CONSOLIDADO DE DADOS (Métricas Gerais via Agregações SQL)
// Total de Livros
$total_livros = $pdo->query("SELECT COUNT(*) FROM livros")->fetchColumn();

// Total de Livros Atualmente Alugados
$total_alugados = $pdo->query("SELECT COUNT(*) FROM livros WHERE status_disponibilidade = 'alugado'")->fetchColumn();

// Total de Usuários Cadastrados
$total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE nivel_acesso = 2")->fetchColumn();

// Total de Usuários Bloqueados
$total_bloqueados = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE status_conta = 'bloqueado'")->fetchColumn();


// 3. QUERY AVANÇADA: Cruzamento de 3 Tabelas (Empréstimos Ativos e cálculo de atraso)
$sql_detalhado = "
    SELECT 
        e.id,
        u.nome AS nome_usuario,
        u.email AS email_usuario,
        l.titulo AS titulo_livro,
        l.autor AS autor_livro,
        e.data_emprestimo,
        e.data_devolucao_prevista,
        DATEDIFF(CURDATE(), e.data_devolucao_prevista) AS dias_atraso
    FROM emprestimos e
    JOIN usuarios u ON e.id_usuario = u.id
    JOIN livros l ON e.id_livro = l.id
    WHERE e.data_devolucao_real IS NULL
    ORDER BY e.data_devolucao_prevista ASC
";
$stmt = $pdo->query($sql_detalhado);
$emprestimos_ativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Gerencial Consolidado - Biblioteca A3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Estilos específicos para a otimização da emissão do PDF */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #fff;
                color: #000;
                font-size: 12pt;
            }
            .card {
                border: 1px solid #ccc !important;
                box-shadow: none !important;
            }
            .table th {
                background-color: #f2f2f2 !important;
                color: #000 !important;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="bg-dark text-white p-3 mb-4 no-print shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Visualização de Relatório</h5>
            <small class="text-muted">Pronto para exportação em PDF</small>
        </div>
        <div>
            <a href="painel_admin.php" class="btn btn-outline-light btn-sm me-2">Voltar ao Painel</a>
            <button onclick="window.print();" class="btn btn-warning btn-sm fw-bold text-dark">Imprimir / Guardar como PDF</button>
        </div>
    </div>
</div>

<div class="container my-4">
    <div class="text-center mb-5 border-bottom pb-3">
        <h2>SISTEMA DE GESTÃO DE BIBLIOTECA - A3</h2>
        <p class="text-muted mb-0">Relatório Gerencial Consolidado de Ocupação e Pendências</p>
        <small class="text-secondary">Gerado em: <?= date('d/m/Y H:i:s') ?> | Operador: <?= htmlspecialchars($_SESSION['usuario_nome']) ?></small>
    </div>

    <h4 class="mb-3 text-uppercase text-secondary" style="font-size: 14pt; letter-spacing: 1px;">1. Indicadores Consolidados do Sistema</h4>
    <div class="row text-center mb-5">
        <div class="col-3">
            <div class="card p-3 bg-white shadow-sm">
                <span class="text-muted small text-uppercase fw-bold">Total do Acervo</span>
                <h2 class="display-6 fw-bold text-primary mt-2"><?= $total_livros ?></h2>
                <small class="text-muted">livros cadastrados</small>
            </div>
        </div>
        <div class="col-3">
            <div class="card p-3 bg-white shadow-sm">
                <span class="text-muted small text-uppercase fw-bold">Taxa de Ocupação</span>
                <h2 class="display-6 fw-bold text-warning mt-2"><?= $total_alugados ?></h2>
                <small class="text-muted">livros alugados hoje</small>
            </div>
        </div>
        <div class="col-3">
            <div class="card p-3 bg-white shadow-sm">
                <span class="text-muted small text-uppercase fw-bold">Leitores Ativos</span>
                <h2 class="display-6 fw-bold text-success mt-2"><?= $total_usuarios ?></h2>
                <small class="text-muted">perfis de estudantes</small>
            </div>
        </div>
        <div class="col-3">
            <div class="card p-3 bg-white shadow-sm">
                <span class="text-muted small text-uppercase fw-bold">Bloqueados</span>
                <h2 class="display-6 fw-bold text-danger mt-2"><?= $total_bloqueados ?></h2>
                <small class="text-muted">com restrição de aluguer</small>
            </div>
        </div>
    </div>

    <h4 class="mb-3 text-uppercase text-secondary" style="font-size: 14pt; letter-spacing: 1px;">2. Detalhamento de Empréstimos e Prazos Pendentes</h4>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-bordered mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 25%;">Livro / Autor</th>
                        <th style="width: 25%;">Utilizador Solicitante</th>
                        <th class="text-center" style="width: 15%;">Data Empréstimo</th>
                        <th class="text-center" style="width: 15%;">Prazo Limite</th>
                        <th class="text-center" style="width: 20%;">Situação / Alerta</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($emprestimos_ativos) > 0): ?>
                        <?php foreach ($emprestimos_ativos as $emp): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($emp['titulo_livro']) ?></strong><br>
                                    <small class="text-muted">Autor: <?= htmlspecialchars($emp['autor_livro']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($emp['nome_usuario']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($emp['email_usuario']) ?></small>
                                </td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($emp['data_emprestimo'])) ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($emp['data_devolucao_prevista'])) ?></td>
                                <td class="text-center">
                                    <?php if ($emp['dias_atraso'] > 0): ?>
                                        <span class="text-danger fw-bold">⚠️ ATRASADO (<?= $emp['dias_atraso'] ?> dias)</span>
                                    <?php else: ?>
                                        <span class="text-success">✓ Regular (Dentro do prazo)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-4 text-muted">Não existem livros alugados ou pendências no momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-5 pt-5 text-center">
        <div class="col-6 mx-auto border-top pt-2">
            <p class="mb-0 small text-uppercase fw-bold">Coordenação de Controle de Acervo</p>
            <p class="text-muted small">Biblioteca Universitária - Trabalho A3 PHP</p>
        </div>
    </div>
</div>

</body>
</html>