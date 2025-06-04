<?php
// /setup/tables.php - Criação das tabelas do banco de dados

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
if (!isset($_SESSION['setup_step']) || $_SESSION['setup_step'] != 2) {
    header('Location: index.php');
    exit;
}

// Carregar configurações do banco de dados
$db_config = require ROOT_PATH . '/config/database.php';

// Inicializar variáveis
$error = '';
$success = false;
$tables_created = false;

// Processar a criação das tabelas
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
        
        // Ler o arquivo schema.sql
        $schema_file = ROOT_PATH . '/schema.sql';
        if (!file_exists($schema_file)) {
            throw new Exception('Arquivo schema.sql não encontrado.');
        }
        
        $sql = file_get_contents($schema_file);
        
        // Dividir o SQL em comandos individuais
        $commands = array_filter(array_map('trim', explode(';', $sql)), 'strlen');
        
        // Executar cada comando SQL
        foreach ($commands as $command) {
            $pdo->exec($command);
        }
        
        // Marcar como sucesso
        $tables_created = true;
        $_SESSION['setup_step'] = 3;
        
        // Redirecionar para a próxima etapa
        header('Location: admin.php');
        exit;
        
    } catch (Exception $e) {
        $error = 'Erro ao criar tabelas: ' . $e->getMessage();
    }
}

// Título da página
$pageTitle = "Criação das Tabelas - Instalação";
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
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .table-list {
            list-style: none;
            margin-bottom: 20px;
        }
        .table-list li {
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .table-list li:last-child {
            border-bottom: none;
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
            <div class="step active">3</div>
            <div class="step">4</div>
            <div class="step">5</div>
        </div>
        
        <div class="card">
            <h2>Criação das Tabelas do Banco de Dados</h2>
            
            <p>Nesta etapa, o sistema criará todas as tabelas necessárias no banco de dados. Este processo pode levar alguns instantes.</p>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($tables_created): ?>
            <div class="alert alert-success">
                <strong>Sucesso!</strong> Todas as tabelas foram criadas com sucesso.
            </div>
            <?php endif; ?>
            
            <h3>Tabelas que serão criadas:</h3>
            <ul class="table-list">
                <li>units - Unidades/Locais de atendimento</li>
                <li>unit_schedules - Horários de funcionamento das unidades</li>
                <li>services - Serviços oferecidos</li>
                <li>unit_services - Associação de serviços às unidades</li>
                <li>users - Usuários (clientes, operadores, admins)</li>
                <li>vehicles - Veículos dos clientes</li>
                <li>appointments - Agendamentos</li>
            </ul>
            
            <div class="alert alert-info">
                <strong>Informação:</strong> Certifique-se de que o usuário do banco de dados tem permissões para criar tabelas.
            </div>
            
            <form method="post" action="">
                <div class="actions">
                    <a href="database.php" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn">Criar Tabelas</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
