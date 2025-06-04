<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Cliente - Sistema de Lavagem</title>
    <style>
        /* Estilos Mobile First - Adaptar do admin/login.php e refinar */
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        .login-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px; /* Limite para telas maiores */
            text-align: center;
            margin-top: 30px;
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .login-container label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #3498db; /* Cor primária (azul) */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        .login-container button:hover {
            background-color: #2980b9;
        }
        .error-message {
            color: #e74c3c;
            margin-top: 15px;
            font-size: 14px;
            text-align: left;
        }
        .register-link {
            margin-top: 20px;
            font-size: 14px;
        }
        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .register-link a:hover {
            text-decoration: underline;
        }

        /* Adaptações para telas maiores (ex: tablets e desktops) */
        @media (min-width: 600px) {
            .login-container {
                padding: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Acessar Minha Conta</h2>

        <?php 
        // Exibir mensagens de erro/sucesso da sessão (ex: após registro)
        require_once __DIR__ . 
'/../core/Auth.php

';
        Auth::startSession();
        if (Auth::getSession(
'auth_message

')) {
            $message = Auth::getSession(
'auth_message

');
            Auth::unsetSession(
'auth_message

');
            echo 
'<p class="message 

' . ($message[
'type

'] ?? 
'info

') . 
'">' . htmlspecialchars($message[
'text

']) . 
'</p>

';
            // Adicionar CSS para .message.success e .message.error
        }
        ?>

        <!-- O action apontará para um endpoint da API -->
        <form id="login-form" action="/api/auth/login" method="POST"> 
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>

            <p id="form-error" class="error-message" style="display: none;"></p>

            <button type="submit">Entrar</button>
        </form>

        <p class="register-link">
            Ainda não tem conta? <a href="/register.php">Cadastre-se aqui</a>
        </p>
    </div>

    <script>
        // Script JS para lidar com o envio do formulário via API (Fetch)
        const loginForm = document.getElementById(
'login-form

');
        const errorElement = document.getElementById(
'form-error

');

        loginForm.addEventListener(
'submit

', async (event) => {
            event.preventDefault(); // Impede o envio tradicional do formulário
            errorElement.style.display = 
'none

';
            errorElement.textContent = 
''

;

            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(
'/api/auth/login

', {
                    method: 
'POST

',
                    headers: {
                        
'Content-Type

': 
'application/json

',
                        
'Accept

': 
'application/json

'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Login bem-sucedido, redireciona para a página principal ou dashboard do cliente
                    window.location.href = result.redirect_url || 
'/

'; // O backend deve indicar para onde redirecionar
                } else {
                    // Exibe a mensagem de erro retornada pela API
                    errorElement.textContent = result.message || 
'Erro ao fazer login. Verifique suas credenciais.

';
                    errorElement.style.display = 
'block

';
                }
            } catch (error) {
                console.error(
'Erro na requisição:

', error);
                errorElement.textContent = 
'Ocorreu um erro de comunicação. Tente novamente.

';
                errorElement.style.display = 
'block

';
            }
        });
    </script>

</body>
</html>

