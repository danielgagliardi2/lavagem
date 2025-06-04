<?php

// /controllers/AdminUnitController.php

require_once __DIR__ . 
'/../core/Auth.php
';
require_once __DIR__ . 
'/../core/Database.php
';
require_once __DIR__ . 
'/../models/UnitModel.php
';
// Potencialmente UnitScheduleModel, UnitServiceModel para operações relacionadas

class AdminUnitController {
    private $db;
    private $unitModel;

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
        $this->unitModel = new UnitModel();
        // $this->unitModel->db = $this->db; // Melhorar com injeção
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header(
'Content-Type: application/json
');
        echo json_encode($data);
        exit;
    }

    // Processa a criação de uma nova unidade via API
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

        // Dados vêm do formulário unit_form.php via POST (incluindo $_FILES para imagem)
        $inputData = $_POST;
        $fileData = $_FILES[
'header_image
'] ?? null;

        // Validação básica
        if (empty($inputData[
'name
']) || empty($inputData[
'slug
'])) {
            $this->jsonResponse([
'error
' => 
'Nome e Slug são obrigatórios.
'], 400);
        }
        if (!preg_match(
'/^[a-z0-9-]+$/
', $inputData[
'slug
'])) {
             $this->jsonResponse([
'error
' => 
'Slug inválido. Use apenas letras minúsculas, números e hífens.
'], 400);
        }

        // Lógica de Upload de Imagem (exemplo simplificado)
        $imagePath = null;
        if ($fileData && $fileData[
'error
'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . 
'/../public/uploads/headers/
';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $fileName = uniqid(
'header_
', true) . 
'.
' . pathinfo($fileData[
'name
'], PATHINFO_EXTENSION);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($fileData[
'tmp_name
'], $targetPath)) {
                $imagePath = 
'/uploads/headers/
' . $fileName; // Caminho relativo à raiz pública
            } else {
                 // Tratar erro de upload
                 Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Falha ao fazer upload da imagem.
']);
                 header(
'Location: /admin/units.php
');
                 exit;
            }
        }

        try {
            // Lógica para criar unidade (chamar $this->unitModel->createUnit(...))
            // $newUnitId = $this->unitModel->createUnit($inputData[
'name
'], $inputData[
'slug
'], $inputData[
'address
'] ?? null, $imagePath);
            // if ($newUnitId) {
                 Auth::setSession(
'admin_message
', [
'type
' => 
'success
', 
'text
' => 
'Unidade criada com sucesso!
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
'Falha ao criar unidade.
']);
            // }
        } catch (PDOException $e) {
             // Verificar erro de slug duplicado (UNIQUE constraint)
             if ($e->getCode() == 23000) { // Código SQLSTATE para violação de constraint UNIQUE
                 Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Erro: O Slug "
' . htmlspecialchars($inputData[
'slug
']) . 
'" já está em uso.
']);
             } else {
                 Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Erro de banco de dados ao criar unidade.
']);
                 // Logar $e->getMessage()
             }
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
'Erro interno ao criar unidade.
']);
        }

        header(
'Location: /admin/units.php
');
        exit;
    }

    // Processa a atualização de uma unidade via API
    public function update() {
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

        $inputData = $_POST;
        $fileData = $_FILES[
'header_image
'] ?? null;

        // Validação básica
        if (empty($inputData[
'unit_id
']) || !is_numeric($inputData[
'unit_id
']) || empty($inputData[
'name
']) || empty($inputData[
'slug
'])) {
            $this->jsonResponse([
'error
' => 
'Dados inválidos para atualizar unidade.
'], 400);
        }
         if (!preg_match(
'/^[a-z0-9-]+$/
', $inputData[
'slug
'])) {
             $this->jsonResponse([
'error
' => 
'Slug inválido. Use apenas letras minúsculas, números e hífens.
'], 400);
        }

        $unitId = intval($inputData[
'unit_id
']);

        // Lógica de Upload (semelhante ao create, talvez deletar imagem antiga se substituir)
        $imagePath = null; // Manter a imagem existente por padrão
        // ... (lógica de upload aqui, se houver nova imagem) ...

        try {
            // Lógica para atualizar unidade (chamar $this->unitModel->updateUnit(...))
            // Passar $imagePath apenas se uma nova imagem foi enviada
            // $success = $this->unitModel->updateUnit($unitId, $inputData[
'name
'], $inputData[
'slug
'], $inputData[
'address
'] ?? null, $newImagePath ?? null);
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
'Unidade atualizada com sucesso!
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
'Falha ao atualizar unidade.
']);
            // }
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) {
                 Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Erro: O Slug "
' . htmlspecialchars($inputData[
'slug
']) . 
'" já está em uso por outra unidade.
']);
             } else {
                 Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'Erro de banco de dados ao atualizar unidade.
']);
                 // Logar $e->getMessage()
             }
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
'Erro interno ao atualizar unidade.
']);
        }

        header(
'Location: /admin/units.php
');
        exit;
    }

    // Processa a exclusão de uma unidade via API (chamado pelo form)
    public function delete() {
        if ($_SERVER[
'REQUEST_METHOD
'] !== 
'POST
') {
             header(
'Location: /admin/units.php
');
             exit;
        }

        $unitId = $_POST[
'unit_id
'] ?? null;

        if (empty($unitId) || !is_numeric($unitId)) {
            Auth::setSession(
'admin_message
', [
'type
' => 
'error
', 
'text
' => 
'ID da unidade inválido.
']);
            header(
'Location: /admin/units.php
');
            exit;
        }

        try {
            // Lógica para deletar unidade (chamar $this->unitModel->deleteUnit(...))
            // Cuidado com ON DELETE CASCADE nas tabelas relacionadas!
            // $success = $this->unitModel->deleteUnit($unitId);
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
'Unidade excluída com sucesso!
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
'Falha ao excluir unidade.
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
'Erro interno ao excluir unidade.
']);
        }

        header(
'Location: /admin/units.php
');
        exit;
    }

}

?>
