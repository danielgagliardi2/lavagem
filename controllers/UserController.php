<?php

// /controllers/UserController.php - Handles user-specific actions (profile, appointments, etc.)

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';

class UserController {
    private $db;
    private $userModel;
    private $appointmentModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new UserModel();
        $this->appointmentModel = new AppointmentModel();
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // GET /api/users/me
    public function getProfile() {
        if (!Auth::isLoggedIn()) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Login requerido.'
            ], 401);
        }
        $userId = Auth::getUserId();

        // $user = $this->userModel->getUserById($userId);
        // Placeholder data:
        $user = [
            'id' => $userId,
            'name' => 'Cliente Teste Logado',
            'email' => 'cliente@teste.com',
            'phone' => '(11) 91234-5678',
            'cpf' => '123.456.789-00', // Should be masked or handled carefully
            'address' => 'Rua Exemplo, 123, São Paulo',
            'role' => 'customer' // Include role if needed
        ];

        if ($user) {
            // Remove sensitive data before sending
            unset($user['password_hash']); 
            $this->jsonResponse([
                'success' => true, 
                'user' => $user
            ]);
        } else {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Usuário não encontrado.'
            ], 404);
        }
    }

    // POST or PATCH /api/users/me
    public function updateProfile() {
        if (!Auth::isLoggedIn()) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Login requerido.'
            ], 401);
        }
        $userId = Auth::getUserId();

        $inputData = json_decode(file_get_contents('php://input'), true);

        if (!$inputData) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Dados inválidos.'
            ], 400);
        }

        // Data validation (basic example)
        $updateData = [];
        if (isset($inputData['name']) && !empty(trim($inputData['name']))) {
            $updateData['name'] = trim($inputData['name']);
        }
        if (isset($inputData['email']) && filter_var($inputData['email'], FILTER_VALIDATE_EMAIL)) {
            // Check if email is already taken by another user (implement in model)
            // if ($this->userModel->isEmailTaken($inputData['email'], $userId)) { ... error ... }
            $updateData['email'] = $inputData['email'];
        }
        if (isset($inputData['phone'])) {
            $updateData['phone'] = $inputData['phone']; // Add more validation if needed
        }
        if (isset($inputData['address'])) {
            $updateData['address'] = $inputData['address'];
        }
        // Password update
        if (isset($inputData['password']) && !empty($inputData['password'])) {
            // Add validation for password strength if needed
            $updateData['password_hash'] = password_hash($inputData['password'], PASSWORD_DEFAULT);
        }

        if (empty($updateData)) {
             $this->jsonResponse([
                'success' => false, 
                'message' => 'Nenhum dado válido para atualizar.'
            ], 400);
        }

        // Update user in the database
        // $success = $this->userModel->updateUser($userId, $updateData);
        // Placeholder:
        $success = true;

        if ($success) {
            // Update session data if needed (e.g., name)
            if (isset($updateData['name'])) {
                $_SESSION['user_name'] = $updateData['name'];
            }
             $this->jsonResponse([
                'success' => true, 
                'message' => 'Perfil atualizado com sucesso!'
            ]);
        } else {
             $this->jsonResponse([
                'success' => false, 
                'message' => 'Erro ao atualizar o perfil.'
            ], 500);
        }
    }
    
    // GET /api/users/me/appointments
    public function getMyAppointments() {
        if (!Auth::isLoggedIn() || !Auth::hasRole('customer')) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Login de cliente requerido.'
            ], 401);
        }
        
        $userId = Auth::getUserId();
        
        try {
            // Buscar agendamentos do usuário usando o método existente no model
            $appointments = $this->appointmentModel->getAppointmentsByUser($userId);
            
            // Processar os dados para o formato adequado para o frontend
            $formattedAppointments = [];
            foreach ($appointments as $appointment) {
                // Formatar data e hora para exibição
                $startDateTime = new DateTime($appointment['start_datetime']);
                $endDateTime = new DateTime($appointment['end_datetime']);
                
                $formattedAppointments[] = [
                    'id' => $appointment['id'],
                    'date' => $startDateTime->format('d/m/Y'),
                    'start_time' => $startDateTime->format('H:i'),
                    'end_time' => $endDateTime->format('H:i'),
                    'service_name' => $appointment['service_name'],
                    'price' => $appointment['price'],
                    'vehicle_plate' => $appointment['vehicle_plate'],
                    'unit_name' => $appointment['unit_name'],
                    'status' => $appointment['status'],
                    'payment_status' => $appointment['payment_status'],
                    // Adicionar campos para facilitar a exibição no frontend
                    'is_upcoming' => $startDateTime > new DateTime(),
                    'is_today' => $startDateTime->format('Y-m-d') === date('Y-m-d'),
                    'is_completed' => $appointment['status'] === 'completed',
                    'is_in_progress' => $appointment['status'] === 'in_progress',
                    'is_cancelled' => $appointment['status'] === 'cancelled',
                    'is_paid' => $appointment['payment_status'] === 'paid'
                ];
            }
            
            // Separar agendamentos em categorias para facilitar a exibição
            $upcoming = array_filter($formattedAppointments, function($a) {
                return $a['is_upcoming'] || $a['is_today'];
            });
            
            $past = array_filter($formattedAppointments, function($a) {
                return !$a['is_upcoming'] && !$a['is_today'];
            });
            
            // Ordenar por data (mais recentes primeiro para passados, próximos primeiro para futuros)
            usort($upcoming, function($a, $b) {
                $dateA = DateTime::createFromFormat('d/m/Y H:i', $a['date'] . ' ' . $a['start_time']);
                $dateB = DateTime::createFromFormat('d/m/Y H:i', $b['date'] . ' ' . $b['start_time']);
                return $dateA <=> $dateB; // Ordem crescente para próximos
            });
            
            usort($past, function($a, $b) {
                $dateA = DateTime::createFromFormat('d/m/Y H:i', $a['date'] . ' ' . $a['start_time']);
                $dateB = DateTime::createFromFormat('d/m/Y H:i', $b['date'] . ' ' . $b['start_time']);
                return $dateB <=> $dateA; // Ordem decrescente para passados
            });
            
            $this->jsonResponse([
                'success' => true,
                'appointments' => [
                    'upcoming' => array_values($upcoming),
                    'past' => array_values($past),
                    'total' => count($formattedAppointments)
                ]
            ]);
            
        } catch (Exception $e) {
            // Log error
            error_log('Erro ao buscar agendamentos: ' . $e->getMessage());
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erro ao buscar seus agendamentos. Por favor, tente novamente.'
            ], 500);
        }
    }
}

