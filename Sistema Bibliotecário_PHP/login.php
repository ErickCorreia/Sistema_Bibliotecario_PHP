<?php
// login.php
require_once 'config.php';

// Se já estiver logado, redireciona para o painel correspondente
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['nivel_acesso'] == 3) {
        header("Location: painel_admin.php");
    } else {
        header("Location: painel_usuario.php");
    }
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (!empty($email) && !empty($senha)) {
        // Busca o usuário no banco
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validação simples (senhas em texto limpo conforme script SQL de carga inicial)
        if ($usuario && $usuario['senha'] === $senha) {
            // Criação das variáveis de sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];

            // Redirecionamento por nível de acesso
            if ($usuario['nivel_acesso'] == 3) {
                header("Location: painel_admin.php");
            } else {
                header("Location: painel_usuario.php");
            }
            exit;
        } else {
            $erro = "E-mail ou senha incorretos.";
        }
    } else {
        $erro = "Preencha todos os campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Biblioteca A3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">

<div class="card shadow" style="width: 100%; max-width: 400px;">
    <div class="card-body p-4">
        <h3 class="text-center mb-4">Acesso ao Sistema</h3>
        
        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Entrar</button>
            <div class="text-center mb-2">
            <span>Não tem uma conta? <a href="cadastro.php" class="text-decoration-none">Cadastre-se</a></span>
            </div>
            <div class="text-center">
                <a href="index.php" class="text-decoration-none">Voltar ao início</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>