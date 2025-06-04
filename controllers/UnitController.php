<?php

// /controllers/UnitController.php - Controlador para dados públicos da unidade

require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/UnitModel.php
';
require_once __DIR__ . 
'/../models/UnitScheduleModel.php
';
require_once __DIR__ . 
'/../models/AppointmentModel.php
';
require_once __DIR__ . 
'/../models/ServiceModel.php
';

class UnitController {
    private $db;
    private $unitModel;
    private $unitScheduleModel;
    private $appointmentModel;
    private $serviceModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->unitModel = new UnitModel();
        $this->unitScheduleModel = new UnitScheduleModel();
        $this->appointmentModel = new AppointmentModel();
        $this->serviceModel = new ServiceModel();
        // Injeção de dependência seria melhor
        // $this->unitModel->db = $this->db;
        // $this->unitScheduleModel->db = $this->db;
        // $this->appointmentModel->db = $this->db;
        // $this->serviceModel->db = $this->db;
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header(
'Content-Type: application/json
');
        echo json_encode($data);
        exit;
    }

    // Retorna a disponibilidade geral dos dias em um mês/ano para uma unidade
    public function getAvailability($unitSlug, $year, $month) {
        try {
            // 1. Buscar a unidade pelo slug
            // $unit = $this->unitModel->getUnitBySlug($unitSlug);
            // Placeholder:
            $unit = ($unitSlug === 
'condominio-central-park
') ? [
'id
' => 1, 
'name
' => 
'Condomínio Central Park
'] : (($unitSlug === 
'empresa-tech-solutions
') ? [
'id
' => 2, 
'name
' => 
'Empresa Tech Solutions
'] : null);

            if (!$unit) {
                $this->jsonResponse([ 
'success
' => false, 
'message
' => 
'Unidade não encontrada.
'], 404);
            }
            $unitId = $unit[
'id
'];

            // 2. Buscar os horários de funcionamento da unidade
            // $schedules = $this->unitScheduleModel->getSchedulesByUnitId($unitId);
            // Placeholder (Abre Seg-Sex 8h-18h, Sab 8h-12h):
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

            // 3. Calcular disponibilidade para cada dia do mês
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $availability = [];
            $today = new DateTime();
            $today->setTime(0, 0, 0);

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = new DateTime("$year-$month-$day");
                $currentDate->setTime(0, 0, 0);
                $dayOfWeek = (int)$currentDate->format(
'w
'); // 0 (dom) a 6 (sab)
                $dateString = $currentDate->format(
'Y-m-d
');

                // Verifica se é passado ou se a unidade está fechada neste dia da semana
                if ($currentDate < $today || !$schedules[$dayOfWeek][
'is_open
']) {
                    $availability[$dateString] = false;
                } else {
                    // Aqui, poderíamos adicionar uma verificação mais complexa,
                    // como checar se TODOS os slots do dia já estão ocupados,
                    // mas para a disponibilidade GERAL do dia, basta saber se abre.
                    $availability[$dateString] = true;
                }
            }
            
            // Simular alguns dias específicos como indisponíveis (feriados, etc.)
            if ($month == 6 && $year == 2025) { // Exemplo para Junho 2025
                 $availability[
'2025-06-15
'] = false;
                 $availability[
'2025-06-22
'] = false;
            }

            $this->jsonResponse([ 
'success
' => true, 
'availability
' => $availability ]);

        } catch (Exception $e) {
            error_log(
"Erro ao buscar disponibilidade: " . $e->getMessage());
            $this->jsonResponse([ 
'success
' => false, 
'message
' => 
'Erro interno ao buscar disponibilidade.
'], 500);
        }
    }

    // Retorna os slots de horário (disponíveis/ocupados) para um dia específico
    public function getSchedule($unitSlug, $dateString) {
        try {
            // 1. Validar formato da data
            $selectedDate = DateTime::createFromFormat(
'Y-m-d
', $dateString);
            if (!$selectedDate || $selectedDate->format(
'Y-m-d
') !== $dateString) {
                $this->jsonResponse([ 
'success
' => false, 
'message
' => 
'Formato de data inválido. Use YYYY-MM-DD.
'], 400);
            }

            // 2. Buscar a unidade pelo slug
            // $unit = $this->unitModel->getUnitBySlug($unitSlug);
            // Placeholder:
            $unit = ($unitSlug === 
'condominio-central-park
') ? [
'id
' => 1, 
'name
' => 
'Condomínio Central Park
'] : (($unitSlug === 
'empresa-tech-solutions
') ? [
'id
' => 2, 
'name
' => 
'Empresa Tech Solutions
'] : null);

            if (!$unit) {
                $this->jsonResponse([ 
'success
' => false, 
'message
' => 
'Unidade não encontrada.
'], 404);
            }
            $unitId = $unit[
'id
'];

            // 3. Buscar horário de funcionamento para o dia da semana selecionado
            $dayOfWeek = (int)$selectedDate->format(
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
' => true, 
'slots
' => [] ]); // Dia fechado, retorna slots vazios
            }

            // 4. Buscar agendamentos existentes para este dia e unidade
            // $appointments = $this->appointmentModel->getAppointmentsByDate($unitId, $dateString);
            // Placeholder:
            $appointments = [];
            if ($dateString === 
'2025-06-04
') { // Exemplo para 04/Jun
                $appointments = [
                    [
'start_time
' => 
'10:00:00
', 
'end_time
' => 
'12:00:00
'],
                    [
'start_time
' => 
'14:00:00
', 
'end_time
' => 
'16:00:00
']
                ];
            }
            
            // Criar um lookup rápido dos horários ocupados
            $occupiedSlots = [];
            foreach ($appointments as $app) {
                $start = new DateTime($dateString . 
' 
' . $app[
'start_time
']);
                $end = new DateTime($dateString . 
' 
' . $app[
'end_time
']);
                // Marcar todos os slots de hora inteira dentro do intervalo como ocupados
                while ($start < $end) {
                    $occupiedSlots[$start->format(
'H:00
')] = true;
                    $start->modify(
'+1 hour
');
                }
            }

            // 5. Gerar slots de horário (ex: a cada 2 horas, como no briefing inicial)
            //    A lógica de duração dinâmica será aplicada na hora de AGENDAR.
            //    Aqui mostramos blocos fixos para visualização inicial.
            $slots = [];
            $slotDuration = new DateInterval(
'PT2H
'); // Intervalo de 2 horas
            $currentTime = new DateTime($dateString . 
' 
' . $schedule[
'open_time
']);
            $closeTime = new DateTime($dateString . 
' 
' . $schedule[
'close_time
']);

            while ($currentTime < $closeTime) {
                $slotStart = clone $currentTime;
                $slotEnd = clone $currentTime;
                $slotEnd->add($slotDuration);

                // Não criar slot se ele terminar depois do horário de fechamento
                if ($slotEnd > $closeTime) {
                    break;
                }

                $startTimeStr = $slotStart->format(
'H:i
');
                $endTimeStr = $slotEnd->format(
'H:i
');
                $timeLabel = "$startTimeStr - $endTimeStr";

                // Verifica se o INÍCIO do slot está ocupado (simplificado)
                $isAvailable = !isset($occupiedSlots[$slotStart->format(
'H:00
')]);

                $slots[] = [
                    
'time
' => $timeLabel,
                    
'startTime
' => $startTimeStr,
                    
'available
' => $isAvailable
                ];

                $currentTime->add($slotDuration);
            }

            $this->jsonResponse([ 
'success
' => true, 
'slots
' => $slots ]);

        } catch (Exception $e) {
            error_log(
"Erro ao buscar horários: " . $e->getMessage());
            $this->jsonResponse([ 
'success
' => false, 
'message
' => 
'Erro interno ao buscar horários.
'], 500);
        }
    }
}

