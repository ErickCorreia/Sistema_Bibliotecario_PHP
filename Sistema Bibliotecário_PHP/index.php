<?php
// index.php
require_once 'config.php';

// Inicializa variáveis de busca
$filtro_titulo = $_GET['titulo'] ?? '';
$filtro_autor = $_GET['autor'] ?? '';
$filtro_status = $_GET['status'] ?? '';

// Monta a query SQL dinamicamente baseada nos filtros preenchidos
$sql = "SELECT * FROM livros WHERE 1=1";
$params = [];

if (!empty($filtro_titulo)) {
    $sql .= " AND titulo LIKE :titulo";
    $params[':titulo'] = "%$filtro_titulo%";
}
if (!empty($filtro_autor)) {
    $sql .= " AND autor LIKE :autor";
    $params[':autor'] = "%$filtro_autor%";
}
if (!empty($filtro_status)) {
    $sql .= " AND status_disponibilidade = :status";
    $params[':status'] = $filtro_status;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca A3 - Início</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Biblioteca A3</a>
        <div class="d-flex">
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <a href="painel_usuario.php" class="btn btn-outline-light me-2">Meu Painel</a>
                <a href="logout.php" class="btn btn-danger">Sair</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">Entrar / Cadastrar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="mb-4 text-center">Catálogo de Livros</h1>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Busca Avançada</h5>
            <form method="GET" action="index.php" class="row g-3">
                <div class="col-md-4">
                    <label for="titulo" class="form-label">Título do Livro</label>
                    <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($filtro_titulo) ?>">
                </div>
                <div class="col-md-4">
                    <label for="autor" class="form-label">Autor</label>
                    <input type="text" class="form-control" id="autor" name="autor" value="<?= htmlspecialchars($filtro_autor) ?>">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Disponibilidade</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="disponivel" <?= $filtro_status == 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                        <option value="alugado" <?= $filtro_status == 'alugado' ? 'selected' : '' ?>>Alugado</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Título</th>
                        <th>Autor</th>
                        <th>Gênero</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($livros) > 0): ?>
                        <?php foreach ($livros as $livro): ?>
                            <tr>
                                <td><?= htmlspecialchars($livro['titulo']) ?></td>
                                <td><?= htmlspecialchars($livro['autor']) ?></td>
                                <td><?= htmlspecialchars($livro['genero']) ?></td>
                                <td>
                                    <?php if ($livro['status_disponibilidade'] == 'disponivel'): ?>
                                        <span class="badge bg-success">Disponível</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Alugado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="avaliacoes_livro.php?id=<?= $livro['id'] ?>" class="btn btn-sm btn-info text-white">Ver Avaliações</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhum livro encontrado com os filtros selecionados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>