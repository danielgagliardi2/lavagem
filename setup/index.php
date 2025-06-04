<?php
// /setup/index.php - Página inicial do instalador

session_start();

// Definir constantes
define('SETUP_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

// Verificar se o sistema já está instalado
if (file_exists(ROOT_PATH . '/config/installed.php')) {
    header('Location: ../public/index.php');
    exit;
}

// Inicializar ou resetar o estado da instalação
if (!isset($_SESSION['setup_step']) || isset($_GET['restart'])) {
    $_SESSION['setup_step'] = 1;
    $_SESSION['setup_data'] = [];
}

// Verificar requisitos do sistema
$requirements = [
    'php_version' => [
        'name' => 'Versão do PHP',
        'required' => '7.4.0',
        'current' => PHP_VERSION,
        'status' => version_compare(PHP_VERSION, '7.4.0', '>=')
    ],
    'pdo_mysql' => [
        'name' => 'PDO MySQL',
        'required' => 'Habilitado',
        'current' => extension_loaded('pdo_mysql') ? 'Habilitado' : 'Não habilitado',
        'status' => extension_loaded('pdo_mysql')
    ],
    'writable_config' => [
        'name' => 'Diretório config gravável',
        'required' => 'Gravável',
        'current' => is_writable(ROOT_PATH . '/config') ? 'Gravável' : 'Não gravável',
        'status' => is_writable(ROOT_PATH . '/config')
    ]
];

$all_requirements_met = true;
foreach ($requirements as $req) {
    if (!$req['status']) {
        $all_requirements_met = false;
        break;
    }
}

// Título da página
$pageTitle = "Instalação - Sistema de Gestão de Lavagem de Veículos";
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
        .requirements-list {
            list-style: none;
            margin-bottom: 20px;
        }
        .requirements-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            justify-content: space-between;
        }
        .requirements-list .status {
            font-weight: bold;
        }
        .status-ok {
            color: #28a745;
        }
        .status-error {
            color: #dc3545;
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
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema de Gestão de Lavagem de Veículos</h1>
            <p>Assistente de Instalação</p>
        </div>
        
        <div class="progress-bar">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
            <div class="step">4</div>
            <div class="step">5</div>
        </div>
        
        <div class="card">
            <h2>Bem-vindo ao Assistente de Instalação</h2>
            
            <p>Este assistente irá guiá-lo através do processo de instalação do Sistema de Gestão de Lavagem de Veículos. Antes de prosseguir, verifique se todos os requisitos do sistema são atendidos.</p>
            
            <div class="alert alert-info">
                <strong>Dica:</strong> Certifique-se de que você tem as informações de acesso ao banco de dados MySQL antes de continuar.
            </div>
            
            <h3>Requisitos do Sistema</h3>
            <ul class="requirements-list">
                <?php foreach ($requirements as $req): ?>
                <li>
                    <span><?php echo $req['name']; ?> (Requerido: <?php echo $req['required']; ?>)</span>
                    <span class="status <?php echo $req['status'] ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $req['current']; ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <?php if (!$all_requirements_met): ?>
            <div class="alert alert-danger">
                <strong>Atenção!</strong> Nem todos os requisitos do sistema foram atendidos. Por favor, corrija os problemas acima antes de continuar.
            </div>
            <?php endif; ?>
            
            <div class="actions">
                <a href="?restart=1" class="btn btn-secondary">Reiniciar</a>
                <?php if ($all_requirements_met): ?>
                <a href="database.php" class="btn">Continuar</a>
                <?php else: ?>
                <button class="btn" disabled>Continuar</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
