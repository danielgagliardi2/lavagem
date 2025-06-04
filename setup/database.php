<?php
// /setup/database.php - Configuração do banco de dados

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
if (!isset($_SESSION['setup_step']) || $_SESSION['setup_step'] != 1) {
    header('Location: index.php');
    exit;
}

// Processar o formulário quando enviado
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';
    $db_port = trim($_POST['db_port'] ?? '3306');
    
    // Validar campos
    if (empty($db_host) || empty($db_name) || empty($db_user)) {
        $error = 'Todos os campos são obrigatórios, exceto a senha (se não houver).';
    } else {
        // Tentar conectar ao banco de dados
        try {
            $dsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, $db_user, $db_pass, $options);
            
            // Verificar se o banco de dados existe, se não, tentar criar
            $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$db_name}'");
            $dbExists = $stmt->fetchColumn();
            
            if (!$dbExists) {
                $pdo->exec("CREATE DATABASE `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
            
            // Selecionar o banco de dados
            $pdo->exec("USE `{$db_name}`");
            
            // Salvar as configurações na sessão
            $_SESSION['setup_data']['db'] = [
                'host' => $db_host,
                'name' => $db_name,
                'user' => $db_user,
                'pass' => $db_pass,
                'port' => $db_port
            ];
            
            // Criar o arquivo de configuração
            $config_content = "<?php\n\n";
            $config_content .= "// Configurações do banco de dados\n";
            $config_content .= "return [\n";
            $config_content .= "    'host' => '{$db_host}',\n";
            $config_content .= "    'name' => '{$db_name}',\n";
            $config_content .= "    'user' => '{$db_user}',\n";
            $config_content .= "    'pass' => '{$db_pass}',\n";
            $config_content .= "    'port' => '{$db_port}',\n";
            $config_content .= "    'charset' => 'utf8mb4'\n";
            $config_content .= "];\n";
            
            // Salvar o arquivo de configuração
            if (file_put_contents(ROOT_PATH . '/config/database.php', $config_content)) {
                // Avançar para o próximo passo
                $_SESSION['setup_step'] = 2;
                header('Location: tables.php');
                exit;
            } else {
                $error = 'Não foi possível salvar o arquivo de configuração. Verifique as permissões do diretório config.';
            }
            
        } catch (PDOException $e) {
            $error = 'Erro de conexão com o banco de dados: ' . $e->getMessage();
        }
    }
}

// Título da página
$pageTitle = "Configuração do Banco de Dados - Instalação";
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
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
            <div class="step active">2</div>
            <div class="step">3</div>
            <div class="step">4</div>
            <div class="step">5</div>
        </div>
        
        <div class="card">
            <h2>Configuração do Banco de Dados</h2>
            
            <p>Por favor, forneça as informações de conexão com o banco de dados MySQL. Se o banco de dados não existir, o sistema tentará criá-lo automaticamente.</p>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="db_host">Servidor MySQL:</label>
                    <input type="text" id="db_host" name="db_host" class="form-control" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label for="db_port">Porta:</label>
                    <input type="text" id="db_port" name="db_port" class="form-control" value="3306" required>
                </div>
                
                <div class="form-group">
                    <label for="db_name">Nome do Banco de Dados:</label>
                    <input type="text" id="db_name" name="db_name" class="form-control" value="lavagem_veiculos" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">Usuário MySQL:</label>
                    <input type="text" id="db_user" name="db_user" class="form-control" value="root" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">Senha MySQL:</label>
                    <input type="password" id="db_pass" name="db_pass" class="form-control">
                </div>
                
                <div class="actions">
                    <a href="index.php" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn">Continuar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
