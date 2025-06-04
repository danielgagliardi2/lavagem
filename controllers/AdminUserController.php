<?php

// /controllers/AdminUserController.php

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/UserModel.php
';

class AdminUserController {
    private $db;
    private $userModel;

    public function __construct() {
        // Protege todas as ações deste controller
        if (!Auth::isLoggedIn() || !Auth::hasRole(
'admin
')) {
            $this->jsonResponse([
'error
' => 
'Acesso não autorizado
'], 403);
        }
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

    // Lista usuários (com filtros, paginação, etc. - simplificado)
    public function listUsers() {
        // Lógica para buscar usuários (ex: todos os admins e operadores)
        // $users = $this->userModel->getUsersByRole(['admin', 'operator']);
        // Placeholder:
        $users = [
            [
'id
' => 1, 
'full_name
' => 
'Admin Principal
', 
'email
' => 
'admin@lavagem.com
', 
'role
' => 
'admin
'],
            [
'id
' => 3, 
'full_name
' => 
'Operador João Silva
', 
'email
' => 
'joao.op@lavagem.com
', 
'role
' => 
'operator
'],
             [
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
'] // Incluindo cliente para teste
        ];
        // Retornar JSON para uma API ou passar para uma view
        return $users; // Para uso na view users.php
    }

    // Processa a criação de um novo usuário (Admin/Operador) via API
    public function create() {
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

        $inputData = $_POST; // Dados do formulário user_form.php

        // Validação básica
        if (empty($inputData[
'full_name
']) || empty($inputData[
'email
']) || empty($inputData[
'password
']) || empty($inputData[
'role
']) || !in_array($inputData[
'role
'], [
'admin
', 
'operator
'])) {
             Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Dados inválidos para criar usuário.
']);
             header(
'Location: /admin/user_form.php
'); // Volta para o form
             exit;
        }

        try {
            // Verificar se email já existe
            // $existingUser = $this->userModel->getUserByEmail($inputData[
'email
']);
            // if ($existingUser) {
            //     Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Este email já está cadastrado.
']);
            //     header(
'Location: /admin/user_form.php
');
            //     exit;
            // }

            // Lógica para criar usuário (chamar $this->userModel->createUserAdmin(...))
            // $hashedPassword = Auth::hashPassword($inputData[
'password
']);
            // $newUserId = $this->userModel->createUserAdmin($inputData[
'full_name
'], $inputData[
'email
'], $hashedPassword, $inputData[
'role
']);
            // if ($newUserId) {
                 Auth::setSession(
'admin_message
', [
'type
' => 
'success
', 
'text
' => 
'Usuário criado com sucesso!
']);
            // } else {
            //     Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Falha ao criar usuário.
']);
            // }
        } catch (Exception $e) {
            // Logar erro
            Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Erro interno ao criar usuário.
']);
        }

        header(
'Location: /admin/users.php
');
        exit;
    }

    // Processa a atualização de um usuário (Admin/Operador) via API
    public function update() {
        if ($_SERVER[
'REQUEST_METHOD
'] !== 
'POST
') {
             header(
'Location: /admin/users.php
');
             exit;
        }

        $inputData = $_POST;

        // Validação básica
        if (empty($inputData[
'user_id
']) || !is_numeric($inputData[
'user_id
']) || empty($inputData[
'full_name
']) || empty($inputData[
'email
']) || empty($inputData[
'role
']) || !in_array($inputData[
'role
'], [
'admin
', 
'operator
', 
'customer
'])) {
             Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Dados inválidos para atualizar usuário.
']);
             header(
'Location: /admin/user_form.php?id=
'.$inputData[
'user_id
']); // Volta para o form
             exit;
        }

        $userId = intval($inputData[
'user_id
']);

        try {
            // Verificar se email (se alterado) já existe por outro usuário
            // ... (lógica de verificação) ...

            // Lógica para atualizar usuário (chamar $this->userModel->updateUserAdmin(...))
            // $newPasswordHash = null;
            // if (!empty($inputData[
'password
'])) {
            //     $newPasswordHash = Auth::hashPassword($inputData[
'password
']);
            // }
            // $success = $this->userModel->updateUserAdmin($userId, $inputData[
'full_name
'], $inputData[
'email
'], $inputData[
'role
'], $newPasswordHash);
            // if ($success) {
                 Auth::setSession(
'admin_message
', [
'type
' => 
'success
', 
'text
' => 
'Usuário atualizado com sucesso!
']);
            // } else {
            //     Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Falha ao atualizar usuário.
']);
            // }
        } catch (Exception $e) {
            // Logar erro
            Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Erro interno ao atualizar usuário.
']);
        }

        header(
'Location: /admin/users.php
');
        exit;
    }

    // Processa a exclusão de um usuário via API
    public function delete() {
        if ($_SERVER[
'REQUEST_METHOD
'] !== 
'POST
') {
             header(
'Location: /admin/users.php
');
             exit;
        }

        $userId = $_POST[
'user_id
'] ?? null;

        if (empty($userId) || !is_numeric($userId)) {
            Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'ID do usuário inválido.
']);
            header(
'Location: /admin/users.php
');
            exit;
        }

        // Regra de negócio: Não permitir excluir o próprio usuário admin logado
        if ($userId == Auth::getUserId()) {
             Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Você não pode excluir seu próprio usuário.
']);
            header(
'Location: /admin/users.php
');
            exit;
        }

        try {
            // Lógica para deletar usuário (chamar $this->userModel->deleteUser(...))
            // Cuidado com dependências (agendamentos, etc.)
            // $success = $this->userModel->deleteUser($userId);
            // if ($success) {
                 Auth::setSession(
'admin_message
', [
'type
' => 
'success
', 
'text
' => 
'Usuário excluído com sucesso!
']);
            // } else {
            //     Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Falha ao excluir usuário.
']);
            // }
        } catch (Exception $e) {
            // Logar erro
            Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Erro interno ao excluir usuário.
']);
        }

        header(
'Location: /admin/users.php
');
        exit;
    }
}

?>
