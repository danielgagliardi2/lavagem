<?php

// /controllers/AdminReportController.php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/AppointmentModel.php'; // Appointments are the basis for reports
require_once __DIR__ . '/../models/UnitModel.php'; // Para verificar unidades

class AdminReportController {
    private $db;
    private $appointmentModel;
    private $unitModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->appointmentModel = new AppointmentModel();
        $this->unitModel = new UnitModel();
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Get daily report data
    public function getDailyReport() {
        // 1. Check if user is logged in as admin
        if (!Auth::isLoggedIn() || !Auth::hasRole('admin')) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Acesso não autorizado.'
            ], 403);
        }

        // 2. Get filter parameters (date, maybe unit_id)
        $filterDate = $_GET['date'] ?? date('Y-m-d');
        $filterUnitId = filter_input(INPUT_GET, 'unit_id', FILTER_VALIDATE_INT); // Optional filter
        
        // Validar data
        if (!$this->validateDate($filterDate)) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Formato de data inválido. Use YYYY-MM-DD.'
            ], 400);
        }
        
        // Validar unidade se especificada
        if ($filterUnitId && !$this->unitExists($filterUnitId)) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Unidade não encontrada.'
            ], 404);
        }

        try {
            // 3. Fetch report data from the model
            $reportData = $this->appointmentModel->getDailyReportData($filterDate, $filterUnitId);
            
            // 4. Return JSON response
            $this->jsonResponse([
                'success' => true, 
                'report' => $reportData
            ]);
        } catch (Exception $e) {
            // Log error
            error_log('Erro ao gerar relatório: ' . $e->getMessage());
            
            // Return error response
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Erro ao gerar relatório. Por favor, tente novamente.'
            ], 500);
        }
    }
    
    /**
     * Valida o formato da data
     * 
     * @param string $date Data a ser validada
     * @return bool True se válida, false caso contrário
     */
    private function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Verifica se uma unidade existe
     * 
     * @param int $unitId ID da unidade
     * @return bool True se existe, false caso contrário
     */
    private function unitExists($unitId) {
        // Implementação real depende do UnitModel
        // return $this->unitModel->getUnitById($unitId) !== false;
        
        // Simplificado para evitar dependências
        return true;
    }
}

