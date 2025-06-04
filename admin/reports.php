<?php

// /admin/reports.php - Página de Relatórios do Admin

require_once __DIR__ . 
'/../core/Auth.php'
;
require_once __DIR__ . 
'/../models/UnitModel.php'
; // Para listar unidades no filtro

Auth::startSession();

// 1. Verificar se usuário está logado como admin
if (!Auth::isLoggedIn() || !Auth::hasRole(
'admin'
)) {
    header(
'Location: /admin/login.php?error=login_required'
);
    exit;
}

$pageTitle = 
"Relatórios"
;

// Buscar unidades para o filtro (opcional)
$unitModel = new UnitModel();
// $units = $unitModel->getAllUnits(); // Implementar no Model
$units = [
    [
'id'
 => 1, 
'name'
 => 
'Condomínio Central Park'
],
    [
'id'
 => 2, 
'name'
 => 
'Empresa XPTO'
]
]; // Placeholder

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin</title>
    <style>
        /* Estilos básicos para admin */
        body {
            font-family: sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .admin-header {
            background-color: #343a40;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h1 {
            margin: 0;
            font-size: 1.5em;
        }
        .admin-header nav a {
            color: #adb5bd;
            text-decoration: none;
            margin-left: 15px;
            transition: color 0.2s;
        }
        .admin-header nav a:hover, .admin-header nav a.active {
            color: white;
            font-weight: bold;
        }
        .admin-header .logout a {
             color: #ffc107;
             font-weight: bold;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 20px auto;
        }
        .filters {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .filters label {
            font-weight: bold;
        }
        .filters input[type="date"],
        .filters select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }
        .filters button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .report-summary {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            text-align: center;
        }
        .summary-item {
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .summary-item h3 {
            margin: 0 0 5px 0;
            font-size: 1em;
            color: #6c757d;
        }
        .summary-item p {
            margin: 0;
            font-size: 1.5em;
            font-weight: bold;
            color: #343a40;
        }
        .report-details {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow-x: auto; /* Para tabelas largas */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px; /* Evita quebra excessiva */
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .loading, .error-message {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 1.1em;
        }
    </style>
</head>
<body>

    <div class="admin-header">
        <h1>Admin - <?php echo $pageTitle; ?></h1>
        <nav>
            <a href="/admin/dashboard.php">Dashboard</a> <!-- Criar dashboard -->
            <a href="/admin/units.php">Unidades</a>
            <a href="/admin/services.php">Serviços</a>
            <a href="/admin/users.php">Usuários</a>
            <a href="/admin/reports.php" class="active">Relatórios</a>
        </nav>
        <div class="logout">
            <a href="/admin/logout.php">Sair</a> <!-- Criar logout -->
        </div>
    </div>

    <div class="container">
        <h2>Relatório Diário de Agendamentos</h2>

        <div class="filters">
            <div>
                <label for="report-date">Data:</label>
                <input type="date" id="report-date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div>
                <label for="unit-filter">Unidade:</label>
                <select id="unit-filter">
                    <option value="">Todas</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button id="filter-button">Filtrar</button>
        </div>

        <div id="report-content">
            <div class="loading">Carregando relatório...</div>
            <!-- Conteúdo do relatório será carregado aqui -->
        </div>

    </div>

    <script>
        const reportContent = document.getElementById('report-content');
        const dateInput = document.getElementById('report-date');
        const unitSelect = document.getElementById('unit-filter');
        const filterButton = document.getElementById('filter-button');
        const sessionCookie = `<?php echo session_name() . '=' . session_id(); ?>`;

        async function fetchReport(date, unitId = '') {
            reportContent.innerHTML = '<div class="loading">Carregando relatório...</div>';
            let apiUrl = `/api/admin/reports/daily?date=${date}`;
            if (unitId) {
                apiUrl += `&unit_id=${unitId}`;
            }

            try {
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Cookie': sessionCookie
                    }
                });
                const data = await response.json();

                if (response.ok && data.success && data.report) {
                    renderReport(data.report);
                } else {
                    console.error('Erro ao buscar relatório:', data.message);
                    reportContent.innerHTML = `<div class="error-message">Erro ao carregar relatório: ${escapeHtml(data.message || 'Tente novamente.')}</div>`;
                }
            } catch (error) {
                console.error('Erro de rede:', error);
                reportContent.innerHTML = '<div class="error-message">Erro de comunicação. Verifique sua conexão.</div>';
            }
        }

        function renderReport(report) {
            const summary = report.summary;
            const details = report.details;

            let summaryHtml = `
                <div class="report-summary">
                    <div class="summary-item">
                        <h3>Total Agendamentos</h3>
                        <p>${summary.total_appointments}</p>
                    </div>
                    <div class="summary-item">
                        <h3>Concluídos</h3>
                        <p>${summary.completed_appointments}</p>
                    </div>
                    <div class="summary-item">
                        <h3>Receita (Pago)</h3>
                        <p>R$ ${parseFloat(summary.total_revenue).toFixed(2).replace('.', ',')}</p>
                    </div>
                </div>
            `;

            let detailsHtml = `
                <div class="report-details">
                    <h3>Detalhes dos Agendamentos (${escapeHtml(summary.date)})</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Horário</th>
                                <th>Unidade</th>
                                <th>Serviço</th>
                                <th>Placa</th>
                                <th>Status</th>
                                <th>Pagamento</th>
                                <th>Valor (R$)</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (details && details.length > 0) {
                details.forEach(item => {
                    detailsHtml += `
                        <tr>
                            <td>${item.id}</td>
                            <td>${item.start_time.substring(0, 5)}</td>
                            <td>${escapeHtml(item.unit_name)}</td>
                            <td>${escapeHtml(item.service_name)}</td>
                            <td>${escapeHtml(item.vehicle_plate)}</td>
                            <td>${getStatusText(item.status)}</td>
                            <td>${getPaymentStatusText(item.payment_status)}</td>
                            <td>${parseFloat(item.price).toFixed(2).replace('.', ',')}</td>
                        </tr>
                    `;
                });
            } else {
                detailsHtml += '<tr><td colspan="8" style="text-align:center;">Nenhum agendamento encontrado para os filtros selecionados.</td></tr>';
            }

            detailsHtml += `
                        </tbody>
                    </table>
                </div>
            `;

            reportContent.innerHTML = summaryHtml + detailsHtml;
        }

        function getStatusText(status) {
            // Reutilizar ou adaptar de operator_dashboard
            switch (status) {
                case 'scheduled': return 'Agendado';
                case 'in_progress': return 'Em Andamento';
                case 'completed': return 'Concluído';
                case 'cancelled': return 'Cancelado';
                default: return status;
            }
        }

        function getPaymentStatusText(status) {
            // Reutilizar ou adaptar de operator_dashboard
             switch (status) {
                case 'pending': return 'Pendente';
                case 'paid': return 'Pago';
                default: return status;
            }
        }

        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return unsafe
                 .toString()
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
        }

        // Event listener para o botão de filtro
        filterButton.addEventListener('click', () => {
            fetchReport(dateInput.value, unitSelect.value);
        });

        // Carregar relatório inicial
        fetchReport(dateInput.value, unitSelect.value);

    </script>

</body>
</html>

