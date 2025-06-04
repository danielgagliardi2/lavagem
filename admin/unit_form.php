<?php

// /admin/unit_form.php - Formulário para Adicionar/Editar Unidade

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
"Adicionar Nova Unidade"
;
$unit = [
    'id' => null,
    'name' => '',
    'address' => '',
    'slug' => '',
    'header_image_path' => null
];
$isEditing = false;

// Verifica se é modo de edição
if (isset($_GET[
'id
']) && is_numeric($_GET[
'id
'])) {
    $unitId = intval($_GET[
'id
']);
    $isEditing = true;
    $pageTitle = 
"Editar Unidade"
;

    // Buscar dados da unidade para preencher o formulário
    try {
        $db = Database::getInstance()->getConnection();
        $unitModel = new UnitModel();
        // $unitModel->db = $db; // Injeção seria melhor
        // $unit = $unitModel->getUnitById($unitId); // Método a ser criado

        // Placeholder data for editing:
        if ($unitId == 1) {
            $unit = [
'id
' => 1, 
'name
' => 
'Condomínio Central Park
', 
'address
' => 
'Rua Principal, 123
', 
'slug
' => 
'condominio-central-park
', 
'header_image_path
' => 
'/uploads/header1.jpg
'];
        } elseif ($unitId == 2) {
             $unit = [
'id
' => 2, 
'name
' => 
'Empresa Tech Solutions
', 
'address
' => 
'Av. Inovação, 456
', 
'slug
' => 
'empresa-tech-solutions
', 
'header_image_path
' => null];
        } else {
             $unit = null; // Unidade não encontrada
        }

        if (!$unit) {
            // Unidade não encontrada, redirecionar ou mostrar erro
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
'Erro ao buscar unidade.
']);
        header(
'Location: /admin/units.php
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
        /* Estilos básicos (copiados de service_form.php, ajustar se necessário) */
        body { font-family: sans-serif; background-color: #f5f7fa; margin: 0; padding: 0; display: flex; }
        .sidebar { width: 250px; background-color: #2c3e50; color: white; min-height: 100vh; padding-top: 20px; }
        /* Adicionar estilos do sidebar aqui */
        .main-content { flex-grow: 1; padding: 20px; }
        .main-content h1 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-group input[type="text"],
        .form-group input[type="file"],
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
        .current-image { max-width: 200px; max-height: 100px; display: block; margin-top: 5px; }
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

        <!-- O action apontará para o endpoint da API que processará o formulário -->
        <!-- Precisa de enctype="multipart/form-data" para upload de imagem -->
        <form action="/api/admin/units/<?php echo $isEditing ? 'update' : 'create'; ?>" method="POST" enctype="multipart/form-data">
            <?php if ($isEditing): ?>
                <input type="hidden" name="unit_id" value="<?php echo htmlspecialchars($unit[
'id
']); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Nome da Unidade:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($unit[
'name
']); ?>" required>
            </div>

            <div class="form-group">
                <label for="slug">Slug (para URL, ex: nome-da-unidade):</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($unit[
'slug
']); ?>" required pattern="[a-z0-9-]+$" title="Apenas letras minúsculas, números e hífens.">
                <small>Usado no link: /units/&lt;slug&gt;</small>
            </div>

            <div class="form-group">
                <label for="address">Endereço:</label>
                <textarea id="address" name="address"><?php echo htmlspecialchars($unit[
'address
']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="header_image">Imagem do Cabeçalho (Opcional):</label>
                <?php if ($isEditing && $unit[
'header_image_path
']): ?>
                    <p>Imagem Atual:</p>
                    <img src="<?php echo htmlspecialchars($unit[
'header_image_path
']); ?>" alt="Imagem atual" class="current-image">
                    <p><small>Enviar uma nova imagem substituirá a atual.</small></p>
                <?php endif; ?>
                <input type="file" id="header_image" name="header_image" accept="image/jpeg, image/png, image/webp">
            </div>

            <button type="submit" class="btn"><?php echo $isEditing ? 'Atualizar Unidade' : 'Adicionar Unidade'; ?></button>
            <a href="/admin/units.php" class="btn btn-secondary">Cancelar</a>
        </form>

    </div>

    <?php // include __DIR__ . 
'/templates/footer.php
'; ?>

</body>
</html>

