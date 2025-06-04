<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Cliente - Sistema de Lavagem</title>
    <style>
        /* Estilos Mobile First - Adaptar do login.php */
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
        .register-container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px; /* Um pouco maior para mais campos */
            text-align: center;
            margin-top: 30px;
        }
        .register-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .register-container label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        .register-container input[type="text"],
        .register-container input[type="email"],
        .register-container input[type="tel"],
        .register-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
         .register-container textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            min-height: 80px;
            resize: vertical;
        }
        .register-container button {
            width: 100%;
            padding: 12px;
            background-color: #27ae60; /* Cor secundária (verde) */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        .register-container button:hover {
            background-color: #229954;
        }
        .error-message {
            color: #e74c3c;
            margin-top: 15px;
            font-size: 14px;
            text-align: left;
        }
        .login-link {
            margin-top: 20px;
            font-size: 14px;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }

        /* Adaptações para telas maiores */
        @media (min-width: 600px) {
            .register-container {
                padding: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Criar Nova Conta</h2>

        <p id="form-error" class="error-message" style="display: none;"></p>

        <!-- O action apontará para um endpoint da API -->
        <form id="register-form" action="/api/auth/register" method="POST">
            <label for="full_name">Nome Completo:</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Telefone:</label>
            <input type="tel" id="phone" name="phone" placeholder="(XX) XXXXX-XXXX">

            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" placeholder="XXX.XXX.XXX-XX">

            <label for="address">Endereço:</label>
            <textarea id="address" name="address"></textarea>

            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>

            <label for="password_confirm">Confirmar Senha:</label>
            <input type="password" id="password_confirm" name="password_confirm" required>

            <button type="submit">Cadastrar</button>
        </form>

        <p class="login-link">
            Já tem conta? <a href="/login.php">Faça login aqui</a>
        </p>
    </div>

    <script>
        // Script JS para lidar com o envio do formulário via API (Fetch)
        const registerForm = document.getElementById(
"register-form"
);
        const errorElement = document.getElementById(
"form-error"
);

        registerForm.addEventListener(
"submit"
, async (event) => {
            event.preventDefault();
            errorElement.style.display = 
"none"
;
            errorElement.textContent = 
""
;

            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData.entries());

            // Validação básica no frontend (ex: senhas coincidem)
            if (data.password !== data.password_confirm) {
                errorElement.textContent = 
"As senhas não coincidem."
;
                errorElement.style.display = 
"block"
;
                return;
            }

            // Remover confirmação de senha antes de enviar
            delete data.password_confirm;

            try {
                const response = await fetch(
"/api/auth/register"
, {
                    method: 
"POST"
,
                    headers: {
                        
"Content-Type"
: 
"application/json"
,
                        
"Accept"
: 
"application/json"

                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Registro bem-sucedido, redireciona para login com mensagem
                    // O backend pode definir a mensagem na sessão
                    window.location.href = result.redirect_url || 
"/login.php?registered=true"
; 
                } else {
                    // Exibe a mensagem de erro retornada pela API
                    errorElement.textContent = result.message || 
"Erro ao fazer cadastro. Verifique os dados informados."
;
                    errorElement.style.display = 
"block"
;
                }
            } catch (error) {
                console.error(
"Erro na requisição:"
, error);
                errorElement.textContent = 
"Ocorreu um erro de comunicação. Tente novamente."
;
                errorElement.style.display = 
"block"
;
            }
        });
    </script>

</body>
</html>

