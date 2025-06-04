<?php

// /admin/services.php - Página para Gerenciar Serviços

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/ServiceModel.php
';

// Protege a página: exige login de admin
Auth::requireLogin(
'admin
', 
'/admin/login.php
');

$pageTitle = 
"Gerenciar Serviços"
;

// Incluir cabeçalho comum do admin (a ser criado)
// include __DIR__ . 
'/templates/header.php
';

// Buscar serviços (exemplo, a lógica real virá do controller/model)
$services = [];
try {
    $db = Database::getInstance()->getConnection();
    $serviceModel = new ServiceModel();
    // $serviceModel->db = $db; // Injeção de dependência seria melhor
    // $services = $serviceModel->getAllServices();
    // Placeholder data:
    $services = [
        [
'id
' => 1, 
'name
' => 
'Lavagem Simples
', 
'duration_minutes
' => 60],
        [
'id
' => 2, 
'name
' => 
'Lavagem Completa com Cera
', 
'duration_minutes
' => 120],
    ];
} catch (Exception $e) {
    // Logar erro
    echo 
"<p>Erro ao buscar serviços.</p>"
;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Admin</title>
    <!-- Incluir CSS comum do admin (baseado em admin_mockup.html) -->
    <link rel="stylesheet" href="/admin/assets/css/admin_style.css"> <!-- Exemplo de caminho -->
    <style>
        /* Estilos básicos para a tabela e botões (adaptar do admin_mockup.css) */
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
    </style>
</head>
<body>

    <?php // include __DIR__ . 
'/templates/sidebar.php
'; // Incluir sidebar comum ?>
    <div class="sidebar">
        <!-- Conteúdo do Sidebar (menu) viria aqui -->
        <p style="padding: 15px;">Menu Admin</p>
        <ul>
            <li><a href="/admin/dashboard.php">Dashboard</a></li>
            <li><a href="/admin/units.php">Unidades</a></li>
            <li><a href="/admin/services.php" class="active">Serviços</a></li>
            <li><a href="/admin/users.php">Usuários</a></li>
            <li><a href="/admin/reports.php">Relatórios</a></li>
            <li><a href="/admin/logout.php">Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

        <a href="/admin/service_form.php" class="btn">Adicionar Novo Serviço</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Duração (min)</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="4">Nenhum serviço cadastrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($service[
'id
']); ?></td>
                            <td><?php echo htmlspecialchars($service[
'name
']); ?></td>
                            <td><?php echo htmlspecialchars($service[
'duration_minutes
']); ?></td>
                            <td class="actions">
                                <a href="/admin/service_form.php?id=<?php echo $service[
'id
']; ?>">Editar</a>
                                <form action="/api/admin/services/delete" method="POST" onsubmit="return confirm(
'Tem certeza que deseja excluir este serviço?
');">
                                    <input type="hidden" name="service_id" value="<?php echo $service[
'id
']; ?>">
                                    <button type="submit">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

    </div>

    <?php // include __DIR__ . 
'/templates/footer.php
'; // Incluir rodapé comum ?>

</body>
</html>

