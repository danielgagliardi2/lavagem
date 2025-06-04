<?php

// /public/operator_dashboard.php - Dashboard do Operador

require_once __DIR__ . 
'/../core/Auth.php
';

Auth::startSession();

// 1. Verificar se usuário está logado como operador
if (!Auth::isLoggedIn() || !Auth::hasRole(
'operator
')) {
    header(
'Location: /operator_login.php?error=login_required
');
    exit;
}

$operatorName = Auth::getUserName(); // Ou buscar nome completo se necessário
// $unitId = $_SESSION['operator_unit_id'] ?? null; // Recuperar ID da unidade se guardado na sessão

$pageTitle = 
"Dashboard do Operador"
;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        /* Estilos baseados em operador_responsivo_mockup.html */
        body {
            font-family: sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            background-color: #343a40;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .header a {
            color: #ffc107;
            text-decoration: none;
            font-weight: bold;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
        }
        .date-filter {
            margin-bottom: 20px;
            text-align: center;
        }
        .date-filter label {
            font-weight: bold;
            margin-right: 10px;
        }
        .date-filter input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }
        .appointment-list {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden; /* Para conter a tabela */
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 0.9em;
            text-transform: uppercase;
            color: #495057;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .status-scheduled { color: #007bff; font-weight: bold; }
        .status-in_progress { color: #ffc107; font-weight: bold; } /* Amarelo */
        .status-completed { color: #28a745; font-weight: bold; } /* Verde */
        .payment-pending { color: #dc3545; } /* Vermelho */
        .payment-paid { color: #28a745; } /* Verde */

        .actions button {
            padding: 6px 12px;
            margin-right: 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s;
        }
        .btn-start {
            background-color: #ffc107;
            color: #333;
        }
        .btn-start:hover { background-color: #e0a800; }
        .btn-complete {
            background-color: #28a745;
            color: white;
        }
        .btn-complete:hover { background-color: #218838; }
        .btn-paid {
            background-color: #007bff;
            color: white;
        }
        .btn-paid:hover { background-color: #0056b3; }

        /* Desabilitar botões */
        .actions button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .loading, .no-appointments {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 1.1em;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            tr {
                border: 1px solid #ccc;
                margin-bottom: 15px;
                border-radius: 5px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            }
            td {
                border: none;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 50%; /* Espaço para o label */
                text-align: right;
            }
            td:before {
                position: absolute;
                top: 12px;
                left: 15px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
                content: attr(data-label);
                font-size: 0.9em;
                color: #495057;
            }
            td:last-child {
                border-bottom: 0;
            }
            .actions button {
                 display: block;
                 width: calc(100% - 10px); /* Ajuste para padding */
                 margin-bottom: 5px;
            }
             .actions button:last-child {
                 margin-bottom: 0;
            }
        }

    </style>
</head>
<body>

    <div class="header">
        <h1>Dashboard Operador</h1>
        <span>Olá, <?php echo htmlspecialchars($operatorName); ?>!</span>
        <a href="/logout.php">Sair</a> <!-- Criar /logout.php -->
    </div>

    <div class="container">
        <div class="date-filter">
            <label for="appointment-date">Filtrar por Data:</label>
            <input type="date" id="appointment-date" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="appointment-list">
            <table>
                <thead>
                    <tr>
                        <th>Horário</th>
                        <th>Cliente</th>
                        <th>Veículo</th>
                        <th>Serviço</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th>Pagamento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="appointments-tbody">
                    <!-- Linhas preenchidas via AJAX -->
                    <tr class="loading-row">
                        <td colspan="8" class="loading">Carregando agendamentos...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const appointmentsTbody = document.getElementById('appointments-tbody');
        const dateInput = document.getElementById('appointment-date');
        const sessionCookie = `<?php echo session_name() . '=' . session_id(); ?>`; // Passa cookie de sessão

        async function fetchAppointments(date) {
            appointmentsTbody.innerHTML = 
'<tr class="loading-row"><td colspan="8" class="loading">Carregando agendamentos...</td></tr>
';
            
            try {
                const response = await fetch(`/api/operator/appointments?date=${date}`, {
                    method: 
'GET
',
                    headers: {
                        
'Cookie
': sessionCookie
                    }
                });
                const data = await response.json();

                appointmentsTbody.innerHTML = 
''
; // Limpa o loading/erro anterior

                if (response.ok && data.success) {
                    if (data.appointments && data.appointments.length > 0) {
                        data.appointments.forEach(app => {
                            const row = document.createElement(
'tr
');
                            row.dataset.appointmentId = app.id;

                            const startTime = app.start_time.substring(0, 5);
                            const endTime = app.end_time.substring(0, 5);

                            row.innerHTML = `
                                <td data-label="Horário">${startTime} - ${endTime}</td>
                                <td data-label="Cliente">${escapeHtml(app.user_name)}</td>
                                <td data-label="Veículo">${escapeHtml(app.vehicle_model)} (${app.vehicle_year})<br>${escapeHtml(app.vehicle_plate)}</td>
                                <td data-label="Serviço">${escapeHtml(app.service_name)}</td>
                                <td data-label="Telefone"><a href="tel:${escapeHtml(app.user_phone)}">${escapeHtml(app.user_phone)}</a></td>
                                <td data-label="Status" class="status status-${app.status}">${getStatusText(app.status)}</td>
                                <td data-label="Pagamento" class="payment payment-${app.payment_status}">${getPaymentStatusText(app.payment_status)}</td>
                                <td data-label="Ações" class="actions">
                                    <button class="btn-start" ${app.status !== 
'scheduled
' ? 
'disabled
' : 
''
} onclick="updateStatus(${app.id}, 
'in_progress
', null)">Início</button>
                                    <button class="btn-complete" ${app.status !== 
'in_progress
' ? 
'disabled
' : 
''
} onclick="updateStatus(${app.id}, 
'completed
', null)">Fim</button>
                                    <button class="btn-paid" ${app.payment_status !== 
'pending
' || app.status !== 
'completed
' ? 
'disabled
' : 
''
} onclick="updateStatus(${app.id}, null, 
'paid
')">Pago</button>
                                </td>
                            `;
                            appointmentsTbody.appendChild(row);
                        });
                    } else {
                        appointmentsTbody.innerHTML = 
'<tr class="no-appointments-row"><td colspan="8" class="no-appointments">Nenhum agendamento para esta data.</td></tr>
';
                    }
                } else {
                    console.error(
'Erro ao buscar agendamentos:
', data.message);
                    appointmentsTbody.innerHTML = 
'<tr class="error-row"><td colspan="8" class="loading">Erro ao carregar agendamentos. Tente novamente.</td></tr>
';
                }
            } catch (error) {
                console.error(
'Erro de rede:
', error);
                appointmentsTbody.innerHTML = 
'<tr class="error-row"><td colspan="8" class="loading">Erro de comunicação. Verifique sua conexão.</td></tr>
';
            }
        }

        async function updateStatus(appointmentId, newStatus, newPaymentStatus) {
            const updateData = {};
            if (newStatus) updateData.status = newStatus;
            if (newPaymentStatus) updateData.payment_status = newPaymentStatus;

            // Desabilitar botões temporariamente (opcional, para evitar cliques duplos)
            const buttons = document.querySelector(`tr[data-appointment-id="${appointmentId}"] .actions button`);
            if (buttons) buttons.forEach(btn => btn.disabled = true);

            try {
                const response = await fetch(`/api/operator/appointments/${appointmentId}/status`, {
                    method: 
'POST
', // Usando POST pois PATCH pode ser bloqueado
                    headers: {
                        
'Content-Type
': 
'application/json
',
                        
'Cookie
': sessionCookie
                    },
                    body: JSON.stringify(updateData)
                });
                const data = await response.json();

                if (response.ok && data.success) {
                    // Atualiza a lista para refletir a mudança
                    fetchAppointments(dateInput.value);
                    alert(data.message || 
'Status atualizado com sucesso!
');
                } else {
                    alert(
'Erro ao atualizar status: 
' + (data.message || 
'Tente novamente.
'));
                    // Reabilitar botões se a atualização falhar
                     if (buttons) buttons.forEach(btn => btn.disabled = false); 
                }
            } catch (error) {
                console.error(
'Erro de rede ao atualizar status:
', error);
                alert(
'Erro de comunicação ao atualizar status.
');
                 // Reabilitar botões se a atualização falhar
                 if (buttons) buttons.forEach(btn => btn.disabled = false);
            }
        }

        function getStatusText(status) {
            switch (status) {
                case 
'scheduled
': return 
'Agendado
';
                case 
'in_progress
': return 
'Em Andamento
';
                case 
'completed
': return 
'Concluído
';
                case 
'cancelled
': return 
'Cancelado
';
                default: return status;
            }
        }

        function getPaymentStatusText(status) {
             switch (status) {
                case 
'pending
': return 
'Pendente
';
                case 
'paid
': return 
'Pago
';
                default: return status;
            }
        }

        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return 
''
;
            return unsafe
                 .toString()
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        // Event listener para mudança de data
        dateInput.addEventListener(
'change
', (event) => {
            fetchAppointments(event.target.value);
        });

        // Carregar agendamentos para a data atual ao iniciar
        fetchAppointments(dateInput.value);

    </script>

</body>
</html>

