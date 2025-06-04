<?php

// /controllers/AuthController.php

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/UserModel.php
';

class AuthController {
    private $db;
    private $userModel;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new UserModel();
        // $this->userModel->db = $this->db; // Melhorar com injeção
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header(
'Content-Type: application/json
');
        echo json_encode($data);
        exit;
    }

    // Processa o login do cliente via API
    public function login() {
        if ($_SERVER[
'REQUEST_METHOD
'] !== 
'POST
') {
            $this->jsonResponse([
'error
' => 
'Método não permitido
'], 405);
        }

        $inputData = json_decode(file_get_contents(
'php://input
'), true);
        $email = $inputData[
'email
'] ?? null;
        $password = $inputData[
'password
'] ?? null;

        if (!$email || !$password) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Email e senha são obrigatórios.
'], 400);
        }

        try {
            // Buscar usuário pelo email
            // $user = $this->userModel->getUserByEmail($email);
            // Placeholder:
            $user = ($email === 
'cliente@teste.com
') ? [
'id
' => 4, 
'full_name
' => 
'Cliente Teste
', 
'email
' => 
'cliente@teste.com
', 
'role
' => 
'customer
', 
'password_hash
' => Auth::hashPassword(
'senha123
')] : null;

            // Verificar se usuário existe, se é cliente e se a senha está correta
            if ($user && $user[
'role
'] === 
'customer
' && Auth::verifyPassword($password, $user[
'password_hash
'])) {
                // Login bem-sucedido
                Auth::loginUser($user[
'id
'], $user[
'role
'], $user[
'full_name
']);
                $this->jsonResponse([
'success
' => true, 
'message
' => 
'Login realizado com sucesso!
', 
'redirect_url
' => 
'/'
]); // Redirecionar para a home ou dashboard do cliente
            } else {
                $this->jsonResponse([
'success
' => false, 
'message
' => 
'Email ou senha inválidos.
'], 401);
            }
        } catch (Exception $e) {
            // Logar erro
            error_log(
"Erro no login API: " . $e->getMessage());
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Erro interno ao processar login.
'], 500);
        }
    }

    // Processa o registro do cliente via API
    public function register() {
        if ($_SERVER[
'REQUEST_METHOD
'] !== 
'POST
') {
            $this->jsonResponse([
'error
' => 
'Método não permitido
'], 405);
        }

        $inputData = json_decode(file_get_contents(
'php://input
'), true);

        // Validação básica (adicionar mais validações: email, cpf, etc.)
        if (empty($inputData[
'full_name
']) || empty($inputData[
'email
']) || empty($inputData[
'password
'])) {
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Nome, email e senha são obrigatórios.
'], 400);
        }
        if (!filter_var($inputData[
'email
'], FILTER_VALIDATE_EMAIL)) {
             $this->jsonResponse([
'success
' => false, 
'message
' => 
'Email inválido.
'], 400);
        }
        // Adicionar validação de força da senha

        try {
            // Verificar se email já existe
            // $existingUser = $this->userModel->getUserByEmail($inputData[
'email
']);
            // if ($existingUser) {
            //     $this->jsonResponse([
'success
' => false, 
'message
' => 
'Este email já está cadastrado.
'], 409); // Conflict
            // }

            // Lógica para criar usuário cliente
            // $newUserId = $this->userModel->createUser(
            //     $inputData[
'full_name
'],
            //     $inputData[
'email
'],
            //     $inputData[
'password
'],
            //     $inputData[
'phone
'] ?? null,
            //     $inputData[
'address
'] ?? null,
            //     $inputData[
'cpf
'] ?? null
            // );

            // if ($newUserId) {
                 // Definir mensagem de sucesso na sessão para exibir no login
                 Auth::setSession(
'auth_message
', [
'type
' => 
'success
', 
'text
' => 
'Cadastro realizado com sucesso! Faça login para continuar.
']);
                 $this->jsonResponse([
'success
' => true, 
'message
' => 
'Cadastro realizado com sucesso!
', 
'redirect_url
' => 
'/login.php
'], 201);
            // } else {
            //     $this->jsonResponse([
'success
' => false, 
'message
' => 
'Falha ao realizar cadastro.
'], 500);
            // }
        } catch (Exception $e) {
            // Logar erro
             error_log(
"Erro no registro API: " . $e->getMessage());
            $this->jsonResponse([
'success
' => false, 
'message
' => 
'Erro interno ao processar cadastro.
'], 500);
        }
    }

}

