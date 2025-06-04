<?php

// /core/Auth.php - Funções/Classe Base para Autenticação

class Auth {

    // Inicia a sessão de forma segura
    public static function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            // Configurações de segurança da sessão (opcional, mas recomendado)
            ini_set(
'session.cookie_httponly
', 1);
            ini_set(
'session.use_only_cookies
', 1);
            // ini_set(
'session.cookie_secure
', 1); // Descomentar se usar HTTPS
            // ini_set(
'session.cookie_samesite
', 
'Strict
'); // Ou 
'Lax
'

            session_start();
        }
    }

    // Define uma variável de sessão
    public static function setSession($key, $value) {
        self::startSession();
        $_SESSION[$key] = $value;
    }

    // Obtém uma variável de sessão
    public static function getSession($key, $default = null) {
        self::startSession();
        return $_SESSION[$key] ?? $default;
    }

    // Remove uma variável de sessão
    public static function unsetSession($key) {
        self::startSession();
        unset($_SESSION[$key]);
    }

    // Destrói a sessão atual
    public static function destroySession() {
        self::startSession();
        $_SESSION = []; // Limpa todas as variáveis de sessão
        if (ini_get(
"session.use_cookies
")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), 
''
, time() - 42000,
                $params[
"path
"], $params[
"domain
"],
                $params[
"secure
"], $params[
"httponly
"]
            );
        }
        session_destroy();
    }

    // Gera o hash de uma senha
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Verifica se uma senha corresponde a um hash
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // Realiza o login do usuário (define variáveis de sessão)
    public static function loginUser($userId, $userRole, $userName) {
        self::startSession();
        // Regenera o ID da sessão para prevenir fixation attacks
        session_regenerate_id(true);
        self::setSession(
'user_id
', $userId);
        self::setSession(
'user_role
', $userRole);
        self::setSession(
'user_name
', $userName);
        self::setSession(
'logged_in
', true);
    }

    // Realiza o logout do usuário
    public static function logoutUser() {
        self::destroySession();
    }

    // Verifica se o usuário está logado
    public static function isLoggedIn() {
        return self::getSession(
'logged_in
', false) === true;
    }

    // Obtém o ID do usuário logado
    public static function getUserId() {
        return self::getSession(
'user_id
');
    }

    // Obtém o papel (role) do usuário logado
    public static function getUserRole() {
        return self::getSession(
'user_role
');
    }

    // Verifica se o usuário logado tem um papel específico (ou um de vários)
    public static function hasRole($role) {
        $userRole = self::getUserRole();
        if (!$userRole) {
            return false;
        }
        if (is_array($role)) {
            return in_array($userRole, $role);
        } else {
            return $userRole === $role;
        }
    }

    // Redireciona se o usuário não estiver logado ou não tiver o papel necessário
    public static function requireLogin($requiredRole = null, $redirectUrl = 
'/login
') {
        if (!self::isLoggedIn()) {
            header(
"Location: " . $redirectUrl);
            exit;
        }
        if ($requiredRole && !self::hasRole($requiredRole)) {
            // Redirecionar para uma página de acesso negado ou para a home
            http_response_code(403); // Forbidden
            echo "Acesso Negado"; // Ou redirecionar
            // header(
"Location: /access-denied
");
            exit;
        }
    }
}

