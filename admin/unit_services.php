<?php

// /admin/unit_services.php - Página para Associar/Desassociar Serviços a uma Unidade

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/UnitModel.php
';
require_once __DIR__ . 
'/../models/ServiceModel.php
';
require_once __DIR__ . 
'/../models/UnitServiceModel.php
';

// Protege a página: exige login de admin
Auth::requireLogin(
'admin
', 
'/admin/login.php
');

// Valida o ID da unidade da URL
if (!isset($_GET[
'unit_id
']) || !is_numeric($_GET[
'unit_id
'])) {
    Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'ID da unidade inválido.
']);
    header(
'Location: /admin/units.php
');
    exit;
}
$unitId = intval($_GET[
'unit_id
']);

$pageTitle = 
"Gerenciar Serviços da Unidade"
;
$unitName = 
"Unidade Desconhecida"
;
$associatedServices = [];
$availableServices = [];

// Buscar dados da unidade e serviços
try {
    $db = Database::getInstance()->getConnection();
    $unitModel = new UnitModel();
    $serviceModel = new ServiceModel();
    $unitServiceModel = new UnitServiceModel();
    // Injeção seria melhor
    // $unitModel->db = $db;
    // $serviceModel->db = $db;
    // $unitServiceModel->db = $db;

    // Buscar nome da unidade
    // $unit = $unitModel->getUnitById($unitId);
    // Placeholder:
    $unit = ($unitId == 1) ? [
'id
' => 1, 
'name
' => 
'Condomínio Central Park
'] : (($unitId == 2) ? [
'id
' => 2, 
'name
' => 
'Empresa Tech Solutions
'] : null);

    if (!$unit) {
        Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Unidade não encontrada.
']);
        header(
'Location: /admin/units.php
');
        exit;
    }
    $unitName = $unit[
'name
'];
    $pageTitle = 
"Gerenciar Serviços - " . htmlspecialchars($unitName);

    // Buscar serviços já associados a esta unidade
    // $associatedServices = $serviceModel->getServicesByUnitId($unitId);
    // Placeholder:
    $associatedServices = ($unitId == 1) ? [
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
' => 120]
    ] : [
        [
'id
' => 1, 
'name
' => 
'Lavagem Simples
', 
'duration_minutes
' => 60]
    ];

    // Buscar todos os serviços disponíveis para adicionar (que ainda não estão associados)
    // $allServices = $serviceModel->getAllServices();
    // Placeholder:
     $allServices = [
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
        [
'id
' => 3, 
'name
' => 
'Higienização Interna
', 
'duration_minutes
' => 90] // Novo serviço exemplo
    ];

    $associatedIds = array_column($associatedServices, 
'id
');
    foreach ($allServices as $service) {
        if (!in_array($service[
'id
'], $associatedIds)) {
            $availableServices[] = $service;
        }
    }

} catch (Exception $e) {
    // Logar erro
    echo 
"<p>Erro ao buscar dados da unidade ou serviços.</p>"
;
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
        /* Estilos básicos (copiados de units.php, ajustar se necessário) */
        body { font-family: sans-serif; background-color: #f5f7fa; margin: 0; padding: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; min-height: 100vh; padding-top: 20px; }
        /* Adicionar estilos do sidebar aqui */
        .main-content { flex-grow: 1; padding: 20px; }
        .main-content h1, .main-content h2 { margin-top: 0; color: #333; }
        .btn { display: inline-block; padding: 8px 15px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px; margin-bottom: 15px; transition: background-color 0.3s; border: none; cursor: pointer; }
        .btn:hover { background-color: #2980b9; }
        .btn-danger { background-color: #e74c3c; }
        .btn-danger:hover { background-color: #c0392b; }
        .btn-secondary { background-color: #7f8c8d; }
        .btn-secondary:hover { background-color: #606c70; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background-color: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .actions form { display: inline-block; margin: 0; }
        .actions button { background: none; border: none; color: #e74c3c; cursor: pointer; padding: 0; font-size: 1em; }
        .actions button:hover { text-decoration: underline; }
        .service-association-container { display: flex; gap: 30px; margin-top: 20px; }
        .service-list { flex: 1; }
        .add-service-form select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px; }
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
            <li><a href="/admin/units.php" class="active">Unidades</a></li>
            <li><a href="/admin/services.php">Serviços</a></li>
            <li><a href="/admin/users.php">Usuários</a></li>
            <li><a href="/admin/reports.php">Relatórios</a></li>
            <li><a href="/admin/logout.php">Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
        <a href="/admin/units.php" class="btn btn-secondary" style="margin-bottom: 20px;">&larr; Voltar para Unidades</a>

        <div class="service-association-container">
            <div class="service-list">
                <h2>Serviços Associados</h2>
                <?php if (empty($associatedServices)): ?>
                    <p>Nenhum serviço associado a esta unidade.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Duração (min)</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($associatedServices as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service[
'name
']); ?></td>
                                    <td><?php echo htmlspecialchars($service[
'duration_minutes
']); ?></td>
                                    <td class="actions">
                                        <!-- Endpoint da API para remover -->
                                        <form action="/api/admin/units/remove_service" method="POST" onsubmit="return confirm(
'Tem certeza que deseja desassociar este serviço da unidade?
');">
                                            <input type="hidden" name="unit_id" value="<?php echo $unitId; ?>">
                                            <input type="hidden" name="service_id" value="<?php echo $service[
'id
']; ?>">
                                            <button type="submit" class="btn-danger">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="service-list">
                <h2>Adicionar Serviço Disponível</h2>
                <?php if (empty($availableServices)): ?>
                    <p>Todos os serviços já estão associados a esta unidade.</p>
                <?php else: ?>
                    <!-- Endpoint da API para adicionar -->
                    <form action="/api/admin/units/add_service" method="POST" class="add-service-form">
                        <input type="hidden" name="unit_id" value="<?php echo $unitId; ?>">
                        <label for="service_id">Selecione um serviço para adicionar:</label>
                        <select name="service_id" id="service_id" required>
                            <option value="">-- Selecione --</option>
                            <?php foreach ($availableServices as $service): ?>
                                <option value="<?php echo $service[
'id
']; ?>">
                                    <?php echo htmlspecialchars($service[
'name
']); ?> (<?php echo htmlspecialchars($service[
'duration_minutes
']); ?> min)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn">Adicionar Serviço à Unidade</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <?php // include __DIR__ . 
'/templates/footer.php
'; ?>

</body>
</html>

