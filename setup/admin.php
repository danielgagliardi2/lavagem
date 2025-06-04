<?php
// /setup/admin.php - Configuração do usuário administrador

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
if (!isset($_SESSION['setup_step']) || $_SESSION['setup_step'] != 3) {
    header('Location: index.php');
    exit;
}

// Carregar configurações do banco de dados
$db_config = require ROOT_PATH . '/config/database.php';

// Inicializar variáveis
$error = '';
$success = false;

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_name = trim($_POST['admin_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_password = $_POST['admin_password'] ?? '';
    $admin_password_confirm = $_POST['admin_password_confirm'] ?? '';
    
    // Validar campos
    if (empty($admin_name) || empty($admin_email) || empty($admin_password)) {
        $error = 'Todos os campos são obrigatórios.';
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'O e-mail informado não é válido.';
    } elseif ($admin_password !== $admin_password_confirm) {
        $error = 'As senhas não conferem.';
    } elseif (strlen($admin_password) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        try {
            // Conectar ao banco de dados
            $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']};charset={$db_config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);
            
            // Gerar hash da senha
            $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
            
            // Inserir o usuário administrador
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password_hash, role, created_at)
                VALUES (:name, :email, :password_hash, 'admin', NOW())
            ");
            
            $stmt->execute([
                'name' => $admin_name,
                'email' => $admin_email,
                'password_hash' => $password_hash
            ]);
            
            // Salvar os dados na sessão
            $_SESSION['setup_data']['admin'] = [
                'name' => $admin_name,
                'email' => $admin_email
            ];
            
            // Avançar para o próximo passo
            $_SESSION['setup_step'] = 4;
            header('Location: initial_data.php');
            exit;
            
        } catch (PDOException $e) {
            $error = 'Erro ao criar usuário administrador: ' . $e->getMessage();
        }
    }
}

// Título da página
$pageTitle = "Configuração do Administrador - Instalação";
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
            <div class="step completed">2</div>
            <div class="step completed">3</div>
            <div class="step active">4</div>
            <div class="step">5</div>
        </div>
        
        <div class="card">
            <h2>Configuração do Usuário Administrador</h2>
            
            <p>Por favor, crie uma conta de administrador para gerenciar o sistema. Esta conta terá acesso completo a todas as funcionalidades administrativas.</p>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="form-group">
                    <label for="admin_name">Nome Completo:</label>
                    <input type="text" id="admin_name" name="admin_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_email">E-mail:</label>
                    <input type="email" id="admin_email" name="admin_email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">Senha:</label>
                    <input type="password" id="admin_password" name="admin_password" class="form-control" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="admin_password_confirm">Confirmar Senha:</label>
                    <input type="password" id="admin_password_confirm" name="admin_password_confirm" class="form-control" required minlength="6">
                </div>
                
                <div class="alert alert-info">
                    <strong>Dica:</strong> Use uma senha forte com pelo menos 6 caracteres, incluindo letras maiúsculas, minúsculas, números e símbolos.
                </div>
                
                <div class="actions">
                    <a href="tables.php" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn">Continuar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
