<?php

// /core/Database.php

class Database {
    private static $instance = null;
    private $conn;

    private $host = 
'localhost
'; // Será lido do config/database.php
    private $db_name = 
'lavagem_db
'; // Será lido do config/database.php
    private $username = 
'root
'; // Será lido do config/database.php
    private $password = 
''
; // Será lido do config/database.php

    private function __construct() {
        // Carregar configurações do banco de dados
        $configPath = __DIR__ . 
'/../config/database.php
';
        if (file_exists($configPath)) {
            $dbConfig = include($configPath);
            $this->host = $dbConfig[
'host
'] ?? $this->host;
            $this->db_name = $dbConfig[
'db_name
'] ?? $this->db_name;
            $this->username = $dbConfig[
'username
'] ?? $this->username;
            $this->password = $dbConfig[
'password
'] ?? $this->password;
        } else {
            // Log ou erro: arquivo de configuração não encontrado
            // Em um cenário real, o setup garantiria a criação deste arquivo.
            // Por enquanto, usaremos os defaults.
        }

        $this->conn = null;

        try {
            $dsn = 
'mysql:host=
' . $this->host . 
';dbname=
' . $this->db_name . 
';charset=utf8mb4
';
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // Em produção, logar o erro em vez de exibir
            echo 
'Connection Error: 
' . $e->getMessage();
            // Poderia lançar uma exceção personalizada aqui
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // Prevenir clonagem da instância (Singleton)
    private function __clone() { }

    // Prevenir desserialização da instância (Singleton)
    public function __wakeup() { }
}

