<?php
// cadastro.php
require_once 'config.php';

// Se o usuário já estiver logado, redireciona para a página inicial
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);

    // Verifica se todos os campos foram preenchidos
    if (!empty($nome) && !empty($email) && !empty($senha) && !empty($confirmar_senha)) {
        
        // Verifica se as senhas batem
        if ($senha !== $confirmar_senha) {
            $erro = "As senhas não coincidem. Tente novamente.";
        } else {
            // Verifica se o e-mail já está cadastrado no banco
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);
            
            if ($stmt->fetch()) {
                $erro = "Este e-mail já está sendo utilizado por outro usuário.";
            } else {
                // Insere o novo usuário no banco de dados
                // O nível_acesso 2 (Usuário) e status 'ativo' são definidos por padrão na nossa tabela,
                // mas vamos forçar aqui na query para garantir a regra de negócio.
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel_acesso, status_conta) VALUES (:nome, :email, :senha, 2, 'ativo')");
                
                try {
                    $stmt->execute([
                        ':nome' => $nome,
                        ':email' => $email,
                        ':senha' => $senha // Nota: mantido em texto limpo para fins acadêmicos conforme script SQL
                    ]);
                    
                    $sucesso = "Cadastro realizado com sucesso! Você será redirecionado para o login.";
                    // Redireciona para o login após 3 segundos
                    header("refresh:3;url=login.php");
                } catch (PDOException $e) {
                    $erro = "Erro interno ao cadastrar: " . $e->getMessage();
                }
            }
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Biblioteca A3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

<div class="card shadow" style="width: 100%; max-width: 500px;">
    <div class="card-body p-5">
        <h3 class="text-center mb-4">Criar Nova Conta</h3>
        
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-success"><?= $sucesso ?></div>
        <?php else: ?>

            <form method="POST" action="cadastro.php">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="<?= isset($nome) ? htmlspecialchars($nome) : '' ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success w-100 mb-3">Finalizar Cadastro</button>
                
                <div class="text-center">
                    <span class="text-muted">Já possui uma conta?</span> <a href="login.php" class="text-decoration-none">Faça login aqui</a><br>
                    <a href="index.php" class="text-decoration-none text-secondary mt-2 d-inline-block">Voltar à página inicial</a>
                </div>
            </form>

        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>