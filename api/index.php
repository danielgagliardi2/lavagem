<?php

// /api/index.php - Roteador Principal para Requisições de API

header(
'Content-Type: application/json


');

require_once __DIR__ . 
'/../core/Auth.php


';
require_once __DIR__ . 
'/../core/Database.php


';
require_once __DIR__ . 
'/../models/ServiceModel.php


';
require_once __DIR__ . 
'/../models/UnitModel.php


';
require_once __DIR__ . 
'/../models/UserModel.php


';
require_once __DIR__ . 
'/../models/UnitServiceModel.php


';
require_once __DIR__ . 
'/../models/VehicleModel.php


';
require_once __DIR__ . 
'/../models/UnitScheduleModel.php


'; // Adicionado
require_once __DIR__ . 
'/../models/AppointmentModel.php


'; // Adicionado
require_once __DIR__ . 
'/../controllers/AdminServiceController.php


';
require_once __DIR__ . 
'/../controllers/AdminUnitController.php


';
require_once __DIR__ . 
'/../controllers/AdminUserController.php


';
require_once __DIR__ . 
'/../controllers/AuthController.php


';
rrequire_once __DIR__ . 
'/../controllers/VehicleController.php
';
require_once __DIR__ . 
'/../controllers/UnitController.php
'; // Adicionado
require_once __DIR__ . 
'/../controllers/OperatorController.php
'; // Adicionado
require_once __DIR__ . 
'/../controllers/AdminReportController.php
'; // Adicionado

Auth::startSession();;

// Obter a URI da requisição e método HTTP
$requestUri = parse_url($_SERVER[
'REQUEST_URI


'], PHP_URL_PATH);
$requestMethod = $_SERVER[
'REQUEST_METHOD


'];
$basePath = 
'/api


'; // Base path para a API
$route = str_replace($basePath, 
''


, $requestUri);
$route = trim($route, 
'/'


);
$routeParts = explode(
'/'


, $route);

// Roteamento
$resource = $routeParts[0] ?? 
''


;
$actionOrId = $routeParts[1] ?? null;
$subResource = $routeParts[2] ?? null;

// --- Resposta Padrão ---
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// --- Rotas de Autenticação ---
if ($resource === 
'auth


') {
    $controller = new AuthController();
    if ($actionOrId === 
'login


' && $requestMethod === 
'POST


') {
        $controller->login();
    } elseif ($actionOrId === 
'register


' && $requestMethod === 
'POST


') {
        $controller->register();
    } else {
        jsonResponse([
'error


' => 
'Endpoint de autenticação não encontrado


'], 404);
    }
}

// --- Rotas do Admin (Protegidas) ---
if ($resource === 
'admin


') {
    // Verifica se é admin
    if (!Auth::isLoggedIn() || !Auth::hasRole(
'admin


')) {
        jsonResponse([
'error


' => 
'Acesso não autorizado


'], 403);
    }

    $adminResource = $actionOrId ?? 
''


;
    $adminAction = $subResource ?? null;
    $adminResourceId = $routeParts[3] ?? null;

    // --- CRUD de Serviços (Admin) ---
    if ($adminResource === 
'services


') {
        $controller = new AdminServiceController();
        if ($adminAction === 
'create


' && $requestMethod === 
'POST


') {
            $controller->create();
        } elseif ($adminAction === 
'update


' && $requestMethod === 
'POST


') {
            $controller->update();
        } elseif ($adminAction === 
'delete


' && $requestMethod === 
'POST


') {
            $controller->delete();
        } else {
            jsonResponse([
'error


' => 
'Ação de serviço admin inválida


'], 404);
        }
    }
    // --- CRUD de Unidades (Admin) ---
    elseif ($adminResource === 
'units


') {
        $controller = new AdminUnitController();
        if ($adminAction === 
'create


' && $requestMethod === 
'POST


') {
            $controller->create();
        } elseif ($adminAction === 
'update


' && $requestMethod === 
'POST


') {
            $controller->update();
        } elseif ($adminAction === 
'delete


' && $requestMethod === 
'POST


') {
            $controller->delete();
        } elseif ($adminAction === 
'add_service


' && $requestMethod === 
'POST


') {
            // Lógica para adicionar serviço à unidade (pode ir para AdminUnitController)
            $inputData = $_POST;
            if (empty($inputData[
'unit_id


']) || empty($inputData[
'service_id


'])) {
                 jsonResponse([
'error


' => 
'IDs de unidade e serviço são obrigatórios


'], 400);
            }
            // Chamar $unitServiceModel->addServiceToUnit(...)
            Auth::setSession(
'admin_message


', [
'type


' => 
'success


', 
'text


' => 
'Serviço associado (placeholder)!


']);
            header(
'Location: /admin/unit_services.php?unit_id=


' . $inputData[
'unit_id


']);
            exit;
        } elseif ($adminAction === 
'remove_service


' && $requestMethod === 
'POST


') {
            // Lógica para remover serviço da unidade (pode ir para AdminUnitController)
             $inputData = $_POST;
            if (empty($inputData[
'unit_id


']) || empty($inputData[
'service_id


'])) {
                 jsonResponse([
'error


' => 
'IDs de unidade e serviço são obrigatórios


'], 400);
            }
            // Chamar $unitServiceModel->removeServiceFromUnit(...)
            Auth::setSession(
'admin_message


', [
'type


' => 
'success


', 
'text


' => 
'Serviço desassociado (placeholder)!


']);
            header(
'Location: /admin/unit_services.php?unit_id=


' . $inputData[
'unit_id


']);
            exit;
        } else {
            jsonResponse([
'error


' => 
'Ação de unidade admin inválida


'], 404);
        }
    }
    // --- CRUD de Usuários (Admin) ---
    elseif ($adminResource === 
'users


') {
        $controller = new AdminUserController();
        if ($adminAction === 
'create


' && $requestMethod === 
'POST


') {
            $controller->create();
        } elseif ($adminAction === 
'update


' && $requestMethod === 
'POST


') {
            $controller->update();
        } elseif ($adminAction === 
'delete


' && $requestMethod === 
'POST


') {
            $controller->delete();
        } else {
            jsonResponse([
'error'
 => 
'Ação de usuário admin inválida'
], 404);
        }
    }
    // --- Relatórios (Admin) ---
    elseif ($adminResource === 
'reports'
) {
        $controller = new AdminReportController();
        // GET /api/admin/reports/daily?date=YYYY-MM-DD[&unit_id=X]
        if ($adminAction === 
'daily'
 && $requestMethod === 
'GET'
) {
            $controller->getDailyReport();
        } else {
            jsonResponse([
'error'
 => 
'Ação de relatório admin inválida'
], 404);
        }
    }
    // --- Adicionar outros recursos admin aqui ---
    else {
         jsonResponse([
'error'
 => 
'Recurso admin não encontrado'
], 404);
    });
    }
}

// --- Rotas do Usuário Logado (Cliente) ---
if ($resource === 
'users


' && $actionOrId === 
'me


') {
    // Verifica se é cliente logado
    if (!Auth::isLoggedIn() || !Auth::hasRole(
'customer


')) {
        jsonResponse([
'error


' => 
'Requer login de cliente


'], 401);
    }

    $userResource = $subResource ?? null;
    $userResourceId = $routeParts[3] ?? null;

    // --- CRUD de Veículos do Cliente ---
    if ($userResource === 
'vehicles


') {
        $controller = new VehicleController();
        if ($requestMethod === 
'GET


' && !$userResourceId) {
            $controller->listMyVehicles();
        } elseif ($requestMethod === 
'POST


' && !$userResourceId) {
            $controller->addVehicle();
        } elseif ($requestMethod === 
'DELETE


' && $userResourceId) {
            $controller->deleteVehicle($userResourceId);
        } else {
             jsonResponse([
'error


' => 
'Ação de veículo inválida


'], 404);
        }
    }
    // --- Perfil e Agendamentos do Usuário ---
    elseif ($userResource === null && $requestMethod === 'GET') {
        // GET /api/users/me
        require_once __DIR__ . '/../controllers/UserController.php';
        $userController = new UserController();
        $userController->getProfile();
    }
    elseif ($userResource === 'appointments' && $requestMethod === 'GET') {
        // GET /api/users/me/appointments
        require_once __DIR__ . '/../controllers/UserController.php';
        $userController = new UserController();
        $userController->getMyAppointments();
    }
    else {
        jsonResponse(['error' => 'Recurso do usuário não encontrado'], 404);
    }
}

// --- Rotas Públicas da API (Unidades, Agendamentos) ---
if ($resource === 
'units


' && $requestMethod === 
'GET


') {
    $unitSlug = $actionOrId;
    $unitAction = $subResource;
    $controller = new UnitController(); // Instancia o controlador público

    if ($unitSlug && $unitAction === 
'availability


') {
        // GET /api/units/{slug}/availability?month=MM&year=YYYY
        $month = $_GET[
'month


'] ?? date(
'm


');
        $year = $_GET[
'year


'] ?? date(
'Y


');
        $controller->getAvailability($unitSlug, $year, $month);
    } elseif ($unitSlug && $unitAction === 
'schedule


') {
        // GET /api/units/{slug}/schedule?day=YYYY-MM-DD
        $day = $_GET[
'day


'] ?? date(
'Y-m-d


');
        $controller->getSchedule($unitSlug, $day);
    } else {
        jsonResponse([
'error


' => 
'Ação inválida para unidades públicas


'], 404);
    }
} elseif ($resource === 
'appointments


' && $requestMethod === 
'POST


') {
    // P    // POST /api/appointments (Criar agendamento - requer login de cliente)
    if (!Auth::isLoggedIn() || !Auth::hasRole(
'customer
')) {
        jsonResponse([
'error
' => 
'Requer login de cliente
'], 401);
    }
    // Instanciar e chamar o controller real
    require_once __DIR__ . 
'/../controllers/AppointmentController.php
';
    $appController = new AppointmentController();
    $appController->createAppointment(); // Chama o método que lê php://input
ata]);
}


// }

// --- Rotas do Operador (Protegidas) ---
if ($resource === 
'operator'
) {
    // Verifica se é operador logado
    if (!Auth::isLoggedIn() || !Auth::hasRole(
'operator'
)) {
        jsonResponse([
'error'
 => 
'Requer login de operador'
], 401);
    }

    $operatorResource = $actionOrId ?? 
''
;
    $operatorResourceId = $subResource ?? null;
    $controller = new OperatorController();

    // GET /api/operator/appointments?date=YYYY-MM-DD
    if ($operatorResource === 
'appointments'
 && $requestMethod === 
'GET'
 && !$operatorResourceId) {
        $controller->listAppointments();
    }
    // PATCH (ou POST) /api/operator/appointments/{id}/status 
    elseif ($operatorResource === 
'appointments'
 && $operatorResourceId && ($requestMethod === 
'PATCH'
 || $requestMethod === 
'POST'
) && ($routeParts[3] ?? null) === 
'status'
) {
         $controller->updateAppointmentStatus($operatorResourceId);
    }
    // --- Adicionar outras rotas do operador aqui ---
    else {
        jsonResponse([
'error'
 => 
'Recurso do operador não encontrado'
], 404);
    }
}


// --- Endpoint não encontrado (se nenhuma rota correspondeu) ---
jsonResponse([
'error'
 => 
'Endpoint não encontrado'
], 404);;

?>
