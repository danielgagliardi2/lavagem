<?php

// /controllers/AppointmentController.php

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
'/../models/ServiceModel.php
';
require_once __DIR__ . 
'/../models/UnitScheduleModel.php
';
require_once __DIR__ . 
'/../models/VehicleModel.php
'; // To validate vehicle ownership

class AppointmentController {
    private $db;
    private $appointmentModel;
    private $serviceModel;
    private $unitScheduleModel;
    private $vehicleModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->appointmentModel = new AppointmentModel();
        $this->serviceModel = new ServiceModel();
        $this->unitScheduleModel = new UnitScheduleModel();
        $this->vehicleModel = new VehicleModel();
        // Inject DB connection (better approach)
        // $this->appointmentModel->db = $this->db;
        // $this->serviceModel->db = $this->db;
        // $this->unitScheduleModel->db = $this->db;
        // $this->vehicleModel->db = $this->db;
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header(
'Content-Type: application/json
');
        echo json_encode($data);
        exit;
    }

    // Create a new appointment
    public function createAppointment() {
        // 1. Check if user is logged in as customer
        if (!Auth::isLoggedIn() || !Auth::hasRole(
'customer
')) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Requer login de cliente.
'], 401);
        }
        $userId = Auth::getUserId();

        // 2. Get input data
        $inputData = json_decode(file_get_contents(
'php://input
'), true);
        if (!$inputData) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Dados inválidos.
'], 400);
        }

        // 3. Validate required fields
        $requiredFields = [
'unit_id
', 
'service_id
', 
'vehicle_id
', 
'appointment_date
', 
'start_time
'];
        foreach ($requiredFields as $field) {
            if (empty($inputData[$field])) {
                $this->jsonResponse([
'success
' => false, 
'message
' => "Campo obrigatório ausente: $field"], 400);
            }
        }

        $unitId = $inputData[
'unit_id
'];
        $serviceId = $inputData[
'service_id
'];
        $vehicleId = $inputData[
'vehicle_id
'];
        $appointmentDate = $inputData[
'appointment_date
']; // YYYY-MM-DD
        $startTime = $inputData[
'start_time
']; // HH:MM

        // 4. Validate data formats (Date, Time)
        $selectedDateTime = DateTime::createFromFormat(
'Y-m-d H:i
', "$appointmentDate $startTime");
        if (!$selectedDateTime || $selectedDateTime->format(
'Y-m-d
') !== $appointmentDate || $selectedDateTime->format(
'H:i
') !== $startTime) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Formato de data ou hora inválido.
'], 400);
        }
        $today = new DateTime();
        if ($selectedDateTime < $today) {
             $this->jsonResponse([
'success
' => false, 
'message
' => 
'Não é possível agendar no passado.
'], 400);
        }

        // 5. Verify vehicle ownership
        // $vehicle = $this->vehicleModel->getVehicleByIdAndUser($vehicleId, $userId);
        // Placeholder check:
        $isOwner = true; // Assume true for now
        if (!$isOwner) {
             $this->jsonResponse([
'success
' => false, 
'message
' => 
'Veículo não pertence ao usuário.
'], 403);
        }

        // 6. Get service details (especially duration)
        // $service = $this->serviceModel->getServiceById($serviceId);
        // Placeholder:
        $service = null;
        if ($serviceId == 1) $service = [
'id
' => 1, 
'name
' => 
'Lavagem Simples
', 
'duration_minutes
' => 60];
        if ($serviceId == 2) $service = [
'id
' => 2, 
'name
' => 
'Lavagem Completa com Cera
', 
'duration_minutes
' => 120];

        if (!$service) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Serviço não encontrado.
'], 404);
        }
        $durationMinutes = $service[
'duration_minutes
'];
        $endTime = clone $selectedDateTime;
        $endTime->modify("+$durationMinutes minutes");

        // 7. Check unit operating hours for the selected day and time
        $dayOfWeek = (int)$selectedDateTime->format(
'w
');
        // $schedule = $this->unitScheduleModel->getScheduleForDay($unitId, $dayOfWeek);
        // Placeholder:
        $schedules = [
            1 => [
'is_open
' => true, 
'open_time
' => 
'08:00
', 
'close_time
' => 
'18:00
'], // Seg
            2 => [
'is_open
' => true, 
'open_time
' => 
'08:00
', 
'close_time
' => 
'18:00
'], // Ter
            3 => [
'is_open
' => true, 
'open_time
' => 
'08:00
', 
'close_time
' => 
'18:00
'], // Qua
            4 => [
'is_open
' => true, 
'open_time
' => 
'08:00
', 
'close_time
' => 
'18:00
'], // Qui
            5 => [
'is_open
' => true, 
'open_time
' => 
'08:00
', 
'close_time
' => 
'18:00
'], // Sex
            6 => [
'is_open
' => true, 
'open_time
' => 
'08:00
', 
'close_time
' => 
'12:00
'], // Sab
            0 => [
'is_open
' => false] // Dom
        ];
        $schedule = $schedules[$dayOfWeek];

        if (!$schedule || !$schedule[
'is_open
']) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'A unidade está fechada neste dia.
'], 400);
        }
        $unitOpenTime = new DateTime($appointmentDate . 
' 
' . $schedule[
'open_time
']);
        $unitCloseTime = new DateTime($appointmentDate . 
' 
' . $schedule[
'close_time
']);

        if ($selectedDateTime < $unitOpenTime || $endTime > $unitCloseTime) {
             $this->jsonResponse([
'success
' => false, 
'message
' => 
'O horário selecionado está fora do horário de funcionamento da unidade.
'], 400);
        }

        // 8. Check for conflicting appointments (using the actual duration)
        $isAvailable = $this->appointmentModel->isSlotAvailable($unitId, $appointmentDate, $startTime, $endTime->format(
'H:i:s
'));
        // Placeholder check:
        // $isAvailable = true;
        // if ($appointmentDate === 
'2025-06-04
' && ($startTime === 
'10:00
' || $startTime === 
'14:00
')) {
        //      $isAvailable = false; // Simulate conflict based on UnitController placeholder
        // }

        if (!$isAvailable) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'O horário selecionado não está mais disponível.
'], 409); // 409 Conflict
        }

        // 9. Create the appointment
        $appointmentData = [
            
'user_id
' => $userId,
            
'unit_id
' => $unitId,
            
'vehicle_id
' => $vehicleId,
            
'service_id
' => $serviceId,
            
'appointment_date
' => $appointmentDate,
            
'start_time
' => $startTime,
            
'end_time
' => $endTime->format(
'H:i:s
'), // Calculate end time based on duration
            
'status
' => 
'scheduled
', // Initial status
            
'payment_status
' => 
'pending
' // Initial status
        ];

        // $newAppointmentId = $this->appointmentModel->create($appointmentData);
        // Placeholder:
        $newAppointmentId = rand(1000, 9999);

        if ($newAppointmentId) {
            // Maybe send confirmation email/notification here
            $this->jsonResponse([
                
'success
' => true,
                
'message
' => 
'Agendamento criado com sucesso!
',
                
'appointment_id
' => $newAppointmentId,
                
'details
' => $appointmentData // Return details for confirmation page
            ], 201); // 201 Created
        } else {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Erro ao salvar o agendamento no banco de dados.
'], 500);
        }
    }

    // Add methods for listing user appointments, cancelling, etc. later
}

