<?php

// /public/operator_login_process.php - Processa o login do operador

require_once __DIR__ . 
'/../core/Auth.php
';

Auth::startSession();

// 1. Verificar se é POST
if ($_SERVER[
'REQUEST_METHOD
'] !== 
'POST
') {
    header(
'Location: /operator_login.php?error=invalid_method
');
    exit;
}

// 2. Obter dados do POST
$email = filter_input(INPUT_POST, 
'email
', FILTER_SANITIZE_EMAIL);
$password = $_POST[
'password
'] ?? null;

// 3. Validar dados
if (empty($email) || empty($password)) {
    header(
'Location: /operator_login.php?error=missing_fields
');
    exit;
}

// 4. Tentar fazer login como operador
// A função Auth::login precisa ser capaz de verificar o role 'operator'
// e talvez retornar o unit_id associado ao operador.
$loginResult = Auth::login($email, $password, 
'operator
');

// 5. Processar resultado
if ($loginResult) {
    // Login bem-sucedido
    // Redirecionar para o dashboard do operador
    // Poderia guardar o unit_id na sessão se necessário: $_SESSION['operator_unit_id'] = $loginResult['unit_id'];
    header(
'Location: /operator_dashboard.php
');
    exit;
} else {
    // Login falhou
    header(
'Location: /operator_login.php?error=invalid_credentials
');
    exit;
}

?>

