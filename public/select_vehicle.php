<?php

// /public/select_vehicle.php - Seleção de Veículo

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
'/../models/VehicleModel.php
';

Auth::startSession();

// 1. Verificar se usuário está logado
if (!Auth::isLoggedIn() || !Auth::hasRole(
'customer
')) {
    $_SESSION[
'pending_appointment_params
'] = $_GET; // Salva todos os params
    header(
'Location: /login.php?message=login_required&redirect=/select_vehicle.php
');
    exit;
}
$userId = Auth::getUserId();

// 2. Obter parâmetros da URL
$unitId = filter_input(INPUT_GET, 
'unit_id
', FILTER_VALIDATE_INT);
$serviceId = filter_input(INPUT_GET, 
'service_id
', FILTER_VALIDATE_INT);
$appointmentDate = filter_input(INPUT_GET, 
'date
'); // YYYY-MM-DD
$startTime = filter_input(INPUT_GET, 
'start_time
'); // HH:MM

// 3. Validar parâmetros básicos
if (!$unitId || !$serviceId || !$appointmentDate || !$startTime || 
    !DateTime::createFromFormat(
'Y-m-d
', $appointmentDate) || 
    !DateTime::createFromFormat(
'H:i
', $startTime)) {
    header(
'Location: /?error=invalid_params_vehicle
'); 
    exit;
}

// 4. Buscar informações da unidade, serviço e veículos do usuário
$unit = null;
$service = null;
$userVehicles = [];
$pageTitle = 
"Selecionar Veículo"
;

try {
    $db = Database::getInstance()->getConnection();
    $unitModel = new UnitModel();
    $serviceModel = new ServiceModel();
    $vehicleModel = new VehicleModel();
    // $unitModel->db = $db;
    // $serviceModel->db = $db;
    // $vehicleModel->db = $db;

    // Buscar unidade pelo ID
    // $unit = $unitModel->getUnitById($unitId);
    // Placeholder:
    if ($unitId == 1) {
        $unit = [
'id
' => 1, 
'name
' => 
'Condomínio Central Park
', 
'slug
' => 
'condominio-central-park
'];
    } elseif ($unitId == 2) {
        $unit = [
'id
' => 2, 
'name
' => 
'Empresa Tech Solutions
', 
'slug
' => 
'empresa-tech-solutions
'];
    }
    if (!$unit) throw new Exception(
"Unidade não encontrada."
);

    // Buscar serviço pelo ID
    // $service = $serviceModel->getServiceById($serviceId);
    // Placeholder:
    if ($serviceId == 1) $service = [
'id
' => 1, 
'name
' => 
'Lavagem Simples
', 
'duration_minutes
' => 60, 
'price
' => 50.00];
    if ($serviceId == 2) $service = [
'id
' => 2, 
'name
' => 
'Lavagem Completa com Cera
', 
'duration_minutes
' => 120, 
'price
' => 100.00];
    if (!$service) throw new Exception(
"Serviço não encontrado."
);

    $pageTitle = 
"Selecionar Veículo - " . htmlspecialchars($unit[
'name
']);

    // Buscar veículos do usuário logado
    // $userVehicles = $vehicleModel->getVehiclesByUserId($userId);
    // Placeholder:
    $userVehicles = [
        [
'id
' => 101, 
'model
' => 
'Honda Civic
', 
'year
' => 2022, 
'color
' => 
'Preto
', 
'plate
' => 
'BRA1Z23
'],
        [
'id
' => 102, 
'model
' => 
'Toyota Corolla
', 
'year
' => 2021, 
'color
' => 
'Prata
', 
'plate
' => 
'MER3C45
']
    ];

} catch (Exception $e) {
    error_log(
"Erro ao buscar dados para seleção de veículo: " . $e->getMessage());
    echo 
"<p>Erro ao carregar informações. Tente novamente mais tarde.</p>"
;
    exit;
}

// Formatar data para exibição
$displayDate = DateTime::createFromFormat(
'Y-m-d
', $appointmentDate)->format(
'd/m/Y
');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        /* Estilos adaptados de select_service.php */
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            margin-top: 30px;
        }
        h1, h2 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 1.5em;
            margin-bottom: 25px;
        }
        .appointment-summary {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            font-size: 1.1em;
            border-left: 5px solid #3498db;
        }
        .appointment-summary p {
            margin: 8px 0;
        }
        .appointment-summary strong {
            color: #0056b3;
        }
        .vehicle-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .vehicle-item {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            transition: box-shadow 0.2s;
        }
         .vehicle-item:hover {
             box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .vehicle-info {
            font-size: 1.1em;
            color: #333;
            line-height: 1.5;
        }
        .vehicle-info strong {
            display: inline-block;
            min-width: 60px; /* Alinhamento */
        }
        .select-button {
            padding: 10px 20px;
            background-color: #2ecc71; /* Verde */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s;
            align-self: flex-end;
        }
        .select-button:hover {
            background-color: #27ae60;
        }
        .add-vehicle-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px dashed #ccc;
            border-radius: 4px;
            color: #555;
            text-decoration: none;
            font-weight: bold;
        }
        .add-vehicle-link:hover {
            background-color: #e9e9e9;
        }
        .back-link {
            display: inline-block; /* Para ficar ao lado do add */
            margin-top: 30px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .actions-container {
             display: flex;
             justify-content: space-between;
             align-items: center;
             margin-top: 30px;
        }

        /* Desktop adjustments */
        @media (min-width: 600px) {
             .vehicle-item {
                 flex-direction: row;
                 justify-content: space-between;
                 align-items: center;
            }
            .select-button {
                align-self: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($unit[
'name
']); ?></h1>
        <h2>Selecione o Veículo</h2>

        <div class="appointment-summary">
            <p><strong>Serviço:</strong> <?php echo htmlspecialchars($service[
'name
']); ?></p>
            <p><strong>Data:</strong> <?php echo $displayDate; ?></p>
            <p><strong>Horário:</strong> <?php echo $startTime; ?></p>
            <p><strong>Duração Estimada:</strong> <?php echo htmlspecialchars($service[
'duration_minutes
']); ?> min</p>
            <p><strong>Preço:</strong> R$ <?php echo number_format($service[
'price
'] ?? 0, 2, 
',
', 
'.
'); ?></p>
        </div>

        <h3>Seus Veículos Cadastrados:</h3>
        <?php if (empty($userVehicles)): ?>
            <p>Você ainda não cadastrou nenhum veículo.</p>
        <?php else: ?>
            <ul class="vehicle-list">
                <?php foreach ($userVehicles as $vehicle): ?>
                    <li class="vehicle-item">
                        <div class="vehicle-info">
                            <strong>Modelo:</strong> <?php echo htmlspecialchars($vehicle[
'model
']); ?><br>
                            <strong>Ano:</strong> <?php echo htmlspecialchars($vehicle[
'year
']); ?><br>
                            <strong>Cor:</strong> <?php echo htmlspecialchars($vehicle[
'color
']); ?><br>
                            <strong>Placa:</strong> <?php echo htmlspecialchars($vehicle[
'plate
']); ?>
                        </div>
                        <form action="/confirm_appointment.php" method="POST"> <!-- Mudar para POST -->
                            <input type="hidden" name="unit_id" value="<?php echo $unitId; ?>">
                            <input type="hidden" name="date" value="<?php echo $appointmentDate; ?>">
                            <input type="hidden" name="start_time" value="<?php echo $startTime; ?>">
                            <input type="hidden" name="service_id" value="<?php echo $serviceId; ?>">
                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle[
'id
']; ?>">
                            <!-- Adicionar CSRF token aqui se implementado -->
                            <button type="submit" class="select-button">Agendar com este Veículo</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="actions-container">
             <a href="/select_service.php?unit_id=<?php echo $unitId; ?>&date=<?php echo $appointmentDate; ?>&start_time=<?php echo $startTime; ?>" class="back-link">&laquo; Voltar para Serviços</a>
             <a href="/my_vehicles.php?redirect=<?php echo urlencode($_SERVER[
'REQUEST_URI
']); ?>" class="add-vehicle-link">Adicionar Novo Veículo</a>
        </div>

    </div>
</body>
</html>

