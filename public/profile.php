<?php

// /public/profile.php - Página de Perfil do Cliente

require_once __DIR__ . 
'/../core/Auth.php'
;

Auth::startSession();

// 1. Verificar se usuário está logado como cliente
if (!Auth::isLoggedIn() || !Auth::hasRole(
'customer'
)) {
    header(
'Location: /login.php?error=login_required'
);
    exit;
}

$userId = Auth::getUserId();
$userName = Auth::getUserName(); // Ou buscar nome completo

$pageTitle = 
"Meu Perfil"
;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        /* Estilos similares a outras páginas públicas */
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: 20px auto;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-info, .edit-form {
            margin-bottom: 30px;
        }
        .profile-info p {
            margin: 10px 0;
            padding-bottom: 10px;
            border-bottom: 1px dotted #eee;
            font-size: 1.1em;
        }
        .profile-info p:last-child {
            border-bottom: none;
        }
        .profile-info strong {
            display: inline-block;
            min-width: 150px;
            color: #555;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
        }
        .actions button, .actions a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-right: 10px;
        }
        .actions button:hover, .actions a:hover {
            background-color: #2980b9;
        }
        .actions button.secondary {
            background-color: #7f8c8d;
        }
        .actions button.secondary:hover {
            background-color: #6c7a7d;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .loading {
             text-align: center;
             padding: 20px;
             color: #6c757d;
        }
        /* Esconder formulário de edição inicialmente */
        .edit-form {
            display: none;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Meu Perfil</h1>

        <div id="profile-message" class="message" style="display: none;"></div>

        <div id="profile-view">
            <div class="loading">Carregando informações do perfil...</div>
            <div class="profile-info" style="display: none;">
                <p><strong>Nome Completo:</strong> <span id="info-name"></span></p>
                <p><strong>Email:</strong> <span id="info-email"></span></p>
                <p><strong>Telefone:</strong> <span id="info-phone"></span></p>
                <p><strong>CPF:</strong> <span id="info-cpf"></span></p>
                <p><strong>Endereço:</strong> <span id="info-address"></span></p>
            </div>
            <div class="actions">
                <button id="edit-profile-btn">Editar Perfil</button>
                <a href="/my_appointments.php">Meus Agendamentos</a> <!-- Link para próxima tela -->
                <a href="/" class="secondary">Voltar</a>
            </div>
        </div>

        <div id="profile-edit" class="edit-form">
            <h2>Editar Perfil</h2>
            <form id="edit-profile-form">
                <div class="form-group">
                    <label for="edit-name">Nome Completo:</label>
                    <input type="text" id="edit-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit-email">Email:</label>
                    <input type="email" id="edit-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="edit-phone">Telefone:</label>
                    <input type="tel" id="edit-phone" name="phone">
                </div>
                 <div class="form-group">
                    <label for="edit-cpf">CPF:</label>
                    <input type="text" id="edit-cpf" name="cpf" readonly> <!-- CPF não editável -->
                </div>
                <div class="form-group">
                    <label for="edit-address">Endereço:</label>
                    <input type="text" id="edit-address" name="address">
                </div>
                <div class="form-group">
                    <label for="edit-password">Nova Senha (deixe em branco para não alterar):</label>
                    <input type="password" id="edit-password" name="password">
                </div>
                <div class="form-group">
                    <label for="edit-password-confirm">Confirmar Nova Senha:</label>
                    <input type="password" id="edit-password-confirm" name="password_confirm">
                </div>
                <div class="actions">
                    <button type="submit">Salvar Alterações</button>
                    <button type="button" id="cancel-edit-btn" class="secondary">Cancelar</button>
                </div>
            </form>
        </div>

    </div>

    <script>
        const profileView = document.getElementById('profile-view');
        const profileEdit = document.getElementById('profile-edit');
        const profileInfo = profileView.querySelector('.profile-info');
        const loadingDiv = profileView.querySelector('.loading');
        const editButton = document.getElementById('edit-profile-btn');
        const cancelButton = document.getElementById('cancel-edit-btn');
        const editForm = document.getElementById('edit-profile-form');
        const messageDiv = document.getElementById('profile-message');
        const sessionCookie = `<?php echo session_name() . '=' . session_id(); ?>`;

        async function fetchProfile() {
            loadingDiv.style.display = 'block';
            profileInfo.style.display = 'none';
            messageDiv.style.display = 'none';

            try {
                // TODO: Criar endpoint GET /api/users/me
                const response = await fetch('/api/users/me', { // Endpoint hipotético
                    method: 'GET',
                    headers: { 'Cookie': sessionCookie }
                });
                const data = await response.json();

                loadingDiv.style.display = 'none';

                if (response.ok && data.success && data.user) {
                    const user = data.user;
                    document.getElementById('info-name').textContent = user.name || '';
                    document.getElementById('info-email').textContent = user.email || '';
                    document.getElementById('info-phone').textContent = user.phone || '';
                    document.getElementById('info-cpf').textContent = user.cpf || '';
                    document.getElementById('info-address').textContent = user.address || '';

                    // Preencher formulário de edição
                    document.getElementById('edit-name').value = user.name || '';
                    document.getElementById('edit-email').value = user.email || '';
                    document.getElementById('edit-phone').value = user.phone || '';
                    document.getElementById('edit-cpf').value = user.cpf || ''; // Readonly
                    document.getElementById('edit-address').value = user.address || '';

                    profileInfo.style.display = 'block';
                } else {
                    showMessage('Erro ao carregar perfil: ' + (data.message || 'Tente novamente.'), 'error');
                }
            } catch (error) {
                loadingDiv.style.display = 'none';
                showMessage('Erro de comunicação ao carregar perfil.', 'error');
                console.error('Fetch profile error:', error);
            }
        }

        function showMessage(text, type = 'success') {
            messageDiv.textContent = text;
            messageDiv.className = `message ${type}`;
            messageDiv.style.display = 'block';
            // Esconder mensagem após alguns segundos
            setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
        }

        editButton.addEventListener('click', () => {
            profileView.style.display = 'none';
            profileEdit.style.display = 'block';
            messageDiv.style.display = 'none'; // Limpa mensagens anteriores
        });

        cancelButton.addEventListener('click', () => {
            profileEdit.style.display = 'none';
            profileView.style.display = 'block';
            // Resetar campos de senha
            document.getElementById('edit-password').value = '';
            document.getElementById('edit-password-confirm').value = '';
        });

        editForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            messageDiv.style.display = 'none';

            const password = document.getElementById('edit-password').value;
            const passwordConfirm = document.getElementById('edit-password-confirm').value;

            if (password !== passwordConfirm) {
                showMessage('As novas senhas não coincidem.', 'error');
                return;
            }

            const formData = new FormData(editForm);
            const dataToSend = {};
            formData.forEach((value, key) => {
                // Não enviar senha se estiver vazia, não enviar confirmação
                if (key === 'password' && !value) return;
                if (key === 'password_confirm') return;
                dataToSend[key] = value;
            });

            try {
                // TODO: Criar endpoint PATCH ou POST /api/users/me
                const response = await fetch('/api/users/me', { // Endpoint hipotético
                    method: 'POST', // Ou PATCH
                    headers: {
                        'Content-Type': 'application/json',
                        'Cookie': sessionCookie
                    },
                    body: JSON.stringify(dataToSend)
                });
                const result = await response.json();

                if (response.ok && result.success) {
                    showMessage('Perfil atualizado com sucesso!');
                    // Voltar para a visualização e recarregar dados
                    profileEdit.style.display = 'none';
                    profileView.style.display = 'block';
                    fetchProfile();
                } else {
                    showMessage('Erro ao atualizar perfil: ' + (result.message || 'Tente novamente.'), 'error');
                }

            } catch (error) {
                showMessage('Erro de comunicação ao atualizar perfil.', 'error');
                console.error('Update profile error:', error);
            }
        });

        // Carregar perfil ao iniciar
        fetchProfile();

    </script>

</body>
</html>

