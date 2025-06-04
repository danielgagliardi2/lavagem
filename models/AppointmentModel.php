<?php

// /models/AppointmentModel.php

// Incluir a conexão com o banco de dados
require_once __DIR__ . '/../core/Database.php';
// Incluir Model de Serviço para obter duração
require_once __DIR__ . '/ServiceModel.php';

class AppointmentModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Criar um novo agendamento
    public function createAppointment($unitId, $userId, $vehicleId, $serviceId, $startDatetime) {
        // Lógica para criar agendamento
        // 1. Buscar a duração do serviço
        $serviceModel = new ServiceModel();
        $service = $serviceModel->getServiceById($serviceId);
        if (!$service) return false;
        $durationMinutes = $service['duration_minutes'];

        // 2. Calcular end_datetime
        $start = new DateTime($startDatetime);
        $end = clone $start;
        $end->add(new DateInterval('PT' . $durationMinutes . 'M'));
        $endDatetime = $end->format('Y-m-d H:i:s');

        // 3. Verificar disponibilidade (lógica complexa, talvez em um Controller/Service)
        // - Checar se o horário está dentro do funcionamento da unidade
        // - Checar se não há conflito com outros agendamentos para a mesma unidade

        // 4. Inserir no banco de dados
        $stmt = $this->db->prepare(
            "INSERT INTO appointments (unit_id, user_id, vehicle_id, service_id, start_datetime, end_datetime, status, payment_status) " .
            "VALUES (:unit_id, :user_id, :vehicle_id, :service_id, :start_datetime, :end_datetime, 'scheduled', 'pending')"
        );
        $stmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':vehicle_id', $vehicleId, PDO::PARAM_INT);
        $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $stmt->bindParam(':start_datetime', $startDatetime);
        $stmt->bindParam(':end_datetime', $endDatetime);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // Buscar agendamentos de uma unidade para um dia específico
    public function getAppointmentsByUnitAndDate($unitId, $date) {
        $startOfDay = $date . ' 00:00:00';
        $endOfDay = $date . ' 23:59:59';
        
        $stmt = $this->db->prepare(
            "SELECT a.*, s.name as service_name, s.price, v.plate as vehicle_plate 
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN vehicles v ON a.vehicle_id = v.id
            WHERE a.unit_id = :unit_id 
            AND a.start_datetime BETWEEN :start_day AND :end_day 
            ORDER BY a.start_datetime"
        );
        
        $stmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
        $stmt->bindParam(':start_day', $startOfDay);
        $stmt->bindParam(':end_day', $endOfDay);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca dados para o relatório diário de agendamentos
     * 
     * @param string $date Data no formato Y-m-d
     * @param int|null $unitId ID da unidade (opcional)
     * @return array Dados do relatório
     */
    public function getDailyReportData($date, $unitId = null) {
        $startOfDay = $date . ' 00:00:00';
        $endOfDay = $date . ' 23:59:59';
        
        // Construir a consulta base
        $query = "
            SELECT 
                a.id, 
                a.start_datetime, 
                a.status, 
                a.payment_status,
                s.name as service_name, 
                s.price, 
                v.plate as vehicle_plate,
                u.name as unit_name,
                u.id as unit_id
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN vehicles v ON a.vehicle_id = v.id
            JOIN units u ON a.unit_id = u.id
            WHERE a.start_datetime BETWEEN :start_day AND :end_day
        ";
        
        // Adicionar filtro de unidade se especificado
        if ($unitId) {
            $query .= " AND a.unit_id = :unit_id";
        }
        
        $query .= " ORDER BY a.start_datetime";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':start_day', $startOfDay);
        $stmt->bindParam(':end_day', $endOfDay);
        
        if ($unitId) {
            $stmt->bindParam(':unit_id', $unitId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Processar os dados para o formato do relatório
        $totalAppointments = count($appointments);
        $completedAppointments = 0;
        $totalRevenue = 0;
        $details = [];
        
        foreach ($appointments as $appointment) {
            // Calcular totais
            if ($appointment['status'] === 'completed') {
                $completedAppointments++;
                if ($appointment['payment_status'] === 'paid') {
                    $totalRevenue += $appointment['price'];
                }
            }
            
            // Formatar detalhes para o relatório
            $details[] = [
                'id' => $appointment['id'],
                'start_time' => date('H:i', strtotime($appointment['start_datetime'])),
                'service_name' => $appointment['service_name'],
                'vehicle_plate' => $appointment['vehicle_plate'],
                'status' => $appointment['status'],
                'payment_status' => $appointment['payment_status'],
                'price' => $appointment['price'],
                'unit_name' => $appointment['unit_name'],
                'unit_id' => $appointment['unit_id']
            ];
        }
        
        // Montar o resultado final
        return [
            'summary' => [
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'total_revenue' => $totalRevenue,
                'date' => $date,
                'unit_id' => $unitId
            ],
            'details' => $details
        ];
    }

    // Buscar um agendamento pelo ID
    public function getAppointmentById($id) {
        $stmt = $this->db->prepare(
            "SELECT a.*, s.name as service_name, s.price, v.plate as vehicle_plate, u.name as unit_name
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN vehicles v ON a.vehicle_id = v.id
            JOIN units u ON a.unit_id = u.id
            WHERE a.id = :id"
        );
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Atualizar o status de um agendamento
    public function updateAppointmentStatus($id, $status, $paymentStatus = null) {
        $query = "UPDATE appointments SET status = :status";
        
        if ($paymentStatus !== null) {
            $query .= ", payment_status = :payment_status";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status);
        
        if ($paymentStatus !== null) {
            $stmt->bindParam(':payment_status', $paymentStatus);
        }
        
        return $stmt->execute();
    }

    // Buscar agendamentos de um usuário
    public function getAppointmentsByUser($userId) {
        $stmt = $this->db->prepare(
            "SELECT a.*, s.name as service_name, s.price, v.plate as vehicle_plate, u.name as unit_name
            FROM appointments a
            JOIN services s ON a.service_id = s.id
            JOIN vehicles v ON a.vehicle_id = v.id
            JOIN units u ON a.unit_id = u.id
            WHERE a.user_id = :user_id
            ORDER BY a.start_datetime DESC"
        );
        
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

