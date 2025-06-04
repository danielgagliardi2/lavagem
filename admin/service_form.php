<?php

// /admin/service_form.php - Formulário para Adicionar/Editar Serviço

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
"Adicionar Novo Serviço"
;
$service = [
    'id' => null,
    'name' => '',
    'description' => '',
    'duration_minutes' => ''
];
$isEditing = false;

// Verifica se é modo de edição
if (isset($_GET[
'id
']) && is_numeric($_GET[
'id
'])) {
    $serviceId = intval($_GET[
'id
']);
    $isEditing = true;
    $pageTitle = 
"Editar Serviço"
;

    // Buscar dados do serviço para preencher o formulário
    try {
        $db = Database::getInstance()->getConnection();
        $serviceModel = new ServiceModel();
        // $serviceModel->db = $db; // Injeção seria melhor
        // $service = $serviceModel->getServiceById($serviceId); // Método a ser criado

        // Placeholder data for editing:
        if ($serviceId == 1) {
            $service = [
'id
' => 1, 
'name
' => 
'Lavagem Simples
', 
'description
' => 
'Lavagem externa básica.
', 
'duration_minutes
' => 60];
        } elseif ($serviceId == 2) {
             $service = [
'id
' => 2, 
'name
' => 
'Lavagem Completa com Cera
', 
'description
' => 
'Lavagem externa, interna e aplicação de cera.
', 
'duration_minutes
' => 120];
        } else {
             $service = null; // Serviço não encontrado
        }

        if (!$service) {
            // Serviço não encontrado, redirecionar ou mostrar erro
            Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Serviço não encontrado.
']);
            header(
'Location: /admin/services.php
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
'Erro ao buscar serviço.
']);
        header(
'Location: /admin/services.php
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
        /* Estilos básicos (adaptar do admin_mockup.css e services.php) */
        body { font-family: sans-serif; background-color: #f5f7fa; margin: 0; padding: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; min-height: 100vh; padding-top: 20px; }
        /* Adicionar estilos do sidebar aqui */
        .main-content { flex-grow: 1; padding: 20px; }
        .main-content h1 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea { min-height: 100px; resize: vertical; }
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

        <!-- O action apontará para o endpoint da API que processará o formulário -->
        <form action="/api/admin/services/<?php echo $isEditing ? 'update' : 'create'; ?>" method="POST">
            <?php if ($isEditing): ?>
                <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service[
'id
']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Nome do Serviço:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($service[
'name
']); ?>" required>
            </div>

            <div class="form-group">
                <label for="duration_minutes">Duração (em minutos):</label>
                <input type="number" id="duration_minutes" name="duration_minutes" value="<?php echo htmlspecialchars($service[
'duration_minutes
']); ?>" required min="1">
            </div>

            <div class="form-group">
                <label for="description">Descrição (opcional):</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($service[
'description
']); ?></textarea>
            </div>

            <button type="submit" class="btn"><?php echo $isEditing ? 'Atualizar Serviço' : 'Adicionar Serviço'; ?></button>
            <a href="/admin/services.php" class="btn btn-secondary">Cancelar</a>
        </form>

    </div>

    <?php // include __DIR__ . 
'/templates/footer.php
'; ?>

</body>
</html>

