<?php

// /public/unit_page.php - Página Pública da Unidade

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

// O slug da unidade virá da URL (tratado pelo roteador)
$unitSlug = $routeParts[1] ?? null; // Exemplo, o roteador deve passar isso

if (!$unitSlug) {
    // Redirecionar para uma página de erro 404 ou para a home
    header(
'Location: /'
);
    exit;
}

$unit = null;
$unitServices = [];
$pageTitle = 
"Unidade não encontrada"
;
$headerImage = 
'/assets/images/default_header.jpg
'; // Imagem padrão

try {
    $db = Database::getInstance()->getConnection();
    $unitModel = new UnitModel();
    $serviceModel = new ServiceModel();
    // $unitModel->db = $db;
    // $serviceModel->db = $db;

    // Buscar unidade pelo slug
    // $unit = $unitModel->getUnitBySlug($unitSlug);
    // Placeholder:
    if ($unitSlug === 
'condominio-central-park'
) {
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
', 
'address
' => 
'Rua Principal, 123
', 
'header_image_path
' => 
'/uploads/headers/header_condominio.jpg
'];
    } elseif ($unitSlug === 
'empresa-tech-solutions'
) {
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
', 
'address
' => 
'Av. Inovação, 456
', 
'header_image_path
' => null];
    }

    if ($unit) {
        $pageTitle = htmlspecialchars($unit[
'name
']);
        if (!empty($unit[
'header_image_path
'])) {
            $headerImage = htmlspecialchars($unit[
'header_image_path
']);
        }

        // Buscar serviços associados a esta unidade
        // $unitServices = $serviceModel->getServicesByUnitId($unit[
'id
']);
        // Placeholder:
        if ($unit[
'id
'] == 1) {
             $unitServices = [
                [
'id
' => 1, 
'name
' => 
'Lavagem Simples
', 
'duration_minutes
' => 60],
                [
'id
' => 2, 
'name
' => 
'Lavagem Completa com Cera
', 
'duration_minutes
' => 120]
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
' => 60]
            ];
        }

        // Buscar horários de funcionamento (a ser implementado)
        // $schedules = $unitScheduleModel->getSchedulesByUnitId($unit[
'id
']);

    } else {
        // Unidade não encontrada, tratar (ex: redirecionar ou mostrar msg)
        http_response_code(404);
        // Poderia incluir um template de 404 aqui
        echo 
"Unidade não encontrada."
;
        exit;
    }

} catch (Exception $e) {
    // Logar erro
    error_log(
"Erro ao buscar dados da unidade: " . $e->getMessage());
    echo 
"<p>Erro ao carregar informações da unidade.</p>"
;
    // Considerar mostrar uma página de erro mais amigável
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Agendamento</title>
    <link rel="stylesheet" href="/assets/css/calendar_style.css"> <!-- CSS específico do calendário -->
    <style>
        /* Estilos básicos Mobile First */
        body {
            font-family: sans-serif;
            margin: 0;
            background-color: #f8f9fa;
            color: #333;
        }
        .header-image {
            width: 100%;
            height: 250px; /* Altura ajustável */
            object-fit: cover; /* Garante que a imagem cubra a área */
            display: block;
        }
        .unit-info {
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .unit-info h1 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.8em;
            color: #0056b3;
        }
        .unit-info p {
            margin: 5px 0;
            font-size: 1em;
            color: #555;
        }
        .calendar-container {
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .calendar-header button {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #007bff;
        }
        .calendar-header h2 {
            margin: 0;
            font-size: 1.4em;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            text-align: center;
        }
        .calendar-grid div {
            padding: 10px 5px;
            font-size: 0.9em;
        }
        .day-header {
            font-weight: bold;
            color: #666;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .day {
            border: 1px solid #eee;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .day.available:hover {
            background-color: #e9f5ff;
        }
        .day.unavailable {
            color: #ccc;
            background-color: #f9f9f9;
            cursor: not-allowed;
        }
        .day.past {
             color: #aaa;
             background-color: #fdfdfd;
             cursor: not-allowed;
             text-decoration: line-through;
        }
        .day.selected {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        .day.other-month {
            color: #ddd;
            background-color: #fff;
            cursor: default;
        }
        .time-slots {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .time-slots h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        .slot {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
        }
        .slot.available {
            background-color: #d4edda; /* Verde claro */
            color: #155724;
            border-color: #c3e6cb;
        }
        .slot.available:hover {
            background-color: #c3e6cb;
        }
        .slot.unavailable {
            background-color: #f8d7da; /* Vermelho claro */
            color: #721c24;
            border-color: #f5c6cb;
            cursor: not-allowed;
            text-decoration: line-through;
        }
        .auth-links {
            text-align: right;
            padding: 10px 20px;
            background-color: #e9ecef;
            font-size: 0.9em;
        }
         .auth-links a {
            margin-left: 15px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
         .auth-links a:hover { text-decoration: underline; }

        /* Loading indicator */
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        /* Desktop adjustments */
        @media (min-width: 768px) {
            .unit-info {
                padding: 30px;
            }
            .calendar-container {
                 padding: 30px;
            }
             .calendar-grid div {
                padding: 15px 10px;
                font-size: 1em;
            }
        }

    </style>
</head>
<body>

    <div class="auth-links">
        <?php if (Auth::isLoggedIn()): ?>
            <span>Olá, <?php echo htmlspecialchars(Auth::getUserName()); ?>!</span>
            <a href="/my_vehicles.php">Meus Veículos</a>
            <a href="/my_appointments.php">Meus Agendamentos</a>
            <a href="/logout.php">Sair</a> <!-- Criar /logout.php -->
        <?php else: ?>
            <a href="/login.php">Login</a>
            <a href="/register.php">Cadastro</a>
        <?php endif; ?>
    </div>

    <img src="<?php echo $headerImage; ?>" alt="Imagem da Unidade" class="header-image">

    <div class="unit-info">
        <h1><?php echo $pageTitle; ?></h1>
        <p><strong>Endereço:</strong> <?php echo htmlspecialchars($unit[
'address
'] ?? 
'Não informado
'); ?></p>
        <!-- Adicionar Horários de Funcionamento aqui quando implementado -->
        <p><strong>Serviços Oferecidos:</strong></p>
        <ul>
            <?php foreach ($unitServices as $service): ?>
                <li><?php echo htmlspecialchars($service[
'name
']); ?> (<?php echo htmlspecialchars($service[
'duration_minutes
']); ?> min)</li>
            <?php endforeach; ?>
             <?php if (empty($unitServices)): ?>
                <li>Nenhum serviço específico cadastrado para esta unidade.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="calendar-container">
        <div class="calendar-header">
            <button id="prev-month">&lt;</button>
            <h2 id="current-month-year">Junho 2025</h2>
            <button id="next-month">&gt;</button>
        </div>
        <div class="calendar-grid" id="calendar-days">
            <div class="day-header">Dom</div>
            <div class="day-header">Seg</div>
            <div class="day-header">Ter</div>
            <div class="day-header">Qua</div>
            <div class="day-header">Qui</div>
            <div class="day-header">Sex</div>
            <div class="day-header">Sáb</div>
            <!-- Dias serão preenchidos pelo JavaScript -->
        </div>

        <div class="time-slots" id="time-slots-container" style="display: none;">
            <h3>Horários Disponíveis para <span id="selected-date"></span></h3>
            <div class="slots-grid" id="slots-grid">
                <!-- Horários serão preenchidos pelo JavaScript -->
            </div>
             <div id="slots-loading" class="loading" style="display: none;">Carregando horários...</div>
        </div>
    </div>

    <script>
        // Lógica do Calendário (será implementada na próxima etapa)
        const currentMonthYearEl = document.getElementById(
'current-month-year
');
        const calendarDaysEl = document.getElementById(
'calendar-days
');
        const prevMonthBtn = document.getElementById(
'prev-month
');
        const nextMonthBtn = document.getElementById(
'next-month
');
        const timeSlotsContainer = document.getElementById(
'time-slots-container
');
        const selectedDateEl = document.getElementById(
'selected-date
');
        const slotsGridEl = document.getElementById(
'slots-grid
');
        const slotsLoadingEl = document.getElementById(
'slots-loading
');

        const unitSlug = 
'<?php echo $unitSlug; ?>
'; // Passa o slug do PHP para o JS
        let currentDate = new Date();
        let selectedDayElement = null;

        function renderCalendar(year, month) {
            // Limpa o calendário anterior (exceto cabeçalhos dos dias da semana)
            const dayElements = calendarDaysEl.querySelectorAll(
'.day, .other-month
');
            dayElements.forEach(el => el.remove());

            const monthNames = [
"Janeiro"
, 
"Fevereiro"
, 
"Março"
, 
"Abril"
, 
"Maio"
, 
"Junho"
, 
"Julho"
, 
"Agosto"
, 
"Setembro"
, 
"Outubro"
, 
"Novembro"
, 
"Dezembro"
];
            currentMonthYearEl.textContent = `${monthNames[month]} ${year}`;

            const firstDayOfMonth = new Date(year, month, 1).getDay(); // 0 = Domingo, 1 = Segunda...
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Zera hora para comparação de data

            // Preenche espaços vazios antes do primeiro dia
            for (let i = 0; i < firstDayOfMonth; i++) {
                const emptyDiv = document.createElement(
'div
');
                calendarDaysEl.appendChild(emptyDiv);
            }

            // Preenche os dias do mês
            for (let day = 1; day <= daysInMonth; day++) {
                const dayDiv = document.createElement(
'div
');
                dayDiv.textContent = day;
                dayDiv.classList.add(
'day
');
                const currentDayDate = new Date(year, month, day);
                currentDayDate.setHours(0, 0, 0, 0);

                dayDiv.dataset.date = `${year}-${String(month + 1).padStart(2, 
'0
')}-${String(day).padStart(2, 
'0
')}`;

                // Marcar dias passados (não clicáveis)
                if (currentDayDate < today) {
                    dayDiv.classList.add(
'past
');
                } else {
                    // Adicionar lógica para verificar disponibilidade GERAL do dia (via API /availability)
                    // Por enquanto, todos os futuros são 'available'
                    dayDiv.classList.add(
'available
'); 
                    dayDiv.addEventListener(
'click
', handleDayClick);
                }
                
                // Adicionar lógica para marcar dias indisponíveis pela unidade (cinza claro, não clicável)
                // Exemplo: Se for domingo e a unidade não abre aos domingos
                // if (currentDayDate.getDay() === 0) { // Se for Domingo
                //     dayDiv.classList.remove(
'available
');
                //     dayDiv.classList.add(
'unavailable
');
                //     dayDiv.removeEventListener(
'click
', handleDayClick);
                // }

                calendarDaysEl.appendChild(dayDiv);
            }
            
            // TODO: Chamar API /availability para marcar dias como available/unavailable
            fetchAvailability(year, month);
        }

        async function fetchAvailability(year, month) {
            // Simulação - Marcar dias específicos como indisponíveis
            const unavailableDates = [
'2025-06-15
', 
'2025-06-22
']; // Exemplo
            const allDayElements = calendarDaysEl.querySelectorAll(
'.day
');
            allDayElements.forEach(dayEl => {
                if (unavailableDates.includes(dayEl.dataset.date)) {
                     dayEl.classList.remove(
'available
');
                     dayEl.classList.add(
'unavailable
');
                     dayEl.removeEventListener(
'click
', handleDayClick);
                }
            });
            
                        /* Implementação real com API: */
            try {
                const response = await fetch(`/api/units/${unitSlug}/availability?year=${year}&month=${String(month + 1).padStart(2, '0')}`);
                const data = await response.json();
                if (response.ok && data.success) {
                    // data.availability deve ser um objeto { 'YYYY-MM-DD': boolean } ou similar
                    const allDayElements = calendarDaysEl.querySelectorAll('.day');
                    allDayElements.forEach(dayEl => {
                        const dateStr = dayEl.dataset.date;
                        if (data.availability && data.availability[dateStr] === false && !dayEl.classList.contains('past')) {
                            dayEl.classList.remove('available');
                            dayEl.classList.add('unavailable');
                            dayEl.removeEventListener('click', handleDayClick);
                        }
                    });
                } else {
                    console.error('Erro ao buscar disponibilidade:', data.message);
                }
            } catch (error) {
                console.error('Erro de rede ao buscar disponibilidade:', error);
            }
            // */    */
        }

        async function handleDayClick(event) {
            const clickedDay = event.target;
            const date = clickedDay.dataset.date;

            if (selectedDayElement) {
                selectedDayElement.classList.remove(
'selected
');
            }
            clickedDay.classList.add(
'selected
');
            selectedDayElement = clickedDay;

            selectedDateEl.textContent = new Date(date + 
'T00:00:00
').toLocaleDateString(
'pt-BR
', { day: 
'numeric
', month: 
'long
', year: 
'numeric
' });
            timeSlotsContainer.style.display = 
'block
';
            slotsGridEl.innerHTML = 
''
; // Limpa horários anteriores
            slotsLoadingEl.style.display = 
'block
';

            // Buscar horários para o dia selecionado via API /schedule
            try {
                const response = await fetch(`/api/units/${unitSlug}/schedule?day=${date}`);
                const data = await response.json();
                slotsLoadingEl.style.display = 
'none
';

                if (response.ok && data.success) {
                    if (data.slots && data.slots.length > 0) {
                        data.slots.forEach(slot => {
                            const slotDiv = document.createElement(
'div
');
                            slotDiv.classList.add(
'slot
');
                            slotDiv.textContent = slot.time; // Ex: "08:00 - 10:00"
                            if (slot.available) {
                                slotDiv.classList.add(
'available
');
                                slotDiv.dataset.startTime = slot.startTime; // Ex: "08:00"
                                slotDiv.addEventListener(
'click
', handleSlotClick);
                            } else {
                                slotDiv.classList.add(
'unavailable
');
                            }
                            slotsGridEl.appendChild(slotDiv);
                        });
                    } else {
                        slotsGridEl.innerHTML = 
'<p>Nenhum horário disponível para este dia.</p>
';
                    }
                } else {
                    slotsGridEl.innerHTML = `<p>Erro ao carregar horários: ${data.message || 
'Tente novamente.
'}</p>`;
                }
            } catch (error) {
                console.error(
'Erro ao buscar horários:
', error);
                slotsLoadingEl.style.display = 
'none
';
                slotsGridEl.innerHTML = 
'<p>Erro de comunicação ao buscar horários.</p>
';
            }
        }

        function handleSlotClick(event) {
            const selectedSlot = event.target;
            const date = selectedDayElement.dataset.date;
            const startTime = selectedSlot.dataset.startTime;

            // Verificar se usuário está logado
            <?php if (!Auth::isLoggedIn()): ?>
                alert(
'Você precisa estar logado para agendar. Redirecionando para o login...
');
                window.location.href = 
'/login.php?redirect=/units/<?php echo $unitSlug; ?>
'; // Redireciona para login
                return;
            <?php endif; ?>

            // Redirecionar para a página de seleção de serviço/veículo, passando data e hora
            const unitId = <?php echo $unit[
'id
']; ?>; // Pega o ID da unidade do PHP
            window.location.href = `/select_service.php?unit_id=${unitId}&date=${date}&start_time=${startTime}`;
        }

        prevMonthBtn.addEventListener(
'click
', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
            timeSlotsContainer.style.display = 
'none
'; // Esconde horários ao mudar mês
            selectedDayElement = null;
        });

        nextMonthBtn.addEventListener(
'click
', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar(currentDate.getFullYear(), currentDate.getMonth());
             timeSlotsContainer.style.display = 
'none
'; // Esconde horários ao mudar mês
             selectedDayElement = null;
        });

        // Renderiza o calendário inicial
        renderCalendar(currentDate.getFullYear(), currentDate.getMonth());

    </script>

</body>
</html>

