<?php
// painel_usuario.php
require_once 'config.php';

// 1. VALIDAÇÃO DE SEGURANÇA E SESSÃO
// Se não estiver logado, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Se for um administrador (Nível 3), redireciona para o painel correto
if ($_SESSION['nivel_acesso'] != 2) {
    header("Location: painel_admin.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$msg_sucesso = '';
$msg_erro = '';

// 2. VERIFICAÇÃO DE STATUS DO USUÁRIO (Ativo ou Bloqueado)
$stmt = $pdo->prepare("SELECT status_conta FROM usuarios WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario_atual = $stmt->fetch(PDO::FETCH_ASSOC);
$is_bloqueado = ($usuario_atual['status_conta'] === 'bloqueado');


// 3. PROCESSAMENTO DAS AÇÕES (POST)

// Ação: Alugar Livro
if (isset($_POST['acao_alugar'])) {
    if ($is_bloqueado) {
        $msg_erro = "Você está bloqueado pelo administrador e não pode realizar novos aluguéis.";
    } else {
        $id_livro = $_POST['id_livro'];
        
        // Registra o empréstimo com devolução prevista para daqui a 7 dias
        $stmt = $pdo->prepare("INSERT INTO emprestimos (id_usuario, id_livro, data_emprestimo, data_devolucao_prevista) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY))");
        $stmt->execute([$usuario_id, $id_livro]);
        
        // Altera o status do livro para alugado
        $stmt = $pdo->prepare("UPDATE livros SET status_disponibilidade = 'alugado' WHERE id = ?");
        $stmt->execute([$id_livro]);
        
        $msg_sucesso = "Livro alugado com sucesso! Retire na recepção.";
    }
}

// Ação: Reservar Livro
if (isset($_POST['acao_reservar'])) {
    $id_livro = $_POST['id_livro'];
    
    $stmt = $pdo->prepare("INSERT INTO reservas (id_usuario, id_livro, data_reserva) VALUES (?, ?, CURDATE())");
    $stmt->execute([$usuario_id, $id_livro]);
    
    $msg_sucesso = "Reserva realizada! Você será notificado assim que o livro estiver disponível.";
}

// Ação: Requisitar Novo Livro
if (isset($_POST['acao_requisitar'])) {
    $titulo = $_POST['titulo_livro'];
    $autor = $_POST['autor_livro'];
    $justificativa = $_POST['justificativa'];
    
    $stmt = $pdo->prepare("INSERT INTO requisicoes_novos (id_usuario, titulo_livro, autor_livro, justificativa) VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $titulo, $autor, $justificativa]);
    
    $msg_sucesso = "Sua sugestão de livro foi enviada com sucesso para análise!";
}

// Ação: Avaliar Livro Alugado
if (isset($_POST['acao_avaliar'])) {
    $id_livro = $_POST['id_livro'];
    $nota = $_POST['nota'];
    $comentario = $_POST['comentario'];
    
    $stmt = $pdo->prepare("INSERT INTO avaliacoes (id_usuario, id_livro, nota, comentario) VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $id_livro, $nota, $comentario]);
    
    $msg_sucesso = "Obrigado por avaliar este livro!";
}


// 4. CONSULTAS PARA EXIBIÇÃO DE DADOS
// Busca todos os livros do acervo
$stmt = $pdo->query("SELECT * FROM livros");
$livros_acervo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca os empréstimos ativos deste usuário (livros que ele está lendo no momento)
$stmt = $pdo->prepare("SELECT e.*, l.titulo, l.autor FROM emprestimos e JOIN livros l ON e.id_livro = l.id WHERE e.id_usuario = ? AND e.data_devolucao_real IS NULL");
$stmt->execute([$usuario_id]);
$meus_emprestimos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Usuário - Biblioteca A3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Biblioteca A3 - Painel do Usuário</a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3 text-white">Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
            <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <?php if (!empty($msg_sucesso)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $msg_sucesso ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($msg_erro)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $msg_erro ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($is_bloqueado): ?>
        <div class="alert alert-warning border-warning shadow-sm" role="alert">
            <h4 class="alert-heading">Atenção: Conta Bloqueada temporariamente!</h4>
            <p class="mb-0">Você possui pendências ou atrasos de devolução. A função de realizar novos aluguéis está desativada até que regularize a situação com o Administrador.</p>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Acervo da Biblioteca</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($livros_acervo as $livro): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($livro['titulo']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($livro['genero']) ?></small></td>
                                    <td><?= htmlspecialchars($livro['autor']) ?></td>
                                    <td>
                                        <?php if ($livro['status_disponibilidade'] == 'disponivel'): ?>
                                            <span class="badge bg-success">Disponível</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Alugado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="painel_usuario.php" style="display:inline;">
                                            <input type="hidden" name="id_livro" value="<?= $livro['id'] ?>">
                                            
                                            <?php if ($livro['status_disponibilidade'] == 'disponivel'): ?>
                                                <button type="submit" name="acao_alugar" class="btn btn-sm btn-success" <?= $is_bloqueado ? 'disabled' : '' ?>>Alugar</button>
                                            <?php else: ?>
                                                <button type="submit" name="acao_reservar" class="btn btn-sm btn-outline-primary">Reservar</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Livros Comigo (Escrever Avaliação)</h5>
                </div>
                <div class="card-body">
                    <?php if (count($meus_emprestimos) > 0): ?>
                        <?php foreach ($meus_emprestimos as $emp): ?>
                            <div class="p-3 mb-3 bg-white border rounded shadow-sm">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6><?= htmlspecialchars($emp['titulo']) ?></h6>
                                        <p class="text-muted small mb-0">Devolução prevista: <?= date('d/m/Y', strtotime($emp['data_devolucao_prevista'])) ?></p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button type="button" class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalAvaliar<?= $emp['id_livro'] ?>">
                                            Avaliar Livro
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="modalAvaliar<?= $emp['id_livro'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="painel_usuario.php">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Avaliar: <?= htmlspecialchars($emp['titulo']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id_livro" value="<?= $emp['id_livro'] ?>">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Sua Nota (1 a 5 estrelas)</label>
                                                    <select class="form-select" name="nota" required>
                                                        <option value="5">⭐⭐⭐⭐⭐ (Excelente)</option>
                                                        <option value="4">⭐⭐⭐⭐ (Muito Bom)</option>
                                                        <option value="3">⭐⭐⭐ (Regular)</option>
                                                        <option value="2">⭐⭐ (Ruim)</option>
                                                        <option value="1">⭐ (Péssimo)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Comentário/Resenha</label>
                                                    <textarea class="form-control" name="comentario" rows="3" placeholder="O que você achou da história?" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" name="acao_avaliar" class="btn btn-warning">Enviar Avaliação</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted my-3">Você não possui nenhum empréstimo ativo no momento.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Requisitar Novo Livro</h5>
                </div>
                <div class="card-body">
                    <p class="card-text small text-muted">Não encontrou o livro que queria no nosso acervo? Faça uma requisição para que a coordenação compre o título.</p>
                    <form method="POST" action="painel_usuario.php">
                        <div class="mb-3">
                            <label for="titulo_livro" class="form-label">Título do Livro</label>
                            <input type="text" class="form-control" id="titulo_livro" name="titulo_livro" required>
                        </div>
                        <div class="mb-3">
                            <label for="autor_livro" class="form-label">Autor</label>
                            <input type="text" class="form-control" id="autor_livro" name="autor_livro" required>
                        </div>
                        <div class="mb-3">
                            <label for="justificativa" class="form-label">Justificativa da Indicação</label>
                            <textarea class="form-control" id="justificativa" name="justificativa" rows="4" placeholder="Ex: Este livro é fundamental para a disciplina X..." required></textarea>
                        </div>
                        <button type="submit" name="acao_requisitar" class="btn btn-info text-white w-100">Enviar Requisição</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>