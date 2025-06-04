<?php

// /public/my_appointments.php - Página "Meus Agendamentos" para o cliente

require_once __DIR__ . '/../core/Auth.php';

Auth::startSession();

// 1. Verificar se usuário está logado como cliente
if (!Auth::isLoggedIn() || !Auth::hasRole('customer')) {
    header('Location: /public/login.php?redirect=' . urlencode('/public/my_appointments.php'));
    exit;
}

$pageTitle = "Meus Agendamentos";
$userId = Auth::getUserId();
$userName = Auth::getUserName();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        /* Estilos gerais - Mobile First */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header h1 {
            font-size: 1.5rem;
            margin: 0;
        }
        .back-button {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
        }
        .container {
            padding: 15px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        .tab {
            padding: 10px 15px;
            cursor: pointer;
            flex: 1;
            text-align: center;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        .tab.active {
            color: #007bff;
            border-bottom: 2px solid #007bff;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .appointment-card {
            background-color: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-left: 4px solid #007bff; /* Default color */
        }
        .appointment-card.completed {
            border-left-color: #28a745; /* Green for completed */
        }
        .appointment-card.in-progress {
            border-left-color: #ffc107; /* Yellow for in progress */
        }
        .appointment-card.cancelled {
            border-left-color: #dc3545; /* Red for cancelled */
            opacity: 0.7;
        }
        .appointment-date {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .appointment-service {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        .appointment-details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            font-size: 0.9rem;
        }
        .appointment-detail {
            background-color: #f8f9fa;
            padding: 5px 10px;
            border-radius: 4px;
            display: flex;
            align-items: center;
        }
        .appointment-detail i {
            margin-right: 5px;
            color: #6c757d;
        }
        .appointment-status {
            margin-top: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-scheduled {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-in-progress {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .payment-status {
            margin-left: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .payment-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .payment-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        .empty-state p {
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            text-align: center;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #0069d9;
        }
        .loading {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Responsividade para tablets e desktops */
        @media (min-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }
            .container {
                padding: 20px;
            }
            .appointment-cards {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 20px;
            }
            .appointment-card {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>

    <div class="header">
        <a href="/public/profile.php" class="back-button">←</a>
        <h1><?php echo $pageTitle; ?></h1>
    </div>

    <div class="container">
        <div class="tabs">
            <div class="tab active" data-tab="upcoming">Próximos</div>
            <div class="tab" data-tab="past">Anteriores</div>
        </div>

        <div id="upcoming-tab" class="tab-content active">
            <div id="upcoming-appointments" class="appointment-cards">
                <div class="loading">
                    <div class="loading-spinner"></div>
                    <p>Carregando seus agendamentos...</p>
                </div>
            </div>
        </div>

        <div id="past-tab" class="tab-content">
            <div id="past-appointments" class="appointment-cards">
                <!-- Conteúdo será carregado via JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // Variáveis globais
        let appointments = {
            upcoming: [],
            past: []
        };
        
        // Elementos DOM
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        const upcomingAppointmentsEl = document.getElementById('upcoming-appointments');
        const pastAppointmentsEl = document.getElementById('past-appointments');
        
        // Funções auxiliares
        function getStatusText(status) {
            switch (status) {
                case 'scheduled': return 'Agendado';
                case 'in_progress': return 'Em Andamento';
                case 'completed': return 'Concluído';
                case 'cancelled': return 'Cancelado';
                default: return status;
            }
        }
        
        function getPaymentStatusText(status) {
            switch (status) {
                case 'pending': return 'Pagamento Pendente';
                case 'paid': return 'Pago';
                default: return status;
            }
        }
        
        function getStatusClass(status) {
            switch (status) {
                case 'scheduled': return 'status-scheduled';
                case 'in_progress': return 'status-in-progress';
                case 'completed': return 'status-completed';
                case 'cancelled': return 'status-cancelled';
                default: return '';
            }
        }
        
        function getPaymentStatusClass(status) {
            switch (status) {
                case 'pending': return 'payment-pending';
                case 'paid': return 'payment-paid';
                default: return '';
            }
        }
        
        function getCardClass(status) {
            switch (status) {
                case 'completed': return 'completed';
                case 'in_progress': return 'in-progress';
                case 'cancelled': return 'cancelled';
                default: return '';
            }
        }
        
        function escapeHtml(unsafe) {
            return unsafe
                .toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
        
        // Renderizar agendamentos
        function renderAppointments() {
            // Renderizar próximos agendamentos
            if (appointments.upcoming.length > 0) {
                let upcomingHtml = '';
                appointments.upcoming.forEach(appointment => {
                    upcomingHtml += createAppointmentCard(appointment);
                });
                upcomingAppointmentsEl.innerHTML = upcomingHtml;
            } else {
                upcomingAppointmentsEl.innerHTML = createEmptyState('próximos');
            }
            
            // Renderizar agendamentos passados
            if (appointments.past.length > 0) {
                let pastHtml = '';
                appointments.past.forEach(appointment => {
                    pastHtml += createAppointmentCard(appointment);
                });
                pastAppointmentsEl.innerHTML = pastHtml;
            } else {
                pastAppointmentsEl.innerHTML = createEmptyState('passados');
            }
        }
        
        function createAppointmentCard(appointment) {
            return `
                <div class="appointment-card ${getCardClass(appointment.status)}">
                    <div class="appointment-date">${escapeHtml(appointment.date)} • ${escapeHtml(appointment.start_time)} - ${escapeHtml(appointment.end_time)}</div>
                    <div class="appointment-service">${escapeHtml(appointment.service_name)}</div>
                    <div class="appointment-details">
                        <div class="appointment-detail">
                            <i>🚗</i> ${escapeHtml(appointment.vehicle_plate)}
                        </div>
                        <div class="appointment-detail">
                            <i>📍</i> ${escapeHtml(appointment.unit_name)}
                        </div>
                        <div class="appointment-detail">
                            <i>💰</i> R$ ${parseFloat(appointment.price).toFixed(2).replace('.', ',')}
                        </div>
                    </div>
                    <div>
                        <span class="appointment-status ${getStatusClass(appointment.status)}">${getStatusText(appointment.status)}</span>
                        <span class="payment-status ${getPaymentStatusClass(appointment.payment_status)}">${getPaymentStatusText(appointment.payment_status)}</span>
                    </div>
                </div>
            `;
        }
        
        function createEmptyState(type) {
            if (type === 'próximos') {
                return `
                    <div class="empty-state">
                        <i>📅</i>
                        <h3>Nenhum agendamento futuro</h3>
                        <p>Você não possui agendamentos futuros.</p>
                        <a href="/public/index.php" class="btn">Agendar Lavagem</a>
                    </div>
                `;
            } else {
                return `
                    <div class="empty-state">
                        <i>🕒</i>
                        <h3>Nenhum agendamento anterior</h3>
                        <p>Você ainda não realizou nenhum agendamento.</p>
                    </div>
                `;
            }
        }
        
        // Buscar agendamentos da API
        async function fetchAppointments() {
            try {
                const response = await fetch('/api/users/me/appointments');
                const data = await response.json();
                
                if (response.ok && data.success) {
                    appointments = data.appointments;
                    renderAppointments();
                } else {
                    showError(data.message || 'Erro ao carregar agendamentos.');
                }
            } catch (error) {
                console.error('Erro ao buscar agendamentos:', error);
                showError('Erro de comunicação. Verifique sua conexão.');
            }
        }
        
        function showError(message) {
            const errorHtml = `
                <div class="error-message">
                    ${escapeHtml(message)}
                    <br>
                    <button onclick="fetchAppointments()" class="btn" style="margin-top: 10px;">Tentar Novamente</button>
                </div>
            `;
            upcomingAppointmentsEl.innerHTML = errorHtml;
            pastAppointmentsEl.innerHTML = '';
        }
        
        // Alternar entre abas
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tabId = tab.getAttribute('data-tab');
                
                // Atualizar classes das abas
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Atualizar conteúdo visível
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === `${tabId}-tab`) {
                        content.classList.add('active');
                    }
                });
            });
        });
        
        // Inicializar a página
        document.addEventListener('DOMContentLoaded', () => {
            fetchAppointments();
        });
    </script>

</body>
</html>
