<?php

// /controllers/OperatorController.php

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/AppointmentModel.php
';
require_once __DIR__ . 
'/../models/UserModel.php
'; // Para buscar info do operador, como unit_id

class OperatorController {
    private $db;
    private $appointmentModel;
    private $userModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
        // Inject DB connection
        // $this->appointmentModel->db = $this->db;
        // $this->userModel->db = $this->db;
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header(
'Content-Type: application/json
');
        echo json_encode($data);
        exit;
    }

    // List appointments for the logged-in operator's unit (e.g., for today)
    public function listAppointments() {
        // 1. Check if user is logged in as operator
        if (!Auth::isLoggedIn() || !Auth::hasRole(
'operator
')) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Requer login de operador.
'], 401);
        }
        $operatorId = Auth::getUserId();

        // 2. Get operator's unit ID (assuming it's stored in users table or session)
        // $operatorInfo = $this->userModel->getUserById($operatorId);
        // $unitId = $operatorInfo['unit_id'] ?? null;
        // Placeholder:
        $unitId = 1; // Assume operador está na unidade 1

        if (!$unitId) {
             $this->jsonResponse([
'success
' => false, 
'message
' => 
'Operador não associado a uma unidade.
'], 403);
        }

        // 3. Get filter parameters (e.g., date - default to today)
        $filterDate = $_GET[
'date
'] ?? date(
'Y-m-d
');

        // 4. Fetch appointments from the model
        // $appointments = $this->appointmentModel->getAppointmentsByUnitAndDate($unitId, $filterDate);
        // Placeholder:
        $appointments = [];
        if ($filterDate === date(
'Y-m-d
')) { // Simula agendamentos para hoje
            $appointments = [
                [
'id
' => 501, 
'appointment_date
' => $filterDate, 
'start_time
' => 
'08:00:00
', 
'end_time
' => 
'10:00:00
', 
'status
' => 
'scheduled
', 
'payment_status
' => 
'pending
', 
'user_name
' => 
'Cliente Exemplo 1
', 
'vehicle_model
' => 
'Honda Civic
', 
'vehicle_year
' => 2022, 
'vehicle_plate
' => 
'BRA1Z23
', 
'user_phone
' => 
'(11) 98765-4321
', 
'service_name
' => 
'Lavagem Completa com Cera
'],
                [
'id
' => 502, 
'appointment_date
' => $filterDate, 
'start_time
' => 
'10:00:00
', 
'end_time
' => 
'11:00:00
', 
'status
' => 
'in_progress
', 
'payment_status
' => 
'pending
', 
'user_name
' => 
'Cliente Exemplo 2
', 
'vehicle_model
' => 
'Toyota Corolla
', 
'vehicle_year
' => 2021, 
'vehicle_plate
' => 
'MER3C45
', 
'user_phone
' => 
'(21) 91234-5678
', 
'service_name
' => 
'Lavagem Simples
'],
                 [
'id
' => 503, 
'appointment_date
' => $filterDate, 
'start_time
' => 
'14:00:00
', 
'end_time
' => 
'16:00:00
', 
'status
' => 
'completed
', 
'payment_status
' => 
'paid
', 
'user_name
' => 
'Cliente Exemplo 3
', 
'vehicle_model
' => 
'Fiat Argo
', 
'vehicle_year
' => 2023, 
'vehicle_plate
' => 
'XYZ7A89
', 
'user_phone
' => 
'(31) 99999-8888
', 
'service_name
' => 
'Lavagem Completa com Cera
'],
            ];
        }

        // 5. Return JSON response
        $this->jsonResponse([
'success
' => true, 
'appointments
' => $appointments]);
    }

    // Update appointment status (start, complete, paid)
    public function updateAppointmentStatus($appointmentId) {
         // 1. Check if user is logged in as operator
        if (!Auth::isLoggedIn() || !Auth::hasRole(
'operator
')) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Requer login de operador.
'], 401);
        }
        $operatorId = Auth::getUserId();

        // 2. Get operator's unit ID (for validation)
        // $operatorInfo = $this->userModel->getUserById($operatorId);
        // $unitId = $operatorInfo['unit_id'] ?? null;
        // Placeholder:
        $unitId = 1; 
        if (!$unitId) {
             $this->jsonResponse([
'success
' => false, 
'message
' => 
'Operador não associado a uma unidade.
'], 403);
        }

        // 3. Get input data (status to update)
        // PATCH requests are tricky in plain PHP, often use POST with _method or read php://input
        $inputData = json_decode(file_get_contents(
'php://input
'), true);
        $newStatus = $inputData[
'status
'] ?? null; // e.g., 'in_progress', 'completed'
        $newPaymentStatus = $inputData[
'payment_status
'] ?? null; // e.g., 'paid'

        if (!$newStatus && !$newPaymentStatus) {
             $this->jsonResponse([
'success
' => false, 
'message
' => 
'Nenhum status para atualizar fornecido.
'], 400);
        }

        // 4. Validate the appointment belongs to the operator's unit
        // $appointment = $this->appointmentModel->getAppointmentById($appointmentId);
        // Placeholder:
        $appointment = [
'id
' => $appointmentId, 
'unit_id
' => 1]; // Assume pertence à unidade 1

        if (!$appointment || $appointment[
'unit_id
'] != $unitId) {
             $this->jsonResponse([
'success
' => false, 
'message
' => 
'Agendamento não encontrado ou não pertence a esta unidade.
'], 404);
        }

        // 5. Update the status in the model
        $updateData = [];
        if ($newStatus) {
            // Add validation for allowed status transitions if needed
            $updateData[
'status
'] = $newStatus;
        }
        if ($newPaymentStatus) {
             // Add validation for allowed payment status transitions if needed
            $updateData[
'payment_status
'] = $newPaymentStatus;
        }

        // $success = $this->appointmentModel->updateStatus($appointmentId, $updateData);
        // Placeholder:
        $success = true;

        // 6. Return JSON response
        if ($success) {
            $this->jsonResponse([
'success
' => true, 
'message
' => 
'Status do agendamento atualizado com sucesso.
']);
        } else {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Erro ao atualizar o status do agendamento.
'], 500);
        }
    }
}

