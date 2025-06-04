<?php

// /admin/login_process.php - Processa o login do administrador

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/UserModel.php';

Auth::startSession();

// Redireciona se já estiver logado como admin
if (Auth::isLoggedIn() && Auth::hasRole('admin')) {
    header('Location: /admin/dashboard.php'); // Redireciona para o dashboard (a ser criado)
    exit;
}

$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if ($email && $password) {
        try {
            $db = Database::getInstance()->getConnection();
            $userModel = new UserModel(); // Idealmente, injetar $db no construtor
            // Temporariamente setando a conexão no model (melhorar com injeção de dependência)
            $userModel->db = $db; 

            $user = $userModel->getUserByEmail($email);

            if ($user && $user['role'] === 'admin' && Auth::verifyPassword($password, $user['password_hash'])) {
                // Login bem-sucedido
                Auth::loginUser($user['id'], $user['role'], $user['full_name']);
                header('Location: /admin/dashboard.php'); // Redireciona para o dashboard (a ser criado)
                exit;
            } else {
                // Credenciais inválidas ou usuário não é admin
                $errorMessage = "Email ou senha inválidos.";
            }
        } catch (PDOException $e) {
            // Logar erro em produção
            $errorMessage = "Erro ao conectar com o banco de dados.";
            // error_log("Erro de DB no login admin: " . $e->getMessage());
        } catch (Exception $e) {
            // Logar erro genérico
            $errorMessage = "Ocorreu um erro inesperado.";
            // error_log("Erro inesperado no login admin: " . $e->getMessage());
        }
    } else {
        $errorMessage = "Por favor, preencha o email e a senha.";
    }

    // Se houve erro, armazena na sessão para exibir na página de login
    if ($errorMessage) {
        Auth::setSession('login_error', $errorMessage);
        header('Location: /admin/login.php');
        exit;
    }

} else {
    // Se não for POST, redireciona para o login
    header('Location: /admin/login.php');
    exit;
}

// Adicionar lógica para exibir mensagem de erro na página login.php
// Modificar login.php para verificar Auth::getSession('login_error') e exibi-lo

?>
