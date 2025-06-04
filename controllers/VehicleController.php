<?php

// /controllers/VehicleController.php

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/VehicleModel.php
';

class VehicleController {
    private $db;
    private $vehicleModel;

    public function __construct() {
        // Protege as ações que exigem login do cliente
        if (!Auth::isLoggedIn() || !Auth::hasRole('customer')) {
            $this->jsonResponse(['error' => 'Requer login de cliente'], 401);
        }
        $this->db = Database::getInstance()->getConnection();
        $this->vehicleModel = new VehicleModel();
        // $this->vehicleModel->db = $this->db; // Melhorar com injeção
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Lista os veículos do usuário logado
    public function listMyVehicles() {
        $userId = Auth::getUserId();
        if (!$userId) {
            $this->jsonResponse(['error' => 'Usuário não identificado'], 401);
        }

        try {
            // $vehicles = $this->vehicleModel->getVehiclesByUserId($userId);
            // Placeholder:
            $vehicles = [
                ['id' => 1, 'user_id' => $userId, 'model' => 'Toyota Corolla', 'year' => 2022, 'color' => 'Prata', 'plate' => 'BRA2E19'],
                ['id' => 2, 'user_id' => $userId, 'model' => 'Honda Civic', 'year' => 2021, 'color' => 'Preto', 'plate' => 'MER1C01'],
            ];
            $this->jsonResponse(['success' => true, 'vehicles' => $vehicles]);
        } catch (Exception $e) {
            error_log("Erro ao listar veículos: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao buscar veículos.'], 500);
        }
    }

    // Adiciona um novo veículo para o usuário logado
    public function addVehicle() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Método não permitido'], 405);
        }

        $userId = Auth::getUserId();
        if (!$userId) {
            $this->jsonResponse(['error' => 'Usuário não identificado'], 401);
        }

        $inputData = json_decode(file_get_contents('php://input'), true);

        // Validação básica
        if (empty($inputData['model']) || empty($inputData['year']) || empty($inputData['color']) || empty($inputData['plate'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Todos os campos do veículo são obrigatórios.'], 400);
        }
        if (!is_numeric($inputData['year']) || $inputData['year'] < 1900 || $inputData['year'] > date('Y') + 1) {
             $this->jsonResponse(['success' => false, 'message' => 'Ano do veículo inválido.'], 400);
        }
        // Adicionar validação de formato da placa (Mercosul/Antiga)

        try {
            // Lógica para adicionar veículo
            // $newVehicleId = $this->vehicleModel->addVehicle($userId, $inputData['model'], $inputData['year'], $inputData['color'], $inputData['plate']);
            // if ($newVehicleId) {
            //     $this->jsonResponse(['success' => true, 'message' => 'Veículo adicionado com sucesso!', 'vehicle_id' => $newVehicleId], 201);
            // } else {
            //     $this->jsonResponse(['success' => false, 'message' => 'Falha ao adicionar veículo.'], 500);
            // }
             $this->jsonResponse(['success' => true, 'message' => 'Veículo adicionado (placeholder)!', 'vehicle_id' => rand(10,100)], 201); // Placeholder
        } catch (Exception $e) {
            error_log("Erro ao adicionar veículo: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erro interno ao adicionar veículo.'], 500);
        }
    }

    // Exclui um veículo do usuário logado
    public function deleteVehicle($vehicleId) {
         if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->jsonResponse(['error' => 'Método não permitido'], 405);
        }

        $userId = Auth::getUserId();
        if (!$userId) {
            $this->jsonResponse(['error' => 'Usuário não identificado'], 401);
        }

        if (empty($vehicleId) || !is_numeric($vehicleId)) {
             $this->jsonResponse(['success' => false, 'message' => 'ID do veículo inválido.'], 400);
        }

        try {
            // Lógica para excluir veículo (verificar se pertence ao usuário antes)
            // $success = $this->vehicleModel->deleteVehicle($vehicleId, $userId);
            // if ($success) {
            //     $this->jsonResponse(['success' => true, 'message' => 'Veículo excluído com sucesso!']);
            // } else {
            //     $this->jsonResponse(['success' => false, 'message' => 'Falha ao excluir veículo ou veículo não encontrado.'], 404);
            // }
             $this->jsonResponse(['success' => true, 'message' => 'Veículo excluído (placeholder)!']); // Placeholder
        } catch (Exception $e) {
            error_log("Erro ao excluir veículo: " . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'Erro interno ao excluir veículo.'], 500);
        }
    }

}

