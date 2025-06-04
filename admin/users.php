<?php

// /admin/users.php - Página para Gerenciar Usuários (Admin, Operadores, Clientes)

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/UserModel.php
';
require_once __DIR__ . 
'/../controllers/AdminUserController.php
'; // Para buscar a lista

// Protege a página: exige login de admin
Auth::requireLogin(
'admin
', 
'/admin/login.php
');

$pageTitle = 
"Gerenciar Usuários"
;

// Incluir cabeçalho comum do admin
// include __DIR__ . 
'/templates/header.php
';

// Buscar usuários
$users = [];
try {
    $controller = new AdminUserController();
    $users = $controller->listUsers(); // Usa o método do controller
} catch (Exception $e) {
    // Logar erro
    echo 
"<p>Erro ao buscar usuários.</p>"
;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Admin</title>
    <link rel="stylesheet" href="/admin/assets/css/admin_style.css"> <!-- Exemplo -->
    <style>
        /* Estilos básicos (copiados de units.php, ajustar se necessário) */
        body { font-family: sans-serif; background-color: #f5f7fa; margin: 0; padding: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; min-height: 100vh; padding-top: 20px; }
        /* Adicionar estilos do sidebar aqui */
        .main-content { flex-grow: 1; padding: 20px; }
        .main-content h1 { margin-top: 0; color: #333; }
        .btn { display: inline-block; padding: 8px 15px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; margin-bottom: 15px; transition: background-color 0.3s; border: none; cursor: pointer; }
        .btn:hover { background-color: #2980b9; }
        .btn-danger { background-color: #e74c3c; }
        .btn-danger:hover { background-color: #c0392b; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background-color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        td a { color: #3498db; text-decoration: none; margin-right: 10px; }
        td a:hover { text-decoration: underline; }
        .actions form { display: inline-block; margin: 0 5px; }
        .actions button { background: none; border: none; color: #e74c3c; cursor: pointer; padding: 0; font-size: 1em; }
        .actions button:hover { text-decoration: underline; }
        .role-admin { color: #c0392b; font-weight: bold; }
        .role-operator { color: #2980b9; }
        .role-customer { color: #27ae60; }
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

        <a href="/admin/user_form.php" class="btn">Adicionar Novo Usuário (Admin/Operador)</a>

        <?php 
        // Exibir mensagens de sucesso/erro da sessão
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
            // Adicionar CSS para .message.success e .message.error
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome Completo</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5">Nenhum usuário encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user[
'id
']); ?></td>
                            <td><?php echo htmlspecialchars($user[
'full_name
']); ?></td>
                            <td><?php echo htmlspecialchars($user[
'email
']); ?></td>
                            <td>
                                <span class="role-<?php echo htmlspecialchars($user[
'role
']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($user[
'role
'])); // Admin, Operator, Customer ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="/admin/user_form.php?id=<?php echo $user[
'id
']; ?>">Editar</a>
                                <?php if ($user[
'id
'] != Auth::getUserId()): // Não permitir excluir a si mesmo ?>
                                <form action="/api/admin/users/delete" method="POST" onsubmit="return confirm(
'Tem certeza que deseja excluir este usuário?
');">
                                    <input type="hidden" name="user_id" value="<?php echo $user[
'id
']; ?>">
                                    <button type="submit">Excluir</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <?php // include __DIR__ . 
'/templates/footer.php
'; ?>

</body>
</html>

