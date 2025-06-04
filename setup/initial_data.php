<?php
// /setup/initial_data.php - Inserção de dados iniciais

session_start();

// Definir constantes
define('SETUP_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

// Verificar se o sistema já está instalado
if (file_exists(ROOT_PATH . '/config/installed.php')) {
    header('Location: ../public/index.php');
    exit;
}

// Verificar se estamos na sequência correta
if (!isset($_SESSION['setup_step']) || $_SESSION['setup_step'] != 4) {
    header('Location: index.php');
    exit;
}

// Carregar configurações do banco de dados
$db_config = require ROOT_PATH . '/config/database.php';

// Inicializar variáveis
$error = '';
$success = false;

// Processar a inserção de dados iniciais
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conectar ao banco de dados
        $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset={$db_config['charset']}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);
        
        // 1. Inserir serviços padrão
        $services = [
            ['name' => 'Lavagem Simples', 'description' => 'Lavagem externa do veículo', 'duration_minutes' => 60, 'price' => 50.00],
            ['name' => 'Lavagem Completa', 'description' => 'Lavagem externa e interna do veículo', 'duration_minutes' => 120, 'price' => 100.00],
            ['name' => 'Polimento', 'description' => 'Polimento da pintura do veículo', 'duration_minutes' => 180, 'price' => 150.00],
            ['name' => 'Higienização', 'description' => 'Limpeza profunda do interior do veículo', 'duration_minutes' => 240, 'price' => 200.00]
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO services (name, description, duration_minutes, price, created_at)
            VALUES (:name, :description, :duration_minutes, :price, NOW())
        ");
        
        foreach ($services as $service) {
            $stmt->execute([
                'name' => $service['name'],
                'description' => $service['description'],
                'duration_minutes' => $service['duration_minutes'],
                'price' => $service['price']
            ]);
        }
        
        // 2. Inserir unidade de exemplo
        $stmt = $pdo->prepare("
            INSERT INTO units (name, address, slug, created_at)
            VALUES (:name, :address, :slug, NOW())
        ");
        
        $stmt->execute([
            'name' => 'Unidade Exemplo',
            'address' => 'Rua Exemplo, 123 - Centro',
            'slug' => 'unidade-exemplo'
        ]);
        
        $unit_id = $pdo->lastInsertId();
        
        // 3. Inserir horários de funcionamento para a unidade
        $stmt = $pdo->prepare("
            INSERT INTO unit_schedules (unit_id, day_of_week, open_time, close_time, is_closed)
            VALUES (:unit_id, :day_of_week, :open_time, :close_time, :is_closed)
        ");
        
        // Horários de segunda a sexta
        for ($day = 1; $day <= 5; $day++) {
            $stmt->execute([
                'unit_id' => $unit_id,
                'day_of_week' => $day,
                'open_time' => '08:00:00',
                'close_time' => '18:00:00',
                'is_closed' => false
            ]);
        }
        
        // Sábado (meio período)
        $stmt->execute([
            'unit_id' => $unit_id,
            'day_of_week' => 6,
            'open_time' => '08:00:00',
            'close_time' => '12:00:00',
            'is_closed' => false
        ]);
        
        // Domingo (fechado)
        $stmt->execute([
            'unit_id' => $unit_id,
            'day_of_week' => 0,
            'open_time' => null,
            'close_time' => null,
            'is_closed' => true
        ]);
        
        // 4. Associar serviços à unidade
        $stmt = $pdo->prepare("
            INSERT INTO unit_services (unit_id, service_id)
            VALUES (:unit_id, :service_id)
        ");
        
        // Buscar IDs dos serviços
        $service_ids = $pdo->query("SELECT id FROM services")->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($service_ids as $service_id) {
            $stmt->execute([
                'unit_id' => $unit_id,
                'service_id' => $service_id
            ]);
        }
        
        // 5. Criar um operador para a unidade
        $password_hash = password_hash('operador123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, email, password_hash, role, unit_id, created_at)
            VALUES (:name, :email, :password_hash, 'operator', :unit_id, NOW())
        ");
        
        $stmt->execute([
            'name' => 'Operador Exemplo',
            'email' => 'operador@exemplo.com',
            'password_hash' => $password_hash,
            'unit_id' => $unit_id
        ]);
        
        // Marcar como sucesso
        $success = true;
        $_SESSION['setup_step'] = 5;
        
        // Criar arquivo de instalação concluída
        $installed_content = "<?php\n\n";
        $installed_content .= "// Arquivo gerado automaticamente pelo instalador\n";
        $installed_content .= "// Data de instalação: " . date('Y-m-d H:i:s') . "\n";
        $installed_content .= "return true;\n";
        
        file_put_contents(ROOT_PATH . '/config/installed.php', $installed_content);
        
        // Redirecionar para a finalização
        header('Location: complete.php');
        exit;
        
    } catch (Exception $e) {
        $error = 'Erro ao inserir dados iniciais: ' . $e->getMessage();
    }
}

// Título da página
$pageTitle = "Dados Iniciais - Instalação";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        /* Estilos gerais */
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
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .header h1 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .progress-bar::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #dee2e6;
            z-index: 1;
        }
        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
            position: relative;
            z-index: 2;
        }
        .step.active {
            background-color: #007bff;
            color: white;
        }
        .step.completed {
            background-color: #28a745;
            color: white;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        .card h2 {
            color: #343a40;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f1f1;
        }
        .card p {
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #0069d9;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .data-list {
            list-style: none;
            margin-bottom: 20px;
        }
        .data-list li {
            padding: 10px;
            margin-bottom: 5px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .data-list li strong {
            display: block;
            margin-bottom: 5px;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema de Gestão de Lavagem de Veículos</h1>
            <p>Assistente de Instalação</p>
        </div>
        
        <div class="progress-bar">
            <div class="step completed">1</div>
            <div class="step completed">2</div>
            <div class="step completed">3</div>
            <div class="step completed">4</div>
            <div class="step active">5</div>
        </div>
        
        <div class="card">
            <h2>Dados Iniciais do Sistema</h2>
            
            <p>Nesta etapa, o sistema irá inserir dados iniciais para que você possa começar a usar o sistema imediatamente após a instalação.</p>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <h3>Dados que serão inseridos:</h3>
            
            <ul class="data-list">
                <li>
                    <strong>Serviços Padrão</strong>
                    Lavagem Simples, Lavagem Completa, Polimento, Higienização
                </li>
                <li>
                    <strong>Unidade de Exemplo</strong>
                    Com horários de funcionamento e serviços associados
                </li>
                <li>
                    <strong>Operador de Exemplo</strong>
                    E-mail: operador@exemplo.com<br>
                    Senha: operador123
                </li>
            </ul>
            
            <div class="alert alert-info">
                <strong>Informação:</strong> Você poderá personalizar ou remover esses dados após a instalação.
            </div>
            
            <form method="post" action="">
                <div class="actions">
                    <a href="admin.php" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn">Finalizar Instalação</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
