<?php
// painel_admin.php
require_once 'config.php';

// 1. VALIDAÇÃO DE SEGURANÇA E SESSÃO ADMIN
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel_acesso'] != 3) {
    // Se não for admin, destrói e manda pro login
    header("Location: login.php");
    exit;
}

$msg_sucesso = '';
$msg_erro = '';

// 2. PROCESSAMENTO DAS AÇÕES (CRUD E GESTÃO)

// Ação: Adicionar Novo Livro (Create)
if (isset($_POST['acao_adicionar_livro'])) {
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $genero = $_POST['genero'];
    
    $stmt = $pdo->prepare("INSERT INTO livros (titulo, autor, genero, status_disponibilidade) VALUES (?, ?, ?, 'disponivel')");
    if ($stmt->execute([$titulo, $autor, $genero])) {
        $msg_sucesso = "Livro adicionado ao acervo com sucesso!";
    }
}

// Ação: Excluir Livro (Delete)
if (isset($_POST['acao_excluir_livro'])) {
    $id_livro = $_POST['id_livro'];
    $stmt = $pdo->prepare("DELETE FROM livros WHERE id = ?");
    $stmt->execute([$id_livro]);
    $msg_sucesso = "Livro removido do sistema.";
}

// Ação: Alternar Status do Usuário (Bloquear/Desbloquear)
if (isset($_POST['acao_status_usuario'])) {
    $id_usuario = $_POST['id_usuario'];
    $novo_status = $_POST['novo_status'];
    
    $stmt = $pdo->prepare("UPDATE usuarios SET status_conta = ? WHERE id = ?");
    $stmt->execute([$novo_status, $id_usuario]);
    $msg_sucesso = "Status do usuário atualizado para: " . strtoupper($novo_status);
}

// Ação: Excluir Usuário
if (isset($_POST['acao_excluir_usuario'])) {
    $id_usuario = $_POST['id_usuario'];
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND nivel_acesso != 3");
    $stmt->execute([$id_usuario]);
    $msg_sucesso = "Usuário excluído com sucesso.";
}

// 3. CONSULTAS PARA EXIBIÇÃO NO PAINEL
// Listar Livros
$stmt = $pdo->query("SELECT * FROM livros ORDER BY id DESC");
$livros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Listar Usuários Comuns (Não exibe administradores)
$stmt = $pdo->query("SELECT id, nome, email, status_conta FROM usuarios WHERE nivel_acesso = 2");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Listar Requisições de Novos Livros
$stmt = $pdo->query("SELECT r.*, u.nome FROM requisicoes_novos r JOIN usuarios u ON r.id_usuario = u.id ORDER BY r.data_requisicao DESC");
$requisicoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Biblioteca A3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <div class="container">
        <a class="navbar-brand" href="#">Biblioteca A3 - Admin</a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text me-3 text-white">Administrador(a): <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
            <a href="relatorio.php" class="btn btn-warning btn-sm me-2 text-dark fw-bold">Gerar Relatório (PDF)</a>
            <a href="logout.php" class="btn btn-dark btn-sm">Sair</a>
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

    <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#livros">Gestão de Livros</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#usuarios">Gestão de Usuários</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#requisicoes">Requisições de Títulos</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="livros">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">Cadastrar Novo Livro</div>
                <div class="card-body">
                    <form method="POST" action="painel_admin.php" class="row g-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="titulo" placeholder="Título do Livro" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="autor" placeholder="Autor" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="genero" placeholder="Gênero" required>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" name="acao_adicionar_livro" class="btn btn-success w-100">+</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Autor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($livros as $livro): ?>
                                <tr>
                                    <td><?= $livro['id'] ?></td>
                                    <td><?= htmlspecialchars($livro['titulo']) ?></td>
                                    <td><?= htmlspecialchars($livro['autor']) ?></td>
                                    <td><?= $livro['status_disponibilidade'] ?></td>
                                    <td>
                                        <form method="POST" action="painel_admin.php" onsubmit="return confirm('Tem certeza que deseja excluir este livro?');">
                                            <input type="hidden" name="id_livro" value="<?= $livro['id'] ?>">
                                            <button type="submit" name="acao_excluir_livro" class="btn btn-sm btn-danger">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="usuarios">
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Status Atual</th>
                                <th>Bloquear/Desbloquear</th>
                                <th>Excluir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['nome']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php if ($user['status_conta'] == 'ativo'): ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bloqueado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="painel_admin.php">
                                            <input type="hidden" name="id_usuario" value="<?= $user['id'] ?>">
                                            <?php if ($user['status_conta'] == 'ativo'): ?>
                                                <input type="hidden" name="novo_status" value="bloqueado">
                                                <button type="submit" name="acao_status_usuario" class="btn btn-sm btn-warning">Bloquear Aluguéis</button>
                                            <?php else: ?>
                                                <input type="hidden" name="novo_status" value="ativo">
                                                <button type="submit" name="acao_status_usuario" class="btn btn-sm btn-success">Desbloquear Conta</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="painel_admin.php" onsubmit="return confirm('Deseja realmente EXCLUIR este usuário? Essa ação apaga todo o histórico.');">
                                            <input type="hidden" name="id_usuario" value="<?= $user['id'] ?>">
                                            <button type="submit" name="acao_excluir_usuario" class="btn btn-sm btn-outline-danger">Excluir Perfil</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="requisicoes">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (count($requisicoes) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($requisicoes as $req): ?>
                                <div class="list-group-item flex-column align-items-start">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1 text-primary"><?= htmlspecialchars($req['titulo_livro']) ?></h5>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($req['data_requisicao'])) ?></small>
                                    </div>
                                    <p class="mb-1"><strong>Autor:</strong> <?= htmlspecialchars($req['autor_livro']) ?></p>
                                    <p class="mb-1 text-muted"><em>"<?= htmlspecialchars($req['justificativa']) ?>"</em></p>
                                    <small><strong>Solicitado por:</strong> <?= htmlspecialchars($req['nome']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Nenhuma requisição pendente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>