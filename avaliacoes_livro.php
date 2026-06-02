<?php
// avaliacoes_livro.php
require_once 'config.php';

// Verifica se o ID do livro foi passado no URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_livro = (int) $_GET['id'];

// 1. Busca os detalhes do Livro
$stmt = $pdo->prepare("SELECT titulo, autor, genero FROM livros WHERE id = ?");
$stmt->execute([$id_livro]);
$livro = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o utilizador tentou um ID que não existe, redireciona para a home
if (!$livro) {
    header("Location: index.php");
    exit;
}

// 2. Busca as avaliações deste livro cruzando com a tabela de utilizadores para obter o nome
$sql = "
    SELECT a.nota, a.comentario, a.data_avaliacao, u.nome 
    FROM avaliacoes a 
    JOIN usuarios u ON a.id_usuario = u.id 
    WHERE a.id_livro = ? 
    ORDER BY a.data_avaliacao DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_livro]);
$avaliacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcula a média das notas (bónus para ficar com bom aspeto)
$media = 0;
$total_avaliacoes = count($avaliacoes);
if ($total_avaliacoes > 0) {
    $soma = 0;
    foreach ($avaliacoes as $av) {
        $soma += $av['nota'];
    }
    $media = number_format($soma / $total_avaliacoes, 1);
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações: <?= htmlspecialchars($livro['titulo']) ?> - Biblioteca A3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Biblioteca A3</a>
        <div class="d-flex">
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <?php if($_SESSION['nivel_acesso'] == 3): ?>
                    <a href="painel_admin.php" class="btn btn-outline-light me-2">Painel Admin</a>
                <?php else: ?>
                    <a href="painel_usuario.php" class="btn btn-outline-light me-2">Meu Painel</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-danger">Sair</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Entrar / Cadastrar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="card shadow-sm mb-4 border-primary">
        <div class="card-body text-center p-4">
            <h2 class="card-title text-primary fw-bold"><?= htmlspecialchars($livro['titulo']) ?></h2>
            <h5 class="text-muted">Por: <?= htmlspecialchars($livro['autor']) ?> | Género: <?= htmlspecialchars($livro['genero']) ?></h5>
            
            <div class="mt-3">
                <?php if ($total_avaliacoes > 0): ?>
                    <span class="badge bg-warning text-dark fs-5">
                        ⭐ <?= $media ?> / 5.0
                    </span>
                    <p class="text-muted small mt-1">Baseado em <?= $total_avaliacoes ?> avaliação(ões)</p>
                <?php else: ?>
                    <span class="badge bg-secondary fs-6">Sem avaliações ainda</span>
                <?php endif; ?>
            </div>
            
            <a href="index.php" class="btn btn-outline-secondary mt-3">Voltar ao Catálogo</a>
        </div>
    </div>

    <h4 class="mb-3">Críticas dos Leitores</h4>
    
    <?php if ($total_avaliacoes > 0): ?>
        <div class="row">
            <?php foreach ($avaliacoes as $av): ?>
                <div class="col-12 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <strong>👤 <?= htmlspecialchars($av['nome']) ?></strong>
                            <span class="text-warning">
                                <?= str_repeat('⭐', $av['nota']) ?><?= str_repeat('☆', 5 - $av['nota']) ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="card-text">"<?= nl2br(htmlspecialchars($av['comentario'])) ?>"</p>
                        </div>
                        <div class="card-footer bg-transparent text-muted small">
                            Publicado a: <?= date('d/m/Y \à\s H:i', strtotime($av['data_avaliacao'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info shadow-sm text-center p-5">
            <h5>Este livro ainda não foi avaliado!</h5>
            <p class="mb-0">Seja o primeiro a ler e partilhar a sua opinião com a comunidade.</p>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>