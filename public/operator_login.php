<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do Operador</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .login-container p {
            color: #666;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
        }
        .login-button {
            width: 100%;
            padding: 12px;
            background-color: #1877f2; /* Azul Facebook */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .login-button:hover {
            background-color: #166fe5;
        }
        .error-message {
            color: #e74c3c;
            background-color: #fdd;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #e74c3c;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login do Operador</h1>
        <p>Acesse para gerenciar os agendamentos da sua unidade.</p>

        <?php 
        // Exibir mensagem de erro, se houver (passada via GET)
        if (isset($_GET[
'error
'])) {
            $errorMessage = 
'Credenciais inválidas ou acesso não permitido.
';
            if ($_GET[
'error
'] === 
'missing_fields
') {
                $errorMessage = 
'Por favor, preencha o email e a senha.
';
            }
            echo 
'<div class="error-message">
' . htmlspecialchars($errorMessage) . 
'</div>
';
        }
        ?>

        <form action="/operator_login_process.php" method="POST">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-button">Entrar</button>
        </form>
        <!-- Adicionar link para "Esqueci minha senha" se necessário -->
    </div>
</body>
</html>

