<?php

// /public/index.php - Roteador Principal para Requisições Web

// Autoload ou includes necessários (ex: Core, Models, Controllers)
// require_once __DIR__ . '/../core/Database.php';
// require_once __DIR__ . '/../core/Router.php'; // Um roteador mais robusto seria ideal

// Obter a URI da requisição
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = ''; // Se o projeto não estiver na raiz do domínio, ajuste aqui
$route = str_replace($basePath, '', $requestUri);
$route = trim($route, '/');

// Roteamento simples (exemplo)
if ($route === '') {
    // Página inicial (pode listar unidades ou redirecionar)
    echo "<h1>Página Inicial</h1>";
    // Exemplo: include __DIR__ . '/../views/home.php';
} elseif (preg_match("|^units/([a-zA-Z0-9-]+)/?$|", $route, $matches)) {
    // Rota para exibir uma unidade específica (ex: /units/unidade-exemplo)
    $unitSlug = $matches[1];
    echo "<h1>Exibindo Unidade: " . htmlspecialchars($unitSlug) . "</h1>";
    // Aqui, chamaríamos um UnitController para buscar dados da unidade pelo slug
    // e carregar a view do calendário/agendamento.
    // Exemplo:
    // require_once __DIR__ . '/../controllers/UnitController.php';
    // $controller = new UnitController();
    // $controller->showUnit($unitSlug);
} elseif ($route === 'login') {
    echo "<h1>Página de Login Cliente</h1>";
    // include __DIR__ . '/../views/customer_login.php';
} elseif ($route === 'register') {
    echo "<h1>Página de Cadastro Cliente</h1>";
    // include __DIR__ . '/../views/customer_register.php';
} else {
    // Rota não encontrada
    http_response_code(404);
    echo "<h1>404 - Página Não Encontrada</h1>";
}

?>

