<?php

// /controllers/AdminServiceController.php

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/ServiceModel.php
';

class AdminServiceController {
    private $db;
    private $serviceModel;

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
        $this->serviceModel = new ServiceModel();
        // $this->serviceModel->db = $this->db; // Melhorar com injeção
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header(
'Content-Type: application/json
');
        echo json_encode($data);
        exit;
    }

    // Processa a criação de um novo serviço via API
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

        $inputData = json_decode(file_get_contents(
'php://input
'), true);

        // Validação básica
        if (empty($inputData[
'name
']) || !isset($inputData[
'duration_minutes
']) || !is_numeric($inputData[
'duration_minutes
']) || $inputData[
'duration_minutes
'] <= 0) {
            $this->jsonResponse([
'error
' => 
'Dados inválidos para criar serviço.
'], 400);
        }

        try {
            // Lógica para criar serviço (chamar $this->serviceModel->createService(...))
            // $newServiceId = $this->serviceModel->createService($inputData[
'name
'], $inputData[
'description
'] ?? null, $inputData[
'duration_minutes
']);
            // if ($newServiceId) {
            //     $this->jsonResponse([
'message
' => 
'Serviço criado com sucesso!
', 
'service_id
' => $newServiceId], 201);
            // } else {
            //     $this->jsonResponse([
'error
' => 
'Falha ao criar serviço.
'], 500);
            // }
            $this->jsonResponse([
'message
' => 
'Serviço criado (placeholder)!
', 
'data
' => $inputData], 201); // Placeholder
        } catch (Exception $e) {
            // Logar erro
            $this->jsonResponse([
'error
' => 
'Erro interno ao criar serviço.
'], 500);
        }
    }

    // Processa a atualização de um serviço via API
    public function update() {
         if ($_SERVER[
'REQUEST_METHOD
'] !== 
'POST
') { // Usando POST por simplicidade do form HTML
            $this->jsonResponse([
'error
' => 
'Método não permitido
'], 405);
        }

        // Dados vêm do formulário service_form.php via POST
        $inputData = $_POST;

        // Validação básica
        if (empty($inputData[
'service_id
']) || !is_numeric($inputData[
'service_id
']) || empty($inputData[
'name
']) || !isset($inputData[
'duration_minutes
']) || !is_numeric($inputData[
'duration_minutes
']) || $inputData[
'duration_minutes
'] <= 0) {
            $this->jsonResponse([
'error
' => 
'Dados inválidos para atualizar serviço.
'], 400);
        }

        try {
            // Lógica para atualizar serviço (chamar $this->serviceModel->updateService(...))
            // $success = $this->serviceModel->updateService($inputData[
'service_id
'], $inputData[
'name
'], $inputData[
'description
'] ?? null, $inputData[
'duration_minutes
']);
            // if ($success) {
                 // Redirecionar de volta para a lista após sucesso
                 Auth::setSession(
'admin_message
', [
'type
' => 
'success
', 
'text
' => 
'Serviço atualizado com sucesso!
']);
                 header(
'Location: /admin/services.php
');
                 exit;
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
'Falha ao atualizar serviço.
']);
            //     header(
'Location: /admin/services.php
');
            //     exit;
            // }
            Auth::setSession(
'admin_message
', [
'type
' => 
'success
', 
'text
' => 
'Serviço atualizado (placeholder)!
']); // Placeholder
            header(
'Location: /admin/services.php
'); // Placeholder
            exit; // Placeholder
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
'Erro interno ao atualizar serviço.
']);
             header(
'Location: /admin/services.php
');
             exit;
        }
    }

    // Processa a exclusão de um serviço via API (chamado pelo form)
    public function delete() {
        if ($_SERVER[
'REQUEST_METHOD
'] !== 
'POST
') { // Usando POST por simplicidade do form HTML
             // Se fosse uma API REST pura, seria DELETE
             header(
'Location: /admin/services.php
'); // Redireciona se não for POST
             exit;
        }

        $serviceId = $_POST[
'service_id
'] ?? null;

        if (empty($serviceId) || !is_numeric($serviceId)) {
            Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'ID do serviço inválido.
']);
            header(
'Location: /admin/services.php
');
            exit;
        }

        try {
            // Lógica para deletar serviço (chamar $this->serviceModel->deleteService(...))
            // $success = $this->serviceModel->deleteService($serviceId);
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
'Serviço excluído com sucesso!
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
'Falha ao excluir serviço.
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
'Erro interno ao excluir serviço.
']);
        }

        header(
'Location: /admin/services.php
');
        exit;
    }

}

?>
