<?php

// /admin/units.php - Página para Gerenciar Unidades

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/UnitModel.php
';

// Protege a página: exige login de admin
Auth::requireLogin(
'admin
', 
'/admin/login.php
');

$pageTitle = 
"Gerenciar Unidades"
;

// Incluir cabeçalho comum do admin (a ser criado)
// include __DIR__ . 
'/templates/header.php
';

// Buscar unidades (exemplo, a lógica real virá do controller/model)
$units = [];
try {
    $db = Database::getInstance()->getConnection();
    $unitModel = new UnitModel();
    // $unitModel->db = $db; // Injeção de dependência seria melhor
    // $units = $unitModel->getAllUnits();
    // Placeholder data:
    $units = [
        [
'id
' => 1, 
'name
' => 
'Condomínio Central Park
', 
'slug
' => 
'condominio-central-park
', 
'address
' => 
'Rua Principal, 123
'],
        [
'id
' => 2, 
'name
' => 
'Empresa Tech Solutions
', 
'slug
' => 
'empresa-tech-solutions
', 
'address
' => 
'Av. Inovação, 456
'],
    ];
} catch (Exception $e) {
    // Logar erro
    echo 
"<p>Erro ao buscar unidades.</p>"
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
        /* Estilos básicos (copiados de services.php, ajustar se necessário) */
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
            <li><a href="/admin/units.php" class="active">Unidades</a></li>
            <li><a href="/admin/services.php">Serviços</a></li>
            <li><a href="/admin/users.php">Usuários</a></li>
            <li><a href="/admin/reports.php">Relatórios</a></li>
            <li><a href="/admin/logout.php">Sair</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1><?php echo htmlspecialchars($pageTitle); ?></h1>

        <a href="/admin/unit_form.php" class="btn">Adicionar Nova Unidade</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Slug (Link)</th>
                    <th>Endereço</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($units)): ?>
                    <tr>
                        <td colspan="5">Nenhuma unidade cadastrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($units as $unit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($unit[
'id
']); ?></td>
                            <td><?php echo htmlspecialchars($unit[
'name
']); ?></td>
                            <td>/units/<?php echo htmlspecialchars($unit[
'slug
']); ?></td>
                            <td><?php echo htmlspecialchars($unit[
'address
']); ?></td>
                            <td class="actions">
                                <a href="/admin/unit_form.php?id=<?php echo $unit[
'id
']; ?>">Editar</a>
                                <a href="/admin/unit_services.php?unit_id=<?php echo $unit[
'id
']; ?>">Serviços</a> <!-- Link para associar serviços -->
                                <a href="/admin/unit_schedule.php?unit_id=<?php echo $unit[
'id
']; ?>">Horários</a> <!-- Link para horários -->
                                <form action="/api/admin/units/delete" method="POST" onsubmit="return confirm(
'Tem certeza que deseja excluir esta unidade? Todos os agendamentos associados também serão excluídos.
');">
                                    <input type="hidden" name="unit_id" value="<?php echo $unit[
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

