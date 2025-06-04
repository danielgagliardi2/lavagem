<?php

// /public/confirm_appointment.php - Processa e confirma o agendamento

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
'; // Para buscar detalhes se necessário
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

// 1. Verificar se usuário está logado e se é POST
if ($_SERVER[
'REQUEST_METHOD
'] !== 
'POST
' || !Auth::isLoggedIn() || !Auth::hasRole(
'customer
')) {
    // Redirecionar para login ou página inicial
    header(
'Location: /login.php?message=invalid_access
');
    exit;
}
$userId = Auth::getUserId();

// 2. Obter parâmetros do POST
$unitId = filter_input(INPUT_POST, 
'unit_id
', FILTER_VALIDATE_INT);
$serviceId = filter_input(INPUT_POST, 
'service_id
', FILTER_VALIDATE_INT);
$vehicleId = filter_input(INPUT_POST, 
'vehicle_id
', FILTER_VALIDATE_INT);
$appointmentDate = filter_input(INPUT_POST, 
'date
'); // YYYY-MM-DD
$startTime = filter_input(INPUT_POST, 
'start_time
'); // HH:MM

// 3. Validar parâmetros básicos
if (!$unitId || !$serviceId || !$vehicleId || !$appointmentDate || !$startTime || 
    !DateTime::createFromFormat(
'Y-m-d
', $appointmentDate) || 
    !DateTime::createFromFormat(
'H:i
', $startTime)) {
    // Redirecionar de volta para seleção de veículo com erro
    header(
'Location: /select_vehicle.php?unit_id=' . ($unitId ?? 
'') . 
'&service_id=' . ($serviceId ?? 
'') . 
'&date=' . ($appointmentDate ?? 
'') . 
'&start_time=' . ($startTime ?? 
'') . 
'&error=invalid_data
');
    exit;
}

// 4. Preparar dados para a API
$apiData = [
    
'unit_id
' => $unitId,
    
'service_id
' => $serviceId,
    
'vehicle_id
' => $vehicleId,
    
'appointment_date
' => $appointmentDate,
    
'start_time
' => $startTime
];

// 5. Chamar a API para criar o agendamento
$apiUrl = 
'http://' . $_SERVER[
'HTTP_HOST
'] . 
'/api/appointments
'; // Assume que a API está no mesmo host
$sessionCookie = session_name() . 
'=' . session_id(); // Passar o cookie de sessão

$options = [
    
'http
' => [
        
'header
'  => "Content-type: application/json\r\n" .
                     "Cookie: $sessionCookie\r\n",
        
'method
'  => 
'POST
',
        
'content
' => json_encode($apiData),
        
'ignore_errors
' => true // Para capturar respostas de erro da API
    ]
];
$context  = stream_context_create($options);
$resultJson = file_get_contents($apiUrl, false, $context);
$responseCode = (int)explode(
' 
', $http_response_header[0])[1]; // Extrai o código HTTP da resposta
$resultData = json_decode($resultJson, true);

// 6. Processar a resposta da API
$success = false;
$message = 
'Ocorreu um erro desconhecido ao tentar agendar.
';
$appointmentDetails = null;

if ($resultData && isset($resultData[
'success
'])) {
    $success = $resultData[
'success
'];
    $message = $resultData[
'message
'] ?? $message;
    if ($success && isset($resultData[
'details
'])) {
        $appointmentDetails = $resultData[
'details
'];
        // Buscar nomes para exibição (Unidade, Serviço, Veículo)
        try {
            $db = Database::getInstance()->getConnection();
            $unitModel = new UnitModel();
            $serviceModel = new ServiceModel();
            $vehicleModel = new VehicleModel();
            
            // Placeholders (substituir por chamadas reais ao Model)
            $unit = (
'Condomínio Central Park
'); // $unitModel->getUnitById($unitId)['name'];
            $service = (
'Lavagem Simples
'); // $serviceModel->getServiceById($serviceId)['name'];
            $vehicle = (
'Honda Civic (BRA1Z23)
'); // $vehicleModel->getVehicleById($vehicleId); formatar

            $appointmentDetails[
'unit_name
'] = $unit;
            $appointmentDetails[
'service_name
'] = $service;
            $appointmentDetails[
'vehicle_description
'] = $vehicle;

        } catch (Exception $e) {
            error_log(
'Erro ao buscar detalhes para confirmação: ' . $e->getMessage());
            // Continua sem os nomes, mas loga o erro
        }
    }
} elseif ($responseCode >= 400) {
    // Tenta pegar a mensagem de erro da API se houver
    if ($resultData && isset($resultData[
'message
'])) {
        $message = $resultData[
'message
'];
    } elseif ($resultData && isset($resultData[
'error
'])) {
         $message = $resultData[
'error
'];
    } else {
        $message = 
'Erro na comunicação com a API (' . $responseCode . 
').
';
    }
}

// 7. Exibir confirmação ou erro
$pageTitle = $success ? 
'Agendamento Confirmado!' : 
'Erro no Agendamento
';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        /* Estilos adaptados de confirmacao_agendamento_mockup.html */
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 600px;
        }
        .icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        .success .icon { color: #2ecc71; } /* Verde */
        .error .icon { color: #e74c3c; } /* Vermelho */
        h1 {
            color: #333;
            margin-bottom: 15px;
        }
        .message {
            font-size: 1.1em;
            color: #555;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .details {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 5px;
            text-align: left;
            margin-bottom: 30px;
            font-size: 1em;
        }
        .details p {
            margin: 10px 0;
            border-bottom: 1px dotted #ddd;
            padding-bottom: 10px;
        }
        .details p:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        .details strong {
            color: #333;
            display: inline-block;
            min-width: 100px; /* Alinhamento */
        }
        .actions a {
            display: inline-block;
            padding: 12px 25px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
            margin: 5px;
        }
        .actions a:hover {
            background-color: #2980b9;
        }
        .actions a.secondary {
            background-color: #7f8c8d;
        }
        .actions a.secondary:hover {
            background-color: #6c7a7d;
        }
    </style>
</head>
<body class="<?php echo $success ? 'success' : 'error'; ?>">
    <div class="container">
        <div class="icon">
            <?php echo $success ? '✔' : '✖'; ?>
        </div>
        <h1><?php echo $pageTitle; ?></h1>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>

        <?php if ($success && $appointmentDetails): ?>
            <div class="details">
                <p><strong>Unidade:</strong> <?php echo htmlspecialchars($appointmentDetails[
'unit_name
'] ?? $unitId); ?></p>
                <p><strong>Serviço:</strong> <?php echo htmlspecialchars($appointmentDetails[
'service_name
'] ?? $serviceId); ?></p>
                <p><strong>Veículo:</strong> <?php echo htmlspecialchars($appointmentDetails[
'vehicle_description
'] ?? $vehicleId); ?></p>
                <p><strong>Data:</strong> <?php echo htmlspecialchars(DateTime::createFromFormat(
'Y-m-d
', $appointmentDetails[
'appointment_date
'])->format(
'd/m/Y
')); ?></p>
                <p><strong>Horário:</strong> <?php echo htmlspecialchars(DateTime::createFromFormat(
'H:i:s
', $appointmentDetails[
'start_time
'])->format(
'H:i
')); ?> - <?php echo htmlspecialchars(DateTime::createFromFormat(
'H:i:s
', $appointmentDetails[
'end_time
'])->format(
'H:i
')); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($appointmentDetails[
'status
'])); ?></p>
                <p><strong>Pagamento:</strong> <?php echo htmlspecialchars(ucfirst($appointmentDetails[
'payment_status
'])); ?></p>
            </div>
            <div class="actions">
                <a href="/my_appointments.php">Ver Meus Agendamentos</a>
                <a href="/" class="secondary">Página Inicial</a>
            </div>
        <?php else: ?>
            <div class="actions">
                <a href="/select_vehicle.php?unit_id=<?php echo $unitId; ?>&service_id=<?php echo $serviceId; ?>&date=<?php echo $appointmentDate; ?>&start_time=<?php echo $startTime; ?>">Tentar Novamente</a>
                <a href="/" class="secondary">Página Inicial</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

