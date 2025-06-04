<?php
// /setup/complete.php - Instalação concluída

session_start();

// Definir constantes
define('SETUP_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

// Verificar se o sistema já está instalado
if (!file_exists(ROOT_PATH . '/config/installed.php')) {
    header('Location: index.php');
    exit;
}

// Título da página
$pageTitle = "Instalação Concluída";
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
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 20px;
            text-align: center;
        }
        .card h2 {
            color: #28a745;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f1f1;
        }
        .card p {
            margin-bottom: 15px;
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 20px;
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
            margin: 10px;
        }
        .btn:hover {
            background-color: #0069d9;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .info-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .info-box h3 {
            margin-bottom: 10px;
            color: #495057;
        }
        .info-box ul {
            list-style-type: none;
            padding-left: 10px;
        }
        .info-box li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }
        .info-box li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Sistema de Gestão de Lavagem de Veículos</h1>
            <p>Assistente de Instalação</p>
        </div>
        
        <div class="card">
            <div class="success-icon">✓</div>
            <h2>Instalação Concluída com Sucesso!</h2>
            
            <p>Parabéns! O Sistema de Gestão de Lavagem de Veículos foi instalado com sucesso e está pronto para uso.</p>
            
            <div class="info-box">
                <h3>Informações de Acesso:</h3>
                <ul>
                    <li><strong>Painel Administrativo:</strong> <a href="../admin/login.php">../admin/login.php</a></li>
                    <li><strong>Área do Cliente:</strong> <a href="../public/index.php">../public/index.php</a></li>
                    <li><strong>Área do Operador:</strong> <a href="../public/operator_login.php">../public/operator_login.php</a></li>
                </ul>
            </div>
            
            <div class="info-box">
                <h3>Credenciais de Exemplo:</h3>
                <ul>
                    <li><strong>Administrador:</strong> E-mail e senha que você definiu durante a instalação</li>
                    <li><strong>Operador:</strong> E-mail: operador@exemplo.com / Senha: operador123</li>
                </ul>
            </div>
            
            <div class="info-box">
                <h3>Próximos Passos:</h3>
                <ul>
                    <li>Faça login no painel administrativo para personalizar as configurações do sistema</li>
                    <li>Adicione mais unidades, serviços e operadores conforme necessário</li>
                    <li>Personalize a aparência das páginas públicas</li>
                    <li>Por segurança, altere a senha do operador de exemplo</li>
                </ul>
            </div>
            
            <div>
                <a href="../admin/login.php" class="btn btn-success">Ir para o Painel Admin</a>
                <a href="../public/index.php" class="btn">Ir para a Página Inicial</a>
            </div>
        </div>
    </div>
</body>
</html>
