<?php

// /admin/user_form.php - Formulário para Adicionar/Editar Usuário (Admin/Operador)

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/UserModel.php
';

// Protege a página: exige login de admin
Auth::requireLogin(
'admin
', 
'/admin/login.php
');

$pageTitle = 
"Adicionar Novo Usuário (Admin/Operador)"
;
$user = [
    'id' => null,
    'full_name' => '',
    'email' => '',
    'role' => 'operator' // Default para novo usuário
];
$isEditing = false;

// Verifica se é modo de edição
if (isset($_GET[
'id
']) && is_numeric($_GET[
'id
'])) {
    $userId = intval($_GET[
'id
']);
    $isEditing = true;
    $pageTitle = 
"Editar Usuário"
;

    // Buscar dados do usuário para preencher o formulário
    try {
        $db = Database::getInstance()->getConnection();
        $userModel = new UserModel();
        // $userModel->db = $db; // Injeção seria melhor
        // $user = $userModel->getUserById($userId); // Método a ser criado

        // Placeholder data for editing:
        if ($userId == 1) {
            $user = [
'id
' => 1, 
'full_name
' => 
'Admin Principal
', 
'email
' => 
'admin@lavagem.com
', 
'role
' => 
'admin
'];
        } elseif ($userId == 3) {
             $user = [
'id
' => 3, 
'full_name
' => 
'Operador João Silva
', 
'email
' => 
'joao.op@lavagem.com
', 
'role
' => 
'operator
'];
        } elseif ($userId == 4) {
             $user = [
'id
' => 4, 
'full_name
' => 
'Cliente Teste
', 
'email
' => 
'cliente@teste.com
', 
'role
' => 
'customer
'];
        } else {
             $user = null; // Usuário não encontrado
        }

        if (!$user) {
            Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Usuário não encontrado.
']);
            header(
'Location: /admin/users.php
');
            exit;
        }
    } catch (Exception $e) {
        // Logar erro
        Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Erro ao buscar usuário.
']);
        header(
'Location: /admin/users.php
');
        exit;
    }
}

// Incluir cabeçalho comum do admin
// include __DIR__ . 
'/templates/header.php
';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Admin</title>
    <link rel="stylesheet" href="/admin/assets/css/admin_style.css"> <!-- Exemplo -->
     <style>
        /* Estilos básicos (copiados de unit_form.php, ajustar se necessário) */
        body { font-family: sans-serif; background-color: #f5f7fa; margin: 0; padding: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; min-height: 100vh; padding-top: 20px; }
        /* Adicionar estilos do sidebar aqui */
        .main-content { flex-grow: 1; padding: 20px; }
        .main-content h1 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn { display: inline-block; padding: 10px 20px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.3s; border: none; cursor: pointer; font-size: 1em; }
        .btn:hover { background-color: #2980b9; }
        .btn-secondary { background-color: #7f8c8d; margin-left: 10px; }
        .btn-secondary:hover { background-color: #606c70; }
    </style>
</head>
<body>

    <?php // include __DIR__ . 
'/templates/sidebar.php
'; ?>
     <div class="sidebar">
        <p style="padding: 15px;">Menu Admin</p>
         <ul>
            <li><a href="/admin/dashboard.php">Dashboard</a></li>
            <li><a href="/admin/units.php">Unidades</a></li>
            <li><a href="/admin/services.php">Serviços</a></li>
            <li><a href="/admin/users.php" class="active">Usuários</a></li>
            <li><a href="/admin/reports.php">Relatórios</a></li>
            <li><a href="/admin/logout.php">Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

        <?php 
        // Exibir mensagens de erro da sessão (ex: email duplicado)
        if (Auth::getSession(
'admin_message
')) {
            $message = Auth::getSession(
'admin_message
');
            Auth::unsetSession(
'admin_message
');
            echo 
'<div class="message 
' . ($message[
'type
'] ?? 
'info
') . 
'">' . htmlspecialchars($message[
'text
']) . 
'</div>
';
        }
        ?>

        <!-- O action apontará para o endpoint da API que processará o formulário -->
        <form action="/api/admin/users/<?php echo $isEditing ? 'update' : 'create'; ?>" method="POST">
            <?php if ($isEditing): ?>
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user[
'id
']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="full_name">Nome Completo:</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user[
'full_name
']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user[
'email
']); ?>" required>
            </div>

            <div class="form-group">
                <label for="role">Tipo de Usuário:</label>
                <select id="role" name="role" required <?php echo ($isEditing && $user[
'role
'] === 'customer') ? 'disabled' : ''; ?>>
                    <option value="admin" <?php echo ($user[
'role
'] === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                    <option value="operator" <?php echo ($user[
'role
'] === 'operator') ? 'selected' : ''; ?>>Operador</option>
                    <?php if ($isEditing && $user[
'role
'] === 'customer'): ?>
                        <option value="customer" selected>Cliente</option>
                    <?php endif; ?>
                </select>
                 <?php if ($isEditing && $user[
'role
'] === 'customer'): ?>
                    <small>Não é possível alterar o tipo de um cliente aqui.</small>
                    <input type="hidden" name="role" value="customer"> <!-- Envia o role mesmo desabilitado -->
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Senha:</label>
                <input type="password" id="password" name="password" <?php echo $isEditing ? '' : 'required'; ?>>
                <?php if ($isEditing): ?>
                    <small>Deixe em branco para não alterar a senha.</small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn"><?php echo $isEditing ? 'Atualizar Usuário' : 'Adicionar Usuário'; ?></button>
            <a href="/admin/users.php" class="btn btn-secondary">Cancelar</a>
        </form>

    </div>

    <?php // include __DIR__ . 
'/templates/footer.php
'; ?>

</body>
</html>

