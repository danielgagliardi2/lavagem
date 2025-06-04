<?php

// /public/select_service.php - Seleção de Serviço

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

Auth::startSession();

// 1. Verificar se usuário está logado
if (!Auth::isLoggedIn() || !Auth::hasRole(
'customer
')) {
    // Salvar parâmetros na sessão para redirecionar após login
    $_SESSION[
'pending_appointment_params
'] = $_GET;
    header(
'Location: /login.php?message=login_required&redirect=/select_service.php
');
    exit;
}

// 2. Obter parâmetros da URL
$unitId = filter_input(INPUT_GET, 
'unit_id
', FILTER_VALIDATE_INT);
$appointmentDate = filter_input(INPUT_GET, 
'date
'); // YYYY-MM-DD
$startTime = filter_input(INPUT_GET, 
'start_time
'); // HH:MM

// 3. Validar parâmetros básicos
if (!$unitId || !$appointmentDate || !$startTime || 
    !DateTime::createFromFormat(
'Y-m-d
', $appointmentDate) || 
    !DateTime::createFromFormat(
'H:i
', $startTime)) {
    // Redirecionar para a página da unidade ou mostrar erro
    // Idealmente, buscar o slug da unidade pelo ID para redirecionar corretamente
    header(
'Location: /?error=invalid_params
'); 
    exit;
}

// 4. Buscar informações da unidade e serviços disponíveis
$unit = null;
$unitServices = [];
$pageTitle = 
"Selecionar Serviço"
;

try {
    $db = Database::getInstance()->getConnection();
    $unitModel = new UnitModel();
    $serviceModel = new ServiceModel();
    // $unitModel->db = $db;
    // $serviceModel->db = $db;

    // Buscar unidade pelo ID (precisaria do método getUnitById)
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

    if (!$unit) {
        throw new Exception(
"Unidade não encontrada."
);
    }
    $pageTitle = 
"Selecionar Serviço - " . htmlspecialchars($unit[
'name
']);

    // Buscar serviços associados a esta unidade
    // $unitServices = $serviceModel->getServicesByUnitId($unitId);
    // Placeholder (mesmo de unit_page.php):
    if ($unitId == 1) {
         $unitServices = [
            [
'id
' => 1, 
'name
' => 
'Lavagem Simples
', 
'duration_minutes
' => 60, 
'description
' => 
'Lavagem externa rápida.'
, 
'price
' => 50.00],
            [
'id
' => 2, 
'name
' => 
'Lavagem Completa com Cera
', 
'duration_minutes
' => 120, 
'description
' => 
'Lavagem externa, interna e aplicação de cera.'
, 
'price
' => 100.00]
        ];
    } else {
         $unitServices = [
             [
'id
' => 1, 
'name
' => 
'Lavagem Simples
', 
'duration_minutes
' => 60, 
'description
' => 
'Lavagem externa rápida.'
, 
'price
' => 55.00]
        ];
    }

} catch (Exception $e) {
    error_log(
"Erro ao buscar dados para seleção de serviço: " . $e->getMessage());
    // Mostrar erro mais amigável
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
        /* Estilos adaptados de selecao_servicos_mockup.html e unit_page.php */
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
        .appointment-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.1em;
        }
        .appointment-info strong {
            color: #0056b3;
        }
        .service-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .service-item {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 5px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: box-shadow 0.2s;
        }
        .service-item:hover {
             box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .service-header h3 {
            margin: 0;
            font-size: 1.3em;
            color: #3498db;
            flex-basis: 100%; /* Mobile */
            margin-bottom: 10px; /* Mobile */
        }
        .service-details {
            font-size: 0.95em;
            color: #555;
            line-height: 1.5;
        }
        .service-price-duration {
             font-weight: bold;
             color: #2ecc71; /* Verde */
             font-size: 1.1em;
             text-align: right;
             flex-basis: 100%; /* Mobile */
             margin-bottom: 15px; /* Mobile */
        }
        .select-button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s;
            align-self: flex-end; /* Alinha botão à direita */
        }
        .select-button:hover {
            background-color: #2980b9;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }

        /* Desktop adjustments */
        @media (min-width: 600px) {
            .service-header {
                flex-wrap: nowrap;
                align-items: center;
            }
             .service-header h3 {
                flex-basis: auto;
                margin-bottom: 0;
            }
            .service-price-duration {
                flex-basis: auto;
                margin-bottom: 0;
            }
            .service-item {
                 flex-direction: row;
                 justify-content: space-between;
                 align-items: center;
                 flex-wrap: wrap; /* Allow wrapping for description */
            }
            .service-info-wrapper { /* Group header, desc, price */
                flex-grow: 1;
                margin-right: 20px; /* Space before button */
            }
             .service-details {
                 width: 100%; /* Description takes full width below header */
                 margin-top: 5px;
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
        <h2>Selecione o Serviço Desejado</h2>

        <div class="appointment-info">
            Agendando para: <strong><?php echo $displayDate; ?></strong> às <strong><?php echo $startTime; ?></strong>
        </div>

        <?php if (empty($unitServices)): ?>
            <p>Nenhum serviço disponível para esta unidade no momento.</p>
        <?php else: ?>
            <ul class="service-list">
                <?php foreach ($unitServices as $service): ?>
                    <li class="service-item">
                        <div class="service-info-wrapper">
                            <div class="service-header">
                                <h3><?php echo htmlspecialchars($service[
'name
']); ?></h3>
                                <span class="service-price-duration">
                                    R$ <?php echo number_format($service[
'price
'] ?? 0, 2, 
',
', 
'.
'); ?> | 
                                    <?php echo htmlspecialchars($service[
'duration_minutes
']); ?> min
                                </span>
                            </div>
                            <p class="service-details"><?php echo htmlspecialchars($service[
'description
'] ?? 
'Sem descrição.'
); ?></p>
                        </div>
                        <form action="/select_vehicle.php" method="GET">
                            <input type="hidden" name="unit_id" value="<?php echo $unitId; ?>">
                            <input type="hidden" name="date" value="<?php echo $appointmentDate; ?>">
                            <input type="hidden" name="start_time" value="<?php echo $startTime; ?>">
                            <input type="hidden" name="service_id" value="<?php echo $service[
'id
']; ?>">
                            <button type="submit" class="select-button">Selecionar</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <a href="/units/<?php echo htmlspecialchars($unit[
'slug
']); ?>" class="back-link">Voltar ao Calendário</a>
    </div>
</body>
</html>

