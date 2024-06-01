<?php

include 'store.php';

try {
    (new DevCoder\DotEnv(__DIR__ . '/../.env'))->load();
} catch (InvalidArgumentException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Environment configuration file not found']);
    exit;
}

class Database {

    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        $this->host = getenv('DBHOST');
        $this->db_name = getenv('DBNAME');
        $this->username = getenv('DBUSER');
        $this->password = getenv('DBPASSWORD');
    }

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log('Connection Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => 'Database connection error']);
            exit;
        }
        return $this->conn;
    }
}

?>